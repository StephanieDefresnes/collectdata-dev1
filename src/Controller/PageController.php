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
use Symfony\Component\Security\Core\Security;
use Symfony\Component\String\Slugger\AsciiSlugger;
use Symfony\Component\String\Slugger\SluggerInterface;
use Symfony\Contracts\Translation\TranslatorInterface;

/**
 * @IsGranted("IS_AUTHENTICATED_FULLY")
 */
class PageController extends AbstractController
{
    /**
     * Back and Front use this page with include
     * And provides different data depending on the context :
     *      - $label for label of submit button "action"
     *      - $action for flash message
     *      - $referentPage for lang contributor in front context
     *      - $route & $url for redirect
     *      - $users in back context: when user can't translate into lang page,
     *      he attributes it to lang contributor
     * 
     * Status values:
     *      - '-1': new, for lang contributor in front
     *      - 1:    on writing
     *      - 2:    submitted to validation, for lang contributor in front
     *      - 3:    validated
     * 
     * @param type $_locale
     * @param type $id
     * @param type $back : front context if empty
     * @return Response
     */
    public function contentEdit(EntityManagerInterface $em,
                                LangService $langService,
                                Request $request,
                                Security $security,
                                SluggerInterface $slugger,
                                TranslatorInterface $translator,
                                UserService $userService,
                                $_locale, $id, $back = null): Response
    {
        $label = 'action.submit';
        $users = [];
        $action = 'submit';
        $referentPage;
        
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
                
                // Array of users to attribute translation page in form
                $users = $userService->getUsersLangContributorByLang($langPage);
                
            } else {
                $referentPage = $this->getDoctrine()->getRepository(Page::class)
                        ->findOneBy([
                            'type' => $page->getType(),
                            'lang' => locale_get_default(),
                        ]);
            
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
            
            // Slug title
            $slugger = new AsciiSlugger();
            $page->setSlug($slugger->slug($form->get('title')->getData()));
            
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
                    $page->setUser(null);
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
                    $status = $em->getRepository(Status::class)->find(2);
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
                if ($back && $currentUser->hasRole('ROLE_SUPER_VISITOR')) {
                    return $this->redirectToRoute('back_access_denied', [
                        '_locale' => locale_get_default()
                    ]);
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
                'referentPage' => $referentPage,
            ]);
        }
    }
}