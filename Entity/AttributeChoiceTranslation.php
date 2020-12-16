<?php

namespace Ekyna\Bundle\ProductBundle\Entity;

use Ekyna\Bundle\ProductBundle\Model\AttributeChoiceTranslationInterface;
use Ekyna\Component\Resource\Model\AbstractTranslation;

/**
 * Class AttributeChoiceTranslation
 * @package Ekyna\Bundle\ProductBundle\Entity
 * @author  Etienne Dauvergne <contact@ekyna.com>
 */
class AttributeChoiceTranslation extends AbstractTranslation implements AttributeChoiceTranslationInterface
{
    /**
     * @var string
     */
    protected $title;


    /**
     * @inheritdoc
     */
    public function getTitle()
    {
        return $this->title;
    }

    /**
     * @inheritdoc
     */
    public function setTitle($title)
    {
        $this->title = $title;

        return $this;
    }
}
