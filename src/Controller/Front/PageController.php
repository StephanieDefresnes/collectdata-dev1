<?php

namespace App\Controller\Front;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Contracts\Translation\TranslatorInterface;

class PageController extends AbstractController
{
    /**
     * @Route("/{_locale<%app_locales%>}",name="front_home")
     */
    public function index(Request $request): Response
    {
        $locale = $request->getLocale();
        return $this->render('front/page/index.html.twig', [
            
        ]);
    }
}
