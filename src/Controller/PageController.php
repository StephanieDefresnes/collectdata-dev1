<?php

namespace App\Controller;

use App\Entity\Lang;
use App\Entity\Page;
use App\Entity\Status;
use App\Service\LangService;
use App\Service\UserService;
use App\Form\Page\PageFormType;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\ORM\EntityManagerInterface;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\IsGranted;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Core\Security;
use Symfony\Component\String\Slugger\AsciiSlugger;
use Symfony\Component\String\Slugger\SluggerInterface;
use Symfony\Contracts\Translation\TranslatorInterface;

/**
 * @IsGranted("IS_AUTHENTICATED_FULLY")
 * @Route("/{_locale<%app_locales%>}")
 */
class PageController extends AbstractController
{
    public function contentEdit(EntityManagerInterface $em,
                                LangService $langService,
                                Request $request,
                                Security $security,
                                SluggerInterface $slugger,
                                TranslatorInterface $translator,
                                UserService $userService,
                                $locale, $id, $back = null): Response
    {
        // $label depending on route and user langs
        // Route front_content_edit
        $label = 'action.submit';
        $users = [];
        $action = 'submit';
        
        if ($back) {
            $this->denyAccessUnlessGranted('ROLE_SUPER_VISITOR');
            // Route back_content_edit & default $label 
            $label = 'action.validate';
            $action = 'validate';
        }
        
        // Update or Create new Page
        if ($id) {
            $page = $this->getDoctrine()->getRepository(Page::class)
                    ->find($id);
            
            if (!$page) {
                return $this->redirectToRoute('back_not_found', [
                    '_locale' => locale_get_default()
                ]);
            }
            
            // If back & user langs contain page lang, user can valid page
            // else he attributes to lang contributor user
            $user = $security->getUser();
            if ($back) {
                $label = 'action.attribute';
                foreach ($user->getLangs()->getValues() as $lang) {
                    if ($lang->getLang() === $page->getLang()) {
                        $label = 'action.validate';
                    }
                }
            
                // Get lang contributor user for page
                $langPage = $this->getDoctrine()->getRepository(Lang::class)
                        ->findOneBy(['lang' => $page->getLang()]);
                $users = $userService->getUsersLangContributorByLang($langPage);
                
            }
        } else {
            $page = new Page();
        }
        
        $originalContents = new ArrayCollection();
        foreach ($page->getPageContents() as $content) {
            $originalContents->add($content);
        }
        
        // Form
        $form = $this->createForm(PageFormType::class, $page, [
                'label' => $label,
                'users' => $users
            ]);
        $form->handleRequest($request);
        
        if ($form->isSubmitted() && $form->isValid()) {
            
            // Slug if empty, esle user can custom
            if (!$page->getSlug()) {
                $slugger = new AsciiSlugger();
                $page->setSlug($slugger->slug($form->get('title')->getData()));
            }
            
            if ($back) {
                foreach ($originalContents as $content) {
                    if (false === $page->getPageContents()->contains($content)) {
                        $page->getPageContents()->removeElement($content);
                        $em->remove($content);
                    }
                }       
            }

            // Enable value depending on submitted button
            if ($form->get('save')->isClicked()) {
                
                $enabled = false;
                $status = $em->getRepository(Status::class)->find(1);
                $action = $form->getClickedButton()->getName();
                
                if ($back) {
                    $url = $this->redirectToRoute('back_content_edit', [
                        'locale' => locale_get_default(),
                        'back' => 'back',
                        'id' => $page->getId(),
                    ]);
                } else {
                    $url = $this->redirectToRoute('front_content_edit', [
                        'locale' => locale_get_default(),
                        'id' => $page->getId(),
                    ]);
                }
            } else {
                
                if ($back) {
                    if ($label === 'action.validate') {
                        $status = $em->getRepository(Status::class)->find(3);
                        $enabled = true;
                        $action = 'validate';
                    } else {
                        $status = $em->getRepository(Status::class)->find(intval('-1'));
                        $enabled = false;
                        $action = 'attribute';
                    }
                    $route = 'back_content_search';
                } else {
                    $status = $em->getRepository(Status::class)->find(3);
                    $enabled = false;
                    $action = $form->getClickedButton()->getName();
                    $route = 'user_translations';
                }
                $url = $this->redirectToRoute($route, [
                    '_locale' => locale_get_default(),
                ]);
            }
            $page->setEnabled($enabled);
            $page->setStatus($status);
                
            $em->persist($page);
            
            try {
                // Super visitor filter
                $currentUser = $security->getUser();
                if ($currentUser->hasRole('ROLE_SUPER_VISITOR')) {
                    return $this->redirectToRoute('visitor_denied', [ '_locale' => locale_get_default()]);
                }
                
                $em->flush();

                $msg = $translator
                        ->trans('content.form.'. $action .'.flash.success', [],
                                'back_messages', $locale = locale_get_default());
                $this->addFlash('success', $msg);
                
            } catch (\Doctrine\DBAL\DBALException $e) {
                $this->addFlash('warning', $e->getMessage());
            }
            return $url;
        }
        
        if ($back) {
            return $this->render('back/page/content/edit.html.twig', [
                'form' => $form->createView(),
                'page' => $page,
            ]);
        } else {
            return $this->render('front/translation/page.html.twig', [
                'form' => $form->createView(),
                'page' => $page,
            ]);
        }
    }
}