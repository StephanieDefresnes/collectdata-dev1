<?php

namespace App\Security\Voter;

use App\Entity\Situ;
use App\Entity\User;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\Security\Core\Authorization\Voter\Voter;
use Symfony\Component\Security\Core\User\UserInterface;
use Symfony\Component\Security\Core\Security;

class SituVoter extends Voter
{
    const DELETE        = 'delete_situ';
    const READ          = 'read_situ';
    const TRANSLATE     = 'translate_situ';
    const UPDATE        = 'update_situ';
    const VALIDATION    = 'validation_situ';
    
    private $security;
    
    public function __construct( Security $security )
    {
        $this->security = $security;
    }
    
    protected function supports( $attribute, $subject )
    {        
        return in_array( $attribute, [
            self::DELETE,
            self::READ,
            self::UPDATE,
            self::VALIDATION,
        ] );
    }

    protected function voteOnAttribute( $attribute, $subject, TokenInterface $token )
    {
        $user = $token->getUser();
        // the user must be logged in; if not, deny access
        if ( ! $user instanceof UserInterface ) return false;

        // ... (check conditions and return true to grant permission) ...
        switch ( $attribute ) {
            case self::DELETE:
                return $this->canDelete( $subject, $user );
            case self::READ:
                return $this->canRead( $subject, $user );
            case self::UPDATE:
                return $this->canUpdate( $subject, $user );
            case self::VALIDATION:
                return $this->canValidation( $subject, $user );
        }
        
        throw new \LogicException('This code should not be reached!');
    }

    private function canDelete( Situ $subject, User $user )
    {
        // Only situ author can delete situ
        if ( $user !== $subject->getUser() ) return false;
        
        return true;
    }

    private function canRead( array $subject, User $user )
    {
        $subjectStatus = $subject['situ']->getStatus()->getId();
        
        // A user needs to be connected and paramter preview not null
        // to be allow to read a validation requested situ
        if ( 2 === $subjectStatus
                && ( ! $user || null === $subject['preview'] ) ) return false;
        
        // None can read a not validated situ 
        if ( 3 !== $subjectStatus && 2 !== $subjectStatus ) return false;
        
        return true;
    }

    private function canUpdate( Situ $subject, User $user )
    {
        // Only situ author can update situ
        if ( $user !== $subject->getUser() ) return false;
        
        return true;
    }

    private function canValidation( Situ $subject, User $user )
    {
        // Only situ author can request validation
        if ( $user !== $subject->getUser() ) return false;
        
        return true;
    }
}