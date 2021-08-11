<?php

namespace App\Controller;

use App\Entity\Situ;
use App\Entity\SituItem;
use App\Entity\Lang;
use App\Entity\Event;
use App\Entity\Category;
use App\Service\SituService;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Core\Security;
use Symfony\Contracts\Translation\TranslatorInterface;

/**
 * @Route("/{_locale<%app_locales%>}")
 */
class SituController extends AbstractController
{
    private $em;
    private $security;
    private $situService;
    private $translator;
    
    public function __construct(EntityManagerInterface $em,
                                Security $security,
                                SituService $situService,
                                TranslatorInterface $translator)
    {
        $this->em = $em;
        $this->security = $security;
        $this->situService = $situService;
        $this->translator = $translator;
    }
    
    /**
     * @Route("/ajaxCreate", methods="GET|POST")
     */
    public function ajaxCreate(): JsonResponse
    {
        $this->denyAccessUnlessGranted('ROLE_CONTRIBUTOR');
            
        // Get current user
        $user = $this->security->getUser();
        $userId = $user->getId();
        $userLang = $user->getLangId() != '' ? $user->getLangId() : 47;
            
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
            
            // Only situ author of moderator can update situ
            if (!$user->hasRole('ROLE_MODERATOR') && $userId != $situ->getUserId()) {

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

            // Clear original collection
            foreach ($situ->getSituItems() as $item) {
                $situ->getSituItems()->removeElement($item);
                $this->em->remove($item);
            }
        }
        
        if (!empty($data['initialId']) || $situ->getTranslatedSituId() != '') {
            $situ->setInitialSitu(false);
            if (!empty($data['initialId']))
                $situ->setTranslatedSituId($data['initialId']);
        } else {
            $situ->setInitialSitu(true);
        }

        $situ->setTitle($data['title']);
        $situ->setDescription($data['description']);

        // Depending on the button save (val = 1) or submit (val = 2) clicked
        if ($statusId == 2) $situ->setDateSubmission($dateNow);
        else $situ->setDateSubmission(null);
        
        $situ->setDateValidation(null); 
        $situ->setLang($langData);
        $situ->setEvent($eventData);
        $situ->setCategoryLevel1($categoryLevel1);
        $situ->setCategoryLevel2($categoryLevel2);
        $situ->setStatusId($statusId);
        $this->em->persist($situ);

        // Add new collection
        foreach ($data['situItems'] as $key => $d) {
            $situItem = new SituItem();
            if ($key == 0) $situItem->setScore(0);
            else $situItem->setScore($d['score']);
            $situItem->setTitle($d['title']);
            $situItem->setDescription($d['description']);
            $this->em->persist($situItem);
            $situItem->setSitu($situ);
        }

        $this->em->flush();

        $msgSaveCreate = $this->translator->trans(
                    'contrib.form.save.flash.success', [],
                    'user_messages', $locale = locale_get_default()
                    );

        $msgSaveUpdate = $this->translator->trans(
                    'contrib.form.save.flash.success_update', [],
                    'user_messages', $locale = locale_get_default()
                    );

        $msgSubmitCreate = $this->translator->trans(
                    'contrib.form.submit.flash.success', [],
                    'user_messages', $locale = locale_get_default()
                    );

        $msgSubmitUpdate = $this->translator->trans(
                    'contrib.form.submit.flash.success_update', [],
                    'user_messages', $locale = locale_get_default()
                    );
        
        if ($statusId == 1)
            $msg = empty($data['id']) ? $msgSaveCreate : $msgSaveUpdate;
        else
            $msg = empty($data['id']) ? $msgSubmitCreate : $msgSubmitUpdate;
        
        $request->getSession()->getFlashBag()->add('success', $msg);

        return $this->json([
            'success' => true,
            'redirection' => $this->redirectToRoute('user_situs',
                    ['_locale' => locale_get_default()]),
        ]);
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
     * @Route("/ajaxEdit", methods="GET")
     */
    public function ajaxEdit(Request $request): JsonResponse
    {
        $this->denyAccessUnlessGranted('IS_AUTHENTICATED_FULLY');
        
        // Get Situ
        $id = $request->query->get('id');
        $situ = $this->situService->getSituById($id);
        
        if (!$situ) { return new NotFoundHttpException(); }
        
        $situItems = $this->situService->getSituItemsBySituId($id);
        return $this->json([
            'success' => true,
            'situ' => $situ,
            'situItems' => $situItems,
        ]);
    }
    
    /**
     * @Route("/ajaxFindTranslation", methods="GET")
     */
    public function ajaxFindTranslation(Request $request): JsonResponse
    {
        $this->denyAccessUnlessGranted('ROLE_CONTRIBUTOR');
        
        // Get Situ
        $situId = $request->query->get('id');
        $situLangId = $request->query->get('langId');
        
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
                ->findOneBy([ 'id' => $situLangId ]);
        
        // If Lang situ is wanted lang or wanted lang is not enabled
        if ($situLangId == $situData->getLang()->getId() || !$langData->getEnabled()) {
            $situTranslated = '';
            $erroMsg = $langDeny;
        } else {
            $situTranslated = $this->situService->searchTranslation($situId, $situLangId);
            $erroMsg = '';
        }
        
        
        return $this->json([
            'situTranslated' => $situTranslated,
            'error' => $erroMsg,
        ]);
    }
    
}
