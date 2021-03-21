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

    public function getUserLang($user_lang_id)
    {
        $repository = $this->em->getRepository(Lang::class);
        
        $lang = $repository->findOneBy(
            ['id' => $user_lang_id]
        );
        return $lang;
    }

    public function getLangByUserLang($lang)
    {
        $repository = $this->em->getRepository(Lang::class);
        
        $lang = $repository->findOneBy(
            ['lang' => $lang]
        );
        return $lang;
    }
    
    public function findLangsEnabledOrNot($boolean)
    {
        
        $repository = $this->em->getRepository(Lang::class);
        
        $langs = $repository->findBy(
            ['enabled' => $boolean]
        );
        return $langs;
    }

    public function getLangsEnabledOrNot($boolean) 
    {
        $langs = $this->findLangsEnabledOrNot($boolean);
        
        $result = [];
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
}