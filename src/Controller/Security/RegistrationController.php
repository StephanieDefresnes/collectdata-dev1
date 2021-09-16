<?php

namespace App\Controller\Security;

use App\Entity\User;
use App\Form\Security\RegistrationFormType;
use App\Security\EmailVerifier;
use Symfony\Bridge\Twig\Mime\TemplatedEmail;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\DependencyInjection\ParameterBag\ParameterBagInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Mime\Address;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Core\Encoder\UserPasswordEncoderInterface;
use Symfony\Contracts\Translation\TranslatorInterface;
use SymfonyCasts\Bundle\VerifyEmail\Exception\VerifyEmailExceptionInterface;

/**
 * @Route("/{_locale<%app_locales%>}")
 */
class RegistrationController extends AbstractController
{
    private $emailVerifier;

    public function __construct(EmailVerifier $emailVerifier)
    {
        $this->emailVerifier = $emailVerifier;
    }

    /**
     * @Route("/register", name="front_register")
     */
    public function register(   ParameterBagInterface $parameters,
                                Request $request,
                                TranslatorInterface $translator,
                                UserPasswordEncoderInterface $passwordEncoder): Response
    {
        
//        $url = $this->router->generate(
//            'TODO Page content',
//            [
//                '_locale' => locale_get_default(),
//            ],
//            UrlGeneratorInterface::ABSOLUTE_URL
//        );
        $url = '#';
        
        $user = new User();
        $form = $this->createForm(RegistrationFormType::class, $user, [
            'gcu_url' => $url
        ]);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            // encode the plain password
            $user->setPassword(
                $passwordEncoder->encodePassword(
                    $user,
                    $form->get('plainPassword')->getData()
                )
            );

            $entityManager = $this->getDoctrine()->getManager();
            $entityManager->persist($user);
            $entityManager->flush();
        
            $nameSite = $parameters->get('configuration')['name'];
            $sender = $parameters->get('configuration')['from_email'];

            $subject = $translator->trans(
                'registration.email.subject',
                [
                    '%user%' => $user
                ],
                'security', $locale = $parameters->get('locale')
            );

            // generate a signed url and email it to the user
            $this->emailVerifier->sendEmailConfirmation('app_verify_email', $user,
                (new TemplatedEmail())
                    ->from(new Address($sender, $nameSite))
                    ->to($user->getEmail())
                    ->subject('Please Confirm your Email')
                    ->htmlTemplate('security/registration/confirmation_email.html.twig')
            );
            // do anything else you need here, like send an email

            return $this->redirectToRoute('front_home');
        }

        return $this->render('security/registration/register.html.twig', [
            'registrationForm' => $form->createView(),
        ]);
    }

    /**
     * @Route("/verify/email", name="app_verify_email")
     */
    public function verifyUserEmail(Request $request): Response
    {
        $this->denyAccessUnlessGranted('IS_AUTHENTICATED_FULLY');

        // validate email confirmation link, sets User::isVerified=true and persists
        try {
            $this->emailVerifier->handleEmailConfirmation($request, $this->getUser());
        } catch (VerifyEmailExceptionInterface $exception) {
            $this->addFlash('verify_email_error', $exception->getReason());

            return $this->redirectToRoute('app_register');
        }

        $msg = $translator->trans(
            'registration.flash.confirmed',
            [
                '%user%' => $user
            ],
            'security', $locale = $parameters->get('locale')
        );
        $this->addFlash('success', $msg);

        return $this->redirectToRoute('front_home');
    }
}
