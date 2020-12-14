<?php

namespace App\Form\Back;

use App\Manager\UserManager;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\IntegerType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Security\Core\Role\RoleHierarchyInterface;
use Symfony\Component\Security\Core\Role\Role;

class UserFilterType extends AbstractType
{
    /**
     *
     * @var RoleHierarchyInterface
     */
    private $roleHierarchy;

    public function __construct(RoleHierarchyInterface $roleHierarchy)
    {
        $this->roleHierarchy = $roleHierarchy;
    }
    
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $reachableRoleNames = $this->roleHierarchy->getReachableRoleNames([ 'ROLE_ADMIN', ]);
        $roles = [];
        foreach ($reachableRoleNames as $reachableRoleName) {
            $roles[$reachableRoleName] = $reachableRoleName;
        }
        $builder
            ->add('search', TextType::class, [
                'label' => false,
                'attr' => [
                    'placeholder' => 'label.filter_search',
                ],
                'required' => false,
            ])
            ->add('role', ChoiceType::class, [
                'label' => false,
                'placeholder' => 'user.label.roles',
                'choices' => $roles,
                'multiple' => false,
                'expanded' => false,
                'required' => false,
            ])
            ->add('number_by_page', IntegerType::class, [
                'label' => false,
                'attr' => [
                    'placeholder' => 'label.filter_number_by_page',
                ],
                'empty_data' => UserManager::NUMBER_BY_PAGE,
            ]);
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'csrf_protection' => false,
            'method' => 'GET',
            'translation_domain' => 'back_messages',
        ]);
    }
    
    public function getBlockPrefix()
    {
        return 'filter';
    }
}
