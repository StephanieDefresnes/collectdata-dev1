<?php

namespace App\Security;

use App\Repository\UserRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\Security\Core\Exception\CustomUserMessageAuthenticationException;
use Symfony\Component\Security\Core\Security;
use Symfony\Component\Security\Http\Authenticator\AbstractLoginFormAuthenticator;
use Symfony\Component\Security\Http\Authenticator\Passport\Badge\CsrfTokenBadge;
use Symfony\Component\Security\Http\Authenticator\Passport\Badge\UserBadge;
use Symfony\Component\Security\Http\Authenticator\Passport\Credentials\PasswordCredentials;
use Symfony\Component\Security\Http\Authenticator\Passport\Passport;
use Symfony\Component\Security\Http\Authenticator\Passport\PassportInterface;
use Symfony\Component\Security\Http\Util\TargetPathTrait;
use Symfony\Contracts\Translation\TranslatorInterface;

class LoginFormAuthenticator extends AbstractLoginFormAuthenticator
{
    use TargetPathTrait;

    public const LOGIN_ROUTE = 'app_login';

    private $em;
    private $translator;
    private $urlGenerator;

    public function __construct(EntityManagerInterface $em,
                                Security $security,
                                TranslatorInterface $translator,
                                UrlGeneratorInterface $urlGenerator,
                                UserRepository $userRepository)
    {
        $this->em = $em;
        $this->security = $security;
        $this->translator = $translator;
        $this->urlGenerator = $urlGenerator;
        $this->userRepository = $userRepository;
    }

    public function supports(Request $request): bool
    {
        return self::LOGIN_ROUTE === $request->attributes->get('_route')
            && $request->isMethod('POST');
    }

    public function authenticate(Request $request): PassportInterface
    {
        $email = $request->request->get('email');
        $password = $request->request->get('password');
        $csrfToken = $request->request->get('_csrf_token');


        $request->getSession()->set(Security::LAST_USERNAME, $email);

        return new Passport(
            new UserBadge($email, function($userIdentifier) {
                $user = $this->userRepository->findOneBy(['email' => $userIdentifier]);
                if (!$user) {
                    // If no user
                    $msg = $this->translator->trans('login.message.invalid', [], 'security');
                    throw new CustomUserMessageAuthenticationException($msg);
                }
                elseif (!$user->isVerified()) {
                    // If user is not verified
                    $msg = $this->translator->trans('login.message.not_verified', [], 'security');
                    throw new CustomUserMessageAuthenticationException($msg);
                }
                elseif (!$user->isEnabled()) {
                    // If user is disabled
                    $msg = $this->translator->trans('login.message.not_activated', [], 'security');
                    throw new CustomUserMessageAuthenticationException($msg);
                }
                return $user;
            }),
            new PasswordCredentials($password),
            [new CsrfTokenBadge('authenticate', $csrfToken)]
        );
    }

    public function onAuthenticationSuccess(Request $request, TokenInterface $token, string $firewallName): ?Response
    {
        $session = $request->getSession();
        $user = $token->getUser();

        $user->setDateLastLogin(new \DateTime());
        $this->em->flush();

        $msg = $this->translator->trans('login.message.welcome', ['%user%' => $user ], 'security');
        $session->getFlashBag()->add('success', $msg);

        if ($targetPath = $this->getTargetPath($session, $firewallName)) {
            return new RedirectResponse($targetPath);
        }
        
        if ($this->security->isGranted("ROLE_MODERATOR")) $route = 'back_home';
        else $route = 'front_home';

        return new RedirectResponse($this->urlGenerator->generate($route));
    }

    protected function getLoginUrl(Request $request): string
    {
        return $this->urlGenerator->generate(self::LOGIN_ROUTE);
    }
}