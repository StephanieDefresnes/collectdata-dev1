<?php

namespace App\Controller\Front;

use App\Entity\User;
use App\Entity\Lang;
use App\Form\Front\User\UserUpdateFormType;
use App\Service\LangService;
use App\Service\SituService;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\DependencyInjection\ParameterBag\ParameterBagInterface;
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
    public function update( EntityManagerInterface $em,
                            Request $request,
                            SluggerInterface $slugger): Response
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
            $imageFilename = $form->get('imageFilename')->getData();
            
            if ($imageFilename != null) {
                
                $imageName = $this->generateUniqueFileName() . '.' . $imageFilename->guessExtension();
                
                try {
                    $imageFilename->move(
                        $this->getParameter('user_img'),
                        $imageName
                    );
                    if ($userImageFilename != null) {
                        try {
                        unlink($this->getParameter('user_img').'/'
                                .$userImageFilename);
                        } catch (FileException $e) {
                            $error = $this->translator->trans(
                                'account.image.flash.remove.error', [],
                                'user_messages', $locale = locale_get_default()
                            );
                            $msg = sprintf($error, $e->getReason());

                            $this->addFlash('error', $msg);

                            return $this->redirectToRoute('user_update');
                        }
                    }
                } catch (FileException $e) {
                    $error = $this->translator->trans(
                        'account.image.flash.add.error', [],
                        'user_messages', $locale = locale_get_default()
                    );
                    $msg = sprintf($error, $e->getReason());

                    $this->addFlash('error', $msg);

                    return $this->redirectToRoute('user_update');
                }
            }
            
            // Switch locale
            $request_lang_id = $request->request->get('user_update_form')['langId'];
            if ($request_lang_id == null) {
                $user_lang = $this->getParameter('locale');
            } else {
                $lang = $this->langService->getLangById($request_lang_id);
                $user_lang = $lang->getLang();
            }
            
            try {
                $em->flush();

                $user->setUpdated();

                $em->flush();

                $msg = $this->translator->trans(
                    'account.update.flash.success', [],
                    'user_messages', $locale = $user_lang
                    );
                $this->addFlash('success', $msg);

                return $this->redirectToRoute('user_account', [
                    '_locale' => $user_lang
                ]);
                        
            } catch (FileException $e) {

                $error = $this->translator->trans(
                    'account.update.flash.error', [],
                    'user_messages', $locale = locale_get_default()
                );
                $msg = sprintf($error, $e->getReason());

                $this->addFlash('error', $msg);

                return $this->redirectToRoute('user_update');
            }
            
        }
        
        return $this->render('front/user/account/update.html.twig', [
            'user' => $user,
            'langs' => $langs,
            'contribLangs' => $contribLangs,
            'form' => $form->createView(),
        ]);
    }
    
}