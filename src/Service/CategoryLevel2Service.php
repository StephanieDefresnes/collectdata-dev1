<?php

namespace App\Service;

use App\Entity\Lang;
use App\Repository\EventRepository;
use Doctrine\ORM\EntityManagerInterface;

class CategoryLevel2Service {

    private $em;
    
    public function __construct(EntityManagerInterface $em)
    {
        $this->em = $em;
    }
    
    
}
