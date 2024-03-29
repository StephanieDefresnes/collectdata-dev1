<?php

namespace App\Controller\Security;

use App\Entity\Lang;
use App\Entity\User;
use App\Form\Security\RegistrationFormType;
use Doctrine\ORM\EntityManagerInterface;
use Exception;
use Symfony\Bridge\Twig\Mime\TemplatedEmail;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\DependencyInjection\ParameterBag\ParameterBagInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Mailer\MailerInterface;
use Symfony\Component\Mime\Address;
use Symfony\Component\Security\Core\Encoder\UserPasswordEncoderInterface;
use Symfony\Contracts\Translation\TranslatorInterface;
use SymfonyCasts\Bundle\VerifyEmail\Exception\VerifyEmailExceptionInterface;
use SymfonyCasts\Bundle\VerifyEmail\VerifyEmailHelperInterface;

class RegistrationController extends AbstractController
{
    private $em;
    private $mailer;
    private $parameters;
    private $translator;
    private $verifyEmailHelper;

    public function __construct(EntityManagerInterface $em,
                                MailerInterface $mailer,
                                ParameterBagInterface $parameters,
                                TranslatorInterface $translator,
                                VerifyEmailHelperInterface $helper)
    {
        $this->em = $em;
        $this->mailer = $mailer;
        $this->parameters = $parameters;
        $this->translator = $translator;
        $this->verifyEmailHelper = $helper;
    }

    public function register(   Request $request,
                                UserPasswordEncoderInterface $passwordEncoder): Response
    {
        // GCU url
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

            $user->setEnabled(false);
            $user->setIsVerified(false);
            $user->setRoles(array('ROLE_CONTRIBUTOR'));
            $user->setDateCreate(new \DateTime());
            
            $lang = $this->em->getRepository(Lang::class)->findOneBy(
                        ['lang' => locale_get_default()]
                    );
            $user->setLang($lang);
            // Duplicate user current lang into langs
            $user->addLang($lang);

            $this->em->persist($user);
            
            try {
                $this->em->flush();

                $nameSite = $this->parameters->get('configuration')['name'];
                $sender = $this->parameters->get('configuration')['mail_noreply'];

                $subject = $this->translator->trans(
                    'registration.email.subject', [],
                    'security', $locale = locale_get_default()
                );
        
                $signatureComponents = $this->verifyEmailHelper->generateSignature(
                        'registration_confirmation_route',
                        $user->getId(),
                        $user->getEmail(),
                        ['id' => $user->getId()]
                    );

                $email = new TemplatedEmail();
                $email->from(new Address($sender, $nameSite))
                    ->to($user->getEmail())
                    ->subject($subject)
                    ->htmlTemplate('security/registration/confirmation_email.html.twig')
                    ->context([
                            'signedUrl' => $signatureComponents->getSignedUrl(),
                            'user' => $user->getName(),
                    ]);

                $this->mailer->send($email);
                
                return $this->redirectToRoute('front_home', [
                    '_locale' => locale_get_default()
                ]);
                        
            } catch (Exception $e) {

                $msg = $this->translator->trans(
                    'registration.confirmed.error', [],
                    'security', $locale = locale_get_default()
                );
                $this->addFlash('error', $msg.PHP_EOL.$e->getMessage());

                return $this->redirectToRoute('app_register', [
                    '_locale' => locale_get_default()
                ]);
            }
        }

        return $this->render('security/registration/register.html.twig', [
            'registrationForm' => $form->createView(),
        ]);
    }

    public function verifyUserEmail(Request $request): Response
    {
        $id = $request->get('id');
        
        if (null === $id) {
            return $this->redirectToRoute('front_home', [
                '_locale' => $this->parameters->get('locale')
            ]);
        }
        $user = $this->em->getRepository(User::class)->find($id);
        if (null === $user) {
            return $this->redirectToRoute('front_home', [
                '_locale' => $this->parameters->get('locale')
            ]);
        }
        $userLang = $this->em->getRepository(Lang::class)->find($user->getLangId());
        
        try {
            $this->verifyEmailHelper->validateEmailConfirmation($request->getUri(), $user->getId(), $user->getEmail());
            
            $user->setEnabled(true);
            $user->setIsVerified(true);
            $user->setDateUpdate(new \DateTime());

            $this->em->persist($user);
            $this->em->flush();

            $msg = $this->translator->trans(
                'registration.confirmed.success', [],
                'security', $locale = $userLang->getLang()
            );
            $this->addFlash('success', $msg);

            return $this->redirectToRoute('app_login', [
                '_locale' => $userLang->getLang()
            ]);

        } catch (VerifyEmailExceptionInterface $exception) {
            $msg = $this->translator->trans(
                $exception->getReason(), [],
                'security', $locale = $this->parameters->get('locale')
            );
            $this->addFlash('verify_email_error', $msg);

            return $this->redirectToRoute('app_register', [
                '_locale' => $this->parameters->get('locale')
            ]);
        }
    }
    
}