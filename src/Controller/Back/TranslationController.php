<?php

namespace App\Controller\Back;

use App\Entity\Lang;
use App\Entity\Translation;
use App\Entity\TranslationField;
use App\Form\Back\Translation\TranslationFormType;
use App\Repository\TranslationRepository;
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
                                TranslationRepository $translationRepository,
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
     * @Route("/forms", name="back_translation_forms", methods="GET|POST")
     */
    public function search( EntityManagerInterface $em,
                            Request $request): Response 
   {
        $this->denyAccessUnlessGranted('IS_AUTHENTICATED_FULLY');
        $this->denyAccessUnlessGranted('ROLE_SUPER_ADMIN');
        
        // Translations list
        $translations = $this->translationService->getAllTranslationsReferent();
        
        return $this->render('back/lang/translation/forms.html.twig', [
            'translations' => $translations,
        ]);
    }

    /**
     * @Route("/create/{id}/{new}", defaults={"id" = null, "new" = null}, name="back_translation_create", methods="GET|POST")
     */
    public function create( EntityManagerInterface $em,
                            Request $request, $id): Response 
   {
        $this->denyAccessUnlessGranted('IS_AUTHENTICATED_FULLY');
        $this->denyAccessUnlessGranted('ROLE_SUPER_ADMIN');
        
        $user = $this->getUser();        
                
        // Update or Create new Situ
        if ($id && !isset($news)) {
            
            $translation = $this->getDoctrine()
                    ->getRepository(Translation::class)->find($id);
            
            if (!$translation) {
                return $this->redirectToRoute('no_found', ['_locale' => locale_get_default()]);
            }
        
            // Only situ author can update situ
            if ($user->getId() != $translation->getUserId()) {

                $msg = $this->translator->trans(
                        'access_deny', [],
                        'user_messages', $locale = locale_get_default()
                    );
                $this->addFlash('error', $msg);

                return $this->redirectToRoute('user_situs', ['_locale' => locale_get_default()]);
            }
        } else {
            $translation = new Translation();
        }
        
        $form = $this->createForm(TranslationFormType::class, $translation);
        $form->handleRequest($request);
        
        if ($form->isSubmitted() && $form->isValid()) {

            $translation->setDateCreation(new \DateTime('now'));
            $translation->setUserId($user->getId());
            $translation->setReferent(1);

            $em->persist($translation);

            // Collection
            $fields = $translation->getFields();
            foreach ($fields as $key => $field) {
                $field->setSorting($key + 1);
                $field->setDateCreation(new \DateTime('now'));
                $field->setUserId($user->getId());
                $field->setReferent(1);
            }

            if ($form->getData()->getStatusId() == 1 ) {
                $type = 'edit';
            } else $type = 'submit';

            try {
                $em->flush();

                $msg = $this->translator
                        ->trans('lang.translation.form.flash.'. $type .'.success',[],
                                'back_messages', $locale = locale_get_default());
                $this->addFlash('success', $msg);

                return $this->redirectToRoute('back_translation_forms',
                                ['_locale' => locale_get_default()]);  

            } catch (Exception $e) {
                $msg = $this->translator
                        ->trans('lang.translation.form.flash.'. $type .'.error',[],
                                'back_messages', $locale = locale_get_default());
                $this->addFlash('success', $msg);

                if ($id && !isset($news)) {
                    return $this->redirectToRoute('back_translation_create', [
                        '_locale' => locale_get_default(),
                        'id' => $id
                    ]);
                } else {
                    return $this->redirectToRoute('back_translation_create',[
                        '_locale' => locale_get_default()]
                    );
                }  
            }
        }
        
        return $this->render('back/lang/translation/create/index.html.twig', [
            'form' => $form->createView(),
            'translation' => $translation,
        ]);
    }
    
}
