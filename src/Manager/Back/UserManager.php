<?php

namespace App\Manager\Back;

use App\Entity\User;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\DependencyInjection\ParameterBag\ParameterBagInterface;
use Symfony\Component\Form\Exception\LogicException;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;
use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Component\Routing\Exception\InvalidParameterException;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Symfony\Contracts\Translation\TranslatorInterface;

class UserManager
{
    private $em;
    private $requestStack;
    private $supremeAdminId;
    private $translator;
    private $urlGenerator;
    
    public function __construct(EntityManagerInterface $em,
                                ParameterBagInterface $parameters,
                                RequestStack $requestStack,
                                TranslatorInterface $translator,
                                UrlGeneratorInterface $urlGenerator)
    {
        $this->em = $em;
        $this->requestStack = $requestStack;
        $this->supremeAdminId = $parameters->get('supreme_admin_id');
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
        if (0 === count($users)) {
            return $this->translator->trans('error.no_element_selected', [], 'back_messages');
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