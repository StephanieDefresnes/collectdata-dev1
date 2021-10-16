<?php

namespace App\Controller\Back;

use App\Entity\Lang;
use App\Entity\Page;
use App\Service\LangService;
use App\Form\Back\Page\PageFormType;
use Doctrine\Common\Collections\ArrayCollection;
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
     * @IsGranted("ROLE_SUPER_ADMIN")
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
     * @IsGranted("ROLE_SUPER_ADMIN")
     * @Route("/content/{id}", defaults={"id" = null}, name="back_content_edit", methods="GET|POST")
     */
    public function contentEdit(Request $request,
                                LangService $langService, $id): Response
    {
        $lang = $this->getDoctrine()->getRepository(Lang::class)->findOneBy(
            ['lang' => locale_get_default()]
        );
        
        
        // Update or Create new Page
        $langPage = '';
        if ($id) {
            $page = $this->getDoctrine()->getRepository(Page::class)
                    ->find($id);
            
            if (!$page) {
                return $this->redirectToRoute('back_not_found', [
                    '_locale' => locale_get_default()
                ]);
            }
            
            $langPage = $this->getDoctrine()->getRepository(Lang::class)
                    ->findOneBy(['lang' => $page->getLang()])
                    ->getEnglishName();
        } else {
            $page = new Page();
        }
        
        $originalContents = new ArrayCollection();
        foreach ($page->getPageContents() as $content) {
            $originalContents->add($content);
        }
        
        // Form
        $form = $this->createForm(PageFormType::class, $page);
        $form->handleRequest($request);
        
        if ($form->isSubmitted() && $form->isValid()) {
            
            foreach ($originalContents as $content) {
                if (false === $page->getPageContents()->contains($content)) {
                    $page->getPageContents()->removeElement($content);
                    $this->em->remove($content);
                }
            }
            
            $msgType = $form->get('enabled')->getData() == 0
                    ? 'save' : 'submit';
                
            $this->em->persist($page);
            
            try {
                $this->em->flush();

                $msg = $this->translator
                        ->trans('content.form.'.$msgType.'.flash.success', [],
                                'back_messages', $locale = locale_get_default());
                $this->addFlash('success', $msg);

                if ($msgType == 'save') {
                    return $this->redirectToRoute('back_content_edit', [
                        'id' => $page->getId(), '_locale' => locale_get_default()
                    ]);
                } else {
                    return $this->redirectToRoute('back_content_search', [
                        '_locale' => locale_get_default()
                    ]);
                }
            } catch (\Doctrine\DBAL\DBALException $e) {
                $this->addFlash('warning', $e->getMessage());
            }
        }
        
        return $this->render('back/page/content/edit/index.html.twig', [
            'form' => $form->createView(),
            'lang' => html_entity_decode($lang->getName(), ENT_QUOTES, 'UTF-8'),
            'page' => $page,
            'langPage' => $langPage,
        ]);
    }
    
    /**
     * @IsGranted("ROLE_SUPER_ADMIN")
     * @Route("/content/{page}/validate", name="back_content_validate", methods="GET|POST")
     */
    public function contentValidate(Request $request, Page $page): Response
    {
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
}