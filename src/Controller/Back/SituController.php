<?php

namespace App\Controller\Back;

use App\Entity\Situ;
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
        $repository = $this->getDoctrine()->getRepository(Situ::class);
        $situs = $repository->findAll();

        return $this->render('back/situ/search/index.html.twig', [
            'situs' => $situs,
        ]);
    }
    
    /**
     * @Route("/validations", name="back_situs_validation", methods="GET")
     */
    public function getSitusToValidate()
    {
        $repository = $this->getDoctrine()->getRepository(Situ::class);
        $situs = $repository->findBy(['statusId' => 2]);
        
        return $this->render('back/situ/validation/index.html.twig', [
            'situs' => $situs,
        ]);
    }
    
    /**
     * @Route("/verify/{id}", name="back_situ_verify", methods="GET")
     */
    public function verifySitu($id): Response
    {
        $repository = $this->getDoctrine()->getRepository(Situ::class);
        $situ = $repository->findOneBy(['id' => $id]);
        
        return $this->render('back/situ/verify/index.html.twig', [
            'situ' => $situ,
        ]);
    }
    
    /**
     * @Route("/read/{id}", name="back_situ_read", methods="GET")
     */
    public function getSitu($id): Response
    {
        $repository = $this->getDoctrine()->getRepository(Situ::class);
        $situ = $repository->findOneBy(['id' => $id]);
        
        return $this->render('back/situ/read/index.html.twig', [
            'situ' => $situ,
        ]);
    }
    
}
