<?php

namespace App\Security\Voter;

use App\Entity\Situ;
use App\Entity\User;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\Security\Core\Authorization\Voter\Voter;
use Symfony\Component\Security\Core\User\UserInterface;
use Symfony\Component\Security\Core\Security;

class LangVoter extends Voter
{
    const LANG = 'lang';
    
    private $security;
    
    public function __construct( Security $security )
    {
        $this->security = $security;
    }
    
    protected function supports( $attribute, $subject )
    {        
        return in_array( $attribute, [
            self::LANG,
        ] );
    }

    protected function voteOnAttribute( $attribute, $subject, TokenInterface $token )
    {
        $user = $token->getUser();
        // the user must be logged in; if not, deny access
        if ( ! $user instanceof UserInterface ) return false;

        // ... (check conditions and return true to grant permission) ...
        switch ( $attribute ) {
            case self::LANG:
                return $this->canTranslate( $subject, $user );
        }
        
        throw new \LogicException('This code should not be reached!');
    }

    private function canTranslate( array $subject, User $user )
    {        
        // If lang !== the lang of situ to translate & lang is enabled and is in user langs
        if ( $subject['lang'] !== $subject['initialLang']
                && $subject['lang']->getEnabled()
                && $user->getLangs()->contains( $subject['lang'] ) ) return true;
        
        return false;
    }
}