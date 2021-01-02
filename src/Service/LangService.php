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

    public function getLangIdByLang($lang)
    {
        
        $repository = $this->em->getRepository(Lang::class);
        
        $lang = $repository->findOneBy(
            ['lang' => $lang]
        );
        
        return $lang->getId();
        
    }

    public function getLangsEnabledOrNot($boolean) 
    {
        $result = [];
        
        $repository = $this->em->getRepository(Lang::class);
        
        $langs = $repository->findBy(
            ['enabled' => $boolean]
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
}