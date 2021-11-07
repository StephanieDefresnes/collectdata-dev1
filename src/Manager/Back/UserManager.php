<?php

namespace App\Manager\Back;

use App\Entity\User;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\DependencyInjection\ParameterBag\ParameterBagInterface;
use Symfony\Component\Form\Exception\LogicException;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;
use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Symfony\Component\Routing\Exception\InvalidParameterException;
use Symfony\Component\Security\Core\Security;
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
                                Security $security,
                                UrlGeneratorInterface $urlGenerator)
    {
        $this->em = $em;
        $this->requestStack = $requestStack;
        $this->security = $security;
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
    
    /**
     * Prevent SUPER_VISITOR before push
     * 
     * @param type $user
     */
    public function preventSuperVisitor($user)
    {
        if ($this->security->getUser()->hasRole('ROLE_SUPER_VISITOR')) {
            return $this->redirectToRoute('back_access_denied', [
                '_locale' => locale_get_default()
            ]);
        }
    }

    /**
     * Anonymize all users contributions and remove before flush
     */
    public function anonymizeContributions($users)
    {   
        
        // Assign user contribs to anonymous
        $anonymous = $this->em->getRepository(User::class)->find(0);

        foreach ($users as $user) {
            
            // If get situs, set them to anonymous
            if ($user->getSitus()) {
                foreach ($user->getSitus() as $situ) {
                    $situ->setUser($anonymous);
                }
            }
            // If get events, set them to anonymous
            if ($user->getEvents()) {
                foreach ($user->getEvents() as $event) {
                    $event->setUser($anonymous);
                }
            }
            // If get categories, set them to anonymous
            if ($user->getCategories()) {
                foreach ($user->getCategories() as $category) {
                    $category->setUser($anonymous);
                }
            }
            // If get translations, set them to anonymous
            if ($user->getTranslations()) {
                foreach ($user->getTranslations() as $translation) {
                    $translation->setUser($anonymous);
                }
            }
            // If recevied messages, set them to anonymous
            if ($user->getRecipients()) {
                foreach ($user->getSenders() as $message) {
                    $message->setRecipientUser($anonymous);
                }
            }
            // If sent messages, removed by orphanRemoval

            // If get image, remove it
            if($user->getImageFilename()) {
                unlink($this->getParameter('user_img').'/'.$user->getImageFilename());
            }

            $this->em->remove($user);
        }
    }
    
}