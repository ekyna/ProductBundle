<?php

namespace Ekyna\Bundle\ProductBundle\Entity;

use Gedmo\Tree\Entity\Repository\NestedTreeRepository;

/**
 * CategoryRepository.
 * 
 * @author Étienne Dauvergne <contact@ekyna.com>
 */
class CategoryRepository extends NestedTreeRepository
{
    public function createNew()
    {
        $class = $this->getClassName();
        return new $class;
    }

    public function findBySlug($categorySlug)
    {
        $category = null;

        $slugs = explode('/', $categorySlug);
        if (count($slugs) > 0) {
            $slugs = array_reverse($slugs);
            if (null === $category = $this->findOneBy(array('slug' => array_shift($slugs)))) {
                $parent = $category;
                while(count($slugs) > 0) {
                    if($parent->getSlug() !== array_shift($slugs)) {
                        $category = null;
                    }
                    if(null === $parent = $parent->getParent()) {
                        break;
                    }
                }
            }
        }

        return $category;
    }
}
