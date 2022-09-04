<?php

namespace App\Mailer;

use App\Entity\Lang;
use App\Entity\Message;
use App\Entity\Situ;
use App\Entity\User;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bridge\Twig\Mime\TemplatedEmail;
use Symfony\Component\DependencyInjection\ParameterBag\ParameterBagInterface;
use Symfony\Component\Mailer\MailerInterface;
use Symfony\Component\Mime\Address;
use Symfony\Component\Mime\Email;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;
use Symfony\Contracts\Translation\TranslatorInterface;

class Mailer
{
    private $em;
    private $mailer;
    private $parameters;
    private $router;
    private $translator;
    
    /**
     * Mailer constructor.
     *
     */
    public function __construct(EntityManagerInterface $em,
                                MailerInterface $mailer,
                                ParameterBagInterface $parameters,
                                TranslatorInterface $translator,
                                UrlGeneratorInterface $router)
    {
        $this->em = $em;
        $this->mailer = $mailer;
        $this->parameters = $parameters;
        $this->router = $router;
        $this->translator = $translator;
    }
    
    public function sendValidationRequestedMail( Situ $situ )
    {
        $moderators = $this->em->getRepository(User::class)
                        ->findRoleByLang('MODERATOR', $situ->getLang());

        if ( $moderators ) {
            foreach ($moderators as $moderator) {
                $this->sendEmail(
                        $situ->getId(),
                        $moderator->getLang()->getLang(),
                        $moderator->getEmail(),
                        $moderator->getName()
                );
            }
            return;
        }
        
        $supremeAdmin = $this->em->getRepository(User::class)
                            ->find($parameters->get('supreme_admin_id'));

        $this->sendEmail(
                $situ->getId(),
                $supremeAdmin->getLang()->getLang(),
                $supremeAdmin->getEmail(),
                $supremeAdmin->getName()
        );
    }
    
    private function validationRequestedMail( $situId, $lang, $recipient, $userName )
    {
        $subject = $this->translator->trans(
                'situ.validate.email.subject',
                [ '%id%' => $situId ],
                'email_messages',
                $lang
            );
        
        $template = 'email/situ/moderator_validation.html.twig';
        
        $url = $this->router->generate(
            'back_situ_verify',
            [ '_locale' => $lang, 'id' => $situId ],
            UrlGeneratorInterface::ABSOLUTE_URL
        );
        
        $contactUrl = $this->router->generate(
            'front_contact',
            [ '_locale' => $lang ],
            UrlGeneratorInterface::ABSOLUTE_URL
        );
        
        $context = [
            'user'          => $userName,
            'url'           => $url,
            'website_name'  => $this->parameters->get('configuration')['name'],
            'contact_url'   => $contactUrl,
        ];
        
        $this->sendEmail($recipient, $subject, $template, $context);
    }
    
    public function sendUserMessage( Message $message, User $user )
    {
        $subject = $this->translator->trans(
            'message.envelope.subject',
            [ '%website_name%' => $this->parameters->get('configuration')['name'] ],
            'email_messages',
            $user->getLang()->getLang()
        );
        
        $template = 'email/situ/envelope.html.twig';
        
        $url = $this->router->generate(
            'front_envelope_read',
            [ 'id' => $message->getId(), '_locale' => locale_get_default() ],
            UrlGeneratorInterface::ABSOLUTE_URL
        );
        
        $contactUrl = $this->router->generate(
            'front_contact',
            [ '_locale' => $user->getLang()->getLang() ],
            UrlGeneratorInterface::ABSOLUTE_URL
        );
        
        $context = [
            'url'           => $url,
            'user'          => $user->getName(),
            'website_name'  => $this->parameters->get('configuration')['name'],
            'contact_url'   => $contactUrl,
        ];
        
        $this->sendEmail( $user->getEmail(), $subject, $template, $context );
    }
    
    public function sendSituValidationMail( Situ $situ )
    {
        $user       = $situ->getUser();
        $lang       = $user->getLang()->getLang();
        
        $subject = $this->translator->trans(
            'situ.validation.subject',
            [ '%website_name%' => $this->parameters->get('configuration')['name'] ],
            'email_messages',
            $lang
        );
        $template = 'email/situ/user_validation.html.twig';
        
        $url = $this->router->generate(
            'read_situ',
            [ '_locale' => $lang,  'slug' => $situ->getSlug() ],
            UrlGeneratorInterface::ABSOLUTE_URL
        );
        
        $contactUrl = $this->router->generate(
            'front_contact',
            [ '_locale' => $lang ],
            UrlGeneratorInterface::ABSOLUTE_URL
        );
        
        $context = [
            'url'           => $url,
            'user'          => $user->getName(),
            'website_name'  => $this->parameters->get('configuration')['name'],
            'contact_url'   => $contactUrl,
        ];
        
        $this->sendEmail( $user->getEmail(), $subject, $template, $context );
    }
    
    public function sendUserToUser( User $sender, User $recipient, $formData )
    {
        $recipientLang = $recipient->getLang()->getLang();
        
        $subject = $this->translator->trans(
            'user.subject',
            [ '%website_name%' => $this->parameters->get('configuration')['name'] ],
            'email_messages', 
            $recipientLang
        );
        
        $contactUrl = $this->router->generate(
            'front_contact',
            [ '_locale' => $recipientLang ],
            UrlGeneratorInterface::ABSOLUTE_URL
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
        $messageContext = $this->translator->trans(
            'user.content',
            [ '%subject%' => $formData['subject'], '%message%' =>$formData['message'] ],
            'email_messages',
            $recipientLang
        );
        
        $emailAgreement     = $formData['agreeEmail'] ? $sender->getEmail() : null;
        
        $replyContextChain  = $formData['agreeEmail'] ? 'user.reply_email_agree'
                                : 'user.reply_email_disagree';
        
        $replyContextParams = $formData['agreeEmail']
                                ? [ '%sender_url%' => $senderUrl,
                                    '%sender_email%' => $sender->getEmail() ]
                                : [ '%sender_url%' => $senderUrl ];
        
        $replyContext = $this->translator->trans(
                            $replyContextChain,
                            $replyContextParams,
                            'email_messages',
                            $recipientLang
                        );
        
        $footerContext  = $formData['agreeEmail'] ? ''
                            : $this->translator->trans(
                                'user.reply_footer', [
                                        '%contact_url%' => $contactUrl,
                                    ],
                                    'email_messages', $recipientLang
                                );
        
        $context = [
            'recipient' => $recipient->getName(),
            'sender' => $sender->getName(),
            'content' => $messageContext,
            'reply' => $replyContext,
            'website_name' => $this->parameters->get('configuration')['name'],
            'footer' => $footerContext,
        ];
        
        $this->sendEmail( $recipient->getEmail(), $subject, $template, $context, $emailAgreement );
    }
    
    private function sendEmail($recipient, $subject, $template, $context, $senderEmail = null)
    {
        $nameSite = $this->parameters->get('configuration')['name'];
        $sender = $this->parameters->get('configuration')['mail_noreply'];
            
        $email = ( new TemplatedEmail() );
        
        $email
            ->from( new Address($sender, $nameSite) )
            ->to( new Address($recipient) )
        ;
        
        if ( $senderEmail ) $email->replyTo( new Address($senderEmail) );
        
        $email
            ->subject( $subject )
            ->htmlTemplate( $template )
            ->context( $context )
        ;

        try {
            $this->mailer->send( $email );
        } catch ( TransportExceptionInterface $e ) {
            throw $e->getResponse()->getStatusCode();
            // some error prevented the email sending; display an
            // error message or try to resend the message
        }
        
    }
    
    public function sendEmailContact( $contactFormData )
    {
        $nameSite   = $this->parameters->get('configuration')['name'];
        $sender     = $this->parameters->get('configuration')['mail_noreply'];
        $toContact  = $this->parameters->get('configuration')['mail_contact'];
        $locale     = $this->parameters->get('locale');
        
        $lang           = $this->em->getRepository(Lang::class)
                                ->findOneBy(['lang' => locale_get_default()]);
        $langNameArray  = explode(';', $lang->getEnglishName());
        
        $subject    = $this->translator->trans(
                        'contact.mailer.subject',
                        [ '%website_name%' => $this->parameters->get('configuration')['name'] ],
                        'front_messages',
                        $locale
                    );
        
        $messageText    = $this->translator->trans(
                            'contact.mailer.content', [
                                '%lang%' => reset($langNameArray),
                            ],
                            'front_messages', $locale
                        );
        $messageLabel   = $this->translator->trans(
                            'contact.form.message.label', [],
                            'front_messages', $locale
                        );
        $senderLabel    = $this->translator->trans(
                            'contact.mailer.sender', [],
                            'front_messages', $locale
                        );
        $subjectLabel   = $this->translator->trans(
                            'contact.form.subject.label', [],
                            'front_messages', $locale
                        );
            
        $email = ( new Email() )
            ->from( new Address( $sender, $nameSite ) )
            ->to( new Address( $toContact ) )
            ->replyTo( new Address( $contactFormData['email'] ) )
            ->subject( $subject )
            ->text( $messageText ."\n\n".
                '<strong>'. $senderLabel .'</strong> '.
                    $contactFormData['name'] .', '. $contactFormData['email'] ."\n".
                '<strong>'. $subjectLabel .'</strong> '.
                    $contactFormData['subject'] ."\n".
                '<strong>'. $messageLabel  .'</strong>'.
                    "\n". $contactFormData['message'] );

        try {
            $this->mailer->send( $email );
        } catch (TransportExceptionInterface $e) {
            throw $e->getResponse()->getStatusCode();
            // some error prevented the email sending; display an
            // error message or try to resend the message
        }
    }
}