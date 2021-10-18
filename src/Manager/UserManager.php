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

/**
 * 
 */
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
     * Get the default data from the filter form
     * 
     *  Get saved data in session or default filter form.
     *  
     * @return array
     */
    public function getDefaultFormSearchData()
    {
        return [ 
            'search' => $this->session->get('back_user_search', null),
            'role' => $this->session->get('back_user_role', null),
        ];
    }
    
    /**
     * Valid the multiple selection form
     *
     *  If the result returned is a string the form is not validated and the message is added in the flash bag
     *  
     * @param FormInterface $form
     * @throws LogicException
     * @return boolean|string
     */
    public function validationBatchForm(FormInterface $form)
    {
        $users = $form->get('users')->getData();
        if (0 === count($users)) { return $this->translator->trans("error.no_element_selected", [], 'back_messages'); }
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
     * Valid the delete action from multiple selection form
     *
     *  If the result returned is a string the form is not validated and the message is added in the flash bag
     *  
     * @param array $users     * @return boolean|string
     */
    public function validationDelete($users)
    {
        foreach($users as $user) {
            // Deny SUPER_ADMIN access except SUPER_ADMIN #1
            if ($user->hasRole('ROLE_SUPER_ADMIN')
                    && !$this->security->isGranted('ROLE_SUPER_ADMIN')
                    && $this->security->getUser()->getId() !== 1) {
                return 'B211921';
            }

            // Deny ADMIN access except SUPER_ADMIN
            if ($user->hasRole('ADMIN') &&
                    !$this->security->isGranted('ROLE_SUPER_ADMIN')) {
                return 'B21121';
            }

            // Deny MODERATOR access except ADMIN
            if ($user->hasRole('ROLE_MODERATOR') &&
                    !$this->security->isGranted('ROLE_ADMIN')) {
                return 'B21131';
            }
        }
        return true;
    }
    
    public function validationPermuteEnabled($users)
    {
        foreach($users as $user) {
            // Deny SUPER_ADMIN access except SUPER_ADMIN #1
            if ($user->hasRole('ROLE_SUPER_ADMIN')
                    && !$this->security->isGranted('ROLE_SUPER_ADMIN')
                    && $this->security->getUser()->getId() !== 1) {
                return 'B161921';
            }

            // Deny ADMIN access except SUPER_ADMIN
            if ($user->hasRole('ADMIN') &&
                    !$this->security->isGranted('ROLE_SUPER_ADMIN')) {
                return 'B16121';
            }

            // Deny MODERATOR access except ADMIN
            if ($user->hasRole('ROLE_MODERATOR') &&
                    !$this->security->isGranted('ROLE_ADMIN')) {
                return 'B16131';
            }
        }
        return true;
    }
    
    public function validationUpdate($user)
    {
        // Deny SUPER_ADMIN access except SUPER_ADMIN #1
        if ($user->hasRole('ROLE_SUPER_ADMIN')
                && !$this->security->isGranted('ROLE_SUPER_ADMIN')
                && $this->security->getUser()->getId() !== 1) {
            return 'B211921';
        }
        
        // Deny ADMIN access except SUPER_ADMIN
        if ($user->hasRole('ADMIN') &&
                !$this->security->isGranted('ROLE_SUPER_ADMIN')) {
            return 'B21121';
        }
        
        // Deny MODERATOR access except ADMIN
        if ($user->hasRole('ROLE_MODERATOR') &&
                !$this->security->isGranted('ROLE_ADMIN')) {
            return 'B21131';
        }
        return true;
    }
    
    public function dispatchBatchForm(FormInterface $form)
    {
        $users = $form->get('users')->getData();
        $action = $form->get('action')->getData();
        switch ($action) {
            case 'delete':
                return $this->urlGenerator->generate('back_user_delete', $this->getIds($users));
            case 'permute_enabled':
                return $this->urlGenerator->generate('back_user_permute_enabled', $this->getIds($users));
        }
        return false;
    }
    
    private function getIds($users)
    {
        $ids = [];
        foreach ($users as $user) {
            $ids[] = $user->getId();
        }
        return [ 'ids' => $ids ];
    }
    
    /**
     * Get $users     * 
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