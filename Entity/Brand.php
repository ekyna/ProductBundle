<?php

namespace Ekyna\Bundle\ProductBundle\Entity;

use Ekyna\Bundle\ProductBundle\Model\BrandInterface;
use Ekyna\Component\Resource\Model as ResourceModel;
use Ekyna\Bundle\CmsBundle\Entity\Seo;
use Ekyna\Bundle\CmsBundle\Model as Cms;
use Ekyna\Bundle\MediaBundle\Model as Media;

/**
 * Class Brand
 * @package Ekyna\Bundle\ProductBundle\Entity
 * @author  Étienne Dauvergne <contact@ekyna.com>
 */
class Brand implements BrandInterface
{
    use Cms\ContentSubjectTrait,
        Cms\SeoSubjectTrait,
        Media\MediaSubjectTrait,
        ResourceModel\TimestampableTrait;

    /**
     * @var int
     */
    protected $id;

    /**
     * @var string
     */
    protected $name;


    /**
     * Constructor
     */
    public function __construct()
    {
        $this->seo = new Seo();
    }

    /**
     * Returns the string representation
     *
     * @return string
     */
    public function __toString()
    {
        return $this->getName();
    }

    /**
     * @inheritdoc
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * @inheritdoc
     */
    public function getName()
    {
        return $this->name;
    }

    /**
     * @inheritdoc
     */
    public function setName($name)
    {
        $this->name = $name;
    }
}
