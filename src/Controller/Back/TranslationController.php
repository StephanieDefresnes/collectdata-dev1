<?php

namespace App\Controller\Back;

use App\Entity\Lang;
use App\Entity\TranslationField;
use App\Entity\TranslationMessage;
use App\Form\Back\Translation\MessageFormType;
use App\Repository\TranslationMessageRepository;
use App\Service\TranslationService;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Doctrine\Common\Collections\ArrayCollection;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Contracts\Translation\TranslatorInterface;

/**
 * @Route("/{_locale<%app_locales%>}/back/translation")
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
     * @Route("/site", name="back_translation_site", methods="GET|POST")
     */
    public function translationSite(Request $request): Response
    {   
        $this->denyAccessUnlessGranted('IS_AUTHENTICATED_FULLY');
        $this->denyAccessUnlessGranted('ROLE_ADMIN');
        
        return $this->render('back/lang/translation/index.html.twig');
    }

    /**
     * @Route("/create", name="back_translation_create", methods="GET|POST")
     */
    public function create( EntityManagerInterface $em,
                            Request $request): Response 
   {
        $this->denyAccessUnlessGranted('IS_AUTHENTICATED_FULLY');
        $this->denyAccessUnlessGranted('ROLE_SUPER_ADMIN');
        
        // Translations list
        $translations = $this->translationService->getAllMessagesReferent();
        
        $user = $this->getUser();
        $userId = $user->getId();
        
        // Form
        $translation = new TranslationMessage();
            
        $fields = new ArrayCollection();
        foreach ($translation->getFields() as $field) {
            $fields->add($field);
        }
        
        $form = $this->createForm(MessageFormType::class, $translation);
        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()) {
            
            $translation->setDateCreation(new \DateTime('now'));
            $translation->setUserId($userId);
            $translation->setReferent(1);
            
            $em->persist($translation);
                        
            // Collection
            $fields = $translation->getFields();
            foreach ($fields as $key => $field) {
                $field->setSorting($key + 1);
                $field->setDateCreation(new \DateTime('now'));
                $field->setUserId($userId);
                $field->setReferent(1);
                $field->setMessage($translation);
            }
            
            $em->flush();
            
            $msg = $this->translator
                    ->trans('lang.translation.form.flash.success',[],
                            'back_messages', $locale = locale_get_default());
            $this->addFlash('success', $msg);
            
            return $this
                    ->redirectToRoute('back_translation_create',
                            ['_locale' => locale_get_default()]); 
            
        } elseif ($form->isSubmitted() && !$form->isValid()) {
            
            $msg = $this->translator
                    ->trans('lang.translation.form.flash.error', [],
                            'back_messages', $locale = locale_get_default());
            $this->addFlash('error', $msg);
            
        }
        
        return $this->render('back/lang/translation/create/index.html.twig', [
            'form' => $form->createView(),
            'translations' => $translations,
        ]);
    }
    
    /**
     * @Route("/edit", methods="GET")
     */
    public function getById(Request $request): JsonResponse
    {
        $this->denyAccessUnlessGranted('IS_AUTHENTICATED_FULLY');
        $this->denyAccessUnlessGranted('ROLE_SUPER_ADMIN');
        
        // Get TranslationMessage
        $id = $request->query->get('id');
        $message = $this->translationService->getTranslationById($id);
        if (!$message) { return new NotFoundHttpException(); }
        
        // Get collection - TranslationField
        $fields = $this->translationService->getFieldsByMessageId($message[0]['id']);

        return $this->json([
            'success' => true,
            'message' => $message,
            'fields' => $fields,
        ]);
    }

    /**
     * @Route("/updateTranslation", methods="GET|POST")
     */
    public function updateTranslation()
    {
        $this->denyAccessUnlessGranted('IS_AUTHENTICATED_FULLY');
        $this->denyAccessUnlessGranted('ROLE_SUPER_ADMIN');
        
        $entityManager = $this->getDoctrine()->getManager();
        
        try {
            
            // Get request data
            $request = $this->get('request_stack')->getCurrentRequest();
            $data = $request->request->all();
            
            dd($data);

            // Current user
            $user = $this->getUser();
            $userId = $user->getId();

            // TranslationMessage to update
            $translationMessage = $this->em->getRepository(TranslationMessage::class)->find($data['id']);

            // Clear original collection
            foreach ($translationMessage->getFields() as $field) {
                $translationMessage->getFields()->removeElement($field);
                $entityManager->remove($field);
            }

            // Add new collection
            $translationMessage->setDateLastUpdate(new \DateTime('now'));
            $translationMessage->setStatusId($data['statusId']);
            $translationMessage->setUserId($userId);
            $translationMessage->setReferent(1);
            $entityManager->persist($translationMessage);

            $fields = $data['data'];

            foreach ($fields as  $key => $field) {
                $translationField = new TranslationField();
                $translationField->setName($field['name']);
                $translationField->setType($field['type']);
                $translationField->setSorting($key + 1);
                $translationField->setDateCreation(new \DateTime('now'));
                $translationField->setUserId($userId);
                $translationField->setReferent(1);
                $translationField->setMessage($translationMessage);
                $entityManager->persist($translationField);
            }

            $entityManager->flush();
            
            $msg = $this->translator
                    ->trans('lang.translation.form.flash.success',[],
                            'back_messages', $locale = locale_get_default());
            $this->addFlash('success', $msg);

            // To reload page in ajax
            return new Response();
            
        } catch (Exception $e) {
            throw new \Exception('An exception appeared while updating the translation');
        }

    }
    
}
