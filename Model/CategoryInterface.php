<?php

declare(strict_types=1);

namespace Ekyna\Bundle\ProductBundle\Model;

use Ekyna\Bundle\MediaBundle\Model\MediaSubjectInterface;
use Ekyna\Component\Resource\Model as RM;
use Ekyna\Bundle\CmsBundle\Model as Cms;

/**
 * Interface CategoryInterface
 * @package Ekyna\Bundle\ProductBundle\Model
 * @author  Etienne Dauvergne <contact@ekyna.com>
 *
 * @method CategoryTranslationInterface translate($locale = null, $create = false)
 */
interface CategoryInterface extends
    VisibilityInterface,
    Cms\ContentSubjectInterface,
    Cms\SeoSubjectInterface,
    MediaSubjectInterface,
    RM\TreeInterface,
    RM\TimestampableInterface,
    RM\TranslatableInterface,
    RM\TaggedEntityInterface
{
    public function getName(): ?string;

    public function setName(?string $name): CategoryInterface;

    /**
     * Returns the (translated) title.
     */
    public function getTitle(): ?string;

    /**
     * Sets the (translated) title.
     */
    public function setTitle(?string $title): CategoryInterface;

    /**
     * Returns the (translated) description.
     */
    public function getDescription(): ?string;

    /**
     * Sets the (translated) description.
     */
    public function setDescription(?string $description): CategoryInterface;

    /**
     * Returns the (translated) slug.
     */
    public function getSlug(): ?string;

    /**
     * Sets the (translated) slug.
     */
    public function setSlug(?string $slug): CategoryInterface;
}
