<?php

namespace App\Controller\Front;

use App\Entity\Lang;
use App\Form\Front\User\UserUpdateFormType;
use App\Service\LangService;
use App\Service\SituService;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\File\Exception\FileException;
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
        
        // Get Contribs count by lang
        $situsLangs = $this->situService
                ->countSitusByLangByUser($this->getUser()->getId());
        
        return $this->render('front/user/account/profile/index.html.twig', [
            'user' => $user,
            'situsLangs' => $situsLangs,
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
        
        $form = $this->createForm(UserUpdateFormType::class, $user);
        $form->handleRequest($request);
        
        // User image
        $currentImage = $this->getUser()->getImageFilename();
        
        if ($form->isSubmitted() && $form->isValid()) {
            
            // Avatar
            $newImage = $form->get('imageFilename')->getData();
            
            if ($newImage) {
                
                $originalFilename = pathinfo(
                        $newImage->getClientOriginalName(),
                        PATHINFO_FILENAME
                    );
                $safeFilename = $slugger->slug($originalFilename);
                $newFilename = $safeFilename.'-'.uniqid().'.'
                        .$newImage->guessExtension();
                
                try {
                    
                    $newImage->move(
                        $this->getParameter('user_img'),
                        $newFilename
                    );
                    
                    if ($currentImage) {
                    unlink($this->getParameter('user_img').'/'
                            .$currentImage);
                    }
                    
                } catch (FileException $e) {
                    
                    $msg = $this->translator->trans(
                        'account.image.flash.add.error', [],
                        'user_messages', $locale = locale_get_default()
                    );
                    $this->addFlash('error', $msg.PHP_EOL.$e->getMessage());

                    return $this->redirectToRoute('user_update');
                }
                
                $user->setImageFilename($newFilename);
                
            }
            
            // Switch locale
            $requestLang = $request->request->get('user_update_form')['lang'];
            
            $lang = $em->getRepository(Lang::class)->find($requestLang);
            $userLang = $lang->getLang();
            
            try {
                $em->flush();

                $user->setUpdated();

                $em->flush();

                $msg = $this->translator->trans(
                    'account.update.flash.success', [],
                    'user_messages', $locale = $userLang
                    );
                $this->addFlash('success', $msg);

                return $this->redirectToRoute('user_account', [
                    '_locale' => $userLang
                ]);
                        
            } catch (Exception $e) {

                $msg = $this->translator->trans(
                    'account.update.flash.error', [],
                    'user_messages', $locale = locale_get_default()
                );
                $this->addFlash('error', $msg.PHP_EOL.$e->getMessage());

                return $this->redirectToRoute('user_update', [
                    '_locale' => $userLang
                ]);
            }
            
        }
        
        return $this->render('front/user/account/update.html.twig', [
            'user' => $user,
            'form' => $form->createView(),
        ]);
    }
    

    /**
     * @Route("/ajaxLangEnabled", methods="GET|POST")
     */
    public function ajaxLangEnabled()
    {
        return $this->json([
            'success' => true,
            'langs' => $this->langService->getLangsEnabledOrNot(1)
        ]);
    }
    
}