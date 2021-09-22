<?php

namespace App\Mailer;

use App\Entity\Situ;
use App\Entity\User;
use App\Service\UserService;
use Symfony\Bridge\Twig\Mime\TemplatedEmail;
use Symfony\Component\Mailer\MailerInterface;
use Symfony\Component\Mime\Address;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;
use Symfony\Component\DependencyInjection\ParameterBag\ParameterBagInterface;
use Symfony\Contracts\Translation\TranslatorInterface;

class Mailer
{
    protected $mailer;
    protected $parameters;
    protected $router;
    protected $translator;
    private $userService;
    
    /**
     * Mailer constructor.
     *
     */
    public function __construct(MailerInterface $mailer,
                                ParameterBagInterface $parameters,
                                TranslatorInterface $translator,
                                UrlGeneratorInterface $router,
                                UserService $userService)
    {
        $this->mailer = $mailer;
        $this->parameters = $parameters;
        $this->router = $router;
        $this->translator = $translator;
        $this->userService = $userService;
    }
    
    public function sendRegistration(User $user)
    {
        $nameSite = $this->parameters->get('configuration')['name'];
        $sender = $this->parameters->get('configuration')['from_email'];
            
        $url = $this->router->generate(
            'app_registration_confirm',
            [
                'token' => $user->getConfirmationToken(),
            ],
            UrlGeneratorInterface::ABSOLUTE_URL
        );
        
        $subject = $this->translator->trans(
            'registration.email.subject',
            [
                '%user%' => $user
            ],
            'security', $locale = $this->parameters->get('locale')
        );
            
        $email = (new TemplatedEmail())
            ->from(new Address($sender, $nameSite))
            ->to(new Address($user->getEmail()))
            ->subject($subject)
            ->htmlTemplate('front/register/email/register.html.twig')
            ->context([
                'user' => $user,
                'website_name' => $nameSite,
                'confirmation_url' => $url,
            ])
        ;

        try {
            $this->mailer->send($email);
        } catch (TransportExceptionInterface $e) {
            throw $e->getResponse()->getStatusCode();
            // some error prevented the email sending; display an
            // error message or try to resend the message
        }
    }
    
    public function sendInvitation(User $user, string $password)
    {
        $nameSite = $this->parameters->get('configuration')['name'];
        $sender = $this->parameters->get('configuration')['from_email'];
        
        $subject = $this->translator->trans(
            'invitation.email.subject', [
                '%user%' => $user,
                '%website_name%' => $this->parameters->get('configuration')['name'],
                ],
            'back_messages', $locale = $this->parameters->get('locale')
        );
        
        $url = $this->router->generate(
            'app_registration_confirm',
            [
                'token' => $user->getConfirmationToken(),
            ],
            UrlGeneratorInterface::ABSOLUTE_URL
        );
            
        $email = (new TemplatedEmail())
            ->from(new Address($sender, $nameSite))
            ->to(new Address($user->getEmail()))
            ->subject($subject)
            ->htmlTemplate('back/email/invite.html.twig')
            ->context([
                'user' => $user,
                'password' => $password,
                'website_name' => $nameSite,
                'confirmation_url' => $url,
            ])
        ;

        try {
            $this->mailer->send($email);
        } catch (TransportExceptionInterface $e) {
            throw $e->getResponse()->getStatusCode();
            // some error prevented the email sending; display an
            // error message or try to resend the message
        }
    }
    
    public function sendModeratorSituValidate(Situ $situ)
    {
        $nameSite = $this->parameters->get('configuration')['name'];
        $sender = $this->parameters->get('configuration')['from_email'];
        $moderators = $this->userService->getRoleByLang('MODERATOR', $situ->getLang());

        if ($moderators) {
            foreach ($moderators as $moderator) {
                
                $moderatorLang = $this->em->getRepository(Lang::class)->find($moderator->getLangId());
            
                $subject = $this->translator->trans(
                    'situ.validate.email.subject', [
                        '%id%' => $situ->getId(),
                    ],
                    'email_messages',
                    $locale = $moderatorLang->getLang()
                );

                $url = $this->router->generate(
                    'back_situ_verify',
                    [
                        '_locale' => $moderatorLang->getLang(),
                        'id' => $situ->getId(),
                    ],
                    UrlGeneratorInterface::ABSOLUTE_URL
                );

                $email = (new TemplatedEmail())
                    ->from(new Address($sender, $nameSite))
                    ->to(new Address($moderator->getEmail()))
                    ->cc(new Address($this->parameters->get('configuration')['to_admin']))
                    ->subject($subject)
                    ->htmlTemplate('back/email/situ/validate.html.twig')
                    ->context([
                        'user' => $moderator->getName(),
                        'moderator_url' => $url,
                        'alert' => '',
                    ])
                ;

                try {
                    $this->mailer->send($email);
                } catch (TransportExceptionInterface $e) {
                    throw $e->getResponse()->getStatusCode();
                    // some error prevented the email sending; display an
                    // error message or try to resend the message
                }
            }
        } else {
            
            $subject = $this->translator->trans(
                'situ.validate.email.subject', [
                    '%id%' => $situ->getId(),
                ],
                'email_messages', $locale = $this->parameters->get('locale')
            );

            $url = $this->router->generate(
                'back_situ_verify',
                [
                    '_locale' => $this->parameters->get('locale'),
                    'id' => $situ->getId(),
                ],
                UrlGeneratorInterface::ABSOLUTE_URL
            );

            $alert =  $this->translator->trans(
                'situ.no_moderator', [],
                'email_messages',
                $locale = $this->parameters->get('locale')
            );
            
            $email = (new TemplatedEmail())
                ->from(new Address($sender, $nameSite))
                ->to(new Address($this->parameters->get('configuration')['to_admin']))
                ->subject($subject)
                ->htmlTemplate('back/email/situ/validate.html.twig')
                ->context([
                    'user' => $moderator->getName(),
                    'moderator_url' => $url,
                    'alert' => $alert,
                ])
            ;

            try {
                $this->mailer->send($email);
            } catch (TransportExceptionInterface $e) {
                throw $e->getResponse()->getStatusCode();
                // some error prevented the email sending; display an
                // error message or try to resend the message
            }
        }
    }
}