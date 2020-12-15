<?php

namespace App\Controller\Front;

use App\Entity\Lang;
use App\Entity\User;
use App\Repository\UserRepository;
use App\Form\Back\UserType;
use App\Form\Back\UserUpdateType;
use App\Manager\UserManager;
use App\Form\Back\UserFilterType;
use App\Form\Back\UserBatchType;
use App\Mailer\Mailer;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Security;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\IsGranted;
use Symfony\Component\Form\FormEvent;
use Symfony\Component\Form\FormEvents;
use Symfony\Component\Form\FormError;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\Session\Session;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Core\Encoder\UserPasswordEncoderInterface;
use Symfony\Contracts\Translation\TranslatorInterface;

/**
 * @Security("is_granted('ROLE_USER')")
 * @Route("/{_locale<%app_locales%>}")
 */
class UserController extends AbstractController
{

    /**
     * 
     * @var UserRepository     */
    private $userRepository;
    
    /**
     * 
     * @var UserManager     */
    private $userManager;
    
    /**
     * 
     * @var TranslatorInterface 
     */
    private $translator;
    
    public function __construct(UserRepository $userRepository, UserManager $userManager, TranslatorInterface $translator)
    {
        $this->userRepository = $userRepository;
        $this->userManager = $userManager;
        $this->translator = $translator;
    }

    /**
     * @Route("/profile/{id}", name="user_account", methods="GET")
     */
    public function read(): Response
    {
        $user = $this->getDoctrine()
            ->getRepository(User::class)
            ->findOneBy([
                'id' => $this->getUser()->getId()
            ]);
        if ($user->getLangId() == null) {
            $user_lang = '';
        } else {
            $lang = $this->getDoctrine()
                ->getRepository(Lang::class)
                ->findOneBy([
                    'id' => $this->getUser()->getLangId()
                ]);
            $user_lang = html_entity_decode($lang->getName(), ENT_QUOTES, 'UTF-8');
        }
        
        return $this->render('user/account/user_account.html.twig', [
            'user' => $user,
            'user_lang' => $user_lang,
        ]);
    }

    /**
     * @Route("/update/{id}", name="user_update", methods="GET|POST")
     */
    public function update(Request $request, User $user): Response
    {
        

        return $this->render('user/account/user_update.html.twig', [
            
        ]);
    }
    
}