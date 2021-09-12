<?php

namespace App\Controller\Security;

use App\Entity\User;
use App\Service\LangService;
use App\Form\Security\ChangePasswordFormType;
use App\Form\Security\ResetPasswordRequestFormType;
use Symfony\Bridge\Twig\Mime\TemplatedEmail;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\DependencyInjection\ParameterBag\ParameterBagInterface;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Mailer\MailerInterface;
use Symfony\Component\Mime\Address;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;
use Symfony\Component\Security\Core\Encoder\UserPasswordEncoderInterface;
use Symfony\Contracts\Translation\TranslatorInterface;
use SymfonyCasts\Bundle\ResetPassword\Controller\ResetPasswordControllerTrait;
use SymfonyCasts\Bundle\ResetPassword\Exception\ResetPasswordExceptionInterface;
use SymfonyCasts\Bundle\ResetPassword\ResetPasswordHelperInterface;

/**
 * @Route("/{_locale<%app_locales%>}/reset-password")
 */
class ResetPasswordController extends AbstractController
{
    use ResetPasswordControllerTrait;

    private $langService;
    private $parameters;
    private $resetPasswordHelper;
    private $router;
    private $translator;

    public function __construct(LangService $langService,
                                ParameterBagInterface $parameters,
                                ResetPasswordHelperInterface $resetPasswordHelper,
                                TranslatorInterface $translator,
                                UrlGeneratorInterface $router)
    {
        $this->langService = $langService;
        $this->parameters = $parameters;
        $this->resetPasswordHelper = $resetPasswordHelper;
        $this->router = $router;
        $this->translator = $translator;
    }

    /**
     * Display & process form to request a password reset.
     *
     * @Route("", name="app_forgot_password_request")
     */
    public function request(Request $request, MailerInterface $mailer): Response
    {
        $form = $this->createForm(ResetPasswordRequestFormType::class);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            return $this->processSendingPasswordResetEmail(
                $form->get('email')->getData(),
                $mailer
            );
        }

        return $this->render('security/reset_password/request.html.twig', [
            'requestForm' => $form->createView(),
        ]);
    }

    /**
     * Confirmation page after a user has requested a password reset.
     *
     * @Route("/check-email", name="app_check_email")
     */
    public function checkEmail(): Response
    {
        // Generate a fake token if the user does not exist or someone hit this page directly.
        // This prevents exposing whether or not a user was found with the given email address or not
        if (null === ($resetToken = $this->getTokenObjectFromSession())) {
            $resetToken = $this->resetPasswordHelper->generateFakeResetToken();
        }
        
        $url = $this->router->generate(
            'app_forgot_password_request',
            [
                '_locale' => locale_get_default(),
            ],
            UrlGeneratorInterface::ABSOLUTE_URL
        );

        return $this->render('security/reset_password/check_email.html.twig', [
            'resetToken' => $resetToken,
            'reset_password_url' => $url,
        ]);
    }

    /**
     * Validates and process the reset URL that the user clicked in their email.
     *
     * @Route("/reset/{token}", name="app_reset_password")
     */
    public function reset(Request $request, UserPasswordEncoderInterface $passwordEncoder, string $token = null): Response
    {
        if ($token) {
            // We store the token in session and remove it from the URL, to avoid the URL being
            // loaded in a browser and potentially leaking the token to 3rd party JavaScript.
            $this->storeTokenInSession($token);

            return $this->redirectToRoute('app_reset_password');
        }

        $token = $this->getTokenFromSession();
        if (null === $token) {
            throw $this->createNotFoundException('No reset password token found in the URL or in the session.');
        }

        try {
            $user = $this->resetPasswordHelper->validateTokenAndFetchUser($token);
        } catch (ResetPasswordExceptionInterface $e) {
            $error = $this->translator->trans(
                'reset_password.flash.error', [],
                'security', $locale = locale_get_default()
            );
            
            $this->addFlash('reset_password_error', sprintf(
                $error,
                $e->getReason()
            ));

            return $this->redirectToRoute('app_forgot_password_request');
        }

        // The token is valid; allow the user to change their password.
        $form = $this->createForm(ChangePasswordFormType::class);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            // A password reset token should be used only once, remove it.
            $this->resetPasswordHelper->removeResetRequest($token);

            // Encode the plain password, and set it.
            $encodedPassword = $passwordEncoder->encodePassword(
                $user,
                $form->get('plainPassword')->getData()
            );

            $user->setPassword($encodedPassword);
            $this->getDoctrine()->getManager()->flush();

            // The session is cleaned up after the password has been changed.
            $this->cleanSessionAfterReset();

            $msg = $this->translator->trans(
                'reset_password.flash.success', [],
                'security', $locale = locale_get_default()
            );
            $this->addFlash('success', $msg);

            return $this->redirectToRoute('app_login');
        }

        return $this->render('security/reset_password/reset.html.twig', [
            'resetForm' => $form->createView(),
        ]);
    }

    private function processSendingPasswordResetEmail(string $emailFormData, MailerInterface $mailer): RedirectResponse
    {
        $user = $this->getDoctrine()->getRepository(User::class)->findOneBy([
            'email' => $emailFormData,
        ]);

        // Do not reveal whether a user account was found or not.
        if (!$user) {
            return $this->redirectToRoute('app_check_email');
        }

        try {
            $resetToken = $this->resetPasswordHelper->generateResetToken($user);
        } catch (ResetPasswordExceptionInterface $e) {
            
            // If you want to tell the user why a reset email was not sent, uncomment
            // the lines below and change the redirect to 'app_forgot_password_request'.
            // Caution: This may reveal if a user is registered or not.
            //
            // $this->addFlash('reset_password_error', sprintf(
            //     'There was a problem handling your password reset request - %s',
            //     $e->getReason()
            // ));

            return $this->redirectToRoute('app_check_email');
        }
        
        $userLang = $this->langService->getLangById($user->getLangId());
        
        $subject = $this->translator->trans(
            'reset_password.email.subject', [],
            'security', $locale = $userLang->getLang()
        );
        
        $url = $this->router->generate(
            'app_reset_password',
            [
                '_locale' => $userLang->getLang(),
                'token' => $resetToken->getToken(),
            ],
            UrlGeneratorInterface::ABSOLUTE_URL
        );
        
        $sender = $this->parameters->get('configuration')['from_email'];
        $nameSite = $this->parameters->get('configuration')['name'];

        $email = (new TemplatedEmail())
            ->from(new Address($sender, $nameSite))
            ->to($user->getEmail())
            ->subject($subject)
            ->htmlTemplate('front/reset_password/email.html.twig')
            ->context([
                'locale' => $userLang->getLang(),
                'user' => $user->getName(),
                'reset_password_url' => $url,
            ])
        ;

        $mailer->send($email);

        // Store the token object in session for retrieval in check-email route.
        $this->setTokenObjectInSession($resetToken);

        return $this->redirectToRoute('app_check_email');
    }
    
}
