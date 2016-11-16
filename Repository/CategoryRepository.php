<?php

namespace Ekyna\Bundle\ProductBundle\Repository;

use Ekyna\Component\Resource\Doctrine\ORM\TranslatableResourceRepositoryInterface;
use Ekyna\Component\Resource\Doctrine\ORM\Util\TranslatableResourceRepositoryTrait;
use Gedmo\Tree\Entity\Repository\NestedTreeRepository;

/**
 * Class CategoryRepository
 * @package Ekyna\Bundle\ProductBundle\Repository
 * @author Étienne Dauvergne <contact@ekyna.com>
 */
class CategoryRepository extends NestedTreeRepository implements TranslatableResourceRepositoryInterface
{
    use TranslatableResourceRepositoryTrait;

    /*public function findBySlug($categorySlug)
    {
        $category = null;

        $slugs = explode('/', trim($categorySlug, '/'));
        if (count($slugs) > 0) {
            $slugs = array_reverse($slugs);
            if (null !== $category = $this->findOneBy(['slug' => array_shift($slugs)])) {
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
    }*/
}
