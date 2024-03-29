<?php

namespace App\Security\Voter;

use App\Entity\User;
use App\Repository\UserRepository;
use Symfony\Component\DependencyInjection\ParameterBag\ParameterBagInterface;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\Security\Core\Authorization\Voter\Voter;
use Symfony\Component\Security\Core\User\UserInterface;
use Symfony\Component\Security\Core\Security;

class UserVoter extends Voter
{
    const DELETE            = 'back_user_delete';
    const PERMUTE_ENABLED   = 'back_user_permute_enabled';
    const UPDATE            = 'back_user_update';
    const TRANSLATOR        = 'situ_translator';
    
    private $security;
    private $supremeAdmin;
    
    /** 
     * @param Security $security
     */
    public function __construct(ParameterBagInterface $parameters,
                                Security $security,
                                UserRepository $userRepository)
    {
        $this->security = $security;
        $this->supremeAdmin = $userRepository->find($parameters->get('supreme_admin_id'));
    }
    
    protected function supports($attribute, $subject)
    {        
        return in_array($attribute, [
            self::DELETE,
            self::PERMUTE_ENABLED,
            self::TRANSLATOR,
            self::UPDATE,
        ]);
    }

    protected function voteOnAttribute( $attribute, $subject, TokenInterface $token )
    {
        $user = $token->getUser();
        
        // the user must be logged in; if not, deny access
        if ( ! $user instanceof UserInterface ) return false;

        // ... (check conditions and return true to grant permission) ...
        switch ($attribute) {
            case self::DELETE:
                return $this->canDelete( $subject, $user );
            case self::PERMUTE_ENABLED:
                return $this->canPermuteEnabled( $subject, $user );
            case self::TRANSLATOR:
                return $this->canTranslate( $user );
            case self::UPDATE:
                return $this->canUpdate( $subject, $user );
        }
        throw new \LogicException('This code should not be reached!');
    }

    private function canDelete( array $subject, User $current )
    {
        foreach ( $subject as $user ) {
            
            // None can delete SUPREME ADMIN
            if ( $user === $this->supremeAdmin ) return false;
            
            // Only SUPREME ADMIN can delete SUPER_ADMIN/VISITOR
            if ( $this->security->isGranted("ROLE_SUPER_ADMIN")
                && $user->hasRole("ROLE_SUPER_ADMIN")
                && $user->hasRole("ROLE_SUPER_VISITOR")
                && $current !== $this->supremeAdmin )
            {
                return false;
            }
            
            // ADMIN can delete MODERATOR and CONTRIBUTOR only
            if ( $this->security->isGranted("ROLE_ADMIN")
                    && ( ! $this->security->isGranted('ROLE_SUPER_ADMIN')
                        || ! $this->security->isGranted('ROLE_SUPER_VISITOR') )
                    && ( $user->hasRole("ROLE_SUPER_ADMIN")
                        || $user->hasRole("ROLE_SUPER_VISITOR")
                        || $user->hasRole("ROLE_ADMIN")
                        || ( $user->hasRole("ROLE_MODERATOR")
                            && ( $user->hasRole("ROLE_SUPER_ADMIN")
                                    || $user->hasRole("ROLE_SUPER_VISITOR")
                                    || $user->hasRole("ROLE_ADMIN") ) ) ) )
            {
                return false;
            }
        }
        return true;
    }
    
    private function canPermuteEnabled( array $subject, User $current )
    {
        foreach ( $subject as $user ) {
            
            // None can permute SUPREME ADMIN
            if ( $user === $this->supremeAdmin ) return false;
            
            // Only SUPREME ADMIN can permute SUPER_ADMIN/VISITOR
            if ( $this->security->isGranted("ROLE_SUPER_ADMIN")
                && $user->hasRole("ROLE_SUPER_ADMIN")
                && $user->hasRole("ROLE_SUPER_VISITOR")
                && $user !== $this->supremeAdmin )
            {
                return false;
            }
            
            // ADMIN can permute MODERATOR and CONTRIBUTOR only
            if ( $this->security->isGranted('ROLE_ADMIN')
                    && ( ! $this->security->isGranted('ROLE_SUPER_ADMIN')
                        || ! $this->security->isGranted('ROLE_SUPER_VISITOR'))
                    && ( $user->hasRole("ROLE_SUPER_ADMIN")
                        || $user->hasRole("ROLE_SUPER_VISITOR")
                        || $user->hasRole("ROLE_ADMIN")
                        || ( $user->hasRole("ROLE_MODERATOR")
                            && ( $user->hasRole("ROLE_SUPER_ADMIN")
                                    || $user->hasRole("ROLE_SUPER_VISITOR")
                                    || $user->hasRole("ROLE_ADMIN") ) ) ) )
            {
                return false;
            }
        }
        return true;
    }
    
    private function canTranslate( User $user )
    {
        if ( count( $user->getLangs()->getValues() ) < 2 ) return false;
        
        return true;
    }

    private function canUpdate( User $subject, User $user )
    {
        // Only SUPREME ADMIN can update SUPER_ADMIN/VISITOR
        if ( $this->security->isGranted("ROLE_SUPER_ADMIN")
            && $subject->hasRole("ROLE_SUPER_ADMIN")
            && $subject->hasRole("ROLE_SUPER_VISITOR")
            && $user !== $this->supremeAdmin )
        {
            return false;
        }
        
        // ADMIN can update MODERATOR & CONTRIBUTOR only
        if ( $this->security->isGranted("ROLE_ADMIN")
            && ( !$this->security->isGranted("ROLE_SUPER_ADMIN")
                || !$this->security->isGranted("ROLE_SUPER_VISITOR") )
            && ( $subject->hasRole("ROLE_SUPER_ADMIN")
                || $subject->hasRole("ROLE_SUPER_VISITOR")
                || $subject->hasRole("ROLE_ADMIN")
                || ( $subject->hasRole("ROLE_MODERATOR")
                    && ( $subject->hasRole("ROLE_SUPER_ADMIN")
                        || $subject->hasRole("ROLE_SUPER_VISITOR")
                        || $subject->hasRole("ROLE_ADMIN") ) ) ) )
        {
            return false;
        }
        return true;
    }
}