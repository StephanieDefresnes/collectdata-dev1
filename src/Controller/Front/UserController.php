<?php

namespace App\Controller\Front;

use App\Entity\Lang;
use App\Entity\User;
use App\Form\Front\User\UserUpdateFormType;
use App\Service\LangService;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Security;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\IsGranted;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Contracts\Translation\TranslatorInterface;

/**
 * @Security("is_granted('ROLE_USER')")
 * @Route("/{_locale<%app_locales%>}")
 */
class UserController extends AbstractController
{    
    /**
     * 
     * @var LangService     */
    private $langService;
    
    /**
     * 
     * @var TranslatorInterface 
     */
    private $translator;
    
    public function __construct(LangService $langService, TranslatorInterface $translator)
    {
        $this->langService = $langService;
        $this->translator = $translator;
    }

    /**
     * @Route("/profile/{id}", name="user_account", methods="GET")
     */
    public function read(): Response
    {
        $user = $this->getDoctrine()
            ->getRepository(User::class)
            ->findOneBy([
                'id' => $this->getUser()->getId()
            ]);
        if ($user->getLangId() == null) {
            $user_lang = '';
        } else {
            $lang = $this->getDoctrine()
                ->getRepository(Lang::class)
                ->findOneBy([
                    'id' => $this->getUser()->getLangId()
                ]);
            $user_lang = html_entity_decode($lang->getName(), ENT_QUOTES, 'UTF-8');
        }
        
        return $this->render('front/user/account/user_account.html.twig', [
            'user' => $user,
            'user_lang' => $user_lang,
        ]);
    }

    /**
     * @Route("/edit/{id}", name="user_update", methods="GET|POST")
     */
    public function update(Request $request, User $user): Response
    {
        $entityManager = $this->getDoctrine()->getManager();
        
        $form = $this->createForm(UserUpdateFormType::class, $user, array(
            'entity_manager' => $entityManager,
        ));
        $form->handleRequest($request);

        $user = $this->getDoctrine()
            ->getRepository(User::class)
            ->findOneBy([
                'id' => $this->getUser()->getId()
            ]);
        $langs = $this->langService->getLangsEnabled();
        
        if ($form->isSubmitted() && $form->isValid()) {
            $this->getDoctrine()->getManager()->flush();
            $msg = $this->translator->trans('account.update.flash.success', [], 'user_messages');
            $this->addFlash('success', $msg);
            return $this->redirectToRoute('user_account', ['id' => $user->getId()]);
        }
        

        return $this->render('front/user/account/user_update.html.twig', [
            'user' => $user,
            'langs' => $langs,
            'form' => $form->createView(),
            
        ]);
    }
    
}