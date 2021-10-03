<?php

namespace App\Controller\Back;

use App\Entity\Translation;
use App\Form\Back\Translation\TranslationFormType;
use App\Manager\TranslationManager;
use App\Service\LangService;
use App\Service\ContributorLangsService;
use App\Service\TranslationService;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\DependencyInjection\ParameterBag\ParameterBagInterface;
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
        $this->denyAccessUnlessGranted('ROLE_ADMIN');
        
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
        
        $translationForms = $this->em->getRepository(Translation::class)->findBy(
            [
                'referent' => 1,
                'statusId' => 3,
            ], [
                'name' => 'ASC',
                'id' => 'DESC',
            ]
        );
        
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
     * Translation forms list
     * 
     * @Route("/forms", name="back_translation_forms", methods="GET|POST")
     */
    public function seachTranslationForms(): Response 
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
     * Create Translation form
     * 
     * @Route("/create/{id}", defaults={"id" = null}, name="back_translation_create", methods="GET|POST")
     */
    public function createTranslationForm(Request $request, $id): Response 
    {
        $this->denyAccessUnlessGranted('IS_AUTHENTICATED_FULLY');
        $this->denyAccessUnlessGranted('ROLE_SUPER_ADMIN');
        
        $user = $this->getUser();        
                
        // Update or Create new Situ
        if ($id) {
            $translation = $this->em->getRepository(Translation::class)->find($id);
            
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
        
        $originalFields = new ArrayCollection();
        foreach ($translation->getFields() as $field) {
            $originalFields->add($translation);
        }
        
        $form = $this->createForm(TranslationFormType::class, $translation);
        $form->handleRequest($request);
        
        if ($form->isSubmitted() && $form->isValid()) {

            $translation->setDateCreation(new \DateTime('now'));
            $translation->setUserId($user->getId());
            $translation->setReferent(true);
            $translation->setEnabled(false);
            $translation->setYamlGenerated(false);
            
            foreach ($originalFields as $field) {
                if (false === $translation->getFields()->contains($field)) {
                    $translation->getFields()->removeElement($field);
                    $this->em->remove($field);
                }
            }
            
            $this->em->persist($translation);

            if ($form->getData()->getStatusId() == 1 ) {
                $type = 'save';
            } else $type = 'validate';

            try {
                $this->em->flush();

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

                if ($id) {
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
    
    /**
     * List of Translations to generate
     * 
     * @Route("/generate", name="back_translation_generate_list", methods="GET|POST")
     */
    public function searchTranslationsGenerate()
    {
        $this->denyAccessUnlessGranted('IS_AUTHENTICATED_FULLY');
        $this->denyAccessUnlessGranted('ROLE_SUPER_ADMIN');
        
        $translations = $this->em->getRepository(Translation::class)->findBy([
            'referent' => 0,
            'statusId' => 3,
            'enabled' => 1,
        ]);
        
        return $this->render('back/lang/translation/generate.html.twig', [
            'translations' =>$translations,
        ]);
    }
    
    /**
     * List of Yaml translation files and clean up old ones
     * 
     * @Route("/clean", name="back_translation_clean", methods="GET|POST")
     */
    public function seachTranslationFilesToClean()
    {
        $translationFolder = $this->getParameter('translation_folder');
        $translationFiles = preg_grep('/^([^.])/', scandir($translationFolder));
        
        $files = [];
        foreach($translationFiles as $file) {
            $strings = explode('.', $file);
            $old = isset($strings[3]) ? 1 : 0;
            $files[] = [
                'file' => $file,
                'old' => $old,
                'content' => file_get_contents($this->getParameter('translation_folder').'/'.$file),
            ];
        }
        
        return $this->render('back/lang/translation/clean.html.twig', [
            'files' =>$files,
        ]);
    }

    /**
     * Clones form and its last collection values in default language
     * 
     * @Route("/cloneForm/{id}", name="back_translation_form_clone", methods="GET|POST")
     */
    public function cloneTranslationForm(ParameterBagInterface $parameters, $id): Response 
    {
        $this->denyAccessUnlessGranted('IS_AUTHENTICATED_FULLY');
        $this->denyAccessUnlessGranted('ROLE_SUPER_ADMIN');
        
        // Default language
        $locale = $parameters->get('locale');
        
        // Current user
        $user = $this->security->getUser();
        
        $referent = $this->em->getRepository(Translation::class)->find($id);
        $localeReferent = $this->em->getRepository(Translation::class)->findBy(
            [
                'referent' => 0,
                'referentId' => $referent->getId(),
                'lang' => $locale,
            ],[
                'id' => 'DESC'
            ],
            $limit = 1
        );
        
        if ($localeReferent) {
            $referentFields = $localeReferent[0]->getFields();
            $translation = clone $localeReferent[0];
        } else {
            $referentFields = $referent->getFields();
            $translation = clone $referent;
        }
        $translation->setReferent(1);
        $translation->setReferentId(null);
        $translation->setLang(null);
        $translation->setLangId(null);
        $translation->setStatusId(1);
        $translation->setDateCreation(new \DateTime('now'));
        $translation->setDateLastUpdate(null);
        $translation->setDateStatus(null);
        $translation->setUserId($user->getId());
        $translation->setEnabled(false);
        $this->em->persist($translation);
        
        foreach ($referentFields as $key => $field) {
            $translationField = clone $field;
            $translationField->setTranslation($translation);
            $this->em->persist($translationField);
        }

        $this->em->flush();
        return $this->redirectToRoute('back_translation_create', ['id' => $translation->getId()]);
    }
    
    /**
     * Permutes property "enabled" to true, to allow YAML generate
     *  
     * @Route("/permute/enabled", name="back_translation_permute_enabled", methods="GET")
     */
    public function permuteEnabled(TranslationManager $translationManager, Request $request): Response
    {    
        $this->denyAccessUnlessGranted('IS_AUTHENTICATED_FULLY');
        $this->denyAccessUnlessGranted('ROLE_ADMIN');
        
        $translations = $translationManager->getTranslations();
        foreach ($translations as $translation) {
            $permute = $translation->getEnabled() ? false : true;
            $translation->setEnabled($permute);
        }
        $this->em->flush();
        return $this->redirectToRoute('back_translation_site');
    }
    
    /**
     * Generate Yaml files
     * 
     * @Route("/generateYaml/{id}", name="back_translation_generate", methods="GET|POST")
     */
    public function generateYaml($id): Response
    {
        $this->denyAccessUnlessGranted('IS_AUTHENTICATED_FULLY');
        $this->denyAccessUnlessGranted('ROLE_SUPER_ADMIN');
        
        $translation = $this->em->getRepository(Translation::class)->find($id);
        
        $newFile = $translation->getName().'.'.$translation->getLang().'.test.yaml';
        
        // Get translation fields
        $arrayFields = [];        
        foreach ($translation->getFields() as $field) {
            $arrayFields[$field->getName()] = $field->getValue();
        }
        
        // Nested multidimentional array depending on key
        $result = [];
        $countArray = [];
        foreach($arrayFields as $path => $value) {
            $temp = &$result;
            $paths = explode('.', $path);
            array_push($countArray, count($paths));            
            foreach($paths as $key) {
                $temp = &$temp[$key];
            }
            $temp = $value;
        }
        unset($temp);
        
        // Convert to yaml content
        try {
            // If translation exists, rename it in .old_"dateNow"
            $dateNow = new \DateTime('now');
            $dateFile = $dateNow->format('Y-m-d_H-i-s');
            if (file_exists($this->getParameter('translation_folder').'/'.$newFile)) {
                $newFilename = $newFile .'.old.'. $dateFile;
                rename(
                    $this->getParameter('translation_folder').'/'.$newFile,
                    $this->getParameter('translation_folder').'/'.$newFilename
                );
            }
            
            // Create new translation file
            $yaml = Yaml::dump($result, max($countArray));
            file_put_contents($this->getParameter('translation_folder').'/'.$newFile, $yaml);
            
            // Update object
            $translation->setYamlGenerated(true);
            $translation->setDateGenerated($dateNow);
            $this->em->persist($translation);
            $this->em->flush();
            
            $msg = $this->translator
                    ->trans('lang.translation.yaml.flash.success',[],
                            'back_messages', $locale = locale_get_default());
            $this->addFlash('success', $msg);
            
        } catch (Exception $ex) {
            $msg = $this->translator
                    ->trans('lang.translation.yaml.flash.error',[],
                            'back_messages', $locale = locale_get_default());
            $this->addFlash('success', $msg);
        }
        return $this->redirectToRoute('back_translation_generate_list');
    }
    
    
    /**
     * Remove old Translations files
     *  
     * @Route("/removeFile/{file}", name="back_translation_remove", methods="GET")
     */
    public function removeFile($file) {
        try {
            unlink($this->getParameter('translation_folder').'/'.$file);
            
            $msg = $this->translator
                    ->trans('lang.translation.yaml.clean.flash.success',[],
                            'back_messages', $locale = locale_get_default());
            $this->addFlash('success', $msg);
            
        } catch (Exception $e) {
            $msg = $this->translator
                    ->trans('lang.translation.yaml.clean.flash.error',[],
                            'back_messages', $locale = locale_get_default());
            $this->addFlash('success', $msg);
        }
        
        return $this->redirectToRoute('back_translation_clean');
    }
    
}
