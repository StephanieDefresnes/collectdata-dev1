<?php

namespace App\Controller\Front;

use Sensio\Bundle\FrameworkExtraBundle\Configuration\IsGranted;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

/**
 * @IsGranted("IS_AUTHENTICATED_FULLY")
 * @Route("/{_locale<%app_locales%>}")
 */
class MessageController extends AbstractController
{
    /**
     * @Route("/message", name="message")
     */
    public function index(): Response
    {
        return $this->render('front/message/index.html.twig', [
            'controller_name' => 'MessageController',
        ]);
    }    
}
