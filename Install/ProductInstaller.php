<?php

declare(strict_types=1);

namespace Ekyna\Bundle\ProductBundle\Install;

use Ekyna\Bundle\InstallBundle\Install\AbstractInstaller;
use Ekyna\Bundle\ProductBundle\Exception\UnexpectedTypeException;
use Ekyna\Bundle\ProductBundle\Model\PricingGroupInterface;
use Ekyna\Component\Resource\Factory\FactoryFactoryInterface;
use Ekyna\Component\Resource\Manager\ManagerFactoryInterface;
use Ekyna\Component\Resource\Repository\RepositoryFactoryInterface;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

use function sprintf;
use function str_pad;

use const STR_PAD_LEFT;

/**
 * Class ProductInstaller
 * @package Ekyna\Bundle\ProductBundle\Install
 * @author  Étienne Dauvergne <contact@ekyna.com>
 */
class ProductInstaller extends AbstractInstaller
{
    public function __construct(
        private readonly RepositoryFactoryInterface $repositoryFactory,
        private readonly FactoryFactoryInterface    $factoryFactory,
        private readonly ManagerFactoryInterface    $managerFactory,
    ) {
    }

    public function install(Command $command, InputInterface $input, OutputInterface $output): void
    {
        $this->createPricingGroup($output);
    }

    private function createPricingGroup(OutputInterface $output): void
    {
        $output->writeln('<info>[Product] Installing default pricing group:</info>');

        $manager = $this->managerFactory->getManager(PricingGroupInterface::class);
        $repository = $this->repositoryFactory->getRepository(PricingGroupInterface::class);
        $factory = $this->factoryFactory->getFactory(PricingGroupInterface::class);

        $name = 'Par défaut';

        $output->write(sprintf(
            '- <comment>%s</comment> %s ',
            $name,
            str_pad('.', 44 - mb_strlen($name), '.', STR_PAD_LEFT)
        ));

        if (null !== $repository->findOneBy(['name' => $name])) {
            $output->writeln('already exists.');

            return;
        }

        $group = $factory->create();
        if (!$group instanceof PricingGroupInterface) {
            throw new UnexpectedTypeException($group, PricingGroupInterface::class);
        }

        $group->setName($name);

        $manager->persist($group);
        $manager->flush();

        $output->writeln('created.');

        $output->writeln('');
    }

    public static function getName(): string
    {
        return 'ekyna_product';
    }
}
