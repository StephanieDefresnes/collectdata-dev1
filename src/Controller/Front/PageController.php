<?php

namespace App\Controller\Front;

use App\Entity\Page;
use App\Form\Front\Contact\ContactType;
use App\Mailer\Mailer;
use App\Service\LangService;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;

class PageController extends AbstractController
{
    /**
     * @Route("/", name="index")
     */
    public function index(LangService $langService)
    {
        $langs = $this->em->getRepository(Lang::class)->findBy(['enabled' => 1]);
        
        return $this->render('front/page/index.html.twig', [
            'langs' => $langs,
        ]);
    }
    
    /**
     * @Route("/{_locale<%app_locales%>}", name="front_home")
     */
    public function home()
    {
        $page = $this->getDoctrine()->getRepository(Page::class)
                    ->findOneBy(['type' => 'home', 'lang' => locale_get_default()]);
        
        return $this->render('front/page/home.html.twig', [
            'page' => $page
        ]);
    }
    
    /**
     * @Route("/{_locale<%app_locales%>}/contact", name="front_contact")
     */
    public function contact(Request $request, Mailer $mailer)
    {
        $form = $this->createForm(ContactType::class);
        $form->handleRequest($request);
        
        if($form->isSubmitted() && $form->isValid()) {
            
            $contactFormData = $form->getData();
            
            try {
                $mailer->sendEmailContact($contactFormData);
                return $this->redirectToRoute('front_contact_confirm');
            } catch (TransportExceptionInterface $e) {
                $msg = $this->translator
                        ->trans('contact.flash.error', [],
                                'front_messages', $locale = locale_get_default());
                $this->addFlash('error', $msg);
                return $this->redirectToRoute('front_contact');
            }
        }
        
        return $this->render('front/page/contact/index.html.twig', [
            'form' => $form->createView()
        ]);
    }
    
    /**
     * @Route("/{_locale<%app_locales%>}/contact/sent", name="front_contact_confirm")
     */
    public function contactConfirm()
    {        
        return $this->render('front/page/contact/confirm.html.twig');
    }
}
