<?php

declare(strict_types=1);

namespace Ekyna\Bundle\ProductBundle\Command;

use Ekyna\Bundle\AdminBundle\Action\ReadAction;
use Ekyna\Bundle\CommerceBundle\Service\Subject\SubjectHelperInterface;
use Ekyna\Bundle\ProductBundle\Model\ProductInterface;
use Ekyna\Bundle\ProductBundle\Model\ProductTypes;
use Ekyna\Bundle\ProductBundle\Service\Pricing\OfferInvalidator;
use Ekyna\Bundle\ResourceBundle\Helper\ResourceHelper;
use Ekyna\Component\Commerce\Manufacture\Model\BillOfMaterialsInterface;
use Ekyna\Component\Commerce\Manufacture\Model\BOMComponentInterface;
use Ekyna\Component\Commerce\Stock\Updater\StockSubjectUpdaterInterface;
use Ekyna\Component\Resource\Copier\CopierInterface;
use Ekyna\Component\Resource\Event\ResourceMessage;
use Ekyna\Component\Resource\Factory\FactoryFactoryInterface;
use Ekyna\Component\Resource\Manager\ManagerFactoryInterface;
use Ekyna\Component\Resource\Repository\RepositoryFactoryInterface;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Helper\QuestionHelper;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Question\ChoiceQuestion;
use Symfony\Component\Console\Question\ConfirmationQuestion;
use Symfony\Component\Validator\Validator\ValidatorInterface;

use function array_filter;
use function array_map;
use function in_array;
use function intval;
use function sprintf;

#[AsCommand(
    name: 'ekyna:product:bundle:convert_into_bom',
    description: 'Convert a bundle into a BOM',
)]
class ConvertBundleIntoBOMCommand extends Command
{
    public function __construct(
        private readonly RepositoryFactoryInterface   $repositoryFactory,
        private readonly ManagerFactoryInterface      $managerFactory,
        private readonly FactoryFactoryInterface      $factoryFactory,
        private readonly SubjectHelperInterface       $subjectHelper,
        private readonly StockSubjectUpdaterInterface $stockSubjectUpdater,
        private readonly CopierInterface              $copier,
        private readonly OfferInvalidator             $offerInvalidator,
        private readonly ValidatorInterface           $validator,
        private readonly ResourceHelper               $resourceHelper,
    ) {
        parent::__construct();
    }

    protected function configure(): void
    {
        $this->addArgument('reference', InputArgument::REQUIRED, 'The bundle reference');
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        if (null === $bundle = $this->getBundle($input->getArgument('reference'))) {
            $output->writeln('<error>Bundle not found</error>');

            return Command::FAILURE;
        }

        if (!$this->assertBundleCanBeConverted($bundle, $output)) {
            return Command::FAILURE;
        }

        if (null === $bom = $this->buildBillOfMaterials($bundle, $output)) {
            return Command::FAILURE;
        }

        $this->convertBundleToSimple($bundle, $input, $output);

        $helper = new QuestionHelper();
        $question = new ConfirmationQuestion('Confirm bundle into BOM conversion?', false);

        if (!$helper->ask($input, $output, $question)) {
            $output->writeln('Abort by user.');

            return Command::SUCCESS;
        }

        $this
            ->managerFactory
            ->getManager(BillOfMaterialsInterface::class)
            ->persist($bom);

        $event = $this
            ->managerFactory
            ->getManager(ProductInterface::class)
            ->update($bundle);

        if ($event->hasMessages()) {
            foreach ($event->getMessages() as $message) {
                if ($message->getType() === ResourceMessage::TYPE_SUCCESS) {
                    continue;
                }

                $type = match ($message->getType()) {
                    ResourceMessage::TYPE_ERROR   => 'error',
                    ResourceMessage::TYPE_WARNING => 'comment',
                    default                       => 'info',
                };

                $output->writeln(sprintf('<%s>%s</%s>', $type, $message->getMessage(), $type));
            }

            if ($event->hasErrors()) {
                return Command::FAILURE;
            }
        }

        $output->writeln('<info>Successfully converted bundle into BOM !</info>');
        $output->writeln($this->resourceHelper->generateResourcePath($bom, ReadAction::class, [], true));

        return Command::SUCCESS;
    }

    private function convertBundleToSimple(
        ProductInterface $bundle,
        InputInterface   $input,
        OutputInterface  $output
    ): void {
        /** @see \Ekyna\Bundle\ProductBundle\Service\Converter\BundleToSimpleConverter */
        $helper = new QuestionHelper();

        $groups = [];
        foreach ($bundle->getBundleSlots() as $slot) {
            $choice = $slot->getChoices()->first();
            $product = $choice->getProduct();
            $choices = [];

            $index = 0;
            $excluded = array_map(fn($id) => intval($id), $choice->getExcludedOptionGroups());
            foreach ($product->getOptionGroups() as $group) {
                if (in_array($group->getId(), $excluded)) {
                    continue;
                }

                $choices[++$index] = $group;
            }

            if (empty($choices)) {
                continue;
            }

            $choices[0] = 'NONE';

            $message = sprintf(
                'Please select the options groups to keep from bundle slot product « [%s] %s »',
                $product->getReference(),
                $product
            );

            $question = new ChoiceQuestion($message, $choices);
            $question->setMultiselect(true);
            $question->setErrorMessage('Group %s is invalid.');

            $answer = $helper->ask($input, $output, $question);

            $answer = array_filter($answer, fn($group) => $group !== 'NONE');

            if (empty($answer)) {
                $output->writeln('All option groups ignored.');
                continue;
            }

            $output->writeln('Selected option groups: ');
            foreach ($answer as $group) {
                $output->writeln(sprintf(' - %s', $group));
            }

            array_push($groups, ...$answer);
        }

        foreach ($groups as $group) {
            $bundle->addOptionGroup($this->copier->copyResource($group));
        }

        $this->stockSubjectUpdater->reset($bundle);
        $this->offerInvalidator->invalidateByProduct($bundle);

        $bundle
            ->setType(ProductTypes::TYPE_SIMPLE)
            ->setPendingOffers(true)
            ->setPendingPrices(true);

        foreach ($bundle->getBundleSlots() as $slot) {
            $bundle->removeBundleSlot($slot);
        }
    }

    private function assertBundleCanBeConverted(
        ProductInterface $bundle,
        OutputInterface  $output
    ): bool {
        foreach ($bundle->getBundleSlots() as $slot) {
            $choice = $slot->getChoices()->first();

            $product = $choice->getProduct();

            if (!ProductTypes::isChildType($product)) {
                $output->writeln(
                    sprintf(
                        "<error>Product '[%s] %s' must be of type 'simple' or 'variant' (currently '%s')</error>",
                        $product->getReference(),
                        $product,
                        $product->getType()
                    )
                );

                return false;
            }
        }

        return true;
    }

    private function buildBillOfMaterials(ProductInterface $bundle, OutputInterface $output): ?BillOfMaterialsInterface
    {
        $bom = $this
            ->factoryFactory
            ->getFactory(BillOfMaterialsInterface::class)
            ->create();

        $this->subjectHelper->assign($bom, $bundle);

        $componentFactory = $this
            ->factoryFactory
            ->getFactory(BOMComponentInterface::class);

        $position = 0;
        foreach ($bundle->getBundleSlots() as $slot) {
            $choice = $slot->getChoices()->first();
            $product = $choice->getProduct();

            $component = $componentFactory->create();
            $this->subjectHelper->assign($component, $product);
            $component->setQuantity($choice->getMinQuantity());
            $component->setPosition($position);

            $bom->addComponent($component);

            $position++;
        }

        $violations = $this->validator->validate($bom);

        if (0 === $violations->count()) {
            return $bom;
        }

        foreach ($violations as $violation) {
            $output->writeln(sprintf('<error>%s</error>', $violation->getMessage()));
        }

        return null;
    }

    private function getBundle(string $reference): ?ProductInterface
    {
        return $this
            ->repositoryFactory
            ->getRepository(ProductInterface::class)->findOneBy(
                [
                    'reference' => $reference,
                    'type'      => ProductTypes::TYPE_BUNDLE,
                ]
            );
    }
}
