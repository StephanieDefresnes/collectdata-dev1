<?php

namespace App\Controller\Front;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class PageController extends AbstractController
{
    /**
     * @Route("/", name="front_home")
     */
    public function index(): Response
    {
        return $this->render('front/page/index.html.twig', [
            
        ]);
    }
}
