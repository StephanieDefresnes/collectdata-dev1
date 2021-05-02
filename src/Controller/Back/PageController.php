<?php

namespace App\Controller\Back;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

/**
 * @Route("/{_locale<%app_locales%>}/back")
 */
class PageController extends AbstractController
{
    /**
     * @Route("/", name="back_home")
     */
    public function index(): Response
    {
        return $this->render('back/page/index.html.twig', [
            
        ]);
    }
}