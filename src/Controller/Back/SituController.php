<?php

namespace App\Controller\Back;

use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Contracts\Translation\TranslatorInterface;

/**
 * @Route("/{_locale<%app_locales%>}/back/situ")
 */
class SituController extends AbstractController
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
     * @Route("/list", name="back_situs_search")
     */
    public function index(): Response
    {
        return $this->render('back/situ/list.html.twig', [
            'controller_name' => 'SituController',
        ]);
    }
    
    /**
     * @Route("/validations", name="back_situs_validation", methods="GET")
     */
    public function getSitusByUser()
    {
        return $this->render('back/situ/validations.html.twig', [
            'controller_name' => 'SituController',
        ]);
    }
    
    /**
     * @Route("/verify/situ/{id}", name="back_situ_verify", methods="GET")
     */
    public function getSitu(Situ $situ): Response
    {
        
        return $this->render('back/situ/verify.html.twig', [
            'controller_name' => 'SituController',
        ]);
    }
    
}
