<?php

namespace App\Controller\Back;

use App\Entity\User;
use App\Form\Back\User\UserBatchType;
use App\Form\Back\User\UserUpdateFormType;
use App\Manager\Back\UserManager;
use Doctrine\ORM\EntityManagerInterface;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\IsGranted;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Security\Core\Security;
use Symfony\Contracts\Translation\TranslatorInterface;

/**
 * @IsGranted("IS_AUTHENTICATED_FULLY")
 */
class UserController extends AbstractController
{
    private $em;
    private $security;
    private $translator;
    private $userManager;
    
    public function __construct(EntityManagerInterface $em,
                                Security $security,
                                TranslatorInterface $translator,
                                UserManager $userManager)
    {
        $this->em = $em;
        $this->security = $security;
        $this->translator = $translator;
        $this->userManager = $userManager;
    }

    public function allUsers(Request $request)
    {
        $users = $this->em->getRepository(User::class)->findUsers();
        
        $formBatch = $this->createForm(UserBatchType::class, null, [
            'action' => $this->generateUrl('back_user_search'),
            'users' => $users,
        ]);
        $formBatch->handleRequest($request);
        if ($formBatch->isSubmitted() && $formBatch->isValid()) {
            $url = $this->userManager->dispatchBatchForm($formBatch);
            if ($url) { return $this->redirect($url); }
        }
        
        return $this->render('back/user/search.html.twig', [
            'form_batch' => $formBatch->createView(),
        ]);
    }

    public function read($id): Response
    {
        $user = $this->em->getRepository(User::class)->find($id);
        if (!$user) {
            return $this->redirectToRoute('back_not_found', ['_locale' => locale_get_default()]);
        }
        
        return $this->render('back/user/read.html.twig', [
            'user' => $user,
            'situs' => count($user->getSitus()),
            'form_delete' => $this->createFormBuilder()->getForm()->createView(),
        ]);
    }

    /**
     * @IsGranted("ROLE_ADMIN")
     */
    public function update(Request $request, $id): Response
    {
        $user = $this->em->getRepository(User::class)->find($id);
        if (!$user) {
            return $this->redirectToRoute('back_not_found', [
                '_locale' => locale_get_default()
            ]);
        }
        
        // Check permission
        $this->denyAccessUnlessGranted('back_user_update', $user);
        
        // Prevent SUPER_VISITOR flush
        $result = $this->userManager->preventSuperVisitor();
        
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
            
            try {
                if (false !== $result) return $this->redirect($result);
            
                $this->em->flush();
                
                $msg = $this->translator
                        ->trans('user.update.flash.success', [], 'back_messages');
                $this->addFlash('success', $msg);
                
            } catch (\Doctrine\DBAL\DBALException $e) {
                $this->addFlash('warning', $e->getMessage());
            }
            return $this->redirectToRoute('back_user_search');
        }

        return $this->render('back/user/update.html.twig', [
            'user' => $user,
            'form' => $form->createView(),
        ]);
    }

    /**
     * @IsGranted("ROLE_ADMIN")
     */
    public function delete(Request $request): Response
    {   
        $users = $this->userManager->getUsers();
        
        // Check permission
        $this->denyAccessUnlessGranted('back_user_delete', $users);
        
        // Prevent SUPER_VISITOR flush
        $result = $this->userManager->preventSuperVisitor();;

        // Replace contributions author
        $this->userManager->anonymizeContributions($users);

        $type = count($users) > 1 ? 'users' : 'user';

        try {
            if (false !== $result) return $this->redirect($result);
            
            $this->em->flush();
            
            $msg = $this->translator
                    ->trans('user.delete.flash.success.'. $type, [], 'back_messages');
            $this->addFlash('success', $msg);

        } catch (\Doctrine\DBAL\DBALException $e) {
            $this->addFlash('warning', $e->getMessage());
        }
        return $this->redirectToRoute('back_user_search');
    }
    
    public function permuteEnabled(Request $request): Response
    {     
        $users = $this->userManager->getUsers();
        
        // Check permission
        $this->denyAccessUnlessGranted('back_user_permute_enabled', $users);
        
        // Prevent SUPER_VISITOR flush
        $result = $this->userManager->preventSuperVisitor();
            
        $permute;
        foreach ($users as $user) {
            $permute = $user->getEnabled() ? false : true;
            $user->setEnabled($permute);
        }
        
        
        $type = count($users) > 1
                    ? 'users'
                    : ( $permute ? 'user_enabled' : 'user_disabled' );

        try {
            if (false !== $result) return $this->redirect($result);

            $this->em->flush();
            
            $msg = $this->translator
                    ->trans('user.permute.flash.success.'. $type, [], 'back_messages');
            $this->addFlash('success', $msg);

        } catch (\Doctrine\DBAL\DBALException $e) {
            $this->addFlash('warning', $e->getMessage());
        }
        return $this->redirectToRoute('back_user_search');
    }
}