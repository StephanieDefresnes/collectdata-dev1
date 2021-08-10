<?php

namespace App\Controller\Back;

use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Contracts\Translation\TranslatorInterface;


/**
 * @Route("/{_locale<%app_locales%>}/back/event")
 */
class EventController extends AbstractController
{
    private $em;
    private $translator;
    
    public function __construct(EntityManagerInterface $em,
                                TranslatorInterface $translator)
    {
        $this->em = $em;
        $this->translator = $translator;
    }
    
    /**
     * @Route("/list", name="back_events")
     */
    public function index(): Response
    {
        return $this->render('back/event/index.html.twig', [
            'controller_name' => 'EventController',
        ]);
    }
}
