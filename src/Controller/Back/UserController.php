<?php

namespace App\Controller\Back;

use App\Entity\User;
use App\Repository\UserRepository;
use App\Form\Back\User\UserBatchType;
use App\Form\Back\User\UserInvitationFormType;
use App\Form\Back\User\UserUpdateFromType;
use App\Mailer\Mailer;
use App\Manager\UserManager;
use App\Service\LangService;
use App\Service\SituService;
use App\Service\UserService;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Form\FormEvent;
use Symfony\Component\Form\FormEvents;
use Symfony\Component\Form\FormError;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\Session\Session;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Core\Encoder\UserPasswordEncoderInterface;
use Symfony\Contracts\Translation\TranslatorInterface;

/**
 * @Route("/{_locale<%app_locales%>}/back/user")
 */
class UserController extends AbstractController
{
    private $em;
    private $langService;
    private $situService;
    private $translator;
    private $userManager;
    private $userRepository;
    private $userService;
    
    public function __construct(EntityManagerInterface $em,
                                LangService $langService,
                                SituService $situService,
                                TranslatorInterface $translator,
                                UserManager $userManager,
                                UserRepository $userRepository,
                                UserService $userService)
    {
        $this->em = $em;
        $this->langService = $langService;
        $this->situService = $situService;
        $this->translator = $translator;
        $this->userManager = $userManager;
        $this->userRepository = $userRepository;
        $this->userService = $userService;
    }

    /**
     * @Route("/search", name="back_user_search", methods="GET|POST")
     */
    public function allUsers(Request $request, Session $session)
    {
        $this->denyAccessUnlessGranted('IS_AUTHENTICATED_FULLY');
        $this->denyAccessUnlessGranted('ROLE_MODERATOR');
        
        $users = $this->em->getRepository(User::class)->findAll();
        $langs = $this->langService->getAll();
        
        $formBatch = $this->createForm(UserBatchType::class, null, [
            'action' => $this->generateUrl('back_user_search'),
            'users' => $users,
        ]);
        $formBatch->handleRequest($request);
        if ($formBatch->isSubmitted() && $formBatch->isValid()) {
            $url = $this->userManager->dispatchBatchForm($formBatch);
            if ($url) { return $this->redirect($url); }
        }
        
        return $this->render('back/user/search/index.html.twig', [
            'users' => $users,
            'langs' => $langs,
            'form_batch' => $formBatch->createView(),
            'form_delete' => $this->createFormBuilder()->getForm()->createView(),
        ]);
    }

    /**
     * @Route("/create", name="back_user_create", methods="GET|POST")
     */
    public function create(Request $request, UserPasswordEncoderInterface $passwordEncoder, Mailer $mailer): Response
    {
        $this->denyAccessUnlessGranted('IS_AUTHENTICATED_FULLY');
        $this->denyAccessUnlessGranted('ROLE_MODERATOR');
        
        $user = new User();
        
        $role = '';
        if ($this->container->get('security.authorization_checker')
                ->isGranted('ROLE_SUPER_ADMIN')) {
            $role = 'super-admin';
        } else if ($this->container->get('security.authorization_checker')
                ->isGranted('ROLE_ADMIN')) {
            $role = 'admin';
        }
        
        $form = $this->createForm(UserInvitationFormType::class, $user, ['role' => $role]);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $password = bin2hex(random_bytes(4));
            $user->setPassword(
                $passwordEncoder->encodePassword(
                    $user,
                    $password
                )
            );

            $user->setEnabled(false);
            $user->setConfirmationToken(random_bytes(24));
            
            $em = $this->getDoctrine()->getManager();
            $em->persist($user);
            $em->flush();
            
            $mailer->sendInvitation($user, $password);
            
            $msg = $this->translator->trans('user.create.flash.success', [ '%identifier%' => $user, ], 'back_messages');
            $this->addFlash('success', $msg);
            return $this->redirectToRoute('back_user_search');
        }

        return $this->render('back/user/create.html.twig', [
            'user' => $user,
            'form' => $form->createView(),
        ]);
    }

    /**
     * @Route("/read/{id}", name="back_user_read", methods="GET")
     */
    public function read(User $user): Response
    {
        $this->denyAccessUnlessGranted('IS_AUTHENTICATED_FULLY');
        $this->denyAccessUnlessGranted('ROLE_MODERATOR');
        
        $situs = $this->situService->countSitusByUser($user->getId());
        
        return $this->render('back/user/read.html.twig', [
            'user' => $user,
            'situs' => $situs,
            'form_delete' => $this->createFormBuilder()->getForm()->createView(),
        ]);
    }

    /**
     * @Route("/update/{id}", name="back_user_update", methods="GET|POST")
     */
    public function updateSuper(Request $request, User $user): Response
    {
        $this->denyAccessUnlessGranted('IS_AUTHENTICATED_FULLY');
        $this->denyAccessUnlessGranted('ROLE_MODERATOR');
        
        // Access denied > add adminNote & logout
        // Deny SUPER_ADMIN access to ADMIN
        $noSuperAccess = $this->userService->getRole('SUPER_ADMIN');
        foreach($noSuperAccess as $noAccess) {
            if ($user == $noAccess) {
                if (!$this->container->get('security.authorization_checker')
                        ->isGranted('ROLE_SUPER_ADMIN')) {
                    return $this->redirectToRoute('access_denied', ['_locale' => locale_get_default()]);
                }
            }
        }     
        // Deny ADMIN access to ADMIN
        $noAdminAccess = $this->userService->getRole('ADMIN');
        foreach($noAdminAccess as $noAccess) {
            if ($user == $noAccess) {
                if (!$this->container->get('security.authorization_checker')
                        ->isGranted('ROLE_SUPER_ADMIN')) {
                    return $this->redirectToRoute('access_denied', ['_locale' => locale_get_default()]);
                }
            }
        }     
        // Deny MODERATOR access to MODERATOR
        $noModeratornAccess = $this->userService->getRole('MODERATOR');
        foreach($noModeratornAccess as $noAccess) {
            if ($user == $noAccess) {
                if (!$this->container->get('security.authorization_checker')
                        ->isGranted('ROLE_ADMIN')) {
                    return $this->redirectToRoute('access_denied', ['_locale' => locale_get_default()]);
                }
            }
        }
        
        // Form depending on user role
        $role = '';
        if ($this->container->get('security.authorization_checker')
                ->isGranted('ROLE_SUPER_ADMIN')) {
            $role = 'super-admin';
        } else if ($this->container->get('security.authorization_checker')
                ->isGranted('ROLE_ADMIN')) {
            $role = 'admin';
        }
        
        $form = $this->createForm(UserUpdateFromType::class, $user, ['role' => $role]);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $this->getDoctrine()->getManager()->flush();
            $msg = $this->translator->trans('user.update.flash.success', [], 'back_messages');
            $this->addFlash('success', $msg);
            return $this->redirectToRoute('back_user_search');
        }

        return $this->render('back/user/update.html.twig', [
            'user' => $user,
            'form' => $form->createView(),
        ]);
    }

    /**
     * @Route("/delete", name="back_user_delete", methods="GET|POST")
     */
    public function delete(Request $request): Response
    {    
        $this->denyAccessUnlessGranted('IS_AUTHENTICATED_FULLY');
        $this->denyAccessUnlessGranted('ROLE_SUPER_ADMIN');
        
        $users = $this->userManager->getUsers();
        
        $formBuilder = $this->createFormBuilder();
        $formBuilder->addEventListener(FormEvents::POST_SUBMIT, function (FormEvent $event) use ($users) {
            $result = $this->userManager->validationDelete($users);
            if (true !== $result) {
                $event->getForm()->addError(new FormError($result));
            }
        });
        $form = $formBuilder->getForm();
        
        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()) {
            $em = $this->getDoctrine()->getManager();
            foreach($users as $user) { 
                $em->remove($user);
            }
            try {
                $em->flush();
                $this->addFlash('success', $this->translator->trans('user.delete.flash.success', [], 'back_messages'));
            } catch (\Doctrine\DBAL\DBALException $e) {
                $this->addFlash('warning', $e->getMessage());
            }
            return $this->redirectToRoute('back_user_search');
        }
        return $this->render('back/user/delete.html.twig', [
            'users' => $users,
            'form' => $form->createView(),
        ]);
    }
    
    /**
     * @Route("/permute/enabled", name="back_user_permute_enabled", methods="GET")
     */
    public function permuteEnabled(Request $request): Response
    {    
        $this->denyAccessUnlessGranted('IS_AUTHENTICATED_FULLY');
        $this->denyAccessUnlessGranted('ROLE_MODERATOR');
        
        // Deny SUPER_ADMIN access to ADMIN
        $noSuperAccess = $this->userService->getRole('SUPER_ADMIN');
        foreach($noSuperAccess as $noUser) {
            if ($user == $noUser) {
                $this->denyAccessUnlessGranted('ROLE_SUPER_ADMIN');
            }
        }     
        // Deny ADMIN access to ADMIN
        $noAdminAccess = $this->userService->getRole('ADMIN');
        foreach($noAdminAccess as $noUser) {
            if ($user == $noUser) {
                $this->denyAccessUnlessGranted('ROLE_SUPER_ADMIN');
            }
        }     
        // Deny MODERATOR access to MODERATOR
        $noModeratornAccess = $this->userService->getRole('ROLE_MODERATOR');
        foreach($noModeratornAccess as $noUser) {
            if ($user == $noUser) {
                $this->denyAccessUnlessGranted('ROLE_ADMIN');
            }
        }
        
        $users = $this->userManager->getUsers();
        
        foreach ($users as $user) {
            $permute = $user->getEnabled() ? false : true;
            $user->setEnabled($permute);
        }
        $this->getDoctrine()->getManager()->flush();
        return $this->redirectToRoute('back_user_search');
    }
}