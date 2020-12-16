<?php

namespace Ekyna\Bundle\ProductBundle\Entity;

use Ekyna\Bundle\ProductBundle\Model\OptionTranslationInterface;
use Ekyna\Component\Resource\Model\AbstractTranslation;

/**
 * Class OptionTranslation
 * @package Ekyna\Bundle\ProductBundle\Entity
 * @author  Etienne Dauvergne <contact@ekyna.com>
 */
class OptionTranslation extends AbstractTranslation implements OptionTranslationInterface
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
