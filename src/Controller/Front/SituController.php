<?php

namespace App\Controller\Front;

use App\Entity\Situ;
use App\Entity\SituItem;
use App\Entity\Category;
use App\Entity\Event;
use App\Entity\Lang;
use App\Form\Front\Situ\SituFormType;
use App\Mailer\Mailer;
use App\Messenger\Messenger;
use App\Service\CategoryService;
use App\Service\SituService;
use Doctrine\ORM\EntityManagerInterface;
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
    private $translator;
    
    public function __construct(EntityManagerInterface $em,
                                Mailer $mailer,
                                Messenger $messenger,
                                ParameterBagInterface $parameters,
                                Security $security,
                                TranslatorInterface $translator)
    {
        $this->em = $em;
        $this->mailer = $mailer;
        $this->messenger = $messenger;
        $this->parameters = $parameters;
        $this->security = $security;
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
     * @Route("/my-contribs", name="user_situs", methods="GET")
     */
    public function getUserSitus()
    {
        $this->denyAccessUnlessGranted('IS_AUTHENTICATED_FULLY');
        $this->denyAccessUnlessGranted('ROLE_CONTRIBUTOR');
        
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
        if ($situ->getStatusId() == 2 && isset($_GET['preview'])) {
            if (!$this->security->getUser()) return $notFoundRoute;
        }
        // None can read a contribution except validated
        else if ($situ->getStatusId() != 3) return $notFoundRoute;
        
        return $this->render('front/situ/read.html.twig', [
            'situ' => $situ,
        ]);
    }
    
    /**
     * @Route("/validation/{situ}", name="validation_situ_request", methods="GET|POST")
     */
    function validationSituRequest(Situ $situ): Response
    {
        $this->denyAccessUnlessGranted('IS_AUTHENTICATED_FULLY');
        $this->denyAccessUnlessGranted('ROLE_CONTRIBUTOR');
        
        // Current user
        $user = $this->security->getUser();
        
        // Only situ author can request situ validation 
        if ($user != $situ->getUser()) {
            return $this->redirectToRoute('access_denied', [
                '_locale' => locale_get_default(),
                'code' => '22181',
            ]);
        }
        
        $situ->setDateSubmission(new \DateTime('now'));
        $situ->setStatusId(2);
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
     * @Route("/delete/{situ}", name="delete_situ", methods="GET|POST")
     */
    function deleteSitu(Situ $situ): Response
    {
        $this->denyAccessUnlessGranted('IS_AUTHENTICATED_FULLY');
        $this->denyAccessUnlessGranted('ROLE_CONTRIBUTOR');
        
        // Current user
        $user = $this->security->getUser();
        
        // Only situ author can delete situ
        if ($user->getId() != $situ->getUser()) {
            return $this->redirectToRoute('access_denied', [
                '_locale' => locale_get_default(),
                'code' => '4191',
            ]);
        }
            
        $situ->setDateDeletion(new \DateTime('now'));
        $situ->setStatusId(5);
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
     * @Route("/contrib/{id}", defaults={"id" = null}, name="create_situ", methods="GET|POST")
     */
    public function createSitu(Request $request, $id): Response
    {
        $this->denyAccessUnlessGranted('IS_AUTHENTICATED_FULLY');
        $this->denyAccessUnlessGranted('ROLE_CONTRIBUTOR');
        
        
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
            if ($situ->getStatusId() == 2) {
                return $this->redirectToRoute('read_situ', [
                    '_locale' => locale_get_default(),
                    'situ' => $situ->getId(),
                    'preview' => ''
                ]);
            }
        
            // Only situ author can update situ
            if (($situ->getStatusId() == 1 || $situ->getStatusId() == 3)
                    && $situ->getUser() != $user) {
                return $this->redirectToRoute('access_denied', [
                    '_locale' => locale_get_default(),
                    'code' => '21191',
                ]);
            }
            
        } else {
            $situ = new Situ();
        }
        
        $form = $this->createForm(SituFormType::class, $situ);
        $form->handleRequest($request);
                
        return $this->render('front/situ/create/index.html.twig', [
            'form' => $form->createView(),
            'langs' => $langs,
            'situ' => $situ,
            'defaultLang' => $defaultLang,
        ]);
    }

    /**
     * @Route("/translate/{situId}/{langId}", name="translate_situ", methods="GET|POST")
     */
    public function translateSitu(Request $request, $situId, $langId): Response
    {
        $this->denyAccessUnlessGranted('IS_AUTHENTICATED_FULLY');
        $this->denyAccessUnlessGranted('ROLE_CONTRIBUTOR');
        
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
        
        return $this->render('front/situ/translation/index.html.twig', [
            'form' => $formSitu->createView(),
            'langs' => $langs,
            'situ' => $situData,
            'lang' => $langData,
            'defaultLang' => $defaultLang,
        ]);
    }
    
    /**
     * Situ creation by ajax because of alternative of create event & category
     * instead of choose them with dynamic form events
     * 
     * @Route("/situ/ajaxCreate", methods="GET|POST")
     */
    public function ajaxCreate(Request $request)
    {
        $this->denyAccessUnlessGranted('ROLE_CONTRIBUTOR');
            
        // Get current user
        $user = $this->security->getUser();
        
        $defaultLang = $this->em->getRepository(Lang::class)
                ->findOneBy(['lang' => $this->parameters->get('locale')]);
        
        $userLang = $user->getLang();
            
        // Get request data        
        if ($request->isXMLHttpRequest()) {
            
            $data = $request->request->get('dataForm');
        
            $landId = isset($data['lang']) ? $data['lang'] : $userLang;

            $langData = $this->em->getRepository(Lang::class)->find($landId);

            if (!$langData->getEnabled()) {
                return $this->redirectToRoute('access_denied', [
                    '_locale' => locale_get_default(),
                    'code' => '1912',
                ]);      
            }

            $eventData = $this->createOrChooseData(
                    $data['event'], 'event', $langData, '', $user
            );
            $categoryLevel1 = $this->createOrChooseData(
                    $data['categoryLevel1'], 'categoryLevel1', $langData,
                    $eventData, $user
            );
            $categoryLevel2 = $this->createOrChooseData(
                    $data['categoryLevel2'], 'categoryLevel2', $langData,
                    $categoryLevel1, $user
            );

            $statusId = $data['statusId'];
            $dateNow = new \DateTime('now');

            // Update or create Situ
            if (empty($data['id'])) {
                $situ = new Situ();
                $situ->setDateCreation($dateNow);
                $situ->setUser($user); 
            } else {
                $situ = $this->em->getRepository(Situ::class)->find($data['id']);

                // Only situ author can update situ
                if ($user != $situ->getUser()) {
                    return $this->redirectToRoute('access_denied', [
                        '_locale' => locale_get_default(),
                        'code' => '21191',
                    ]);              
                }

                $situ->setDateLastUpdate($dateNow);

                // Clear original collection
                foreach ($situ->getSituItems() as $item) {
                    $situ->getSituItems()->removeElement($item);
                    $this->em->remove($item);
                }
            }

            if (!empty($data['translatedSituId'])) {
                $situ->setInitialSitu(false);
                $situ->setTranslatedSituId($data['translatedSituId']);
            } else {
                $situ->setInitialSitu(true);
            }

            $situ->setTitle($data['title']);
            $situ->setDescription($data['description']);

            // Depending on the button save (val = 1) or submit (val = 2) clicked
            if ($statusId == 2) {
                $situ->setDateSubmission($dateNow);
                $msgAction = 'submit';
            } else {
                $situ->setDateSubmission(null);
                $msgAction = 'save';
            }

            $situ->setDateValidation(null); 
            $situ->setLang($langData);
            $situ->setEvent($eventData);
            $situ->setCategoryLevel1($categoryLevel1);
            $situ->setCategoryLevel2($categoryLevel2);
            $situ->setStatusId($statusId);
            $this->em->persist($situ);

            // Add new collection
            foreach ($data['situItems'] as $key => $dataItem) {
                $situItem = new SituItem();
                if ($key == 0) $situItem->setScore(0);
                else $situItem->setScore($dataItem['score']);
                $situItem->setTitle($dataItem['title']);
                $situItem->setDescription($dataItem['description']);
                $this->em->persist($situItem);
                $situItem->setSitu($situ);
            }

            try {
                $this->em->flush();

                if ($statusId == 2) {
                    $this->mailer->sendModeratorSituValidate($situ);
                    $this->messenger->sendModeratorAlert('situ', $situ);
                
                    $msg = $this->translator->trans(
                                'contrib.form.'. $msgAction .'.flash.success', [],
                                'user_messages', $locale = locale_get_default()
                                );
                } else {
                    $msg = $this->translator->trans(
                                'contrib.form.'. $msgAction .'.flash.success_update', [],
                                'user_messages', $locale = locale_get_default()
                                );
                }
                $request->getSession()->getFlashBag()->add('success', $msg);

                return $this->json(['success' => true]);

            } catch (\Doctrine\DBAL\DBALException $e) {
                $msg = $this->translator->trans(
                            'contrib.form.'. $msgAction .'.flash.error', [],
                            'user_messages', $locale = locale_get_default()
                            );
                $this->addFlash('error', $msg.PHP_EOL.$e->getMessage());
                
                return $this->json(['success' => false]);
            }
        }
    }
    
    /**
     * Load data depending on selection or creation
     * Used by ajaxSitu()
     */
    public function createOrChooseData($dataEntity, $entity, $lang, $parent, $user)
    {        
        if (is_array($dataEntity)) {
            switch ($entity) {
                case 'event':
                    $data = new Event();
                    break;
                case 'categoryLevel1':
                    $data = new Category();
                    $data->setDateCreation(new \DateTime('now'));
                    $data->setDescription($dataEntity['description']);
                    $data->setEvent($parent);
                    break;
                case 'categoryLevel2':
                    $data = new Category();
                    $data->setDateCreation(new \DateTime('now'));
                    $data->setDescription($dataEntity['description']);
                    $data->setParent($parent);
                    break;
            }
            $data->setTitle($dataEntity['title']);
            $data->setUser($user);
            $data->setValidated(0);
            $data->setLang($lang);
            $this->em->persist($data);
        } else {
            switch ($entity) {
                case 'event':
                    $data = $this->em->getRepository(Event::class)->find($dataEntity);
                    break;
                case 'categoryLevel1':
                    $data = $this->em->getRepository(Category::class)->find($dataEntity);
                    break;
                case 'categoryLevel2':
                    $data = $this->em->getRepository(Category::class)->find($dataEntity);
                    break;
            }
        }
        return $data;
    }
    
    /**
     * Load Category description on select with dynamic form events
     * 
     * @Route("/situ/ajaxGetData", methods="GET|POST")
     */
    public function ajaxGetData(CategoryService $categoryService,
                                Request $request): JsonResponse
    {
        if ($request->isXMLHttpRequest()) {
            
            $data = $request->request->get('dataForm');
            
            $categoryLevel1 = isset($data['categoryLevel1'])
                    ? $categoryService->getDescriptionById($data['categoryLevel1']) : '';
            
            $categoryLevel2 = isset($data['categoryLevel2'])
                    ? $categoryService->getDescriptionById($data['categoryLevel2']) : '';

            return $this->json([
                'success' => true,
                'categoryLevel1' => $categoryLevel1,
                'categoryLevel2' => $categoryLevel2,
            ]);
        }
    }
    
    /**
     * Search for any translation in the selected language
     * 
     * @Route("/situ/ajaxFindTranslation", methods="GET")
     */
    public function ajaxFindTranslation(SituService $situService, Request $request)
    {
        $this->denyAccessUnlessGranted('ROLE_CONTRIBUTOR');
        
        // Situ to translate
        $situId = $request->query->get('id');
        
        // Lang wanted
        $langId = $request->query->get('langId');
        
        $situData = $this->em->getRepository(Situ::class)->find($situId);
        $langData = $this->em->getRepository(Lang::class)->find($langId);
        
        // If wanted lang is Lang situ to translate or wanted lang is not enabled
        if ($langId == $situData->getLang()->getId() || !$langData->getEnabled()) {
            return new JsonResponse([
                'success' => false,
                'redirect' => $this->generateUrl('access_denied', [
                            '_locale' => locale_get_default(),
                            'code' => '1912',
                        ])
            ]);
        } else {
            $situTranslated = $situService->searchTranslation($situId, $langId);
            return new JsonResponse([
                'success'  => true,
                'situTranslated' => $situTranslated,
            ]);
        }
    }
    
}