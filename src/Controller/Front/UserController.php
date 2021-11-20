<?php

namespace App\Controller\Front;

use App\Entity\Lang;
use App\Entity\Situ;
use App\Entity\User;
use App\Form\Front\User\UserContactType;
use App\Form\Front\User\UserUpdateFormType;
use App\Messenger\Messenger;
use App\Service\FileUploader;
use Doctrine\ORM\EntityManagerInterface;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\IsGranted;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Core\Security;
use Symfony\Contracts\Translation\TranslatorInterface;

/**
 * @Route("/{_locale<%app_locales%>}")
 */
class UserController extends AbstractController
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
     * @IsGranted("IS_AUTHENTICATED_FULLY")
     */
    public function read(Request $request): Response
    {
        // Get current user
        $user = $this->security->getUser();
        
        // Get Contribs count by lang
        $situsLangs = $this->em->getRepository(Situ::class)
                        ->findUserSitusCountByLang($user->getId());
        
        return $this->render('front/user/account/profile/index.html.twig', [
            'user' => $user,
            'situsLangs' => $situsLangs,
        ]);
    }

    /**
     * @IsGranted("IS_AUTHENTICATED_FULLY")
     */
    public function update( FileUploader $fileUploader,
                            Request $request): Response
    {        
        // Get current user
        $user = $this->security->getUser();
        
        $form = $this->createForm(UserUpdateFormType::class, $user);
        $form->handleRequest($request);
        
        // User image
        $currentImage = $this->getUser()->getImageFilename();
        
        if ($form->isSubmitted() && $form->isValid()) {
            
            if ($request->request->get('removeImg')) {
                $user->setImageFilename(null);
                unlink($this->getParameter('user_img').'/'
                        .$currentImage);
            }
            
            // Avatar
            $newImage = $form->get('imageFilename')->getData();
            
            if ($newImage) {
                $newImageName = $fileUploader->upload($newImage);
                $user->setImageFilename($newImageName);
                if ($currentImage) {
                    unlink($this->getParameter('user_img').'/'
                            .$currentImage);
                }
            }
            
            // Switch locale
            $requestLang = $request->request->get('user_update_form')['lang'];
            
            $lang = $this->em->getRepository(Lang::class)->find($requestLang);
            
            // Duplicate user current lang into langs
            if (false === $user->getLangs()->contains($lang)) {
                $user->addLang($lang);
            }
            if ($user->getLang() !== $lang) {
                $user->removeLang($user->getLang());
            }
            
            $user->setUpdated();
            
            try {

                $this->em->flush();

                $msg = $this->translator->trans(
                    'account.update.flash.success', [],
                    'user_messages', $locale = $lang->getLang()
                    );
                $this->addFlash('success', $msg);

                return $this->redirectToRoute('user_account', [
                    '_locale' => $lang->getLang()
                ]);
                        
            } catch (\Doctrine\DBAL\DBALException $e) {
                
                $msg = $this->translator->trans(
                    'account.update.flash.error', [],
                    'user_messages', $locale = locale_get_default()
                );
                $this->addFlash('error', $msg);
                
                return $this->redirectToRoute('user_update', [
                    '_locale' => $lang->getLang()
                ]);
            }
            
        }
        
        return $this->render('front/user/account/update.html.twig', [
            'user' => $user,
            'form' => $form->createView(),
        ]);
    }

    public function visit(Messenger $messenger, Request $request, $slug): Response
    {
        $user = $this->em->getRepository(User::class)
                ->findOneBy(['slug' => $slug]);
        
        // Get Contribs count by lang
        $situsLangs = $this->em->getRepository(Situ::class)
                        ->findUserSitusCountByLang($user->getId());
        
        $form = $this->createForm(UserContactType::class);
        $form->handleRequest($request);
        
        if($form->isSubmitted() && $form->isValid()) {
            
            // TODO messenger
        }
        
        return $this->render('front/user/account/profile/index.html.twig', [
            'form' => $form->createView(),
            'user' => $user,
            'situsLangs' => $situsLangs,
        ]);
    }
    
}