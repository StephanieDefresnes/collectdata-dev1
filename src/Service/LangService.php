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
    
    public function getLangsEnabledOrNot($boolean)
    {
        $langs = $this->em->getRepository(Lang::class)->findBy(
            ['enabled' => $boolean]
        );
        
        $result = [];
        foreach ($langs as $lang) {
            $result[] = [ 
                'id' => $lang->getId(),
                'lang' => $lang->getLang(),
                'name' => html_entity_decode($lang->getName(), ENT_QUOTES, 'UTF-8'),
                'englishName' => $lang->getEnglishName(),
            ];
        }
        return $result;
    }

    public function getAll()
    {
        $langs = $this->em->getRepository(Lang::class)->findAll();
        
        $result = [];
        foreach ($langs as $lang) {
            $result[] = [ 
                'id' => $lang->getId(),
                'lang' => $lang->getLang(),
                'name' => html_entity_decode($lang->getName(), ENT_QUOTES, 'UTF-8'),
                'englishName' => $lang->getEnglishName(),
                'enabled' => $lang->getEnabled(),
            ];
        }
        return $result;
    }
    
}