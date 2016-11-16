<?php

namespace Ekyna\Bundle\ProductBundle\Model;

use Ekyna\Component\Resource\Model as ResourceModel;
use Ekyna\Bundle\CmsBundle\Model as Cms;
use Ekyna\Bundle\MediaBundle\Model as Media;

/**
 * Interface BrandInterface
 * @package Ekyna\Bundle\ProductBundle\Model
 * @author  Etienne Dauvergne <contact@ekyna.com>
 */
interface BrandInterface
    extends Cms\ContentSubjectInterface,
            Cms\SeoSubjectInterface,
            Media\MediaSubjectInterface,
            ResourceModel\SortableInterface,
            ResourceModel\TimestampableInterface,
            ResourceModel\ResourceInterface
{
    /**
     * Returns the name.
     *
     * @return string
     */
    public function getName();

    /**
     * Sets the name.
     * @param string $name
     *
     * @return $this|BrandInterface
     */
    public function setName($name);

    /**
     * Returns the (translated) title.
     *
     * @return string
     */
    public function getTitle();

    /**
     * Sets the (translated) title.
     *
     * @param string $title
     *
     * @return $this|BrandInterface
     */
    //public function setTitle($title);

    /**
     * Returns the (translated) description.
     *
     * @return string
     */
    public function getDescription();

    /**
     * Sets the (translated) description.
     *
     * @param string $description
     *
     * @return $this|BrandInterface
     */
    //public function setDescription($description);
}
