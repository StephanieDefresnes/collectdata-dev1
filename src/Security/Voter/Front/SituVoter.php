<?php

namespace App\Security\Voter\Front;

use App\Entity\Situ;
use App\Entity\User;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\Security\Core\Authorization\Voter\Voter;
use Symfony\Component\Security\Core\User\UserInterface;
use Symfony\Component\Security\Core\Security;

class SituVoter extends Voter
{
    const DELETE = 'delete_situ';
    const READ = 'read_situ';
    const TRANSLATE = 'translate_situ';
    const UPDATE = 'create_situ';
    const VALIDATION = 'validation_situ';
    
    private $security;
    
    public function __construct(Security $security)
    {
        $this->security = $security;
    }
    
    protected function supports($attribute, $subject)
    {        
        return in_array($attribute, [
            self::DELETE,
            self::READ,
            self::TRANSLATE,
            self::UPDATE,
            self::VALIDATION,
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
            case self::DELETE:
                return $this->canDelete($subject, $user);
            case self::READ:
                return $this->canRead($subject, $user);
            case self::TRANSLATE:
                return $this->canTranslate($subject);
            case self::UPDATE:
                return $this->canUpdate($subject, $user);
            case self::VALIDATION:
                return $this->canValidation($subject, $user);
        }
        throw new \LogicException('This code should not be reached!');
    }

    private function canDelete(Situ $situ, User $user)
    {
        // Only situ author can delete situ
        if ($user !== $situ->getUser()) { return false; }
        return true;
    }

    private function canRead(array $subject, User $user)
    {
        // - A user must be connected and paramter preview not null
        // to be allow to read a validation requested situ
        // - Else none can read a not validated situ 
        if ($subject['situ']->getStatus()->getId() === 2) {
            if ((null !== $subject['preview'] && !$user)
                    || (null === $subject['preview'] && $user)) {
                return false;
            }
        } elseif ($subject['situ']->getStatus()->getId() !== 3) {
            return false;
        }
        return true;
    }

    private function canUpdate(Situ $situ, User $user)
    {
        // Only situ author can delete situ
        if ($user !== $situ->getUser()) { return false; }
        return true;
    }

    private function canTranslate(array $subject)
    {        
        // If lang is Lang situ to translate or lang is not enabled
        if ($subject['lang'] === $subject['situ']->getLang()
                || true !== $subject['lang']->getEnabled()) { return false; }
        return true;
    }

    private function canValidation(Situ $situ, User $user)
    {
        // Only situ author can delete situ
        if ($user !== $situ->getUser()) { return false; }
        return true;
    }
}