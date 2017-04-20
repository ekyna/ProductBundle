<?php

declare(strict_types=1);

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
interface BrandInterface extends
    VisibilityInterface,
    Cms\ContentSubjectInterface,
    Cms\SeoSubjectInterface,
    MediaSubjectInterface,
    RM\SortableInterface,
    RM\TimestampableInterface,
    RM\TranslatableInterface,
    RM\TaggedEntityInterface
{
    public function getName(): ?string;

    public function setName(?string $name): BrandInterface;

    /**
     * Returns the (translated) title.
     */
    public function getTitle(): ?string;

    /**
     * Sets the (translated) title.
     */
    public function setTitle(?string $title): BrandInterface;

    /**
     * Returns the (translated) description.
     */
    public function getDescription(): ?string;

    /**
     * Sets the (translated) description.
     */
    public function setDescription(?string $description): BrandInterface;

    /**
     * Returns the (translated) slug.
     */
    public function getSlug(): ?string;

    /**
     * Sets the (translated) slug.
     */
    public function setSlug(?string $slug): BrandInterface;
}
