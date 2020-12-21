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

    public function getLangsEnabled()
    {
        $result = [];
        
        $repository = $this->em->getRepository(Lang::class);
        
        $langs = $repository->findBy(
            ['enabled' => 1]
        );
        
        foreach ($langs as $lang) {
            $result[] = [ 
                'id' => $lang->getId(),
                'lang' => $lang->getLang(),
                'name' => html_entity_decode($lang->getName(), ENT_QUOTES, 'UTF-8'),
                'english_name' => $lang->getEnglishName(),
            ];
        }
        return $result;
        
    }

    public function getLangsNotEnabled()
    {
        $result = [];
        
        $repository = $this->em->getRepository(Lang::class);
        
        $langs = $repository->findBy(
            ['enabled' => 0]
        );
        
        foreach ($langs as $lang) {
            $result[] = [ 
                'id' => $lang->geId(),
                'lang' => $lang->getLang(),
                'name' => html_entity_decode($lang->getName(), ENT_QUOTES, 'UTF-8'),
                'english_name' => $lang->getEnglishName(),
            ];
        }
        return $result;
        
    }
}