<?php

namespace App\Controller;

use App\Entity\Lang;
use App\Entity\Translation;
use App\Entity\TranslationField;
use App\Form\Translation\TranslationFormType;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Security\Core\Security;
use Symfony\Contracts\Translation\TranslatorInterface;

class TranslationController extends AbstractController
{    
    public function createTranslate(EntityManagerInterface $em,
                                    Request $request,
                                    Security $security,
                                    TranslatorInterface $translator,
                                    $locale, $referentId, $langId, $id, $back = null): Response 
    {
        $this->denyAccessUnlessGranted('IS_AUTHENTICATED_FULLY');
        if ($back) {
            $this->denyAccessUnlessGranted('ROLE_SUPER_ADMIN');        
        }
        
        // Current user
        $user = $security->getUser();
        
        $referent = $em->getRepository(Translation::class)->find($referentId);
        
        // Lang translation
        try {
            $translationLang = $em->getRepository(Lang::class)->find($langId);
        } catch (Exception $e) {
            throw new \Exception('An exception appeared while get Lang translation');
        }
        
        // Form
        if ($id) {
            try {
                $translation = $em->getRepository(Translation::class)->find($id);
                $newTranslation = $translation;
            } catch (Exception $e) {
                throw new \Exception('An exception appeared while get Translation');
            }
        } else {
            $translation = new Translation();
            $newTranslation = clone $referent;
        }
        
        $form = $this->createForm(TranslationFormType::class, $newTranslation, [
            'lang' => $translationLang->getLang(),
        ]);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {

            if ($id) {
                $translation->setDateLastUpdate(new \DateTime('now'));
            } else {
                $translation->setDateCreation(new \DateTime('now'));
            }
            $translation->setReferent(false);
            $translation->setReferentId($referentId);
            $translation->setLang($form->get('lang')->getData());
            $translation->setLangId($langId);
            $translation->setName($referent->getName());
            $translation->setStatusId($form->get('statusId')->getData());
            $translation->setUserId($user->getId());
            $translation->setEnabled(false);
            $translation->setYamlGenerated(false);
            
            if (!$id) {
                $fields = $form->get('fields');
                foreach ($fields as $field) {
                    $translationField = new TranslationField();
                    $translationField->setName($field->get('name')->getData());
                    $translationField->setType($field->get('type')->getData());
                    $translationField->setValue($field->get('value')->getData());
                    $translationField->setTranslation($translation);
                    $em->persist($translationField);
                }
            }
            
            // as detach() do not works, persit empty value
            $referentFields = $referent->getFields();
            foreach ($referentFields as $field) {
                $field->setValue(null);
                $em->persist($field);
            }

            $em->persist($translation);

            if ($form->getData()->getStatusId() == 1 ) {
                $msgType = 'save';
            } else if ($form->getData()->getStatusId() == 2 ) {
                $msgType = 'submit';
            } else $msgType = 'validate';

            try {
                $em->flush();

                $msg = $translator
                        ->trans('translation.form.flash.'.$msgType.'.success', [],
                                'user_messages', $locale = locale_get_default());
                $this->addFlash('success', $msg);

                if ($back) {
                    return $this->redirectToRoute('back_translation_site', ['_locale' => locale_get_default()]);
                } else {
                    return $this->redirectToRoute('user_translations', ['_locale' => locale_get_default()]);
                }

            } catch (Exception $e) {
                $msg = $translator
                        ->trans('translation.form.flash.'.$msgType.'.error', [],
                                'user_messages', $locale = locale_get_default());
                $this->addFlash('error', $msg);
            }

        }
        if ($back) {
            return $this->render('back/lang/translation/translate.html.twig', [
                'form' => $form->createView(),
                'langName' => html_entity_decode($translationLang->getName(), ENT_QUOTES, 'UTF-8'),
                'referent' => $referent,
                'translation' => $translation,
            ]);
        } else {
            return $this->render('front/translation/translate.html.twig', [
                'form' => $form->createView(),
                'langName' => html_entity_decode($translationLang->getName(), ENT_QUOTES, 'UTF-8'),
                'referent' => $referent,
                'translation' => $translation,
            ]);
        }
    }    
}
