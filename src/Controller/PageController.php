<?php

namespace App\Controller;

use App\Entity\Lang;
use App\Entity\Page;
use App\Entity\Status;
use App\Entity\User;
use App\Form\Page\PageFormType;
use App\Manager\PageManager;
use App\Manager\Back\UserManager;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\ORM\EntityManagerInterface;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\IsGranted;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Security\Core\Security;
use Symfony\Contracts\Translation\TranslatorInterface;

/**
 * @IsGranted("IS_AUTHENTICATED_FULLY")
 */
class PageController extends AbstractController
{
    /**
     * Back and Front use this page with include
     * And provides different data depending on the context :
     *      - $action           = flash message var
     *      - $label            = form button label
     *      - $referentPage     = front context : locale content to translate
     *      - $route & $params  = redirectToRoute params
     *      - $template         = depending on context
     *      - $users            = back context :
     *                                  if page lang is not a current user lang,
     *                                  user attributes page to a contributor user
     * 
     * Status values:
     *      - '-1'  = new : content attributed to langContributor user
     *      - 1     = on writing
     *      - 2     = submitted to validation : for langContributor user
     *      - 3     = validated
     * 
     * @param type $_locale
     * @param type $id
     * @param type $back : front context if empty
     * @return Response
     */
    public function contentEdit(EntityManagerInterface $em,
                                PageManager $pageManager,
                                Request $request,
                                Security $security,
                                TranslatorInterface $translator,
                                UrlGeneratorInterface $urlGenerator,
                                UserManager $userManager,
                                $_locale, $id, $back = null): Response
    {
        if ( $id ) {
            $page = $em->getRepository(Page::class)->find($id);
            
            if ( $back && !$page ) {
                return $this->redirectToRoute('back_not_found', [
                    '_locale' => locale_get_default()
                ]);
            }
        } else $page = new Page();
        
        $action         = $back ? 'validate' : 'submit';
        $label          = 'action.'. $action;
        $referentPage   = '';
        $template       = $back ? 'back/page/content/edit.html.twig'
                                : 'front/translation/page.html.twig';
        $users          = [];
        
        if ( $id ) {
            $user           = $security->getUser();
            $isUserLang     = $pageManager->checkUserLangs(
                                        $user->getLangs()->getValues(),
                                        $user->getContributorLangs()->getValues(),
                                        $page
                                    );
            $label          = $isUserLang   ? 'action.validate'
                                            : 'action.attribute';
            
            $langPage = $em->getRepository(Lang::class)
                                ->findOneBy(['lang' => $page->getLang()]);
            
            // Get langContributor users for current page lang
            if ( $back ) $users = $em->getRepository(User::class)
                                    ->findLangContributors($langPage);
            
            // Get current lang page for front context
            $referentPage   = $back ? $referentPage
                                    : $em->getRepository(Page::class)
                                                ->findOneBy([
                                                    'type' => $page->getType(),
                                                    'lang' => locale_get_default(),
                                                ]);
        }
        
        // Get contents collection
        $originalContents = new ArrayCollection();
        foreach ($page->getPageContents() as $content) {
            $originalContents->add($content);
        }
            
        // Prevent SUPER_VISITOR flush
        $preventSV          = $userManager->preventSuperVisitor( $back );
//        $preventFrontUrl    = $urlGenerator->generate('error_403', [
//                                    '_locale' => locale_get_default()
//                                ]);
//        $preventUrl         = $back ? $preventSV : $preventFrontUrl;
        
        // Form
        $form = $this->createForm(PageFormType::class, $page, [
                'back'  => $back,
                'label' => $label,
                'users' => $users,
            ]);
        $form->handleRequest($request);
        
        if ( $form->isSubmitted() && $form->isValid() ) {
            
//            $userManager->preventSuperVisitor( $back );
            if ( $preventSV ) return $this->redirect( $preventSV );

            // Set value depending on submitted button & back
            $isSaved    = $form->get('save')->isClicked();
            
            $enabled    = $isSaved  ? false
                                    : ( $back   ? ( 'action.validate' === $label
                                                    ? true : false )
                                                : false );
            
            $statusId   = $isSaved  ? 1
                                    : ( $back   ? ( 'action.validate' === $label
                                                    ? 3 : intval('-1') )
                                                : 2 );
            $status     = $em->getRepository(Status::class)->find($statusId);
            
            $page->setEnabled($enabled);
            $page->setStatus($status);
            
            // Update collections
            foreach ($originalContents as $content) {
                if ( $back && ! $page->getPageContents()->contains($content) ) {
                    $page->getPageContents()->removeElement($content);
                    $em->remove($content);
                }
            }
                
            $em->persist($page);
            
            // Set translation string var depending on context and clicked button
            $buttonName = $form->getClickedButton()->getName();
            $action = $isSaved  ? $buttonName
                                : ( $back   ? ( 'action.validate' === $label
                                                ? 'validate' : 'attribute' )
                                            : $buttonName );
            
            // Set route depending on context and clicked button
            $route  = $isSaved  ? ( $back   ? 'back_content_edit'
                                        : 'front_content_edit' )
                                : ( $back   ? 'back_content_search'
                                            : 'user_translations' );

            try {                
                $em->flush();

                $msg = $translator
                        ->trans('content.form.'. $action .'.flash.success', [],
                                'back_messages', locale_get_default());
                $this->addFlash('success', $msg);
                
                // Set params depending on context and clicked button
                $params     = $isSaved  ? ( $back
                                            ? [ '_locale' => locale_get_default(),
                                                'back' => 'back',
                                                'id' => $page->getId() ]
                                            : [ '_locale' => locale_get_default(),
                                                'id' => $page->getId() ] )
                                        : [ '_locale' => locale_get_default() ];
                
                return $this->redirectToRoute( $route, $params );
                
            } catch (\Doctrine\DBAL\DBALException $e) {
                $this->addFlash('warning', $e->getMessage());
                
                return $this->redirect($request->getUri());
            }
        }
        
        return $this->render($template, [
            'form'          => $form->createView(),
            'page'          => $page,
            'referentPage'  => $referentPage,
        ]);
    }
}