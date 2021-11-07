<?php

namespace App\Service;

use App\Entity\Category;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Security\Core\Security;

class CategoryService {

    private $em;
    
    public function __construct(EntityManagerInterface $em, Security $security)
    {
        $this->em = $em;
    }
    
    /* === Called in twig === */
    
    /**
     * back\message\alerts.html.twig
     * 
     * @param type $categoryId
     * @return type
     */
    public function getCategory($categoryId) {
        return $this->em->getRepository(Category::class)->find($categoryId);
    }
}
