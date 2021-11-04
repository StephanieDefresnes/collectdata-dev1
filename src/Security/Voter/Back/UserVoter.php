<?php

namespace App\Security\Voter\Back;

use App\Entity\User;
use Symfony\Component\DependencyInjection\ParameterBag\ParameterBagInterface;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\Security\Core\Authorization\Voter\Voter;
use Symfony\Component\Security\Core\User\UserInterface;
use Symfony\Component\Security\Core\Security;

class UserVoter extends Voter
{
    const UPDATE = 'back_user_update';
    const DELETE = 'back_user_delete';
    const PERMUTE_ENABLED = 'back_user_permute_enabled';
    
    /**
     * @var Security
     */
    private $security;
    private $supremeAdminId;
    
    /** 
     * @param Security $security
     */
    public function __construct(ParameterBagInterface $parameters,
                                Security $security)
    {
        $this->security = $security;
        $this->supremeAdminId = $parameters->get('supreme_admin_id');
    }
    
    protected function supports($attribute, $subject)
    {        
        return in_array($attribute, [
            self::UPDATE,
            self::DELETE,
            self::PERMUTE_ENABLED,
        ]);
    }

    protected function voteOnAttribute($attribute, $subject, TokenInterface $token)
    {
        $user = $token->getUser();
        // the user must be logged in; if not, deny access
        if (!$user instanceof UserInterface) {
            return false;
        }

        // ... (check conditions and return true to grant permission) ...
        switch ($attribute) {
            case self::UPDATE:
                return $this->canUpdate($subject, $user);
            case self::DELETE:
                return $this->canDelete($subject, $user);
            case self::PERMUTE_ENABLED:
                return $this->canPermuteEnabled($subject, $user);
        }
        throw new \LogicException('This code should not be reached!');
    }

    private function canUpdate(User $subject, User $user)
    {
        if ($this->security->isGranted('ROLE_SUPER_ADMIN')
                || $this->security->isGranted('ROLE_SUPER_VISITOR')) {
            return true;
        }
        
        if ($this->security->isGranted('ROLE_ADMIN')) {
            // ADMIN can update MODERATOR only
            if ($subject->hasRole("ROLE_SUPER_ADMIN")
                || $subject->hasRole("ROLE_SUPER_VISITOR")
                || $subject->hasRole("ROLE_ADMIN")
                || ($subject->hasRole("ROLE_MODERATOR")
                    && ($subject->hasRole("ROLE_SUPER_ADMIN")
                        || $subject->hasRole("ROLE_SUPER_VISITOR")
                        || $subject->hasRole("ROLE_ADMIN")))
                    ) {
                return false;
            }
        }
        return true;
    }

    private function canDelete(array $subject, User $user)
    {
        foreach ($subject as $user) {
                
            if ($this->security->isGranted('ROLE_SUPER_ADMIN')) {
                return true;
            }
            // ROLE_SUPER_VISITOR can access to page (but filtered before pushing)
            if ($this->security->isGranted('ROLE_SUPER_VISITOR')) {
                return true;
            }
            
            if (!$this->security->isGranted('ROLE_ADMIN')) {
                
                if ($user->hasRole("ROLE_SUPER_ADMIN")
                    || $user->hasRole("ROLE_SUPER_VISITOR")
                    || $user->hasRole("ROLE_ADMIN")
                    || ($user->hasRole("ROLE_MODERATOR")
                        // ADMIN can permute MODERATOR only
                        && (($user->hasRole("ROLE_SUPER_ADMIN")
                                || $user->hasRole("ROLE_SUPER_VISITOR")
                                || $user->hasRole("ROLE_ADMIN"))
                            || !$this->security->isGranted('ROLE_ADMIN')))) {
                    return false;
                }
            }
        }
        return true;
    }
    
    private function canPermuteEnabled(array $subject, User $user)
    {
        foreach ($subject as $user) {
                
            if (!$this->security->isGranted('ROLE_SUPER_ADMIN')) {
                
                // ROLE_SUPER_VISITOR can access to page (but filtered before pushing)
                if ($this->security->isGranted('ROLE_SUPER_VISITOR')) {
                    return true;
                }
            
                // ADMIN can permute MODERATOR & CONTRIBUTOR only
                if ($this->security->isGranted('ROLE_ADMIN')) {
                    
                    if ($user->hasRole("ROLE_SUPER_ADMIN")
                        || $user->hasRole("ROLE_SUPER_VISITOR")
                        || $user->hasRole("ROLE_ADMIN")) {
                        return false;
                    }
                }
                // MODERATOR can permute CONTRIBUTOR only
                else {                    
                    if ($user->hasRole("ROLE_SUPER_ADMIN")
                        || $user->hasRole("ROLE_SUPER_VISITOR")
                        || $user->hasRole("ROLE_ADMIN")
                        || $user->hasRole("ROLE_MODERATOR")) {
                        return false;
                    }
                }
            }
            // SUPER ADMIN only
            else {
                // Only SUPREME_ADMIN can permute SUPER_VISITOR/ADMIN
                if ($this->security->getUser()->getId() !== $this->supremeAdminId
                    && ($user->hasRole('ROLE_SUPER_ADMIN')
                        || $user->hasRole("ROLE_SUPER_VISITOR"))) {
                    
                }
            }
        }
        return true;
    }
}