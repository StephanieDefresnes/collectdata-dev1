<?php

namespace App\Controller\Back;

use App\Entity\Page;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\IsGranted;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

/**
 * @IsGranted("IS_AUTHENTICATED_FULLY")
 * @Route("/{_locale<%app_locales%>}/back")
 */
class PageController extends AbstractController
{    
    /**
     * @Route("/", name="back_home")
     */
    public function dashboard(): Response
    {
        return $this->render('back/page/index.html.twig', [
            
        ]);
    }
    
    /**
     * @IsGranted("ROLE_SUPER_VISITOR")
     * @Route("/content/all", name="back_content_search", methods="GET")
     */
    public function contentList(): Response
    {
        $repository = $this->getDoctrine()->getRepository(Page::class);
        $pages = $repository->findAll();
        
        return $this->render('back/page/content/search.html.twig', [
            'pages' => $pages,
        ]);
    }
}