<?php

namespace App\Controller\Back;

use App\Entity\Lang;
use App\Entity\Translation;
use App\Entity\TranslationField;
use App\Form\Translation\TranslationFormType;
use App\Service\LangService;
use App\Service\ContributorLangsService;
use App\Service\TranslationService;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Core\Security;
use Symfony\Component\Yaml\Yaml;
use Symfony\Contracts\Translation\TranslatorInterface;

/**
 * @Route("/{_locale<%app_locales%>}/back/translation")
 */
class TranslationController extends AbstractController
{
    private $em;
    private $langService;
    private $security;
    private $translator;
    private $translationService;
    
    public function __construct(EntityManagerInterface $em,
                                LangService $langService,
                                Security $security,
                                TranslatorInterface $translator,
                                TranslationService $translationService)
    {
        $this->em = $em;
        $this->langService = $langService;
        $this->security = $security;
        $this->translator = $translator;
        $this->translationService = $translationService;
    }
    
    /**
     * @Route("/site", name="back_translation_site", methods="GET|POST")
     */
    public function translationSite(ContributorLangsService $contributorLangsService): Response 
   {
        $this->denyAccessUnlessGranted('IS_AUTHENTICATED_FULLY');
        $this->denyAccessUnlessGranted('ROLE_SUPER_ADMIN');
        
        // Translation user contribs
        $usersContributorLangs = $contributorLangsService->getContributorLangs();
        $contributorLangs = [];
        foreach ($usersContributorLangs as $lang) {
            array_push($contributorLangs, $lang['lang']);
        }
        $translationsContributor = $this->translationService->getTranslations($contributorLangs);
        
        // Translation lang enabled
        $langsEnabled = $this->langService->getLangsEnabledOrNot(1);
        $langs = [];
        foreach ($langsEnabled as $lang) {
            array_push($langs, $lang['lang']);
        }
        $translationsSite = $this->translationService->getTranslations($langs);
        
        $translationForms = $this->getDoctrine()
                    ->getRepository(Translation::class)->findBy([
                        'referent' => 1,
                        'statusId' => 3,
                    ]);
        
        // Get current user
        $user = $this->security->getUser();
        
        return $this->render('back/lang/translation/site/index.html.twig', [
            'translationsContributor' => $translationsContributor,
            'translationsSite' => $translationsSite,
            'translationForms' => $translationForms,
            'user' => $user,
        ]);
    }

    /**
     * @Route("/forms", name="back_translation_forms", methods="GET|POST")
     */
    public function search(): Response 
   {
        $this->denyAccessUnlessGranted('IS_AUTHENTICATED_FULLY');
        $this->denyAccessUnlessGranted('ROLE_SUPER_ADMIN');
        
        // Translation forms list
        $translationsForms = $this->translationService->getTranslationsForms();
        
        // Translation lang enabled
        $langsEnabled = $this->langService->getLangsEnabledOrNot(1);
        $langs = [];
        foreach ($langsEnabled as $lang) {
            array_push($langs, $lang['lang']);
        }
        $translationsSite = $this->translationService->getTranslations($langs);
        
        return $this->render('back/lang/translation/forms.html.twig', [
            'translationsForms' => $translationsForms,
            'translations' => $translationsSite,
            'langsEnabled' => $langsEnabled,
        ]);
    }
    
    /**
     * @Route("/permute/enabled", name="back_translation_permute_enabled", methods="GET")
     */
    public function permuteEnabled(TranslationManager $translationManager, Request $request): Response
    {    
        $this->denyAccessUnlessGranted('IS_AUTHENTICATED_FULLY');
        $this->denyAccessUnlessGranted('ROLE_ADMIN');
        
        $translations = $translationManager->getLangs();
        foreach ($translations as $translation) {
            $permute = $translation->getEnabled() ? false : true;
            $translation->setEnabled($permute);
        }
        $this->getDoctrine()->getManager()->flush();
        return $this->redirectToRoute('back_translation_forms');
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

            if ($form->getData()->getStatusId() == 1 ) {
                $type = 'save';
            } else $type = 'validate';

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
        
        return $this->render('back/lang/translation/form/index.html.twig', [
            'form' => $form->createView(),
            'translation' => $translation,
        ]);
    }
    
}
