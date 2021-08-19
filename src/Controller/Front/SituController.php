<?php

namespace App\Controller\Front;

use App\Entity\Situ;
use App\Entity\SituItem;
use App\Entity\Category;
use App\Entity\Event;
use App\Entity\Lang;
use App\Entity\User;
use App\Form\Situ\CreateSituFormType;
use App\Service\SituService;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
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
    
    /**
     * @Route("/ajaxCreate", methods="GET|POST")
     */
    public function ajaxCreate(): JsonResponse
    {
        $this->denyAccessUnlessGranted('ROLE_CONTRIBUTOR');
            
        // Get current user
        $user = $this->security->getUser();
        $userId = $user->getId();
        $userLang = $user->getLangId() != '' ? $user->getLangId() : 47;
            
        // Get request data
        $request = $this->get('request_stack')->getCurrentRequest();
        $dataForm = $request->request->all();
        $data = $dataForm['dataForm'];
        
        $landId = isset($data['lang']) ? $data['lang'] : $userLang;
        
        $langData = $this->getDoctrine()
                ->getRepository(Lang::class)
                ->findOneBy([ 'id' => $landId ]);
        
        if (!$langData->getEnabled()) {
            $msg = $this->translator->trans(
                'lang_deny', [],
                'user_messages', $locale = locale_get_default()
                );
            $request->getSession()->getFlashBag()->add('error', $msg);

            return $this->json([
                'success' => false,
                'redirection' => $this->redirectToRoute('user_situs', [
                    'id' => $userId, '_locale' => locale_get_default()
                ]),
            ]);  
        }

        $eventData = $this->createOrChooseData(
                $data['event'], 'event', $langData, '', $userId
        );
        $categoryLevel1 = $this->createOrChooseData(
                $data['categoryLevel1'], 'categoryLevel1', $langData,
                $eventData, $userId
        );
        $categoryLevel2 = $this->createOrChooseData(
                $data['categoryLevel2'], 'categoryLevel2', $langData,
                $categoryLevel1, $userId
        );

        $statusId = $data['statusId'];
        $dateNow = new \DateTime('now');

        // Update or create Situ
        if (empty($data['id'])) {
            $situ = new Situ();
            $situ->setDateCreation($dateNow);
            $situ->setUserId($userId); 
        } else {
            $situ = $this->em->getRepository(Situ::class)->find($data['id']);
            
            // Only situ author or moderator can update situ
            if (!$user->hasRole('ROLE_MODERATOR') && $userId != $situ->getUserId()) {

                $msg = $this->translator->trans(
                    'access_deny', [],
                    'user_messages', $locale = locale_get_default()
                    );
                $request->getSession()->getFlashBag()->add('error', $msg);
                
                return $this->json([
                    'success' => false,
                    'redirection' => $this->redirectToRoute('user_situs', [
                        'id' => $userId, '_locale' => locale_get_default()
                    ]),
                ]);                
            }
            
            $situ->setDateLastUpdate($dateNow);

            // Clear original collection
            foreach ($situ->getSituItems() as $item) {
                $situ->getSituItems()->removeElement($item);
                $this->em->remove($item);
            }
        }
        
        if (!empty($data['initialId']) || $situ->getTranslatedSituId() != '') {
            $situ->setInitialSitu(false);
            if (!empty($data['initialId']))
                $situ->setTranslatedSituId($data['initialId']);
        } else {
            $situ->setInitialSitu(true);
        }

        $situ->setTitle($data['title']);
        $situ->setDescription($data['description']);

        // Depending on the button save (val = 1) or submit (val = 2) clicked
        if ($statusId == 2) $situ->setDateSubmission($dateNow);
        else $situ->setDateSubmission(null);
        
        $situ->setDateValidation(null); 
        $situ->setLang($langData);
        $situ->setEvent($eventData);
        $situ->setCategoryLevel1($categoryLevel1);
        $situ->setCategoryLevel2($categoryLevel2);
        $situ->setStatusId($statusId);
        $this->em->persist($situ);

        // Add new collection
        foreach ($data['situItems'] as $key => $d) {
            $situItem = new SituItem();
            if ($key == 0) $situItem->setScore(0);
            else $situItem->setScore($d['score']);
            $situItem->setTitle($d['title']);
            $situItem->setDescription($d['description']);
            $this->em->persist($situItem);
            $situItem->setSitu($situ);
        }

        $this->em->flush();

        $msgSaveCreate = $this->translator->trans(
                    'contrib.form.save.flash.success', [],
                    'user_messages', $locale = locale_get_default()
                    );

        $msgSaveUpdate = $this->translator->trans(
                    'contrib.form.save.flash.success_update', [],
                    'user_messages', $locale = locale_get_default()
                    );

        $msgSubmitCreate = $this->translator->trans(
                    'contrib.form.submit.flash.success', [],
                    'user_messages', $locale = locale_get_default()
                    );

        $msgSubmitUpdate = $this->translator->trans(
                    'contrib.form.submit.flash.success_update', [],
                    'user_messages', $locale = locale_get_default()
                    );
        
        if ($statusId == 1)
            $msg = empty($data['id']) ? $msgSaveCreate : $msgSaveUpdate;
        else
            $msg = empty($data['id']) ? $msgSubmitCreate : $msgSubmitUpdate;
        
        $request->getSession()->getFlashBag()->add('success', $msg);

        return $this->json([
            'success' => true,
            'redirection' => $this->redirectToRoute('user_situs',
                    ['_locale' => locale_get_default()]),
        ]);
    }    
    
    /**
     * Load data depending on selection or creation
     * Used by ajaxSitu()
     */
    public function createOrChooseData($dataEntity, $entity, $lang, $parent, $userId)
    {        
        if (is_array($dataEntity)) {
            switch ($entity) {
                case 'event':
                    $data = new Event();
                    break;
                case 'categoryLevel1':
                    $data = new Category();
                    $data->setDateCreation(new \DateTime('now'));
                    $data->setDescription($dataEntity['description']);
                    $data->setEvent($parent);
                    break;
                case 'categoryLevel2':
                    $data = new Category();
                    $data->setDateCreation(new \DateTime('now'));
                    $data->setDescription($dataEntity['description']);
                    $data->setParent($parent);
                    break;
            }
            $data->setTitle($dataEntity['title']);
            $data->setUserId($userId);
            $data->setValidated(0);
            $data->setLang($lang);
            $this->em->persist($data);
        } else {
            switch ($entity) {
                case 'event':
                    $data = $this->getDoctrine()
                        ->getRepository(Event::class)
                        ->findOneBy([ 'id' => $dataEntity ]);
                    break;
                case 'categoryLevel1':
                    $data = $this->getDoctrine()
                        ->getRepository(Category::class)
                        ->findOneBy([ 'id' => $dataEntity ]);
                    break;
                case 'categoryLevel2':
                    $data = $this->getDoctrine()
                        ->getRepository(Category::class)
                        ->findOneBy([ 'id' => $dataEntity ]);
                    break;
            }
        }
        return $data;
    }
}
