<?php

namespace App\Controller\Back;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

/**
 * @Route("/back")
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
