<?php

namespace App\Controller;

use App\Entity\Situ;
use App\Entity\Lang;
use App\Service\CategoryService;
use App\Service\EventService;
use App\Service\SituService;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Contracts\Translation\TranslatorInterface;

/**
 * @Route("/{_locale<%app_locales%>}")
 */
class SituController extends AbstractController
{
    private $situService;
    private $translator;
    
    public function __construct(SituService $situService,
                                TranslatorInterface $translator)
    {
        $this->situService = $situService;
        $this->translator = $translator;
    }
    
    /**
     * @Route("/situ/ajaxGetData", methods="GET|POST")
     */
    public function ajaxGetData(CategoryService $categoryService,
                                EventService $eventService): JsonResponse
    {
        $this->denyAccessUnlessGranted('ROLE_MODERATOR');
    
        // Get request data
        $request = $this->get('request_stack')->getCurrentRequest();
        $dataForm = $request->request->all();
        $data = $dataForm['dataForm'];
        
        $event = isset($data['event'])
                ? $eventService->getDataById($data['event']) : '';
        $categoryLevel1 = isset($data['categoryLevel1'])
                ? $categoryService->getDataById($data['categoryLevel1']) : '';
        $categoryLevel2 = isset($data['categoryLevel2'])
                ? $categoryService->getDataById($data['categoryLevel2']) : '';
        
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
    public function ajaxFindTranslation(Request $request): JsonResponse
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
            $situTranslated = $this->situService->searchTranslation($situId, $langId);
            $erroMsg = '';
        }
        
        return $this->json([
            'situTranslated' => $situTranslated,
            'error' => $erroMsg,
        ]);
    }
    
}
