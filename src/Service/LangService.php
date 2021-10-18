<?php

namespace App\Service;

use App\Entity\Lang;
use Doctrine\ORM\EntityManagerInterface;

class LangService {
    
    /**
     * Called in twig
     */
    public function getEnabled(EntityManagerInterface $em) {
        return $em->getRepository(Lang::class)->findBy(['enabled' => 1]);
    }
    
}