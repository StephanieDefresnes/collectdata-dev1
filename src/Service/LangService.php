<?php

namespace App\Service;

use App\Entity\Lang;
use Doctrine\ORM\EntityManagerInterface;

class LangService {

    private $em;
    
    public function __construct(EntityManagerInterface $em)
    {
        $this->em = $em;
    }
    
    /**
     * Called in twig
     */
    public function getEnabled() {
        return $this->em->getRepository(Lang::class)->findBy(['enabled' => 1]);
    }
    public function getLang($lang) {
        return $this->em->getRepository(Lang::class)->findOneBy(['lang' => $lang]);
    }
    
}