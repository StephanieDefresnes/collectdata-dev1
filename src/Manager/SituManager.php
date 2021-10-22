<?php

namespace App\Manager;

use App\Entity\Category;
use App\Entity\Event;
use App\Entity\Lang;
use App\Entity\Situ;
use App\Entity\SituItem;
use App\Entity\Status;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\DependencyInjection\ParameterBag\ParameterBagInterface;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;
use Symfony\Component\Security\Core\Security;
use Symfony\Contracts\Translation\TranslatorInterface;

class SituManager {
    
    private $em;
    private $parameters;
    private $security;
    private $translator;
    private $urlGenerator;
    
    public function __construct(EntityManagerInterface $em,
                                ParameterBagInterface $parameters,
                                Security $security,
                                TranslatorInterface $translator,
                                UrlGeneratorInterface $urlGenerator)
    {
        $this->em = $em;
        $this->parameters = $parameters;
        $this->security = $security;
        $this->translator = $translator;
        $this->urlGenerator = $urlGenerator;
    }
    
    /**
     * Distribute data to persist
     * 
     * Return object and message depending on status
     * 
     * @param FormInterface $form
     * @param type $id
     * @return type
     */
    public function setData(Request $request)
    {        
        $dateNow = new \DateTime('now');
        
//        dd($data = $request->request->get('dataForm'));
        
        // Get current user
        $user = $this->security->getUser();        
        $userLang = $user->getLang();
            
        // Get request data        
        if ($request->isXMLHttpRequest()) {
            
            $data = $request->request->get('dataForm');
        
            $landId = isset($data['lang']) ? $data['lang'] : $userLang;

            $langData = $this->em->getRepository(Lang::class)->find($landId);

            if (!$langData->getEnabled()) {
                return $this->redirectToRoute('access_denied', [
                    '_locale' => locale_get_default(),
                    'code' => '1912',
                ]);      
            }

            $eventData = $this->createOrChooseData(
                    $data['event'], 'event', $langData, '', $user
            );
            $categoryLevel1 = $this->createOrChooseData(
                    $data['categoryLevel1'], 'categoryLevel1', $langData,
                    $eventData, $user
            );
            $categoryLevel2 = $this->createOrChooseData(
                    $data['categoryLevel2'], 'categoryLevel2', $langData,
                    $categoryLevel1, $user
            );

            $dateNow = new \DateTime('now');

            // Update or create Situ
            if (empty($data['id'])) {
                $situ = new Situ();
                $update = false;
                $situ->setDateCreation($dateNow);
                $situ->setUser($user);
            } else {
                $situ = $this->em->getRepository(Situ::class)->find($data['id']);

                // Only situ author can update situ
                if ($user !== $situ->getUser()) {
                    return $this->redirectToRoute('access_denied', [
                        '_locale' => locale_get_default(),
                        'code' => '21191',
                    ]);              
                }
                $update = true;

                $situ->setDateLastUpdate($dateNow);

                // Clear original collection
                foreach ($situ->getSituItems() as $item) {
                    $situ->getSituItems()->removeElement($item);
                    $this->em->remove($item);
                }
            }

            if (!empty($data['translatedSituId'])) {
                $situ->setInitialSitu(false);
                $situ->setTranslatedSituId($data['translatedSituId']);
            } else {
                $situ->setInitialSitu(true);
            }

            $situ->setTitle($data['title']);
            $situ->setDescription($data['description']);

            // Depending on the button save (val = 1) or submit (val = 2) clicked
            if ($data['action'] == 'save') {
                $situ->setDateSubmission(null);
                $status = $this->em->getRepository(Status::class)->find(1);
            } else {
                $situ->setDateSubmission($dateNow);
                $status = $this->em->getRepository(Status::class)->find(2);
            }

            $situ->setDateValidation(null); 
            $situ->setLang($langData);
            $situ->setEvent($eventData);
            $situ->setCategoryLevel1($categoryLevel1);
            $situ->setCategoryLevel2($categoryLevel2);
            $situ->setStatus($status);
//            $this->em->persist($situ);

            // Add new collection
            foreach ($data['situItems'] as $key => $dataItem) {
                $situItem = new SituItem();
                if ($key === 0) $situItem->setScore(0);
                else $situItem->setScore($dataItem['score']);
                $situItem->setTitle($dataItem['title']);
                $situItem->setDescription($dataItem['description']);
                $this->em->persist($situItem);
                $situItem->setSitu($situ);
            }
            
            return $result = [
                'situ' => $situ,
                'action' => $data['action'],
                'update' => $update,
            ];
//            try {
//                $this->em->flush();
//
//                if ($statusId === 2) {
//                    $this->mailer->sendModeratorSituValidate($situ);
//                    $this->messenger->sendModeratorAlert('situ', $situ);
//                
//                    $msg = $this->translator->trans(
//                                'contrib.form.'. $msgAction .'.flash.success', [],
//                                'user_messages', $locale = locale_get_default()
//                                );
//                } else {
//                    $msg = $this->translator->trans(
//                                'contrib.form.'. $msgAction .'.flash.success_update', [],
//                                'user_messages', $locale = locale_get_default()
//                                );
//                }
//                $request->getSession()->getFlashBag()->add('success', $msg);
//
//                return $this->json(['success' => true]);
//
//            } catch (\Doctrine\DBAL\DBALException $e) {
//                $msg = $this->translator->trans(
//                            'contrib.form.'. $msgAction .'.flash.error', [],
//                            'user_messages', $locale = locale_get_default()
//                            );
//                $this->addFlash('error', $msg.PHP_EOL.$e->getMessage());
//                
//                return $this->json(['success' => false]);
//            }
        }
    }
    
    /**
     * Load data depending on selection or creation
     * Used by ajaxSitu()
     */
    protected function createOrChooseData($dataEntity, $entity, $lang, $parent, $user)
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
            $data->setUser($user);
            $data->setValidated(0);
            $data->setLang($lang);
            $this->em->persist($data);
        } else {
            switch ($entity) {
                case 'event':
                    $data = $this->em->getRepository(Event::class)->find($dataEntity);
                    break;
                case 'categoryLevel1':
                    $data = $this->em->getRepository(Category::class)->find($dataEntity);
                    break;
                case 'categoryLevel2':
                    $data = $this->em->getRepository(Category::class)->find($dataEntity);
                    break;
            }
        }
        return $data;
    }
}
