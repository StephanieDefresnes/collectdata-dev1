<?php

namespace App\Service;

use App\Entity\Lang;
use App\Entity\User;
use Doctrine\ORM\EntityManagerInterface;

class LangService {

    private $em;
    
    public function __construct(EntityManagerInterface $em)
    {
        $this->em = $em;
    }

    public function getLangById($lang_id)
    {
        $repository = $this->em->getRepository(Lang::class);
        
        $default = $repository->findOneBy(
            ['englishName' => 'French']
        );
        if ($lang_id == '') $lang_id = $default->getId();
        
        $lang = $repository->findOneBy(
            ['id' => $lang_id]
        );
        return $lang;
    }

    public function getLangByLang($lang)
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

    public function getAll()
    {
        $repository = $this->em->getRepository(Lang::class);
        $langs = $repository->findAll();
        
        $result = [];
        foreach ($langs as $lang) {
            $result[] = [ 
                'id' => $lang->getId(),
                'lang' => $lang->getLang(),
                'name' => html_entity_decode($lang->getName(), ENT_QUOTES, 'UTF-8'),
                'english_name' => $lang->getEnglishName(),
                'enabled' => $lang->getEnabled(),
            ];
        }
        return $result;
    }
    
    
    public function getUsedUserLangs($userId)
     {
        $query = $this->em->createQueryBuilder()
            ->from(Lang::class,'lang')
            ->select('user.langId, user.langs')
            ->leftJoin(User::class, 'user', 'WITH', 'user.langId=lang.id')
            ->where("user.langId = '$userId' ");
        
        $langs = $query->getQuery()->getResult();
        
        return $langs;
    }
}