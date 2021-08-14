<?php

namespace App\Controller\Front;

use App\Entity\Situ;
use App\Entity\Lang;
use App\Entity\User;
use App\Form\Situ\CreateSituFormType;
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
class SituController extends AbstractController
{
    private $em;
    private $security;
    private $translator;
    
    public function __construct(EntityManagerInterface $em,
                                Security $security,
                                TranslatorInterface $translator)
    {
        $this->em = $em;
        $this->security = $security;
        $this->translator = $translator;
    }
    
    /**
     * @Route("/contribs", name="all_situs")
     */
    public function index(): Response
    {
        $this->denyAccessUnlessGranted('IS_AUTHENTICATED_FULLY');
        
        return $this->render('front/situ/index.html.twig', [
            'controller_name' => 'SituController',
        ]);
    }
    
    /**
     * @Route("/my-contribs", name="user_situs", methods="GET")
     */
    public function getUserSitus()
    {
        $this->denyAccessUnlessGranted('IS_AUTHENTICATED_FULLY');
        
        $user = $this->security->getUser();
        $userLangs = $user->getLangs();
        
        $situs = $this->em->getRepository(Situ::class)
                ->findBy(['userId' => $user->getId()]);
        
        return $this->render('front/situ/user.html.twig', [
            'situs' => $situs,
            'userLangs' => $userLangs,
        ]);
    }
    
    /**
     * @Route("/read/{id}", name="read_situ", methods="GET")
     */
    public function readSitu(Situ $situ): Response
    {
        // Only user can read not validated situ
        if ($situ->getStatusId() != 3) {
            $this->denyAccessUnlessGranted('IS_AUTHENTICATED_FULLY');
        }
        
        $user = $this->em->getRepository(User::class)
                ->findOneBy(['id' => $situ->getUserId()]);
        
        return $this->render('front/situ/read.html.twig', [
            'situ' => $situ,
            'user' => $user,
        ]);
    }

    /**
     * @Route("/contrib/{id}", defaults={"id" = null}, name="create_situ", methods="GET|POST")
     */
    public function createSitu(Request $request, $id): Response
    {
        $this->denyAccessUnlessGranted('IS_AUTHENTICATED_FULLY');
        $this->denyAccessUnlessGranted('ROLE_CONTRIBUTOR');
        
        // Current user
        $user = $this->security->getUser();
        $langs = $user->getLangs()->getValues();
        
        $situData = '';
        if ($id) {
            $situData = $this->em->getRepository(Situ::class)
                    ->findOneBy(['id' => $id]);
            if (!$situData) {dd('redirect page error');}
        }
        
        // Only situ author can update situ
        if (!empty($situData) && $user->getId() != $situData->getUserId()) {
            
            $msg = $this->translator->trans(
                    'access_deny', [],
                    'user_messages', $locale = locale_get_default()
                );
            $this->addFlash('error', $msg);

            return $this->redirectToRoute('user_situs', ['_locale' => locale_get_default()]);
        }
        
        // Form
        $situ = new Situ();
        $formSitu = $this->createForm(CreateSituFormType::class, $situ);
        $formSitu->handleRequest($request);
        
        return $this->render('front/situ/create.html.twig', [
            'form' => $formSitu->createView(),
            'langs' => $langs,
            'situ' => $situData,
        ]);
    }

    /**
     * @Route("/translate/{id}/{langId}", name="translate_situ", methods="GET|POST")
     */
    public function translateSitu(Request $request, $id, $langId): Response
    {
        $this->denyAccessUnlessGranted('IS_AUTHENTICATED_FULLY');
        $this->denyAccessUnlessGranted('ROLE_CONTRIBUTOR');
        
        // Current user langs
        $langs = $this->security->getUser()->getLangs()->getValues();
        
        // Situ to translate
        $situData = $this->em->getRepository(Situ::class)
                ->findOneBy(['id' => $id]);
        
        // Translation lang
        $langData = $this->em->getRepository(Lang::class)
                ->findOneBy(['id' => $langId]);
        
        // Form
        $situ = new Situ();
        $formSitu = $this->createForm(CreateSituFormType::class, $situ);
        $formSitu->handleRequest($request);
        
        return $this->render('front/situ/translation.html.twig', [
            'form' => $formSitu->createView(),
            'langs' => $langs,
            'situ' => $situData,
            'lang' => $langData,
        ]);
    }
    
    /**
     * @Route("/validation/{id}", methods="GET|POST")
     */
    function validationSituRequest(Situ $situ): Response
    {
        $this->denyAccessUnlessGranted('IS_AUTHENTICATED_FULLY');
        $this->denyAccessUnlessGranted('ROLE_CONTRIBUTOR');
        
        // Current user
        $user = $this->security->getUser();
        
        // Only situ author can request situ validation 
        if ($user->getId() != $situ->getUserId()) {
            
            $msg = $this->translator->trans(
                    'access_deny', [],
                    'user_messages', $locale = locale_get_default()
                );
            $this->addFlash('error', $msg);

            return $this->redirectToRoute('user_situs', ['_locale' => locale_get_default()]);
        }
            
        try {
            $situ->setDateSubmission(new \DateTime('now'));
            $situ->setStatusId(2);
            $this->em->persist($situ);
            $this->em->flush();

            $msg = $this->translator->trans(
                    'contrib.form.submit.flash.success', [],
                    'user_messages', $locale = locale_get_default()
                );
            $this->addFlash('success', $msg);

            return $this->redirectToRoute('user_situs', ['_locale' => locale_get_default()]);

        } catch (Exception $e) {
            throw new \Exception('An exception appeared while updating the translation');
        }
    }
    
    /**
     * @Route("/delete/{id}", methods="GET|POST")
     */
    function deleteSitu(Situ $situ): Response
    {
        $this->denyAccessUnlessGranted('IS_AUTHENTICATED_FULLY');
        $this->denyAccessUnlessGranted('ROLE_CONTRIBUTOR');
        
        // Current user
        $user = $this->security->getUser();
        
        // Only situ author can delete situ
        if ($user->getId() != $situ->getUserId()) {
            
            $msg = $this->translator->trans(
                    'access_deny', [],
                    'user_messages', $locale = locale_get_default()
                );
            $this->addFlash('error', $msg);

            return $this->redirectToRoute('user_situs', ['_locale' => locale_get_default()]);
        }
            
        try {
            $situ->setDateDeletion(new \DateTime('now'));
            $situ->setStatusId(5);
            $this->em->persist($situ);
            $this->em->flush();

            $msg = $this->translator->trans(
                    'contrib.table.deletion.success', [],
                    'user_messages', $locale = locale_get_default()
                );
            $this->addFlash('success', $msg);

            return $this->redirectToRoute('user_situs', ['_locale' => locale_get_default()]);

        } catch (Exception $e) {
            throw new \Exception('An exception appeared while deleting the translation');
        }
    }    
}
