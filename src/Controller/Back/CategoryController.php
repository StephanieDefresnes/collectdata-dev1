<?php

namespace App\Controller\Back;

use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Contracts\Translation\TranslatorInterface;

/**
 * @Route("/{_locale<%app_locales%>}/back/category")
 */
class CategoryController extends AbstractController
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
     * @Route("/list", name="back_categories")
     */
    public function index(): Response
    {
        $this->denyAccessUnlessGranted('IS_AUTHENTICATED_FULLY');
        
        return $this->render('back/category/index.html.twig', [
            'controller_name' => 'CategoryController',
        ]);
    }
    
}
