<?php

namespace App\Controller\Front;

use App\Repository\LangRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;

class LangController extends AbstractController
{    
    private $langRepository;
    
    public function __construct(LangRepository $langRepository)
    {
        $this->langRepository = $langRepository;
    }
    
    /**
     * @IsGranted("IS_AUTHENTICATED_FULLY")
     */
    public function ajaxLangEnabled()
    {
        return $this->json([
            'success' => true,
            'langs' => $this->langRepository->findBy(['enabled' => 1])
        ]);
    }
    
}
