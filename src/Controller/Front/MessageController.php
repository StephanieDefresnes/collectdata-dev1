<?php

namespace App\Controller\Front;

use Sensio\Bundle\FrameworkExtraBundle\Configuration\IsGranted;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;

/**
 * @IsGranted("IS_AUTHENTICATED_FULLY")
 */
class MessageController extends AbstractController
{
    public function index(): Response
    {
        return $this->render('front/message/index.html.twig', [
            'controller_name' => 'MessageController',
        ]);
    }    
}
