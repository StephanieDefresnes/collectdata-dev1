<?php

namespace App\Controller\Front;

use App\Entity\User;
use App\Entity\Lang;
use App\Entity\UserFile;
use App\Form\Front\User\UserFilesFormType;
use App\Form\Front\User\UserUpdateFormType;
use App\Service\LangService;
use App\Service\SituService;
use App\Service\UserFileService;
use Doctrine\ORM\EntityManagerInterface;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Security;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\IsGranted;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\File\Exception\FileException;
use Symfony\Component\HttpFoundation\File\UploadedFile;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\String\Slugger\SluggerInterface;
use Symfony\Contracts\Translation\TranslatorInterface;

/**
 * @Security("is_granted('ROLE_USER')")
 * @Route("/{_locale<%app_locales%>}")
 */
class UserController extends AbstractController
{    
    private $langService;
    private $situService;
    private $userFileService;
    private $translator;
    
    public function __construct(LangService $langService,
                                SituService $situService,
                                UserFileService $userFileService,
                                TranslatorInterface $translator)
    {
        $this->langService = $langService;
        $this->situService = $situService;
        $this->userFileService = $userFileService;
        $this->translator = $translator;
    }

    /**
     * @Route("/profile/{id}", name="user_account", methods="GET|POST")
     */
    public function read(   Request $request,
                            SluggerInterface $slugger,
                            EntityManagerInterface $em,
                            User $user): Response
    {
        $user = $this->getDoctrine()
            ->getRepository(User::class)
            ->findOneBy([
                'id' => $this->getUser()->getId()
            ]);
        
        // Get User current Language
        if ($user->getLangId() == null) {
            $user_lang = '';
            $user_lang_lg = '';
        } else {
            $lang = $this->getDoctrine()
                ->getRepository(Lang::class)
                ->findOneBy([
                    'id' => $this->getUser()->getLangId()
                ]);
            $user_lang = html_entity_decode($lang->getName(), ENT_QUOTES, 'UTF-8');
            $user_lang_lg = $lang->getLang();
        }
        
        // Get Contribs count
        $situs = $this->situService->countSitusByUser($this->getUser()->getId());
        
        // Get Contribs count by lang
        $situsLangs = $this->situService
                ->countSitusByLangByUser($this->getUser()->getId());
        
        // Get Contribs translated count
        $situsTranslated = $this->situService
                ->countSitusTranslatedByLangByUser($this->getUser()->getId());
        
        // Uploaded Translation files
        $contributorLangs = $user->getContributorLangs();
        $translationFiles = [];
        foreach ($contributorLangs as $lang) {
            $translationFiles[] = [
                'lang' => html_entity_decode($lang->getName()),
                'file' => $this->userFileService
                    ->getTranslationUserFilesByLang(
                            $this->getUser()->getId(), $lang->getId()
                    )];
        }
        
        // Upload Translation file
        $file = new UserFile();
        $form = $this->createForm(UserFilesFormType::class, $file);
        $form->handleRequest($request);
        
        if ($form->isSubmitted() && $form->isValid()) {
            
            $uploadedFile = $form['file']->getData();
            $userFileName = $form['filename']->getData();
            
            if ($uploadedFile) {
                $originalFilename = pathinfo(
                        $uploadedFile->getClientOriginalName(),
                        PATHINFO_FILENAME
                    );
                $safeFilename = $slugger->slug($originalFilename);
                $newFilename = $safeFilename.'-'.uniqid().'.'
                        .$uploadedFile->guessExtension();
                
                try {
                    $uploadedFile->move(
                        $this->getParameter('user_translations'),
                        $newFilename
                    );
                
                    $file->setUser($user);
                    $file->setLang($form['lang']->getData());
                    $file->setStatusId(2);
                    $file->setFilename($newFilename);
                    $file->setType('translation');
                    $file->setDateCreation(new \DateTime('now'));
                    $em->persist($file);
                    $em->flush();

                    $msg = $this->translator->trans(
                            'account.translator.file.flash.add.success', [],
                            'user_messages', $locale = locale_get_default()
                        );
                    $this->addFlash('success', $msg);
                    
                } catch (FileException $e) {
                    $msg = $this->translator->trans(
                            'account.translator.file.flash.add.error', [],
                            'user_messages', $locale = locale_get_default()
                        );
                    $this->addFlash('error', $msg);
                }
                
            } else if ($userFileName) {
                
                try {
                    $userFile = $this->getDoctrine()->getRepository(UserFile::class)
                        ->findOneBy(['filename' => $userFileName]);
                    $em->remove($userFile);
                    $em->flush();
                    unlink($this->getParameter('user_translations').'/'.$userFileName);
                    
                    $msg = $this->translator->trans(
                            'account.translator.file.flash.delete.success', [],
                            'user_messages', $locale = locale_get_default()
                        );
                    $this->addFlash('success', $msg);
                } catch (FileException $e) {
                    $msg = $this->translator->trans(
                            'account.translator.file.flash.delete.error', [],
                            'user_messages', $locale = locale_get_default()
                        );
                    $this->addFlash('error', $msg);
                }
                
            }
            
            return $this->redirectToRoute('user_account', [
                'id' => $user->getId(), '_locale' => locale_get_default()
            ]);      
        
        } elseif ($form->isSubmitted() && !$form->isValid()) {
            
            $msg = $this->translator->trans(
                    'account.translator.file.flash.error', [],
                    'user_messages', $locale = locale_get_default()
                );
            $this->addFlash('error', $msg);
            
        }
        
        return $this->render('front/user/account/index.html.twig', [
            'user' => $user,
            'user_lang' => $user_lang,
            'user_lang_lg' => $user_lang_lg,
            'situs' => $situs,
            'situsLangs' => $situsLangs,
            'situsTranslated' => $situsTranslated,
            'translationFiles' => $translationFiles,
            'form' => $form->createView(),
        ]);
    }

    /**
     * @Route("/edit/{id}", name="user_update", methods="GET|POST")
     */
    public function update( Request $request,
                            SluggerInterface $slugger,
                            EntityManagerInterface $em,
                            User $user): Response
    {
        $entityManager = $this->getDoctrine()->getManager();
        
        $form = $this->createForm(UserUpdateFormType::class, $user, array(
            'entity_manager' => $entityManager,
        ));
        $form->handleRequest($request);

        $user = $this->getDoctrine()
            ->getRepository(User::class)
            ->findOneBy([
                'id' => $this->getUser()->getId()
            ]);
        
        // User image
        $userImageFilename = $this->getUser()->getImageFilename();
        
        // Optional lang choices
        $langs = $this->langService->getLangsEnabledOrNot(1);
        
        // Translation contrib choices
        $contribLangs = $this->langService->getLangsEnabledOrNot(0);
        
        if ($form->isSubmitted() && $form->isValid()) {
            
            // Avatar
            $imageFilename = $form['imageFilename']->getData();
            if ($imageFilename) {
                $originalFilename = pathinfo(
                        $imageFilename->getClientOriginalName(),
                        PATHINFO_FILENAME
                    );
                $safeFilename = $slugger->slug($originalFilename);
                $newFilename = $safeFilename.'-'.uniqid().'.'
                        .$imageFilename->guessExtension();
                
                try {
                    if ($userImageFilename) {
                        unlink($this->getParameter('user_img').'/'
                                .$userImageFilename);
                    }
                    $imageFilename->move(
                        $this->getParameter('user_img'),
                        $newFilename
                    );
                    $user->setImageFilename($newFilename);
                } catch (FileException $e) {
                    $msg = $this->translator->trans(
                            'account.image.flash.add.error', [],
                            'user_messages', $locale = locale_get_default()
                        );
                    $this->addFlash('error', $msg);
                }
                
            }
            
            // Switch locale
            $request_lang_id = $request->request->get('user_update_form')['langId'];
            if ($request_lang_id == null) {
                $user_lang = $this->defaultLocale;
            } else {
                $lang = $this->langService->getUserLang($request_lang_id);
                $user_lang = $lang->getLang();
            }
            
            // Optional langs
            $optionalLangs = $form->get('langs');
            foreach ($optionalLangs as $optionLang) {
                $lang = new Lang();
                $lang->addUser($optionLang);
            }
            $em->persist($lang);
            $em->flush();
            
            $user->setUpdated();
        
            $this->getDoctrine()->getManager()->flush();
            $msg = $this->translator->trans(
                    'account.update.flash.success', [],
                    'user_messages', $locale = $user_lang
                );
            $this->addFlash('success', $msg);
            
            return $this->redirectToRoute('user_account', [
                'id' => $user->getId(), '_locale' => $user_lang
            ]);
        } elseif ($form->isSubmitted() && !$form->isValid()) {
            
            $msg = $this->translator->trans(
                    'account.update.flash.error', [],
                    'user_messages', $locale = $user_lang
                );
            $this->addFlash('error', $msg);
            
        }
        
        return $this->render('front/user/account/update.html.twig', [
            'user' => $user,
            'langs' => $langs,
            'contribLangs' => $contribLangs,
            'form' => $form->createView(),
        ]);
    }
    
}