<?php

namespace App\Controller\Front;

use App\Entity\Lang;
use App\Entity\TranslationField;
use App\Entity\TranslationMessage;
use App\Entity\User;
use App\Form\Front\Translation\MessageFormType;
use App\Repository\TranslationMessageRepository;
use App\Service\TranslationService;
use App\Service\UserFileService;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\Session\Session;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Contracts\Translation\TranslatorInterface;

/**
 * @Route("/{_locale<%app_locales%>}/translation")
 */
class TranslationController extends AbstractController
{
    private $em;
    private $translator;
    private $translationRepository;
    private $translationService;
    
    public function __construct(EntityManagerInterface $em,
                                TranslatorInterface $translator,
                                TranslationMessageRepository $translationRepository,
                                TranslationService $translationService)
    {
        $this->em = $em;
        $this->translator = $translator;
        $this->translationRepository = $translationRepository;
        $this->translationService = $translationService;
    }
    
    /**
     * @Route("/{id}/all", name="front_translations", methods="GET|POST")
     */
    public function index(): Response
    {
        $this->denyAccessUnlessGranted('IS_AUTHENTICATED_FULLY');
        
        return $this->render('front/translation/index.html.twig', [
        ]);
    }
    
    /**
     * @Route("/{id}/add", name="front_translation_add", methods="GET|POST")
     */
    public function create( EntityManagerInterface $em,
                            Request $request,
                            User $user): Response
    {
        $this->denyAccessUnlessGranted('IS_AUTHENTICATED_FULLY');
//        $this->denyAccessUnlessGranted('ROLE_USER');
        
        // Current user
        $user = $this->getDoctrine()
            ->getRepository(User::class)
            ->findOneBy([
                'id' => $this->getUser()->getId()
            ]);
        
        // Valideted translations list
        $translations = $this->translationService->getMessagesByStatusId(3);
        
        // User translations
        $userTranslations = $this->translationService->getUserMessages(3);
        
        // Form
        $translation = new TranslationMessage();
            
        $fields = new ArrayCollection();
        foreach ($translation->getFields() as $field) {
            $fields->add($field);
        }
        
        $form = $this->createForm(MessageFormType::class, $translation);
        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()) {
            
            
            
            
            
        }
        
        return $this->render('front/translation/create/index.html.twig', [
            'translations' => $translations,
            'userTranslations' => $userTranslations,
        ]);
    }
    
    /**
     * @Route("/edit", methods="GET")
     */
    public function getById(Request $request): JsonResponse
    {
        $this->denyAccessUnlessGranted('IS_AUTHENTICATED_FULLY');
//        $this->denyAccessUnlessGranted('ROLE_USER');
        
        // Get TranslationMessage
        $id = $request->query->get('id');
        $message = $this->translationService->getTranslationById($id);
        if (!$message) { return new NotFoundHttpException(); }  // TODO JS management
        
        // Get collection - TranslationField
        $fields = $this->translationService->getFieldsByMessageId($message[0]['id']);

        return $this->json([
            'success' => true,
            'message' => $message,
            'fields' => $fields,
        ]);
    }
}
