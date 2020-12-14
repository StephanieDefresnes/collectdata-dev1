<?php
namespace App\Mailer;

use App\Entity\User;
use App\Repository\UserRepository;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;
use Symfony\Component\Templating\Loader\FilesystemLoader;
use Symfony\Component\Templating\PhpEngine;
use Symfony\Component\Templating\Helper\SlotsHelper;
use Symfony\Component\Templating\TemplateNameParser;
use Symfony\Component\DependencyInjection\ParameterBag\ParameterBagInterface;
use Symfony\Contracts\Translation\TranslatorInterface;

class Mailer
{
    
    /**
     * @var MailerInterface
     */
    protected $mailer;
    
    /**
     * @var UrlGeneratorInterface
     */
    protected $router;
    
    /**
     * @var TranslatorInterface
     */
    protected $translator;
    
    /**
     * @var ParameterBagInterface
     */
    protected $parameters;
    
    /**
     * Mailer constructor.
     *
     */
    public function __construct(\Swift_Mailer $mailer, UrlGeneratorInterface $router, TranslatorInterface $translator, ParameterBagInterface $parameters)
    {
        $this->mailer = $mailer;
        $this->router = $router;
        $this->translator = $translator;
        $this->parameters = $parameters;
        
        $filesystemLoader = new FilesystemLoader(__DIR__.'/views/%name%');
        $templating = new PhpEngine(new TemplateNameParser(), $filesystemLoader);
        $templating->set(new SlotsHelper());

    }
    
    public function sendRegistration(User $user)
    {
        $url = $this->router->generate(
            'app_registration_confirm',
            [
                'token' => $user->getConfirmationToken(),
            ],
            UrlGeneratorInterface::ABSOLUTE_URL
        );
        $subject = $this->translator->trans('registration.email.subject', [ '%user%' => $user ], 'security');
        $template = 'front/email/register.html.twig';
        $from = [
            $this->parameters->get('configuration.')['from_email'] => $this->parameters->get('configuration')['name'],
        ];
        $to = $user->getEmail();
        $body = $this->templating->render($template, [
            'user' => $user,
            'website_name' => $this->parameters->get('configuration')['name'],
            'confirmation_url' => $url,
        ]);
        $message = (new \Swift_Message())
            ->setSubject($subject)
            ->setFrom($from)
            ->setTo($to)
            ->setContentType("text/html")
            ->setBody($body);
        $this->mailer->send($message);
    }
    
    public function sendInvitation(User $user, string $password)
    {
        $url = $this->router->generate(
            'app_registration_confirm',
            [
                'token' => $user->getConfirmationToken(),
            ],
            UrlGeneratorInterface::ABSOLUTE_URL
        );
        $subject = $this->translator->trans('invitation.email.subject', [
            '%user%' => $user,
            '%website_name%' => $this->parameters->get('configuration')['name'],
        ], 'back_messages');
        $template = 'back/email/invite.html.twig';
        $from = [
            $this->parameters->get('configuration')['from_email'] => $this->parameters->get('configuration')['name'],
        ];
        $to = $user->getEmail();
        $body = $this->templating->render($template, [
            'user' => $user,
            'password' => $password,
            'website_name' => $this->parameters->get('configuration')['name'],
            'confirmation_url' => $url,
        ]);
        $message = (new \Swift_Message())
            ->setSubject($subject)
            ->setFrom($from)
            ->setTo($to)
            ->setContentType("text/html")
            ->setBody($body);
        $this->mailer->send($message);
    }
}

