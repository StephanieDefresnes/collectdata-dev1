<?php

namespace App\Controller\Front;

use App\Entity\Lang;
use App\Entity\Situ;
use App\Entity\Status;
use App\Form\Front\Situ\SituDynamicDataForm;
use App\Form\Front\Situ\SituForm;
use App\Mailer\Mailer;
use App\Manager\Front\SituManager;
use App\Messager\Messager;
use App\Service\SituEditor;
use Doctrine\ORM\EntityManagerInterface;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\IsGranted;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\DependencyInjection\ParameterBag\ParameterBagInterface;
use Symfony\Component\Form\FormError;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Security\Core\Security;
use Symfony\Contracts\Translation\TranslatorInterface;

class SituController extends AbstractController
{
    private $em;
    private $mailer;
    private $messager;
    private $parameters;
    private $security;
    private $situEditor;
    private $situManager;
    private $translator;
    
    public function __construct(EntityManagerInterface $em,
                                Mailer $mailer,
                                Messager $messager,
                                ParameterBagInterface $parameters,
                                Security $security,
                                SituEditor $situEditor,
                                SituManager $situManager, 
                                TranslatorInterface $translator)
    {
        $this->em = $em;
        $this->mailer = $mailer;
        $this->messager = $messager;
        $this->parameters = $parameters;
        $this->security = $security;
        $this->situEditor = $situEditor;
        $this->situManager = $situManager;
        $this->translator = $translator;
        $this->locale = locale_get_default();
    }
    
    /**
     * All situs list
     * 
     * @return Response
     */
    public function search(): Response
    {
        return $this->render( 'front/situ/search.html.twig', [
            'controller_name' => 'SituController',
        ] );
    }
    
    /**
     * User situs list
     * 
     * @IsGranted("IS_AUTHENTICATED_FULLY")
     * @IsGranted("ROLE_CONTRIBUTOR")
     */
    public function userSitus()
    {
        return $this->render( 'front/situ/user.html.twig' );
    }
    
    /**
     * Create / edit situ
     * 
     * @IsGranted("IS_AUTHENTICATED_FULLY")
     * @IsGranted("ROLE_CONTRIBUTOR")
     */
    public function create( Request $request, $id ): Response
    {
        $defaultLang = $this->em->getRepository(Lang::class)
                ->findOneBy([ 'lang' => $this->locale ])
                ->getId();
        
        if ( $id ) {
            
            $situ = $this->em->getRepository(Situ::class)->find($id);
        
            if ( ! $situ ) {
                throw $this->createNotFoundException();
            }
        
            // Check permission
            $this->denyAccessUnlessGranted('create_situ', $situ);
            
            // Check if situ lang is in user langs
            $allowResult = $this->situManager->allowEdit( $situ );
            
            if ( true !== $allowResult ) return $this->redirect( $allowResult );
            
        } else {
            $situ = new Situ();
        }
        
        // Situ entity form
        $form = $this->createForm( SituForm::class, $situ );
        $form->handleRequest($request);

        // Select, create, edit mapped data
        $currentUser = $this->security->getUser();
        $formData = $this->createForm( SituDynamicDataForm::class, $situ, ['user' => $currentUser] );
        $formData->handleRequest($request);
        
        /**
         * isSubmitted() method is used by dynamics fields
         * so if user really submits form, isClicked() method is used with buttonType
         */
        if ( $form->get('save')->isClicked() || $form->get('submit')->isClicked() ) {
            
            $formSituData = $form->getViewData();
            $validatedForm = $this->situManager->validationForm( $formSituData );
            
            if ( $validatedForm ) {
                $url = $this->situEditor->setSitu( $formSituData, $request );
                return $this->redirect($url);
            }
            
            $form->addError( new FormError($result) );
        }
        
        return $this->render( 'front/situ/new/create.html.twig', [
            'defaultLang'   => $defaultLang,
            'form'          => $form->createView(),
            'formData'      => $formData->createView(),
            'langs'         => count($currentUser->getLangs()),
            'situ'          => $situ,
        ] );
    }
    
    /**
     * Delete User
     * 
     * @IsGranted("IS_AUTHENTICATED_FULLY")
     * @IsGranted("ROLE_CONTRIBUTOR")
     * 
     * @param Situ $situ
     * @return Response
     */
    public function delete( Situ $situ ): Response
    {
        // Check permission
        $this->denyAccessUnlessGranted( 'delete_situ', $situ );
            
        $situ->setDateDeletion( new \DateTime('now') );
        $situ->setStatus( $this->em->getRepository(Status::class)->find(5) );
        $this->em->persist( $situ );
        
        try {
            $this->em->flush();

            $msg = $this->translator->trans(
                    'contrib.delete.success', [], 'user_messages', $this->locale
                );
            $this->addFlash('success', $msg);

            return $this->redirectToRoute( 'user_situs', ['_locale' => $this->locale] );

        } catch ( \Doctrine\DBAL\DBALException $e ) {
            
            $msg = $this->translator->trans(
                    'contrib.delete.error', [], 'user_messages', $this->locale
                );
            $this->addFlash( 'warning', $msg ."\n". $e->getMessage() );
        }
        
        return $this->redirectToRoute( 'user_situs', [ '_locale' => $this->locale ] );
    }
    
    /**
     * Read validated situ, or on validation in preview mode
     * 
     * @param type $slug
     * @param type $preview
     * @return Response
     * @throws type
     */
    public function read( $slug, $preview ): Response
    {   
        $situ = $this->em->getRepository(Situ::class)->findOneBy(['slug' => $slug]);
        
        if ( ! $situ ) {
            throw $this->createNotFoundException();
        }
        
        // Check permission
        $subject = ['situ' => $situ, 'preview' => $preview];
        $this->denyAccessUnlessGranted( 'read_situ', $subject );
        
        return $this->render( 'front/situ/read.html.twig', [ 'situ' => $situ ] );
    }

    /**
     * Translate situ
     * 
     * @IsGranted("IS_AUTHENTICATED_FULLY")
     * @IsGranted("ROLE_CONTRIBUTOR")
     */
    public function translate( Request $request, $situId, $langId ): Response
    {
        $situ = new Situ();

        $situToTranslate = $this->em->getRepository(Situ::class)->find($situId);
        
        if ( ! $situToTranslate ) throw $this->createNotFoundException();
        
        $defaultLang = $this->em->getRepository(Lang::class)
                ->findOneBy(['lang' => $this->parameters->get('locale')])
                ->getId();
        
        // Current user langs
        $langs = $this->security->getUser()->getLangs()->getValues();
        
        // Situ to translate
        $situData = $this->em->getRepository(Situ::class)->find($situId);
        
        // Translation lang
        $lang = $this->em->getRepository(Lang::class)->find($langId);
        
        if ( ! $lang->getEnabled() ) throw $this->createNotFoundException();
        
        // Check permission
        $subject = [ 'situ' => $situData, 'lang' => $lang ];
        $this->denyAccessUnlessGranted( 'translate_situ', $subject );
        
        
        // Situ entity form
        $form = $this->createForm( SituForm::class, $situ );
        $form->handleRequest($request);
        
        // Relation entities form
        $formData = $this->createForm( SituDynamicDataForm::class, $situ, [
            'user' => $this->security->getUser()
        ] );
        $formData->handleRequest($request);
        
        /**
         * isSubmitted() method is used by dynamics fields
         * So when user really submits form, we use isClicked() method
         * to get data requested
         */
        if ( $form->get('save')->isClicked() || $form->get('submit')->isClicked() ) {
            
            $formSituData = $form->getViewData();
            $result = $this->situManager->validationForm( $formSituData );
            
            if ( $result ) {
                $url = $this->situEditor->setSitu( $formSituData, $request, $situId );
                return $this->redirect($url);
            }
            
            $form->addError(new FormError($result));
        }
        
        return $this->render('front/situ/new/translate.html.twig', [
            'defaultLang'   => $defaultLang,
            'form'          => $form->createView(),
            'formData'      => $formData->createView(),
            'lang'          => $lang,
            'langs'         => $langs,
            'situ'          => $situ,
            'situData'      => $situData,
        ]);
    }
    
    /**
     * Request situ validation
     * 
     * @IsGranted("IS_AUTHENTICATED_FULLY")
     * @IsGranted("ROLE_CONTRIBUTOR")
     */
    public function validation( Situ $situ ): Response
    {
        if ( ! $situ ) throw $this->createNotFoundException();
        
        // Check permission
        $this->denyAccessUnlessGranted( 'validation_situ', $situ );
        
        $situ->setDateSubmission( new \DateTime('now') );
        $situ->setStatus( $this->em->getRepository(Status::class)->find(2) );
        $this->em->persist($situ);
            
        try {
            $this->em->flush();

            $this->mailer->sendModeratorSituValidate( $situ );
            $this->messager->sendModeratorAlert( 'submission', 'situ', $situ );
            
            $msg =  $this->translator->trans(
                        'contrib.form.submit.flash.success', [], 'user_messages', $this->locale
                    );
            $this->addFlash( 'success', $msg );
            
        } catch ( \Doctrine\DBAL\DBALException $e ) {
            
            $msg =  $this->translator->trans(
                        'contrib.form.submit.flash.error', [], 'user_messages', $this->locale
                    );
            $this->addFlash( 'warning', $msg ."\n". $e->getMessage() );
        }
        return $this->redirectToRoute( 'user_situs', [ '_locale' => $this->locale ] );
    }
    
    /**
     * Search for any situ translation in the selected language
     * 
     * @IsGranted("IS_AUTHENTICATED_FULLY")
     * @IsGranted("ROLE_CONTRIBUTOR")
     */
    public function ajaxFindTranslation( Request $request )
    {
        // Situ to translate
        $situId = $request->query->get('situId');
        $situ = $this->em->getRepository(Situ::class)->find($situId);
        
        // Translation lang
        $langId = $request->query->get('langId');
        $lang = $this->em->getRepository(Lang::class)->find($langId);
        
        if ( $lang === $situ->getLang() || ! $lang->getEnabled() ) {
            // If lang is Lang situ to translate or wanted lang is not enabled
            // (if user tries to tamper with the form)
            return new JsonResponse([
                'success' => false,
                'redirect' => $this->generateUrl( 'access_denied', [
                                    '_locale' => $this->locale,
                                ] )
            ]);
        }
        
        $situTranslated = $this->em->getRepository(Situ::class)
                            ->findTranslations($situId, $langId);
        
        return new JsonResponse([
            'success' => true,
            'situTranslated' => $situTranslated,
        ]);
    }
    
}