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

    public function getLangEnabled()
    {
        $result = [];
        
        $repository = $this->em->getRepository(Lang::class);
        
        $langs = $repository->findBy(
            ['enabled' => 1]
        );
        
        foreach ($langs as $lang) {
            $result[] = [ 
                'id' => $lang->geId(),
                'lang' => $lang->getLang(),
                'name' => $lang->getName(),
                'english_name' => $lang->getEnglishName(),
            ];
        }
        $result = implode(",", $result);
        return $result;
        
    }

    public function getLangNotEnabled()
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
                'english_name' => $lang->getEnglishName(),
            ];
        }
        return $result;
        
    }
}
