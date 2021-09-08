<?php

namespace App\Controller\Front;

use App\Entity\User;
use App\Entity\Lang;
use App\Entity\UserFile;
use App\Form\Front\User\UserFilesFormType;
use App\Form\Front\User\UserFilesRemoveFormType;
use App\Form\Front\User\UserUpdateFormType;
use App\Service\LangService;
use App\Service\SituService;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\File\Exception\FileException;
use Symfony\Component\HttpFoundation\File\UploadedFile;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Core\Security;
use Symfony\Component\String\Slugger\SluggerInterface;
use Symfony\Contracts\Translation\TranslatorInterface;

/**
 * @Route("/{_locale<%app_locales%>}")
 */
class UserController extends AbstractController
{    
    private $langService;
    private $security;
    private $situService;
    private $translator;
    
    public function __construct(LangService $langService,
                                Security $security,
                                SituService $situService,
                                TranslatorInterface $translator)
    {
        $this->langService = $langService;
        $this->security = $security;
        $this->situService = $situService;
        $this->translator = $translator;
    }

    /**
     * @Route("/profile", name="user_account", methods="GET|POST")
     */
    public function read(Request $request): Response
    {
        $this->denyAccessUnlessGranted('IS_AUTHENTICATED_FULLY');
        
        // Get current user
        $user = $this->security->getUser();
        
        // Get user Language
        if (!$user->getLangId()) {
            $user_lang = 'Français';
            $user_lang_lg = 'fr';
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
        
        return $this->render('front/user/account/profile/index.html.twig', [
            'user' => $user,
            'user_lang' => $user_lang,
            'user_lang_lg' => $user_lang_lg,
            'situs' => $situs,
            'situsLangs' => $situsLangs,
            'situsTranslated' => $situsTranslated,
        ]);
    }

    /**
     * @Route("/profile/edit", name="user_update", methods="GET|POST")
     */
    public function update( Request $request,
                            SluggerInterface $slugger,
                            EntityManagerInterface $em): Response
    {
        $this->denyAccessUnlessGranted('IS_AUTHENTICATED_FULLY');
        
        // Get current user
        $user = $this->security->getUser();
        
        $entityManager = $this->getDoctrine()->getManager();
        
        $form = $this->createForm(UserUpdateFormType::class, $user);
        $form->handleRequest($request);
        
        // User image
        $userImageFilename = $this->getUser()->getImageFilename();
        
        // Optional lang choices
        $langs = $this->langService->getLangsEnabledOrNot(1);
        
        // Translation contrib choices
        $contribLangs = $this->langService->getLangsEnabledOrNot(0);
        
        if ($form->isSubmitted() && $form->isValid()) {
            
            $langid = $form['langId']->getData();
            $user->addLang($this->langService->getLangById($langid));
            
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
                
            } else {
                if ($userImageFilename) {
                    try {
                        unlink($this->getParameter('user_img').'/'
                                .$userImageFilename);
                        $user->setImageFilename(null);
                    } catch (FileException $e) {
                        $msg = $this->translator->trans(
                                'account.image.flash.delete.error', [],
                                'user_messages', $locale = locale_get_default()
                            );
                        $this->addFlash('error', $msg);
                    }
                }
            }
            
            // Switch locale
            $request_lang_id = $request->request->get('user_update_form')['langId'];
            if ($request_lang_id == null) {
                $user_lang = 'fr';
            } else {
                $lang = $this->langService->getLangById($request_lang_id);
                $user_lang = $lang->getLang();
            }
            
            // Optional langs
            $optionalLangs = $form->get('langs');
            foreach ($optionalLangs as $optionLang) {
                $lang = new Lang();
                $lang->addUser($optionLang);
                $em->persist($lang);
            }
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