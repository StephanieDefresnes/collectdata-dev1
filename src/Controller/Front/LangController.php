<?php

namespace App\Controller\Front;

use App\Repository\LangRepository;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\IsGranted;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Routing\Annotation\Route;

class LangController extends AbstractController
{    
    private $langRepository;
    
    public function __construct(LangRepository $langRepository)
    {
        $this->langRepository = $langRepository;
    }
    
    /**
     * @IsGranted("IS_AUTHENTICATED_FULLY")
     * @Route("/{_locale<%app_locales%>}/ajaxLangEnabled", methods="GET|POST")
     */
    public function ajaxLangEnabled()
    {
        return $this->json([
            'success' => true,
            'langs' => $this->langRepository->findBy(['enabled' => 1])
        ]);
    }
    
}
