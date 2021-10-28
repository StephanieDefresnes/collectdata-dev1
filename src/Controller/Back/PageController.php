<?php

namespace App\Controller\Back;

use App\Entity\Page;
use Doctrine\ORM\EntityManagerInterface;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\IsGranted;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Contracts\Translation\TranslatorInterface;

/**
 * @IsGranted("IS_AUTHENTICATED_FULLY")
 * @Route("/{_locale<%app_locales%>}/back")
 */
class PageController extends AbstractController
{
    private $em;
    private $translator;
    
    public function __construct(EntityManagerInterface $em, TranslatorInterface $translator)
    {
        $this->em = $em;
        $this->translator = $translator;
    }
    
    /**
     * @Route("/404", name="back_not_found")
     */
    public function notFoundPage(): Response
    {
        return $this->render('back/page/404.html.twig');
    }
    
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
    
    /**
     * @IsGranted("ROLE_SUPER_VISITOR")
     * @Route("/content/{page}/validate", name="back_content_validate", methods="GET|POST")
     */
    public function contentValidate(Request $request, Page $page): Response
    {      
        // Super visitor filter
        $currentUser = $security->getUser();
        if ($currentUser->hasRole('ROLE_SUPER_VISITOR')) {
            return $this->redirectToRoute('visitor_denied', [ '_locale' => locale_get_default()]);
        }
        
        $page->setEnabled(1);
        $this->em->persist($page);
        $this->em->flush();

        $msg = $this->translator
                ->trans('content.form.submit.flash.success', [],
                        'back_messages', $locale = locale_get_default());
        $this->addFlash('success', $msg);

        return $this->redirectToRoute('back_content_search', [
            '_locale' => locale_get_default()
        ]);
    }
    
    /**
     * @Route("/403", name="visitor_denied")
     */
    public function visitorAccessDenied(): Response
    {
        return $this->render('back/page/visitor.html.twig', [
            
        ]);
    }
}