<?php

namespace App\Controller\Front;

use App\Entity\Situ;
use App\Entity\Lang;
use App\Entity\User;
use App\Form\Situ\CreateSituFormType;
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
     * @Route("/contribs", name="all_situs")
     */
    public function index(): Response
    {
        $this->denyAccessUnlessGranted('IS_AUTHENTICATED_FULLY');
        
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
        
        $situs = $this->getDoctrine()->getRepository(Situ::class)
                ->findBy(['userId' => $user->getId()]);
        
        return $this->render('front/situ/user.html.twig', [
            'situs' => $situs,
            'userLangs' => $userLangs,
        ]);
    }

    /**
     * @Route("/contrib/{id}", defaults={"id" = null}, name="create_situ", methods="GET|POST")
     */
    public function createSitu( Request $request,
                                EntityManagerInterface $em,
                                $id): Response
    {
        $this->denyAccessUnlessGranted('IS_AUTHENTICATED_FULLY');
        $this->denyAccessUnlessGranted('ROLE_CONTRIBUTOR');
        
        // Current user
        $user = $this->security->getUser();
        $userId = $user->getId();
        $langs = $user->getLangs()->getValues();
        
        $situData = '';
        if ($id) {
            $situData = $this->em->getRepository(Situ::class)
                    ->findOneBy(['id' => $id]);
            if (!$situData) {dd('redirect page error');}
        }
        
        // Only situ author of moderator can update situ
        if (!empty($situData) && !$user->hasRole('ROLE_MODERATOR')
                && $userId != $situData->getUserId()) {
            
            $msg = $this->translator->trans(
                    'access_deny', [],
                    'user_messages', $locale = locale_get_default()
                );
            $this->addFlash('error', $msg);

            return $this->redirectToRoute('user_situs', [
                'id' => $userId, '_locale' => locale_get_default()
            ]);
        }
        
        // Form
        $situ = new Situ();
        $formSitu = $this->createForm(CreateSituFormType::class, $situ);
        $formSitu->handleRequest($request);
        
        return $this->render('front/situ/create.html.twig', [
            'form' => $formSitu->createView(),
            'langs' => $langs,
            'situ' => $situData,
        ]);
    }
    
    /**
     * @Route("/ajaxValidationRequest", methods="GET|POST")
     */
    function ajaxValidationRequest(Request $request): JsonResponse
    {
        $this->denyAccessUnlessGranted('IS_AUTHENTICATED_FULLY');
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
    public function readSitu(Situ $situ): Response
    {           
        $user = $this->em->getRepository(User::class)
                ->findOneBy(['id' => $situ->getUserId()]);
        
        return $this->render('front/situ/read.html.twig', [
            'situ' => $situ,
            'user' => $user,
        ]);
    }

    /**
     * @Route("/translate/{id}/{langId}", name="translate_situ", methods="GET|POST")
     */
    public function translateSitu( Request $request,
                                EntityManagerInterface $em,
                                $id, $langId): Response
    {
        $this->denyAccessUnlessGranted('IS_AUTHENTICATED_FULLY');
        $this->denyAccessUnlessGranted('ROLE_CONTRIBUTOR');
        
        // Current user langs
        $langs = $this->security->getUser()->getLangs()->getValues();
        
        // Situ to translate
        $situData = $this->getDoctrine()->getRepository(Situ::class)
                ->findOneBy(['id' => $id]);
        
        // Translation lang
        $langData = $this->em->getRepository(Lang::class)
                ->findOneBy(['id' => $langId]);
        
        // Form
        $situ = new Situ();
        $formSitu = $this->createForm(CreateSituFormType::class, $situ);
        $formSitu->handleRequest($request);
        
        return $this->render('front/situ/translation.html.twig', [
            'form' => $formSitu->createView(),
            'langs' => $langs,
            'situ' => $situData,
            'lang' => $langData,
        ]);
    }
    
}
