<?php

namespace App\Controller\Front;

use App\Entity\Situ;
use App\Entity\SituItem;
use App\Entity\Category;
use App\Entity\Event;
use App\Entity\Lang;
use App\Entity\User;
use App\Form\Front\Situ\SituFormType;
use App\Mailer\Mailer;
use App\Service\CategoryService;
use App\Service\EventService;
use App\Service\SituService;
use Doctrine\Common\Collections\ArrayCollection;
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
    private $parameters;
    private $security;
    private $translator;
    
    public function __construct(EntityManagerInterface $em,
                                Mailer $mailer,
                                ParameterBagInterface $parameters,
                                Security $security,
                                TranslatorInterface $translator)
    {
        $this->em = $em;
        $this->mailer = $mailer;
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
        
        $user = $this->security->getUser();
        $userLangs = $user->getLangs();
        
        $situs = $this->em->getRepository(Situ::class)
                ->findBy(['userId' => $user->getId()]);
        
        return $this->render('front/situ/user.html.twig', [
            'situs' => $situs,
            'userLangs' => $userLangs,
        ]);
    }
    
    /**
     * @Route("/read/{id}", name="read_situ", methods="GET")
     */
    public function readSitu($id): Response
    {
        $situ = $this->em->getRepository(Situ::class)
                ->findOneBy(['id' => $id]);
        
        if (!$situ) {
            return $this->redirectToRoute('no_found', ['_locale' => locale_get_default()]);
        }
        
        // Only user can read not validated situ
        if ($situ->getStatusId() != 3) {
            
            $this->denyAccessUnlessGranted('IS_AUTHENTICATED_FULLY');
            $this->denyAccessUnlessGranted('ROLE_CONTRIBUTOR');
            
            // Only author can read refused situ
            $author = $this->security->getUser();
            if ($situ->getStatusId() == 4 && !$author->getId() != $situ->getUserId()) {
                return $this->redirectToRoute('access_denied', [
                    '_locale' => locale_get_default(),
                    'd' => '191841'
                ]);
            }
            // None can read deleted situ
            else if ($situ->getStatusId() == 5) {
                return $this->redirectToRoute('access_denied', [
                    '_locale' => locale_get_default(),
                    'd' => '19184'
                ]);
            }
        }
        
        $user = $this->em->getRepository(User::class)
                ->findOneBy(['id' => $situ->getUserId()]);
        
        
        return $this->render('front/situ/read.html.twig', [
            'situ' => $situ,
            'user' => $user,
        ]);
    }
    
    /**
     * @Route("/validation/{id}", methods="GET|POST")
     */
    function validationSituRequest(Situ $situ): Response
    {
        $this->denyAccessUnlessGranted('IS_AUTHENTICATED_FULLY');
        $this->denyAccessUnlessGranted('ROLE_CONTRIBUTOR');
        
        // Current user
        $user = $this->security->getUser();
        
        // Only author can request situ validation 
        if ($user->getId() != $situ->getUserId()) {
            return $this->redirectToRoute('access_denied', [
                '_locale' => locale_get_default(),
                'd' => '1211'
            ]);
        }
            
        try {
            $situ->setDateSubmission(new \DateTime('now'));
            $situ->setStatusId(2);
            $this->em->persist($situ);
            $this->em->flush();

            $this->mailer->sendModeratorSituValidate($situ);
            
            $msg = $this->translator->trans(
                    'contrib.form.submit.flash.success', [],
                    'user_messages', $locale = locale_get_default()
                );
            $this->addFlash('success', $msg);

        } catch (Exception $e) {

            $msg = $this->translator->trans(
                'contrib.form.submit.flash.error', [],
                'user_messages', $locale = locale_get_default()
            );
            $this->addFlash('error', $msg.PHP_EOL.$e->getMessage());
        }
        
        return $this->redirectToRoute('user_situs', ['_locale' => locale_get_default()]);
    }
    
    /**
     * @Route("/delete/{id}", methods="GET|POST")
     */
    function deleteSitu(Situ $situ): Response
    {
        $this->denyAccessUnlessGranted('IS_AUTHENTICATED_FULLY');
        $this->denyAccessUnlessGranted('ROLE_CONTRIBUTOR');
        
        // Current user
        $user = $this->security->getUser();
        
        // Only situ can delete situ
        if ($user->getId() != $situ->getUserId()) {
            if ($user->getId() != $situ->getUserId()) {
                return $this->redirectToRoute('access_denied', [
                    '_locale' => locale_get_default(),
                    'd' => '1411'
                ]);
            }
        }
            
        try {
            $situ->setDateDeletion(new \DateTime('now'));
            $situ->setStatusId(5);
            $this->em->persist($situ);
            $this->em->flush();

            $msg = $this->translator->trans(
                    'contrib.delete.success', [],
                    'user_messages', $locale = locale_get_default()
                );
            $this->addFlash('success', $msg);

            return $this->redirectToRoute('user_situs', ['_locale' => locale_get_default()]);

        } catch (Exception $e) {

            $msg = $this->translator->trans(
                'contrib.delete.error', [],
                'user_messages', $locale = locale_get_default()
            );
            $this->addFlash('error', $msg.PHP_EOL.$e->getMessage());
        }
    }
    
    /**
     * @Route("/contrib/{id}", defaults={"id" = null}, name="create_situ", methods="GET|POST")
     */
    public function situEdit($id, Request $request): Response
    {
        $this->denyAccessUnlessGranted('IS_AUTHENTICATED_FULLY');
        $this->denyAccessUnlessGranted('ROLE_CONTRIBUTOR');
        
        $defaultLang = $this->em->getRepository(Lang::class)
                ->findOneBy(['englishName' => 'French'])
                ->getId();
        
        // Current user
        $user = $this->security->getUser();
        $langs = $user->getLangs()->getValues();
                
        // Update or Create new Situ
        if ($id) {
            
            $situ = $this->getDoctrine()->getRepository(Situ::class)->find($id);
        
            if (!$situ) {
                return $this->redirectToRoute('no_found', ['_locale' => locale_get_default()]);
            }
        
            // Only author can update situ
            if ($user->getId() != $situ->getUserId()) {
                return $this->redirectToRoute('access_denied', [
                    '_locale' => locale_get_default(),
                    'd' => '19211'
                ]);
            }
            
        } else {
            $situ = new Situ();
        }
        
        $form = $this->createForm(SituFormType::class, $situ);
        $form->handleRequest($request);
                
        return $this->render('front/situ/create.html.twig', [
            'form' => $form->createView(),
            'langs' => $langs,
            'situ' => $situ,
            'defaultLang' => $defaultLang,
        ]);
    }

    /**
     * @Route("/translate/{id}/{langId}", name="translate_situ", methods="GET|POST")
     */
    public function translateSitu(Request $request, $id, $langId): Response
    {
        $this->denyAccessUnlessGranted('IS_AUTHENTICATED_FULLY');
        $this->denyAccessUnlessGranted('ROLE_CONTRIBUTOR');
        
        $defaultLang = $this->em->getRepository(Lang::class)
                ->findOneBy(['englishName' => 'French'])
                ->getId();
        
        // Current user langs
        $langs = $this->security->getUser()->getLangs()->getValues();
        
        // Situ to translate
        $situData = $this->em->getRepository(Situ::class)
                ->findOneBy(['id' => $id]);
        
        // Translation lang
        $langData = $this->em->getRepository(Lang::class)
                ->findOneBy(['id' => $langId]);
        
        // Form
        $situ = new Situ();
        $formSitu = $this->createForm(CreateSituFormType::class, $situ);
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
     * @Route("/situ/ajaxCreate", methods="GET|POST")
     */
    public function ajaxCreate(): JsonResponse
    {
        $this->denyAccessUnlessGranted('ROLE_CONTRIBUTOR');
            
        // Get current user
        $user = $this->security->getUser();
        $userId = $user->getId();
        
        $defaultLang = $this->getDoctrine()->getRepository(Lang::class)->findBy([
                        'lang' => $this->parameters->get('locale'),
                    ]);
        
        $userLang = $user->getLangId() == '' ? $defaultLang->getId() : $user->getLangId();
            
        // Get request data
        $request = $this->get('request_stack')->getCurrentRequest();
        $dataForm = $request->request->all();        
        $data = $dataForm['dataForm'];
        
        $landId = isset($data['lang']) ? $data['lang'] : $userLang;
        
        $langData = $this->getDoctrine()
                ->getRepository(Lang::class)
                ->findOneBy([ 'id' => $landId ]);
        
        if (!$langData->getEnabled()) {
            $msg = $this->translator->trans(
                'lang_deny', [],
                'user_messages', $locale = locale_get_default()
                );
            $request->getSession()->getFlashBag()->add('error', $msg);

            return $this->json([
                'success' => false,
                'redirection' => $this->redirectToRoute('user_situs', [
                    'id' => $userId, '_locale' => locale_get_default()
                ]),
            ]);  
        }

        $eventData = $this->createOrChooseData(
                $data['event'], 'event', $langData, '', $userId
        );
        $categoryLevel1 = $this->createOrChooseData(
                $data['categoryLevel1'], 'categoryLevel1', $langData,
                $eventData, $userId
        );
        $categoryLevel2 = $this->createOrChooseData(
                $data['categoryLevel2'], 'categoryLevel2', $langData,
                $categoryLevel1, $userId
        );

        $statusId = $data['statusId'];
        $dateNow = new \DateTime('now');

        // Update or create Situ
        if (empty($data['id'])) {
            $situ = new Situ();
            $situ->setDateCreation($dateNow);
            $situ->setUserId($userId); 
        } else {
            $situ = $this->em->getRepository(Situ::class)->find($data['id']);
            
            // Only situ author can update situ
            if ($userId != $situ->getUserId()) {

                $msg = $this->translator->trans(
                    'access_deny', [],
                    'user_messages', $locale = locale_get_default()
                    );
                $request->getSession()->getFlashBag()->add('error', $msg);
                
                return $this->json([
                    'success' => false,
                    'redirection' => $this->redirectToRoute('user_situs', [
                        'id' => $userId, '_locale' => locale_get_default()
                    ]),
                ]);                
            }
            
            $situ->setDateLastUpdate($dateNow);
        }
        
        $originalSituItems = new ArrayCollection();
        foreach ($situ->getSituItems() as $item) {
            $originalSituItems->add($item);
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
        
        // Check if orgiginal situItems are delete
        foreach ($originalSituItems as $situItem) {
            if (!in_array($situItem, $data['situItems'])) {
                $situ->getSituItems()->removeElement($situItem);
                $this->em->remove($situItem);
            }
        }
        // Add new or update situItems
        foreach ($data['situItems'] as $key => $dataItem) {
            if (false === $situ->getSituItems()->contains($dataItem)) {
                $situItem = new SituItem();
            }
            if ($key == 0) $situItem->setScore(0);
            else $situItem->setScore($dataItem['score']);
            $situItem->setTitle($dataItem['title']);
            $situItem->setDescription($dataItem['description']);
            $this->em->persist($situItem);
            $situItem->setSitu($situ);
        }
    
        $msgType = empty($data['id']) ? 'success_update' : 'success';
        
        try {
            $this->em->flush();

            $msg = $this->translator->trans(
                'contrib.form.'. $msgAction .'.flash.'. $msgType, [],
                'user_messages', $locale = locale_get_default()
            );

            $request->getSession()->getFlashBag()->add('success', $msg);

            if ($statusId == 2)
                $this->mailer->sendModeratorSituValidate($situ);
            
            return $this->json([
                'success' => true,
            ]);

        } catch (Exception $e) {
            
            $msg = $this->translator->trans(
                'contrib.form.'. $msgAction .'.flash.error', [],
                'user_messages', $locale = locale_get_default()
            );
            $this->addFlash('error', $msg.PHP_EOL.$e->getMessage());
            
            if (empty($data['id'])) {
                return $this->redirectToRoute('create_situ', ['_locale' => locale_get_default()]);
            } else {
                return $this->redirectToRoute('create_situ', [
                    '_locale' => locale_get_default(),
                    'id' => $data['id'],
                ]);
            }
        }
    }
    
    /**
     * Load data depending on selection or creation
     * Used by ajaxSitu()
     */
    public function createOrChooseData($dataEntity, $entity, $lang, $parent, $userId)
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
            $data->setUserId($userId);
            $data->setValidated(0);
            $data->setLang($lang);
            $this->em->persist($data);
        } else {
            switch ($entity) {
                case 'event':
                    $data = $this->getDoctrine()
                        ->getRepository(Event::class)
                        ->findOneBy([ 'id' => $dataEntity ]);
                    break;
                case 'categoryLevel1':
                    $data = $this->getDoctrine()
                        ->getRepository(Category::class)
                        ->findOneBy([ 'id' => $dataEntity ]);
                    break;
                case 'categoryLevel2':
                    $data = $this->getDoctrine()
                        ->getRepository(Category::class)
                        ->findOneBy([ 'id' => $dataEntity ]);
                    break;
            }
        }
        return $data;
    }
    
    /**
     * @Route("/situ/ajaxGetData", methods="GET|POST")
     */
    public function ajaxGetData(CategoryService $categoryService,
                                EventService $eventService): JsonResponse
    {
        $this->denyAccessUnlessGranted('ROLE_CONTRIBUTOR');
    
        // Get request data
        $request = $this->get('request_stack')->getCurrentRequest();
        $dataForm = $request->request->all();
        $data = $dataForm['dataForm'];
        
        $event = isset($data['event'])
                ? $this->em->getRepository(Event::class)->find($data['event']) : '';
        $categoryLevel1 = isset($data['categoryLevel1'])
                ? $this->em->getRepository(Category::class)
                    ->findBy([ 'event' => $data['categoryLevel1'] ]) : '';
        $categoryLevel2 = isset($data['categoryLevel2'])
                ? $this->em->getRepository(Category::class)
                    ->findBy([ 'parent' => $data['categoryLevel2'] ]) : '';
        
        return $this->json([
            'success' => true,
            'event' => $event,
            'categoryLevel1' => $categoryLevel1,
            'categoryLevel2' => $categoryLevel2,
        ]);
    }
    
    /**
     * @Route("/situ/ajaxFindTranslation", methods="GET")
     */
    public function ajaxFindTranslation(SituService $situService,Request $request): JsonResponse
    {
        $this->denyAccessUnlessGranted('ROLE_CONTRIBUTOR');
        
        // Situ to translate
        $situId = $request->query->get('id');
        
        // Lang wanted
        $langId = $request->query->get('langId');
        
        // Check if lang denied
        $langDeny = $this->translator->trans(
            'lang_deny', [],
            'user_messages', $locale = locale_get_default()
            );
        $situData = $this->getDoctrine()
                ->getRepository(Situ::class)
                ->findOneBy([ 'id' => $situId ]);
        $langData = $this->getDoctrine()
                ->getRepository(Lang::class)
                ->findOneBy([ 'id' => $langId ]);
        
        // If wanted lang is Lang situ ti translate or wanted lang is not enabled
        if ($langId == $situData->getLang()->getId() || !$langData->getEnabled()) {
            $situTranslated = '';
            $erroMsg = $langDeny;
        } else {
            $situTranslated = $situService->searchTranslation($situId, $langId);
            $erroMsg = '';
        }
        
        return $this->json([
            'situTranslated' => $situTranslated,
            'error' => $erroMsg,
        ]);
    }
    
}