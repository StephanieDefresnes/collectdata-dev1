<?php

namespace App\Manager;

use App\Entity\User;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Form\Exception\LogicException;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;
use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Component\HttpFoundation\Session\SessionInterface;
use Symfony\Component\Routing\Exception\InvalidParameterException;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Symfony\Contracts\Translation\TranslatorInterface;
use Symfony\Component\Security\Core\Security;

class UserManager
{
    private $em;
    private $requestStack;
    private $security;
    private $session;
    private $translator;
    private $urlGenerator;
    
    public function __construct(EntityManagerInterface $em,
                                RequestStack $requestStack,
                                Security $security,
                                SessionInterface $session,
                                TranslatorInterface $translator,
                                UrlGeneratorInterface $urlGenerator)
    {
        $this->em = $em;
        $this->requestStack = $requestStack;
        $this->security = $security;
        $this->session = $session;
        $this->translator = $translator;
        $this->urlGenerator = $urlGenerator;
    }
    
    /**
     * Valid the multiple selection form
     *  If the result returned is a string
     *      the form is not validated and the message is added in the flash bag
     *  
     * @param FormInterface $form
     */
    public function validationBatchForm(FormInterface $form)
    {
        $users = $form->get('users')->getData();
        if (0 === count($users)) { return $this->translator->trans('error.no_element_selected', [], 'back_messages'); }
        $action = $form->get('action')->getData();
        
        switch ($action) {
            case 'delete':
                return $this->validationDelete($users);
            case 'permute_enabled':
                return $this->validationPermuteEnabled($users);
        }
        return true;
    }

    /**
     * Valid the delete action
     *  If current user is not super admin and the result returned is not true
     *      the form is not validated and access is denied
     *  If current user is super admin and the result returned is not true
     *      the form is not validated and the message is added in the flash bag
     * 
     * @param array $users
     * @return boolean|array
     */
    public function validationDelete($users)
    {
        foreach($users as $user) {
            
            if (!$this->security->isGranted('ROLE_SUPER_ADMIN')) {
                
                // Deny SUPER_ADMIN access except SUPER_ADMIN #1
                if ($user->hasRole('ROLE_SUPER_ADMIN')) {
                    return $result = ['denied' => true, 'msg' => 'B211921'];
                }

                // Deny ADMIN access except SUPER_ADMIN
                if ($user->hasRole('ADMIN')) {
                    return $result = ['denied' => true, 'msg' => 'B21121'];
                }

                // Deny MODERATOR access except ADMIN
                if ($user->hasRole('ROLE_MODERATOR') &&
                        !$this->security->isGranted('ROLE_ADMIN')) {
                    return $result = ['denied' => true, 'msg' => 'B21131'];
                }
            } else {
                // Cannot delete SUPER_ADMIN #1
                if ($user->hasRole('ROLE_SUPER_ADMIN') && $user->getId() === 1) {
                    return $result = [
                        'denied' => false,
                        'msg' => $this->translator
                            ->trans('user.error.cannot_delete_super_admin',[], 'back_messages'),
                    ];
                }
            }
        }
        return true;
    }

    /**
     * Valid the permute action
     *  If current user is not super admin and the result returned is not true
     *      the form is not validated and access is denied
     *  If current user is super admin and the result returned is not true
     *      the form is not validated and the message is added in the flash bag
     * 
     * @param array $users     
     * @return boolean|array
     */
    public function validationPermuteEnabled($users)
    {
        foreach($users as $user) {
            
            if (!$this->security->isGranted('ROLE_SUPER_ADMIN')) {
                
                // Deny SUPER_ADMIN access except SUPER_ADMIN #1
                if ($user->hasRole('ROLE_SUPER_ADMIN')) {
                    return $result = ['denied' => true, 'msg' => 'B161921'];
                }

                // Deny ADMIN access except SUPER_ADMIN
                if ($user->hasRole('ADMIN')) {
                    return $result = ['denied' => true, 'msg' => 'B16121'];
                }

                // Deny MODERATOR access except ADMIN
                if ($user->hasRole('ROLE_MODERATOR') &&
                        !$this->security->isGranted('ROLE_ADMIN')) {
                    return $result = ['denied' => true, 'msg' => 'B16131'];
                }
            } else {
                // Cannot permute SUPER_ADMIN #1
                if ($user->hasRole('ROLE_SUPER_ADMIN') && $user->getId() === 1) {
                    return $result = [
                        'denied' => false,
                        'msg' => $this->translator
                            ->trans('user.error.cannot_update_super_admin', [], 'back_messages'),
                    ];
                }
            }
        }
        return true;
    }

    /**
     * Valid the update action
     *  If current user is not super admin and the result returned is not true
     *      the form is not validated and access is denied
     * 
     * @param array $users     
     * @return boolean|string
     */
    public function validationUpdate($user)
    {
        // Except SUPER_ADMIN #1
        if ($this->security->getUser()->getId() !== 1) {
            
            // Deny SUPER_ADMIN access
            if ($user->hasRole('ROLE_SUPER_ADMIN')) {
                return 'B211921';
            }

            // Deny ADMIN access except SUPER_ADMIN
            elseif ($user->hasRole('ROLE_ADMIN') &&
                    !$this->security->isGranted('ROLE_SUPER_ADMIN')) {
                return 'B21121';
            }

            // Deny MODERATOR except ADMIN
            elseif ($user->hasRole('ROLE_MODERATOR') &&
                    !$this->security->isGranted('ROLE_ADMIN')) {
                return 'B21131';
            }
        }
        return true;
    }

    /**
     * Dispatch the action
     * 
     * @param FormInterface $form   
     * @return boolean|url
     */
    public function dispatchBatchForm(FormInterface $form)
    {
        $users = $form->get('users')->getData();
        $action = $form->get('action')->getData();
        switch ($action) {
            case 'delete':
                return $this->urlGenerator->generate(
                            'back_user_delete',
                            $this->getIds($users)
                        );
            case 'permute_enabled':
                return $this->urlGenerator->generate(
                            'back_user_permute_enabled',
                            $this->getIds($users)
                        );
        }
        return false;
    }
    
    
    /**
     * Get ids
     *  Transform entities list into an array compatible with url parameters.
     *  The returned array must be merged with the parameters of the route.
     *
     * @param array $users
     * @return array
     */
    private function getIds($users)
    {
        $ids = [];
        foreach ($users as $user) {
            $ids[] = $user->getId();
        }
        return [ 'ids' => $ids ];
    }
    
    /**
     * Get $users
     *  Transform query parameter ids list into an array entities list.
     * 
     * @throws InvalidParameterException
     * @throws NotFoundHttpException
     * @return array
     */
    public function getUsers()
    {    
        $request = $this->requestStack->getCurrentRequest();
        $ids = $request->query->get('ids', null);
        if (!is_array($ids)) { throw new InvalidParameterException(); }
        $users = $this->em->getRepository(User::class)->findById($ids);
        if (count($ids) !== count($users)) { throw new NotFoundHttpException(); }
        return $users;
    }
}