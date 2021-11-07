<?php

namespace App\Service;

use App\Entity\Situ;
use Doctrine\ORM\EntityManagerInterface;

class SituService {

    private $em;
    
    public function __construct(EntityManagerInterface $em)
    {
        $this->em = $em;
    }
    
    /* === Called in twig === */
    
    /**
     * back\message\alerts.html.twig
     * 
     * @param type $situId
     * @return type
     */
    public function getSitu($situId) {
        return $this->em->getRepository(Situ::class)->find($situId);
    }
    
    /**
     *  back\situ\read.html.twig
     * 
     * @param type $situId
     * @return type Get translations read situ
     */
    public function getTranslations($situId) {
        return $this->em->getRepository(Situ::class)
                ->findby(['translatedSituId' => $situId]);
    }
    
}