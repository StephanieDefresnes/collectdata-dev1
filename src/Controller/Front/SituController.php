<?php

namespace App\Controller\Front;

use App\Entity\Lang;
use App\Entity\Situ;
use App\Entity\Status;
use App\Form\Front\Situ\SituFormType;
use App\Mailer\Mailer;
use App\Manager\Front\SituManager;
use App\Messenger\Messenger;
use App\Service\SituEditor;
use Doctrine\ORM\EntityManagerInterface;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\IsGranted;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\DependencyInjection\ParameterBag\ParameterBagInterface;
use Symfony\Component\Form\FormError;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Core\Security;
use Symfony\Contracts\Translation\TranslatorInterface;

/**
 * @Route("/{_locale<%app_locales%>}")
 */
class SituController extends AbstractController
{
    private $em;
    private $mailer;
    private $messenger;
    private $parameters;
    private $security;
    private $situEditor;
    private $situManager;
    private $translator;
    
    public function __construct(EntityManagerInterface $em,
                                Mailer $mailer,
                                Messenger $messenger,
                                ParameterBagInterface $parameters,
                                Security $security,
                                SituEditor $situEditor,
                                SituManager $situManager, 
                                TranslatorInterface $translator)
    {
        $this->em = $em;
        $this->mailer = $mailer;
        $this->messenger = $messenger;
        $this->parameters = $parameters;
        $this->security = $security;
        $this->situEditor = $situEditor;
        $this->situManager = $situManager;
        $this->translator = $translator;
    }
    
    /**
     * @Route("/contribs", name="all_situs")
     */
    public function index(): Response
    {
        return $this->render('front/situ/index.html.twig', [
            'controller_name' => 'SituController',
        ]);
    }
    
    /**
     * @IsGranted("IS_AUTHENTICATED_FULLY")
     * @IsGranted("ROLE_CONTRIBUTOR")
     * @Route("/my-contribs", name="user_situs", methods="GET")
     */
    public function getUserSitus()
    {
        return $this->render('front/situ/user.html.twig');
    }
    
    /**
     * @Route("/read/{situ}/{preview}", defaults={"preview" = null}, name="read_situ", methods="GET")
     */
    public function read(Situ $situ, $preview): Response
    {   
        // Check permission
        $subject = ['situ' => $situ, 'preview' => $preview];
        $this->denyAccessUnlessGranted('read_situ', $subject);
        
        return $this->render('front/situ/read.html.twig', [
            'situ' => $situ,
        ]);
    }
    
    /**
     * @IsGranted("IS_AUTHENTICATED_FULLY")
     * @IsGranted("ROLE_CONTRIBUTOR")
     * @Route("/validation/{situ}", name="validation_situ", methods="GET|POST")
     */
    function validation(Situ $situ): Response
    {
        // Check permission
        $this->denyAccessUnlessGranted('validation_situ', $situ);
        
        $situ->setDateSubmission(new \DateTime('now'));
        $situ->setStatus($this->em->getRepository(Status::class)->find(2));
        $this->em->persist($situ);
            
        try {
            $this->em->flush();

            $this->mailer->sendModeratorSituValidate($situ);
            $this->messenger->sendModeratorAlert('situ', $situ);
            
            $msg = $this->translator->trans(
                    'contrib.form.submit.flash.success', [],
                    'user_messages', $locale = locale_get_default()
                );
            $this->addFlash('success', $msg);
            
        } catch (\Doctrine\DBAL\DBALException $e) {
            
            $msg = $this->translator->trans(
                    'contrib.form.submit.flash.error', [],
                    'user_messages', $locale = locale_get_default()
                );
            $this->addFlash('warning', $msg.PHP_EOL.$e->getMessage());
        }
        return $this->redirectToRoute('user_situs', ['_locale' => locale_get_default()]);
    }
    
    /**
     * @IsGranted("IS_AUTHENTICATED_FULLY")
     * @IsGranted("ROLE_CONTRIBUTOR")
     * @Route("/delete/{situ}", name="delete_situ", methods="GET|POST")
     */
    function delete(Situ $situ): Response
    {        
        // Check permission
        $this->denyAccessUnlessGranted('delete_situ', $situ);
            
        $situ->setDateDeletion(new \DateTime('now'));
        $situ->setStatus($this->em->getRepository(Status::class)->find(5));
        $this->em->persist($situ);
        
        try {
            $this->em->flush();

            $msg = $this->translator->trans(
                    'contrib.delete.success', [],
                    'user_messages', $locale = locale_get_default()
                );
            $this->addFlash('success', $msg);

            return $this->redirectToRoute('user_situs', ['_locale' => locale_get_default()]);

        } catch (\Doctrine\DBAL\DBALException $e) {
            
            $msg = $this->translator->trans(
                    'contrib.delete.error', [],
                    'user_messages', $locale = locale_get_default()
                );
            $this->addFlash('warning', $msg.PHP_EOL.$e->getMessage());
        }
        
        return $this->redirectToRoute('user_situs', ['_locale' => locale_get_default()]);
    }
    
    /**
     * @IsGranted("IS_AUTHENTICATED_FULLY")
     * @IsGranted("ROLE_CONTRIBUTOR")
     * @Route("/contrib/{id}", defaults={"id" = null}, name="create_situ", methods="GET|POST")
     */
    public function create(Request $request, $id): Response
    {
        $defaultLang = $this->em->getRepository(Lang::class)
                ->findOneBy(['lang' => $this->parameters->get('locale')])
                ->getId();
        
        // Current user
        $user = $this->security->getUser();
        $langs = $user->getLangs()->getValues();
                
        if ($id) {
            
            $situ = $this->em->getRepository(Situ::class)->find($id);
        
            // Check permission
            $this->denyAccessUnlessGranted('create_situ', $situ);
            
            // If validation requested return to preview
            if ($situ->getStatus()->getId() === 2) {
                return $this->redirectToRoute('read_situ', [
                    '_locale' => locale_get_default(),
                    'situ' => $situ->getId(),
                    'p' => 'preview'
                ]);
            }
            
        } else {
            $situ = new Situ();
        }
        
        // Form
        $form = $this->createForm(SituFormType::class, $situ);
        $form->handleRequest($request);
        
        /**
         * isSubmitted() method is used by dynamics fields
         * So when user really submits form, we use isClicked() method
         * to get data requested
         */
        if ($form->get('save')->isClicked() || $form->get('submit')->isClicked()) {
            
            $result = $this->situManager->validationForm($request);
            
            if (true !== $result) {
                $form->addError(new FormError($result));
            } else {
                $url = $this->situEditor->setSitu($form, $request, $id);
                return $this->redirect($url);
            }
        }
        
        return $this->render('front/situ/new/create.html.twig', [
            'defaultLang' => $defaultLang,
            'form' => $form->createView(),
            'langs' => $langs,
            'situ' => $situ,
        ]);
    }
    

    /**
     * @IsGranted("IS_AUTHENTICATED_FULLY")
     * @IsGranted("ROLE_CONTRIBUTOR")
     * @Route("/translate/{situId}/{langId}", name="translate_situ", methods="GET|POST")
     */
    public function translate(Request $request, $situId, $langId): Response
    {        
        $defaultLang = $this->em->getRepository(Lang::class)
                ->findOneBy(['lang' => $this->parameters->get('locale')])
                ->getId();
        
        // Current user langs
        $langs = $this->security->getUser()->getLangs()->getValues();
        
        // Situ to translate
        $situData = $this->em->getRepository(Situ::class)->find($situId);
        
        // Translation lang
        $lang = $this->em->getRepository(Lang::class)->find($langId);
        
        // Check permission
        $subject = ['situ' => $situData, 'lang' => $lang];
        $this->denyAccessUnlessGranted('translate_situ', $subject);
        
        // Form
        $situ = new Situ();
        $form = $this->createForm(SituFormType::class, $situ);
        $form->handleRequest($request);
        
        /**
         * isSubmitted() method is used by dynamics fields
         * So when user really submits form, we use isClicked() method
         * to get data requested
         */
        if ($form->get('save')->isClicked() || $form->get('submit')->isClicked()) {
            
            $result = $this->situManager->validationForm($request);
            
            if (true !== $result) {
                $form->addError(new FormError($result));
            } else {
                $url = $this->situEditor->setSitu($form, $request, $id);
                return $this->redirect($url);
            }
        }
        
        return $this->render('front/situ/new/translate.html.twig', [
            'defaultLang' => $defaultLang,
            'form' => $form->createView(),
            'lang' => $lang,
            'langs' => $langs,
            'situ' => $situ,
            'situData' => $situData,
        ]);
    }
    
    /**
     * Search for any translation in the selected language
     * 
     * @IsGranted("IS_AUTHENTICATED_FULLY")
     * @IsGranted("ROLE_CONTRIBUTOR")
     * @Route("/situ/ajaxFindTranslation", name="situ_find_translation", methods="GET")
     */
    public function ajaxFindTranslation(Request $request)
    {
        // Situ to translate
        $situId = $request->query->get('situId');
        $situ = $this->em->getRepository(Situ::class)->find($situId);
        
        // Translation lang
        $langId = $request->query->get('langId');
        $lang = $this->em->getRepository(Lang::class)->find($langId);
        
        if ($lang === $situ->getLang() || false === $lang->getEnabled()) {
            // If lang is Lang situ to translate or wanted lang is not enabled
            // (if the user tries to tamper with the form)
            return new JsonResponse([
                'success' => false,
                'redirect' => $this->generateUrl('access_denied', [
                                    '_locale' => locale_get_default(),
                                ])
            ]);
        } else {
            $situTranslated = $this->em->getRepository(Situ::class)
                                ->findTranslations($situId, $langId);
            return new JsonResponse([
                'success'  => true,
                'situTranslated' => $situTranslated,
            ]);
        }
    }
    
}