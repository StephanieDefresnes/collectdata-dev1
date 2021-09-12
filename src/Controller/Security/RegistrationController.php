<?php

namespace App\Controller\Security;

use App\Entity\User;
use App\Form\Security\RegistrationFormType;
use App\Mailer\Mailer;
use App\Service\LangService;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Core\Encoder\UserPasswordEncoderInterface;
use Symfony\Contracts\Translation\TranslatorInterface;

class RegistrationController extends AbstractController
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
     * @Route("/{_locale<%app_locales%>}/register",name="front_register")
     */
    public function register(   LangService $langService,
                                Mailer $mailer,
                                Request $request,
                                UserPasswordEncoderInterface $passwordEncoder): Response
    {        
        
//        $url = $this->router->generate(
//            'TODO',
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
            $user->setConfirmationToken(random_bytes(24));
            $user->setCreated();
            
            $lang = $langService->getLangByLang(locale_get_default());
            $user->setLangId($lang->getId());
            
            $entityManager = $this->getDoctrine()->getManager();
            $entityManager->persist($user);
            $entityManager->flush();

            $mailer->sendRegistration($user);

            $msg = $this->translator->trans('registration.flash.check_email', [ '%email%' => $user->getEmail(), ], 'security');
            $this->addFlash('info', $msg);

            return $this->redirectToRoute('front_home');
        }

        return $this->render('security/registration.html.twig', [
            'form' => $form->createView(),
        ]);
    }
}