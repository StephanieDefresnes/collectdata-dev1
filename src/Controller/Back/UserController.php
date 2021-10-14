<?php

namespace App\Controller\Back;

use App\Entity\User;
use App\Form\Back\User\UserBatchType;
use App\Form\Back\User\UserUpdateFormType;
use App\Mailer\Mailer;
use App\Manager\UserManager;
use App\Service\LangService;
use App\Service\SituService;
use App\Service\UserService;
use Doctrine\ORM\EntityManagerInterface;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\IsGranted;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\Session\Session;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Core\Security;
use Symfony\Contracts\Translation\TranslatorInterface;

/**
 * @IsGranted("IS_AUTHENTICATED_FULLY")
 * @Route("/{_locale<%app_locales%>}/back/user")
 */
class UserController extends AbstractController
{
    private $em;
    private $langService;
    private $security;
    private $situService;
    private $translator;
    private $userManager;
    private $userService;
    
    public function __construct(EntityManagerInterface $em,
                                LangService $langService,
                                Security $security,
                                SituService $situService,
                                TranslatorInterface $translator,
                                UserManager $userManager,
                                UserService $userService)
    {
        $this->em = $em;
        $this->langService = $langService;
        $this->security = $security;
        $this->situService = $situService;
        $this->translator = $translator;
        $this->userManager = $userManager;
        $this->userService = $userService;
    }

    /**
     * @IsGranted("ROLE_MODERATOR")
     * @Route("/search", name="back_user_search", methods="GET|POST")
     */
    public function allUsers(Request $request, Session $session)
    {
        $users = $this->userService->getUsers();
        
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
            'form_batch' => $formBatch->createView(),
            'form_delete' => $this->createFormBuilder()->getForm()->createView(),
        ]);
    }

    /**
     * @IsGranted("ROLE_MODERATOR")
     * @Route("/read/{id}", name="back_user_read", methods="GET")
     */
    public function read(User $user): Response
    {
        return $this->render('back/user/read.html.twig', [
            'user' => $user,
            'situs' => count($user->getSitus()),
            'form_delete' => $this->createFormBuilder()->getForm()->createView(),
        ]);
    }

    /**
     * @IsGranted("ROLE_MODERATOR")
     * @Route("/update/{id}", name="back_user_update", methods="GET|POST")
     */
    public function update(Request $request, $id): Response
    {
        $user = $this->em->getRepository(User::class)->find($id);
        if (!$user) {
            return $this->redirectToRoute('not_found', ['_locale' => locale_get_default()]);
        }
        
        $result = $this->userManager->validationUpdate($user);
        if (true !== $result) {
            return $this->redirectToRoute('access_denied', [
                '_locale' => locale_get_default(),
                'code' => $result,
            ]);
        }
        
        // Form depending on user role
        $role = '';
        if ($this->security->isGranted('ROLE_SUPER_ADMIN')) {
            $role = 'super-admin';
        } else if ($this->security->isGranted('ROLE_ADMIN')) {
            $role = 'admin';
        }
        $form = $this->createForm(UserUpdateFormType::class, $user, ['role' => $role]);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $this->em->flush();
            $msg = $this->translator->trans('user.update.flash.success.', [], 'back_messages');
            $this->addFlash('success', $msg);
            return $this->redirectToRoute('back_user_search');
        }

        return $this->render('back/user/update.html.twig', [
            'user' => $user,
            'form' => $form->createView(),
        ]);
    }

    /**
     * @IsGranted("ROLE_ADMIN")
     * @Route("/delete", name="back_user_delete", methods="GET|POST")
     */
    public function delete(Request $request): Response
    {    
        $users = $this->userManager->getUsers();
        
        $result = $this->userManager->validationPermuteEnabled($users);
        if (true !== $result) {
            return $this->redirectToRoute('access_denied', [
                '_locale' => locale_get_default(),
                'code' => $result,
            ]);
        }
        
        $anonymous = $this->em->getRepository(User::class)->find(intval('-99'));
        
        foreach ($users as $user) {
            
            // If get situs, set them to anonymous
            if($user->getSitus()) {
                foreach ($user->getSitus() as $situ) {
                    $situ->setUser($anonymous);
                }
            }
            // If get events, set them to anonymous
            if($user->getEvents()) {
                foreach ($user->getEvents() as $event) {
                    $event->setUser($anonymous);
                }
            }
            // If get categories, set them to anonymous
            if($user->getCategories()) {
                foreach ($user->getCategories() as $category) {
                    $category->setUser($anonymous);
                }
            }
            // If get translations, set them to anonymous
            if($user->getTranslations()) {
                foreach ($user->getTranslations() as $translation) {
                    $translation->setUser($anonymous);
                }
            }
            // If recevied messages, set them to anonymous
            if($user->getRecipients()) {
                foreach ($user->getSenders() as $message) {
                    $message->setRecipientUser($anonymous);
                }
            }
            // If sent messages, removed by orphanRemoval
             
            // If get image, remove it
            if($user->getImageFilename()) {
                unlink($this->getParameter('user_img').'/'.$user->getImageFilename());
            }
            
            $this->em->remove($user);
        }
        
        if (count($users) > 1) $type = 'users';
        else $type = 'user';
            
        try {
            $this->em->flush();
            $msg = $this->translator
                    ->trans('user.delete.flash.success.'. $type, [], 'back_messages');
            $this->addFlash('success', $msg);
        } catch (\Doctrine\DBAL\DBALException $e) {
            $this->addFlash('warning', $e->getMessage());
        }
        return $this->redirectToRoute('back_user_search');
    }
    
    /**
     * @IsGranted("ROLE_ADMIN")
     * @Route("/permute/enabled", name="back_user_permute_enabled", methods="GET")
     */
    public function permuteEnabled(Request $request): Response
    {    
        $users = $this->userManager->getUsers();
        
        $result = $this->userManager->validationPermuteEnabled($users);
        if (true !== $result) {
            return $this->redirectToRoute('access_denied', [
                '_locale' => locale_get_default(),
                'code' => $result,
            ]);
        }
        $permute = '';
        foreach ($users as $user) {
            $permute = $user->getEnabled() ? false : true;
            $user->setEnabled($permute);
        }
        
        if (count($users) > 1) $type = 'users';
        else {
            if ($permute) $type = 'user_disabled';
            else $type = 'user_enabled';
        }
        
        try {
            $this->em->flush();
            $msg = $this->translator
                    ->trans('user.update.flash.success.'. $type, [], 'back_messages');
            $this->addFlash('success', $msg);
        } catch (\Doctrine\DBAL\DBALException $e) {
            $this->addFlash('warning', $e->getMessage());
        }
        return $this->redirectToRoute('back_user_search');
    }
}