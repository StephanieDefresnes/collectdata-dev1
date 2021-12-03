<?php

namespace App\Mailer;

use App\Entity\Message;
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
    
    public function sendModeratorSituValidate(Situ $situ)
    {
        $nameSite = $this->parameters->get('configuration')['name'];
        $sender = $this->parameters->get('configuration')['from_email'];
        $moderators = $this->userRepository->findRoleByLang('MODERATOR', $situ->getLang());
        
        $situId = $situ->getId();

        if ($moderators) {
            foreach ($moderators as $moderator) {
                $this->validationMail(
                        $situId,
                        $moderator->getLang()->getLang(),
                        $moderator->getEmail(),
                        $moderator->getName()
                );
            }
        } else {
            $alert = $this->translator->trans(
                'situ.no_moderator', [],
                'email_messages',
                $locale = $this->parameters->get('locale')
            );
            
            $this->validationMail(
                    $situId,
                    $this->parameters->get('locale'),
                    $this->parameters->get('configuration')['to_admin'],
                    $moderator->getName(),
                    $alert
            );
        }
    }
    
    private function validationMail($situId, $userLang, $recipient, $userName, $alert = null)
    {
        $subject = $this->translator->trans(
            'situ.validate.email.subject', [
                '%id%' => $situId,
            ],
            'email_messages',
            $locale = $userLang
        );
        $template = 'email/situ/moderator_validate.html.twig';
        
        $url = $this->router->generate(
            'back_situ_verify',
            [
                '_locale' => $userLang,
                'id' => $situId,
            ],
            UrlGeneratorInterface::ABSOLUTE_URL
        );
        
        $contactUrl = $this->router->generate(
            'front_contact', ['_locale' => $recipientLang],
            UrlGeneratorInterface::ABSOLUTE_URL
        );
        
        $context = [
            'url' => $url,
            'user' => $userName,
            'url' => $url,
            'alert' => $alert ? $alert : '',
            'website_name' => $this->parameters->get('configuration')['name'],
            'contact_url' => $contactUrl,
        ];
        
        $this->sendEmail($recipient, $subject, $template, $context);
    }
    
    public function sendUserMessage(Message $message, User $user)
    {
        $recipient = $user->getEmail();
        $subject = $this->translator->trans(
            'message.envelope.subject', [
                '%website_name%' => $this->parameters->get('configuration')['name'],
                ],
            'email_messages', $locale = $this->parameters->get('locale')
        );
        $template = 'email/situ/envelope.html.twig';
        
        $url = $this->router->generate(
            'front_envelope_read', [
                'id' => $message->getId(),
                '_locale' => locale_get_default()
            ],
            UrlGeneratorInterface::ABSOLUTE_URL
        );
        
        $contactUrl = $this->router->generate(
            'front_contact', ['_locale' => $recipientLang],
            UrlGeneratorInterface::ABSOLUTE_URL
        );
        
        $context = [
            'url' => $url,
            'user' => $user->getName(),
            'website_name' => $this->parameters->get('configuration')['name'],
            'contact_url' => $contactUrl,
        ];
        
        $this->sendEmail($recipient, $subject, $template, $context);
    }
    
    public function sendUserSituValidation(Situ $situ, User $user)
    {
        $recipient = $user->getEmail();
        $recipientLang = $user->getLang()->getLang();
        $subject = $this->translator->trans(
            'situ.validation.subject', [
                '%website_name%' => $this->parameters->get('configuration')['name'],
                ],
            'email_messages', $locale = $recipientLang
        );
        $template = 'email/situ/contributor_validation.html.twig';
        
        $url = $this->router->generate(
            'read_situ',
            [
                '_locale' => locale_get_default(),
                'slug' => $situ->getSlug(),
            ],
            UrlGeneratorInterface::ABSOLUTE_URL
        );
        
        $contactUrl = $this->router->generate(
            'front_contact', ['_locale' => $recipientLang],
            UrlGeneratorInterface::ABSOLUTE_URL
        );
        
        $context = [
            'url' => $url,
            'user' => $user->getName(),
            'website_name' => $this->parameters->get('configuration')['name'],
            'contact_url' => $contactUrl,
        ];
        
        $this->sendEmail($recipient, $subject, $template, $context);
    }
    
    public function sendUserToUser(User $sender, User $recipient, $formData)
    {
        $recipientLang = $recipient->getLang()->getLang();
        
        $subject = $this->translator->trans(
            'user.subject', [
                '%website_name%' => $this->parameters->get('configuration')['name'],
            ],
            'email_messages', $locale = $recipientLang
        );
        
        $senderUrl = $this->router->generate(
            'user_visit',
            [
                '_locale' => $recipientLang,
                'slug' => $sender->getSlug(),
            ],
            UrlGeneratorInterface::ABSOLUTE_URL
        );
        
        $template = 'email/user/email.html.twig';
        
        // Context
        $contextMessage = $this->translator->trans(
            'user.content', [
                '%subject%' => $formData['subject'],
                '%message%' =>$formData['message'],
            ],
            'email_messages', $locale = $recipientLang
        ); 
        
        // Reply depending on sender agreement
        
        if (true === $formData['agreeEmail']) {
            
            $translationChain = 'user.reply_email_agree';
            $senderEmailAgree = $sender->getEmail();
            $contextFooter = '';
            
        } else {
            
            $translationChain = 'user.reply_email_disagree';
            $senderEmailAgree = null;
        
            $contactUrl = $this->router->generate(
                'front_contact', ['_locale' => $recipientLang],
                UrlGeneratorInterface::ABSOLUTE_URL
            );
            
            $contextFooter = $this->translator->trans(
                    'user.reply_footer', [
                            '%contact_url%' => $contactUrl,
                        ],
                        'email_messages', $locale = $recipientLang
                    );
        }
        
        $contextReply = $this->translator->trans(
                    $translationChain, [
                        '%sender_url%' => $senderUrl,
                        '%sender_email%' => $sender->getEmail(),
                    ],
                    'email_messages', $locale = $recipientLang
                );
        
        $context = [
            'recipient' => $recipient->getName(),
            'sender' => $sender->getName(),
            'content' => $contextMessage,
            'reply' => $contextReply,
            'website_name' => $this->parameters->get('configuration')['name'],
            'footer' => $contextFooter,
        ];
        
        $this->sendEmail($recipient->getEmail(), $subject, $template, $context, $senderEmailAgree);
    }
    
    private function sendEmail($recipient, $subject, $template, $context, $senderEmail = null)
    {
        $nameSite = $this->parameters->get('configuration')['name'];
        $sender = $this->parameters->get('configuration')['from_email'];
            
        $email = (new TemplatedEmail());
        
        $email
            ->from(new Address($sender, $nameSite))
            ->to(new Address($recipient))
        ;
        
        if ($senderEmail) $email->replyTo(new Address($senderEmail));
        
        $email
            ->subject($subject)
            ->htmlTemplate($template)
            ->context($context)
        ;

        try {
            $this->mailer->send($email);
        } catch (TransportExceptionInterface $e) {
            throw $e->getResponse()->getStatusCode();
            // some error prevented the email sending; display an
            // error message or try to resend the message
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
            ->replyTo(new Address($contactFormData['email']))
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