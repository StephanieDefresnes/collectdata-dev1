<?php

namespace App\Security\Voter;

use App\Entity\Message;
use App\Entity\User;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\Security\Core\Authorization\Voter\Voter;
use Symfony\Component\Security\Core\User\UserInterface;
use Symfony\Component\Security\Core\Security;

class MessageVoter extends Voter
{
    const ACCESS = 'access_message';
    const READ = 'read_message';
    
    protected function supports( $attribute, $subject )
    {        
        return in_array($attribute, [
            self::ACCESS,
            self::READ,
        ]);
    }

    protected function voteOnAttribute( $attribute, $subject, TokenInterface $token )
    {
        $user = $token->getUser();
        // the user must be logged in; if not, deny access
        if ( ! $user instanceof UserInterface ) return false;

        // ... (check conditions and return true to grant permission) ...
        switch ( $attribute ) {
            case self::ACCESS:
                return $this->canAccess( $subject, $user );
            case self::READ:
                return $this->canRead( $subject, $user );
        }
        throw new \LogicException('This code should not be reached!');
    }

    private function canAccess( Message $subject, User $user )
    {
        // Only recipient can delete message
        if ( $user !== $subject->getRecipientUser() ) return false;
        return true;
    }

    private function canRead( Message $subject, User $user )
    {
        // Only recipient or sender can read message
        if ( $user !== $subject->getRecipientUser() ) return false;
        
        // Only envelope can be read message
        if ( 'envelope' !== $subject->getType() ) return false;
        return true;
    }
}