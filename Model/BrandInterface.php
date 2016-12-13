<?php

namespace Ekyna\Bundle\ProductBundle\Model;

use Ekyna\Component\Resource\Model as RM;
use Ekyna\Bundle\CmsBundle\Model as Cms;
use Ekyna\Bundle\MediaBundle\Model\MediaSubjectInterface;

/**
 * Interface BrandInterface
 * @package Ekyna\Bundle\ProductBundle\Model
 * @author  Etienne Dauvergne <contact@ekyna.com>
 *
 * @method BrandTranslationInterface translate($locale = null, $create = false)
 */
interface BrandInterface
    extends Cms\ContentSubjectInterface,
            Cms\SeoSubjectInterface,
            MediaSubjectInterface,
            RM\SortableInterface,
            RM\TimestampableInterface,
            RM\TranslatableInterface,
            RM\TaggedEntityInterface
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
     * Returns the (translated) description.
     *
     * @return string
     */
    public function getDescription();

    /**
     * Returns the (translated) slug.
     *
     * @return string
     */
    public function getSlug();
}
