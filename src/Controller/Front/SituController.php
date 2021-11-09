<?php

namespace App\Controller\Front;

use App\Entity\Lang;
use App\Entity\Situ;
use App\Entity\Status;
use App\Form\Front\Situ\SituFormType;
use App\Mailer\Mailer;
use App\Manager\Front\SituManager;
use App\Messenger\Messenger;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\ORM\EntityManagerInterface;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\IsGranted;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\DependencyInjection\ParameterBag\ParameterBagInterface;
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
    private $situManager;
    private $translator;
    
    public function __construct(EntityManagerInterface $em,
                                Mailer $mailer,
                                Messenger $messenger,
                                ParameterBagInterface $parameters,
                                Security $security,
                                SituManager $situManager, 
                                TranslatorInterface $translator)
    {
        $this->em = $em;
        $this->mailer = $mailer;
        $this->messenger = $messenger;
        $this->parameters = $parameters;
        $this->security = $security;
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
     * @Route("/read/{situ}", name="read_situ", methods="GET")
     */
    public function readSitu(Situ $situ): Response
    {
        $notFoundRoute = $this->redirectToRoute('not_found', ['_locale' => locale_get_default()]);
        
        if (!$situ) return $notFoundRoute;
        
        // Only user can read a contribution requested to validate (with preview mode)
        if ($situ->getStatus()->getId() === 2 && isset($_GET['preview'])) {
            if (!$this->security->getUser()) return $notFoundRoute;
        }
        // None can read a contribution except validated
        else if ($situ->getStatus()->getId() !== 3) return $notFoundRoute;
        
        return $this->render('front/situ/read.html.twig', [
            'situ' => $situ,
        ]);
    }
    
    /**
     * @IsGranted("IS_AUTHENTICATED_FULLY")
     * @IsGranted("ROLE_CONTRIBUTOR")
     * @Route("/validation/{situ}", name="validation_situ_request", methods="GET|POST")
     */
    function validationSituRequest(Situ $situ): Response
    {
        // Current user
        $user = $this->security->getUser();
        
        // Only situ author can request situ validation 
        if ($user !== $situ->getUser()) {
            return $this->redirectToRoute('access_denied', [
                '_locale' => locale_get_default(),
            ]);
        }
        
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
    function deleteSitu(Situ $situ): Response
    {
        // Current user
        $user = $this->security->getUser();
        
        // Only situ author can delete situ
        if ($user->getId() !== $situ->getUser()) {
            return $this->redirectToRoute('access_denied', [
                '_locale' => locale_get_default(),
            ]);
        }
            
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
    public function createSitu(Request $request, $id): Response
    {
        $defaultLang = $this->em->getRepository(Lang::class)
                ->findOneBy(['lang' => $this->parameters->get('locale')])
                ->getId();
        
        // Current user
        $user = $this->security->getUser();
        $langs = $user->getLangs()->getValues();
                
        // Update or Create new Situ
        if ($id) {
            
            $situ = $this->em->getRepository(Situ::class)->find($id);
        
            // Only situ author can update situ
            if (!$situ) {
                return $this->redirectToRoute('not_found', ['_locale' => locale_get_default()]);
            }
            
            // If validation requested return to preview
            if ($situ->getStatus()->getId() === 2) {
                return $this->redirectToRoute('read_situ', [
                    '_locale' => locale_get_default(),
                    'situ' => $situ->getId(),
                    'preview' => ''
                ]);
            }
        
            // Only situ author can update situ
            if (($situ->getStatus()->getId() === 1 || $situ->getStatus()->getId() === 3)
                    && $situ->getUser() !== $user) {
                return $this->redirectToRoute('access_denied', [
                    '_locale' => locale_get_default(),
                ]);
            }
            
        } else {
            $situ = new Situ();
        }
        
        $originalItems = new ArrayCollection();
        foreach ($situ->getSituItems() as $item) {
            $originalItems->add($item);
        }
        
        $form = $this->createForm(SituFormType::class, $situ);
        $form->handleRequest($request);
        
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
    public function translateSitu(Request $request, $situId, $langId): Response
    {
        $defaultLang = $this->em->getRepository(Lang::class)
                ->findOneBy(['lang' => $this->parameters->get('locale')])
                ->getId();
        
        // Current user langs
        $langs = $this->security->getUser()->getLangs()->getValues();
        
        // Situ to translate
        $situData = $this->em->getRepository(Situ::class)->find($situId);
        
        // Translation lang
        $langData = $this->em->getRepository(Lang::class)->find($langId);
        
        // Form
        $situ = new Situ();
        $formSitu = $this->createForm(SituFormType::class, $situ);
        $formSitu->handleRequest($request);
        
        return $this->render('front/situ/new/translate.html.twig', [
            'defaultLang' => $defaultLang,
            'form' => $formSitu->createView(),
            'lang' => $langData,
            'langs' => $langs,
            'situ' => $situData,
        ]);
    }
    
    /**
     * Situ creation by ajax because of alternative of create event & category
     * instead of choose them with dynamic form events
     * 
     * @IsGranted("IS_AUTHENTICATED_FULLY")
     * @IsGranted("ROLE_CONTRIBUTOR")
     * @Route("/situ/ajaxCreate", methods="GET|POST")
     */
    public function ajaxCreate(Request $request)
    {   
        $result = $this->situManager->setData($request);
        $situ = $result['situ'];
        
        $this->em->persist($situ);
        
        try {       
            $this->em->flush();

            if ($result['update']) { $actionSuccess = 'success_update'; }
            else { $actionSuccess = 'success'; }

            $msg = $this->translator->trans(
                        'contrib.form.'. $result['action'] .'.flash.'. $actionSuccess, [],
                        'user_messages', $locale = locale_get_default()
                        );
            $request->getSession()->getFlashBag()->add('success', $msg);

            return $this->json(['success' => true]);

        } catch (\Doctrine\DBAL\DBALException $e) {
            $msg = $this->translator->trans(
                        'contrib.form.'. $result['action'] .'.flash.error', [],
                        'user_messages', $locale = locale_get_default()
                        );
            $this->addFlash('error', $msg.PHP_EOL.$e->getMessage());

            return $this->json(['success' => false]);
        }
    }
    
    /**
     * Load Category description on select with dynamic form events
     * 
     * @IsGranted("IS_AUTHENTICATED_FULLY")
     * @IsGranted("ROLE_CONTRIBUTOR")
     * @Route("/situ/ajaxGetData", methods="GET|POST")
     */
    public function ajaxGetData(Request $request): JsonResponse
    {
        if ($request->isXMLHttpRequest()) {
            
            $data = $request->request->get('dataForm');
            
            $categoryLevel1Description = isset($data['categoryLevel1'])
                    ? $this->em->getRepository(Category::class)
                        ->find($data['categoryLevel1'])->getDescritption()
                    : '';
            
            $categoryLevel1Description = isset($data['categoryLevel2'])
                    ? $this->em->getRepository(Category::class)
                        ->find($data['categoryLevel2'])->getDescritption()
                    : '';

            return $this->json([
                'success' => true,
                'categoryLevel1' => $categoryLevel1Description,
                'categoryLevel2' => $categoryLevel1Description,
            ]);
        }
    }
    
    /**
     * Search for any translation in the selected language
     * 
     * @IsGranted("IS_AUTHENTICATED_FULLY")
     * @IsGranted("ROLE_CONTRIBUTOR")
     * @Route("/situ/ajaxFindTranslation", methods="GET")
     */
    public function ajaxFindTranslation(Request $request)
    {
        // Situ to translate
        $situId = $request->query->get('id');
        
        // Lang wanted
        $langId = $request->query->get('langId');
        
        $situData = $this->em->getRepository(Situ::class)->find($situId);
        $langData = $this->em->getRepository(Lang::class)->find($langId);
        
        // If wanted lang is Lang situ to translate or wanted lang is not enabled
        if ($langId === $situData->getLang()->getId() || !$langData->getEnabled()) {
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