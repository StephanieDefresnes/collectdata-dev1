<?php

namespace App\Security\Voter\Back;

use App\Entity\User;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\Security\Core\Authorization\Voter\Voter;
use Symfony\Component\Security\Core\User\UserInterface;
use Symfony\Component\Security\Core\Security;

class UserVoter extends Voter
{
    const SEARCH = 'back_user_search';
    const CREATE = 'back_user_create';
    const READ = 'back_user_read';
    const UPDATE = 'back_user_update';
    const DELETE = 'back_user_delete';
    const PERMUTE_ENABLED = 'back_user_permute_enabled';
    
    /**
     * @var Security
     */
    private $security;
    
    /** 
     * @param Security $security
     */
    public function __construct(Security $security)
    {
        $this->security = $security;
    }
    
    protected function supports($attribute, $subject)
    {        
        return in_array($attribute, [
            self::READ,
            self::UPDATE,
            self::DELETE,
            self::PERMUTE_ENABLED,
        ]);
    }

    protected function voteOnAttribute($attribute, $subject, TokenInterface $token)
    {
        $user = $token->getUser();
        // if the user is anonymous, do not grant access
        if (!$user instanceof UserInterface) {
            return false;
        }

        // ... (check conditions and return true to grant permission) ...
        switch ($attribute) {
            case self::READ:
                return $this->canRead($subject, $user);
            case self::UPDATE:
                return $this->canUpdate($subject, $user);
            case self::DELETE:
                return $this->canDelete($subject, $user);
            case self::PERMUTE_ENABLED:
                return $this->canPermuteEnabled($subject, $user);
        }
        throw new \LogicException('This code should not be reached!');
    }

    private function canRead(User $subject, User $user)
    {
        if (!$this->security->isGranted('ROLE_ADMIN')) {
            return false;
        }
        return true;
    }

    private function canUpdate(User $subject, User $user)
    {
        if (!$this->security->isGranted('ROLE_ADMIN')) {
            return false;
        }
        if ($subject->hasRole("ROLE_SUPER_ADMIN")) {
           return false;
        }
        return true;
    }

    private function canDelete(array $subject, User $user)
    {
        if (!$this->security->isGranted('ROLE_ADMIN')) {
            return false;
        }
        foreach ($subject as $user) {
            if ($user->hasRole("ROLE_SUPER_ADMIN")) {
                return false;
            }
        }
        return true;
    }
    
    private function canPermuteEnabled(array $subject, User $user)
    {
        if (!$this->security->isGranted('ROLE_ADMIN')) {
            return false;
        }
        foreach ($subject as $user) {
            if ($user->hasRole("ROLE_SUPER_ADMIN")) {
                return false;
            }
        }
        return true;
    }
}