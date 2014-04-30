<?php

namespace Ekyna\Bundle\ProductBundle\Entity;

use Ekyna\Bundle\CoreBundle\Entity\AbstractImage;

/**
 * CategoryImage.
 *
 * @author Étienne Dauvergne <contact@ekyna.com>
 */
class CategoryImage extends AbstractImage
{
    /**
     * @var Category
     */
    protected $category;


    /**
     * Set category
     *
     * @param Category $category
     * @return CategoryImage
     */
    public function setCategory(Category $category = null)
    {
        $this->category = $category;

        return $this;
    }

    /**
     * Get category
     *
     * @return Category 
     */
    public function getCategory()
    {
        return $this->category;
    }
}
