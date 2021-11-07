<?php

namespace App\Mailer;

use App\Entity\Lang;
use App\Entity\Situ;
use App\Entity\User;
use App\Repository\UserRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Mime\Email;
use Symfony\Bridge\Twig\Mime\TemplatedEmail;
use Symfony\Component\Mailer\MailerInterface;
use Symfony\Component\Mime\Address;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;
use Symfony\Component\DependencyInjection\ParameterBag\ParameterBagInterface;
use Symfony\Contracts\Translation\TranslatorInterface;

class Mailer
{
    private $em;
    private $mailer;
    private $parameters;
    private $router;
    private $translator;
    private $userRepository;
    
    /**
     * Mailer constructor.
     *
     */
    public function __construct(EntityManagerInterface $em,
                                MailerInterface $mailer,
                                ParameterBagInterface $parameters,
                                TranslatorInterface $translator,
                                UrlGeneratorInterface $router,
                                UserRepository $userRepository)
    {
        $this->em = $em;
        $this->mailer = $mailer;
        $this->parameters = $parameters;
        $this->router = $router;
        $this->translator = $translator;
        $this->userRepository = $userRepository;
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
            ->htmlTemplate('email/back/user/invite.html.twig')
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
        $moderators = $this->userRepository->findRoleByLang('MODERATOR', $situ->getLang());

        if ($moderators) {
            foreach ($moderators as $moderator) {
                
                $moderatorLang = $this->em->getRepository(Lang::class)->find($moderator->getLang());
            
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
                        'situ' => $situ->getId(),
                    ],
                    UrlGeneratorInterface::ABSOLUTE_URL
                );

                $email = (new TemplatedEmail())
                    ->from(new Address($sender, $nameSite))
                    ->to(new Address($moderator->getEmail()))
                    ->cc(new Address($this->parameters->get('configuration')['to_admin']))
                    ->subject($subject)
                    ->htmlTemplate('email/back/situ/validate.html.twig')
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
    
    public function sendEmailContact($contactFormData)
    {
        $nameSite = $this->parameters->get('configuration')['name'];
        $sender = $this->parameters->get('configuration')['from_email'];
        $toContact = $this->parameters->get('configuration')['to_contact'];
        
        $subject = $this->translator->trans(
            'contact.mailer.subject', [
                '%website_name%' => $this->parameters->get('configuration')['name'],
                ],
            'front_messages', $locale = $this->parameters->get('locale')
        );
        
        $messageContent  = $this->translator->trans(
            'contact.mailer.content', [
                '%lang%' => strtoupper(locale_get_default()),
            ],
            'front_messages', $locale = $this->parameters->get('locale')
        );
        $messageLabel  = $this->translator->trans(
            'contact.form.message.label', [],
            'front_messages', $locale = $this->parameters->get('locale')
        );
        $senderLabel = $this->translator->trans(
            'contact.mailer.sender', [],
            'front_messages', $locale = $this->parameters->get('locale')
        );
        $subjectLabel = $this->translator->trans(
            'contact.form.subject.label', [],
            'front_messages', $locale = $this->parameters->get('locale')
        );
            
        $email = (new Email())
            ->from(new Address($sender, $nameSite))
            ->to(new Address($toContact))
            ->subject($subject)
            ->text($messageContent.\PHP_EOL.\PHP_EOL.
                $senderLabel.' '.$contactFormData['name'].', '.$contactFormData['email'].\PHP_EOL.
                $subjectLabel.' '.$contactFormData['subject'].\PHP_EOL.
                $messageLabel.\PHP_EOL.$contactFormData['message']);
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