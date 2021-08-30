<?php

namespace App\Controller\Front;

use App\Service\LangService;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Contracts\Translation\TranslatorInterface;

class PageController extends AbstractController
{
    /**
     * @Route("/", name="index")
     */
    public function index(LangService $langService)
    {
        $langs = $langService->getLangsEnabledOrNot(1);
        
        return $this->render('front/page/index.html.twig', [
            'langs' => $langs,
        ]);
    }
    
    /**
     * @Route("/{_locale<%app_locales%>}",name="front_home")
     */
    public function home(Request $request): Response
    {
        $locale = $request->getLocale();
        return $this->render('front/page/home.html.twig', [
            
        ]);
    }
}
