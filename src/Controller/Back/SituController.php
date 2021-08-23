<?php

namespace App\Controller\Back;

use App\Entity\Situ;
use App\Form\Back\Situ\VerifySituFormType;
use App\Service\CategoryService;
use App\Service\EventService;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Core\Security;
use Symfony\Contracts\Translation\TranslatorInterface;

/**
 * @Route("/{_locale<%app_locales%>}/back/situ")
 */
class SituController extends AbstractController
{
    private $em;
    private $security;
    private $translator;
    
    public function __construct(EntityManagerInterface $em,
                                Security $security,
                                TranslatorInterface $translator)
    {
        $this->em = $em;
        $this->security = $security;
        $this->translator = $translator;
    }
    
    /**
     * @Route("/list", name="back_situs_search")
     */
    public function index(): Response
    {   
        $this->denyAccessUnlessGranted('IS_AUTHENTICATED_FULLY');
        
        $repository = $this->getDoctrine()->getRepository(Situ::class);
        $situs = $repository->findAll();

        return $this->render('back/situ/search/index.html.twig', [
            'situs' => $situs,
        ]);
    }
    
    /**
     * @Route("/read/{id}", name="back_situ_read", methods="GET")
     */
    public function getSitu($id): Response
    {
        $this->denyAccessUnlessGranted('IS_AUTHENTICATED_FULLY');
        
        $repository = $this->em->getRepository(Situ::class);
        $situ = $repository->findOneBy(['id' => $id]);
        
        return $this->render('back/situ/read/index.html.twig', [
            'situ' => $situ,
        ]);
    }
    
    /**
     * @Route("/validation", name="back_situs_validation", methods="GET")
     */
    public function getSitusToValidate()
    {
        $this->denyAccessUnlessGranted('IS_AUTHENTICATED_FULLY');
        
        $repository = $this->em->getRepository(Situ::class);
        $situs = $repository->findBy(['statusId' => 2]);
        
        return $this->render('back/situ/validation/index.html.twig', [
            'situs' => $situs,
        ]);
    }
    
    /**
     * @Route("/verify/{id}", name="back_situ_verify", methods="GET|POST")
     */
    public function verifySitu(Request $request, $id): Response
    {
        $this->denyAccessUnlessGranted('IS_AUTHENTICATED_FULLY');
        $this->denyAccessUnlessGranted('ROLE_MODERATOR');
        
        $situData = $this->em->getRepository(Situ::class)->findOneBy(['id' => $id]);
        
        // Form
        $situ = new Situ();
        $formSitu = $this->createForm(VerifySituFormType::class, $situ);
        $formSitu->handleRequest($request);
        
        return $this->render('back/situ/verify/index.html.twig', [
            'form' => $formSitu->createView(),
            'situ' => $situData,
        ]);
    }
    
    /**
     * @Route("/ajaxGetData", methods="GET|POST")
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
    
}
