<?php

namespace App\Controller\Front;

use App\Entity\TranslationMessage;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\Session\Session;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Contracts\Translation\TranslatorInterface;

/**
 * @Route("/{_locale<%app_locales%>}")
 */
class TranslationController extends AbstractController
{
    private $em;
    
    public function __construct(EntityManagerInterface $em)
    {
        $this->em = $em;
    }
    
    /**
     * @Route("/{id}/translations", name="front_translation", methods="GET|POST")
     */
    public function index(): Response
    {
        return $this->render('front/translation/index.html.twig', [
            'controller_name' => 'TranslationController',
        ]);
    }
    
    /**
     * @Route("/{id}/translation", name="front_translation_add", methods="GET|POST")
     */
    public function create(): Response
    {
        return $this->render('front/translation/create.html.twig', [
//            'controller_name' => 'TranslationController',
        ]);
    }
}
