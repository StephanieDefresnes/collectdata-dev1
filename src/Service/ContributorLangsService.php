<?php

namespace App\Service;

use Doctrine\ORM\EntityManagerInterface;

class ContributorLangsService
{

    private $em;
    
    public function __construct(EntityManagerInterface $em)
    {
        $this->em = $em;
    }
    
    public function getContributorLangs()
    {   
        $conn = $this->em->getConnection();
        $sql = 'SELECT lang.lang FROM contributor_langs LEFT JOIN lang ON lang.id = contributor_langs.lang_id';
        $stmt = $conn->prepare($sql);
        $stmt->execute();
        return $stmt->fetchAll();
    }
}
