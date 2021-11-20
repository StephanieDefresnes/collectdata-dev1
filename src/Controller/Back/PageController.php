<?php

namespace App\Controller\Back;

use App\Entity\Page;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\IsGranted;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;

/**
 * @IsGranted("IS_AUTHENTICATED_FULLY")
 */
class PageController extends AbstractController
{    
    public function dashboard(): Response
    {
        return $this->render('back/page/index.html.twig', [
            
        ]);
    }
    
    public function contentList(): Response
    {
        $repository = $this->getDoctrine()->getRepository(Page::class);
        $pages = $repository->findAll();
        
        return $this->render('back/page/content/search.html.twig', [
            'pages' => $pages,
        ]);
    }
}