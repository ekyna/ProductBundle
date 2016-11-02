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
}
