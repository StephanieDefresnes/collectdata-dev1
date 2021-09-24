<?php

namespace App\Controller\Front;

use App\Entity\Translation;
use App\Entity\User;
use App\Form\Front\Translation\TranslationFormType;
use App\Repository\TranslationRepository;
use App\Service\TranslationService;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Core\Security;
use Symfony\Contracts\Translation\TranslatorInterface;

/**
 * @Route("/{_locale<%app_locales%>}")
 */
class TranslationController extends AbstractController
{
    private $em;
    private $translator;
    private $translationRepository;
    private $translationService;
    
    public function __construct(EntityManagerInterface $em,
                                TranslatorInterface $translator,
                                TranslationRepository $translationRepository,
                                TranslationService $translationService)
    {
        $this->em = $em;
        $this->translator = $translator;
        $this->translationRepository = $translationRepository;
        $this->translationService = $translationService;
    }
    
    /**
     * @Route("/my-translations", name="user_translations", methods="GET|POST")
     */
    public function index(Security $security): Response
    {
        $this->denyAccessUnlessGranted('IS_AUTHENTICATED_FULLY');
        
        // Valideted translations list
        $referents = $this->em->getRepository(Translation::class)->findBy([
            'statusId' => 3,
            'referent' => 1,
        ]);
        
        // User translations
        $user = $security->getUser();
        $userTranslations = $this->em->getRepository(Translation::class)->findBy([
            'userId' => $user->getId(),
            'referent' => 0,
        ]);
        
        return $this->render('front/translation/index.html.twig', [
            'referents' => $referents,
            'userTranslations' => $userTranslations,
        ]);
    }
    
    /**
     * @Route("/{referent}/translate/{id}", defaults={"id" = null}, name="front_translation_create", methods="GET|POST")
     */
    public function create( EntityManagerInterface $em,
                            Request $request,
                            Security $security,
                            $referent, $id): Response
    {
        $this->denyAccessUnlessGranted('IS_AUTHENTICATED_FULLY');
        
        // Current user
        $user = $security->getUser();
        
        $referent = $this->em->getRepository(Translation::class)->find($referent);
        
        if ($id) {
            $translation = $this->em->getRepository(Translation::class)->find($id);
        } else {
            $translation = new Translation();
        }
        $userTranslations = $this->em->getRepository(Translation::class)->findBy([
            'userId' => $user->getId(),
            'referent' => 0,
        ]);
        
        // Form
        $form = $this->createForm(TranslationFormType::class, $translation);
        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()) {
            
            
            // TODO
            
            
        }
        
        return $this->render('front/translation/create.html.twig', [
            'referent' => $referent,
            'userTranslations' => $userTranslations,
        ]);
    }
    
}
