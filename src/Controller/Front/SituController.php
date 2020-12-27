<?php

namespace App\Controller\Front;

use App\Entity\Situ;
use App\Entity\User;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Security;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\IsGranted;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

/**
 * @Route("/{_locale<%app_locales%>}")
 */
class SituController extends AbstractController
{
    /**
     * @Route("/situ", name="situ")
     */
    public function index(): Response
    {
        return $this->render('situ/index.html.twig', [
            'controller_name' => 'SituController',
        ]);
    }
    
    /**
     * @Security("is_granted('ROLE_USER')")
     * @Route("/situs/{id}", name="user_situs", methods="GET")
     * @param type $user_id
     */
    public function getSitusByUser()
    {
        $situs = $this->getDoctrine()
            ->getRepository(Situ::class)
            ->findBy([
                'userId' => $this->getUser()->getId()
            ]);
        
        return $this->render('front/situ/situs.html.twig', [
            'situs' => $situs,
        ]);
    }
}
