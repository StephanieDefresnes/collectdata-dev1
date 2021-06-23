<?php

namespace App\Controller\Back;

use App\Entity\User;
use App\Repository\UserRepository;
use App\Form\Back\UserType;
use App\Form\Back\UserUpdateType;
use App\Form\Back\UserBatchType;
use App\Mailer\Mailer;
use App\Manager\UserManager;
use App\Service\SituService;
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
    private $situService;
    private $translator;
    private $userManager;
    private $userRepository;
    
    public function __construct(SituService $situService,
                                TranslatorInterface $translator,
                                UserManager $userManager,
                                UserRepository $userRepository)
    {
        $this->situService = $situService;
        $this->translator = $translator;
        $this->userManager = $userManager;
        $this->userRepository = $userRepository;
    }

    /**
     * @Route("/search", name="back_user_search", methods="GET|POST")
     */
    public function search(Request $request, Session $session)
    {
        $this->denyAccessUnlessGranted('back_user_search');
        
        $users = $this->userRepository->findAll();
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
            'form_batch' => $formBatch->createView(),
            'form_delete' => $this->createFormBuilder()->getForm()->createView(),
        ]);
    }

    /**
     * @Route("/create", name="back_user_create", methods="GET|POST")
     */
    public function create(Request $request, UserPasswordEncoderInterface $passwordEncoder, Mailer $mailer): Response
    {
        $this->denyAccessUnlessGranted('back_user_create');
        
        $user = new User();
        $form = $this->createForm(UserType::class, $user);
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
        $this->denyAccessUnlessGranted('back_user_read', $user);
        
        $situs = $this->situService->countSitusByUser($user->getId());
        
        return $this->render('back/user/read.html.twig', [
            'user' => $user,
            'situs' => $situs,
            'form_delete' => $this->createFormBuilder()->getForm()->createView(),
        ]);
    }

    /**
     * @Route("/{id}/update", name="back_user_update", methods="GET|POST")
     */
    public function update(Request $request, User $user): Response
    {
        $this->denyAccessUnlessGranted('back_user_update', $user);
        
        $form = $this->createForm(UserUpdateType::class, $user);
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
        $users = $this->userManager->getUsers();
        $this->denyAccessUnlessGranted('back_user_delete', $users);
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
        $users = $this->userManager->getUsers();
        $this->denyAccessUnlessGranted('back_user_permute_enabled', $users);
        foreach ($users as $user) {
            $permute = $user->getEnabled() ? false : true;
            $user->setEnabled($permute);
        }
        $this->getDoctrine()->getManager()->flush();
        return $this->redirectToRoute('back_user_search');
    }
}