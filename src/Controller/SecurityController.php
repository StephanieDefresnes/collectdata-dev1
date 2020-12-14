<?php

namespace App\Controller;

use App\Entity\User;
use App\Form\ForgetPasswordFormType;
use App\Form\ResetPasswordFormType;
use App\Mailer\Mailer;
use App\Repository\UserRepository;
use App\Security\LoginFormAuthenticator;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Core\Authentication\Provider\UserAuthenticationProvider;
use Symfony\Component\Security\Http\Authentication\AuthenticationUtils;
use Symfony\Component\Security\Core\Exception\LogicException;
use Symfony\Component\Security\Core\Encoder\UserPasswordEncoderInterface;
use Symfony\Component\Security\Guard\GuardAuthenticatorHandler;
use Symfony\Contracts\Translation\TranslatorInterface;

class SecurityController extends AbstractController
{
    /**
     * @var TranslatorInterface
     */
    private $translator;

    public function __construct(TranslatorInterface $translator)
    {
        $this->translator = $translator;
    }
    
    
    /**
     * @Route("/{_locale<%app_locales%>}/login",name="app_login")
     */
    public function login(Request $request, AuthenticationUtils $authenticationUtils): Response
    {
        $locale = $request->getLocale();
        
        // if ($this->getUser()) {
        //     return $this->redirectToRoute('target_path');
        // }
        

        // get the login error if there is one
        $error = $authenticationUtils->getLastAuthenticationError();
        // last username entered by the user
        $lastUsername = $authenticationUtils->getLastUsername();

        return $this->render('security/login.html.twig', ['last_username' => $lastUsername, 'error' => $error]);
    }

    /**
     * @Route("/{_locale<%app_locales%>}/logout", name="app_logout")
     */
    public function logout()
    {
        throw new \LogicException('This method can be blank - it will be intercepted by the logout key on your firewall.');
    }

    /**
     * @Route("/registration_confirm", name="app_registration_confirm")
     */
    public function registrationConfirm(Request $request, UserRepository $userRepository, GuardAuthenticatorHandler $guardHandler, LoginFormAuthenticator $authenticator): Response
    {
        $token = $request->query->get('token');
        $user = $userRepository->findOneByConfirmationToken($token);
        if (null === $user) {
            throw $this->createNotFoundException(sprintf('The user with confirmation token "%s" does not exist', $token));
        }
        
        $user->setConfirmationToken(null);
        $user->setEnabled(true);
        $this->getDoctrine()->getManager()->flush();
        
        $msg = $this->translator->trans('registration.flash.confirmed', [ '%user%' => $user, ], 'security');
        $this->addFlash('success', $msg);
        
        return $guardHandler->authenticateUserAndHandleSuccess(
            $user,
            $request,
            $authenticator,
            'main' // firewall name in security.yaml
        );
    }
    
    /**
     * @Route("/{_locale<%app_locales%>}/forget_password", name="app_forget_password")
     */
    public function forgetPassword(Request $request, UserRepository $userRepository, Mailer $mailer): Response
    {
        $form = $this->createForm(ForgetPasswordFormType::class);
        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()) {
            $user = $userRepository->findOneByEmail($form->get('email')->getData());
            if ($user) {
                $user->setConfirmationToken(random_bytes(24));
                $this->getDoctrine()->getManager()->flush();
                $mailer->sendForgetPassword($user);
                $msg = $this->translator->trans('forget_password.flash.check_email', [ '%user%' => $user, ], 'security');
                $this->addFlash('success', $msg);
            }
            return $this->redirectToRoute('front_home');
        }
        return $this->render('security/forget_password.html.twig', [
            'form' => $form->createView(),
        ]);
    }
    
    /**
     * @Route("/{_locale<%app_locales%>}/reset_password/{id}", defaults={"id"=null}, name="app_reset_password")
     */
    public function resetPassword(
        Request $request,
        UserRepository $userRepository,
        UserPasswordEncoderInterface $passwordEncoder,
        GuardAuthenticatorHandler $guardHandler,
        LoginFormAuthenticator $authenticator,
        User $user=null
    ): response {
        if ($token = $request->query->get('token')) {
            $user = $userRepository->findOneByConfirmationToken($token);
            if (!$user) { throw $this->createNotFoundException(sprintf('The user with confirmation token "%s" does not exist', $token)); }
        } elseif (!$user) { throw new LogicException("No user selected."); }
        $form = $this->createForm(ResetPasswordFormType::class, null, [
            'with_token' => null !== $token,
        ]);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $user->setPassword($passwordEncoder->encodePassword($user, $form->get('plainPassword')->getData()));
            if ($token) {
                $user->setConfirmationToken(null);
            }
            $this->getDoctrine()->getManager()->flush();
            $msg = $this->translator->trans('reset_password.flash.success', [], 'security');
            $this->addFlash('info', $msg);
            return $guardHandler->authenticateUserAndHandleSuccess($user, $request, $authenticator, 'main');
        }
        return $this->render('security/reset_password.html.twig', [
            'form' => $form->createView(),
        ]);
    }
}
