<?php

declare(strict_types=1);

namespace Ekyna\Bundle\ProductBundle\Entity;

use Ekyna\Bundle\ProductBundle\Model\ProductMentionTranslationInterface;
use Ekyna\Component\Commerce\Common\Entity\AbstractMentionTranslation;

/**
 * Class ProductMentionTranslation
 * @package Ekyna\Bundle\ProductBundle\Entity
 * @author  Etienne Dauvergne <contact@ekyna.com>
 */
class ProductMentionTranslation extends AbstractMentionTranslation implements ProductMentionTranslationInterface
{

}
