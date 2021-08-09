<?php

namespace App\Controller\Front;

use App\Entity\Situ;
use App\Entity\Lang;
use App\Entity\Event;
use App\Entity\Category;
use App\Entity\User;
use App\Form\Situ\CreateSituFormType;
use App\Service\SituService;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
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
    public function getSitusByUser()
    {
        $user = $this->security->getUser();
        $situs = $this->situService->getSitusByUser($user->getId());
        $userLangs = $user->getLangs();
        
        return $this->render('front/situ/list.html.twig', [
            'situs' => $situs,
            'userLangs' => $userLangs,
        ]);
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
        
        $situItems = $this->situService->getSituItemsBySituId($situ['id']);
        
        $url = $request->query->get('location') == true
                ? $this->redirectToRoute('create_situ', [
                       'id' => $situ['id'], 
                       '_locale' => locale_get_default()
                   ])
                : '';
        
        return $this->json([
            'success' => true,
            'redirection' => $url,
            'situ' => $situ,
            'situItems' => $situItems,
        ]);
    }
    
    /**
     * @Route("/ajaxValidationRequest", methods="GET|POST")
     */
    function ajaxValidationRequest(Request $request): JsonResponse
    {
        $this->denyAccessUnlessGranted('ROLE_CONTRIBUTOR');
        
        // Get Situ
        $id = $request->query->get('id');
        $situ = $this->em->getRepository(Situ::class)->find($id);
        
        if (!$situ) { return new NotFoundHttpException(); }
        
        $situ->setDateSubmission(new \DateTime('now'));
        $situ->setStatusId(2);
        $this->em->persist($situ);
        $this->em->flush();
        
        return $this->json([ 'success' => true ]);
    }
    
    /**
     * @Route("/read/{id}", name="read_situ", methods="GET")
     */
    public function getSitu(Situ $situ): Response
    {   
        $situData = $this->situService->getSituById($situ->getId());
        $situItems = $this->situService->getSituItemsBySituId($situ->getId());
        
        $user = $this->security->getUser();
        
        $lang = $this->getDoctrine()
            ->getRepository(Lang::class)
            ->findOneBy([
                'id' => $situData['langId']
            ]);
        
        $event = $this->getDoctrine()
            ->getRepository(Event::class)
            ->findOneBy([
                'id' => $situData['eventId']
            ]);
        
        $categoryLevel1 = $this->getDoctrine()
            ->getRepository(Category::class)
            ->findOneBy([
                'id' => $situData['categoryLevel1Id']
            ]);
        
        $categoryLevel2 = $this->getDoctrine()
            ->getRepository(Category::class)
            ->findOneBy([
                'id' => $situData['categoryLevel2Id']
            ]);
        
        return $this->render('front/situ/read.html.twig', [
            'situ' => $situData,
            'situItems' => $situItems,
            'user' => $user,
            'lang' => $lang,
            'event' => $event->getTitle(),
            'categoryLevel1' => $categoryLevel1->getTitle(),
            'categoryLevel2' => $categoryLevel2->getTitle(),
        ]);
    }
    
    /**
     * @Route("/ajaxFindTranslate", methods="GET")
     */
    public function ajaxFindTranslate(Request $request): JsonResponse
    {
        $this->denyAccessUnlessGranted('ROLE_CONTRIBUTOR');
        
        // Get Situ
        $situId = $request->query->get('id');
        $situLangId = $request->query->get('langId');
        $situTranslated = $this->situService->searchTranslation($situId, $situLangId);
        
        return $this->json([
            'situTranslated' => $situTranslated,
        ]);
    }

    /**
     * @Route("/translate/{id}/{langId}", name="translate_situ", methods="GET|POST")
     */
    public function translateSitu( Request $request,
                                EntityManagerInterface $em,
                                $id, $langId): Response
    {
        $this->denyAccessUnlessGranted('ROLE_CONTRIBUTOR');
        
        // Current user langs
        $langs = $this->security->getUser()->getLangs()->getValues();
        
        // Translation lang
        $repository = $this->em->getRepository(Lang::class);
        $translationLang = $repository->findOneBy(
            ['id' => $langId]
        );
        
        // Situ to translate
        $repoSitu = $this->getDoctrine()->getRepository(Situ::class);
        $situData = $repoSitu->findOneBy(['id' => $id]);
        
        $repoEvent = $this->getDoctrine()->getRepository(Event::class);
        
        // Form
        $situ = new Situ();
        $formSitu = $this->createForm(CreateSituFormType::class, $situ);
        $formSitu->handleRequest($request);
        
        return $this->render('front/situ/translation.html.twig', [
            'form' => $formSitu->createView(),
            'langs' => $langs,
            'situ' => $situData,
            'translationLang' => $translationLang,
            'initialId' => $id,
        ]);
    }
    
}
