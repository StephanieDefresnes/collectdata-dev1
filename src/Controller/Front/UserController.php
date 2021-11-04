<?php

namespace App\Controller\Front;

use App\Entity\Lang;
use App\Entity\User;
use App\Form\Front\User\UserContactType;
use App\Form\Front\User\UserUpdateFormType;
use App\Messenger\Messenger;
use App\Service\LangService;
use App\Service\SituService;
use Doctrine\ORM\EntityManagerInterface;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\IsGranted;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\File\Exception\FileException;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Core\Security;
use Symfony\Component\String\Slugger\AsciiSlugger;
use Symfony\Component\String\Slugger\SluggerInterface;
use Symfony\Contracts\Translation\TranslatorInterface;

/**
 * @Route("/{_locale<%app_locales%>}")
 */
class UserController extends AbstractController
{    
    private $em;
    private $langService;
    private $security;
    private $situService;
    private $translator;
    
    public function __construct(EntityManagerInterface $em,
                                LangService $langService,
                                Security $security,
                                SituService $situService,
                                TranslatorInterface $translator)
    {
        $this->em = $em;
        $this->langService = $langService;
        $this->security = $security;
        $this->situService = $situService;
        $this->translator = $translator;
    }

    /**
     * @Route("/visit/{slug}", name="user_visit", methods="GET")
     */
    public function visit(Messenger $messenger, Request $request, $slug): Response
    {
        $user = $this->em->getRepository(User::class)
                ->findOneBy(['slug' => $slug]);
        
        // Get Contribs count by lang
        $situsLangs = $this->situService
                ->countSitusByLangByUser($user->getId());
        
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

    /**
     * @IsGranted("IS_AUTHENTICATED_FULLY")
     * @Route("/profile", name="user_account", methods="GET")
     */
    public function read(Request $request): Response
    {
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
     * @IsGranted("IS_AUTHENTICATED_FULLY")
     * @Route("/profile/edit", name="user_update", methods="GET|POST")
     */
    public function update( Request $request,
                            SluggerInterface $slugger): Response
    {        
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
            
            // Slug user name
            if ($user->getName() !== $form->get('name')->getData()) {
                $slugger = new AsciiSlugger();
                $user->setSlug($slugger->slug($user->getName()));
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
    

    /**
     * @IsGranted("IS_AUTHENTICATED_FULLY")
     * @Route("/ajaxLangEnabled", methods="GET|POST")
     */
    public function ajaxLangEnabled()
    {
        return $this->json([
            'success' => true,
            'langs' => $this->em->getRepository(Lang::class)->findBy(['enabled' => 1])
        ]);
    }
    
}