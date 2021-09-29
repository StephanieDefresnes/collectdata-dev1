<?php

namespace App\Controller\Front;

use App\Entity\Translation;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Core\Security;

/**
 * @Route("/{_locale<%app_locales%>}")
 */
class TranslationController extends AbstractController
{    
    /**
     * @Route("/my-translations", name="user_translations", methods="GET|POST")
     */
    public function index(  EntityManagerInterface $em,
                            Security $security): Response
    {
        $this->denyAccessUnlessGranted('IS_AUTHENTICATED_FULLY');
        
        // Valideted translations list
        $referents = $em->getRepository(Translation::class)->findBy([
            'enabled' => 1,
            'referent' => 1,
        ]);
        
        return $this->render('front/translation/index.html.twig', [
            'referents' => $referents,
        ]);
    }
    
}
