<?php

namespace App\Messager;

use App\Entity\Message;
use App\Entity\Situ;
use App\Entity\User;
use App\Repository\UserRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\DependencyInjection\ParameterBag\ParameterBagInterface;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;
use Symfony\Contracts\Translation\TranslatorInterface;

class Messager {
    
    private $em;
    private $generateUrl;
    private $supremeAdminId;
    private $translator;
    private $userRepository;
    
    /**
     * Messenger constructor
     */
    public function __construct(EntityManagerInterface $em,
                                UrlGeneratorInterface $generateUrl,
                                ParameterBagInterface $parameters,
                                TranslatorInterface $translator,
                                UserRepository $userRepository)
    {
        $this->em = $em;
        $this->generateUrl = $generateUrl;
        $this->supremeAdminId = $parameters->get('supreme_admin_id');
        $this->translator = $translator;
        $this->userRepository = $userRepository;
    }
    
    /**
     * Send Message alert to Moderator
     * 
     * @param type $type        - string
     * @param type $entity      - string
     * @param type $data        - object
     * @throws \Exception
     */
    public function sendModeratorAlert($action, $entity, $data)
    {
        // Default
        $admin = true;
        $senderUser = $data->getUser();
        $entityId = $data->getId();
        
        // FLP recipient(s)
        $moderators = $this->em->getRepository(User::class)
                            ->findRoleByLang('MODERATOR', $data->getLang());
        $supremeAdmin = $this->em->getRepository(User::class)
                            ->find($this->supremeAdminId);
        
        $userName = $data->getUser()->getName();
        
        // Situ validation requested
        if($action === 'submission') {
            $channel = 'primary';
        }

        if ($moderators) {
            foreach ($moderators as $moderator) {
                // Moderator current default lang
                $moderatorLang = $moderator->getLang()->getLang();

                $subject = $this->translator->trans(
                    'back.alert.'.$action.'.'.$entity, [
                        '%id%' => $entityId,
                        '%user%' => $userName,
                    ],
                    'messenger_messages',
                    $locale = $moderatorLang
                );

                // Persist Message
                $this->message( $admin, 'alert',
                                $senderUser, $moderator,
                                $subject, null, null,
                                $entity, $entityId,
                                null, $channel);
            }
        } else {
            // SupremeAdmin current default lang
            $supremeAdminLang = $supremeAdmin->getLang()->getLang();
            
            $subject = $this->translator->trans(
                'back.alert.'.$type.'.'.$entity, [
                    '%id%' => $entityId,
                    '%user%' => $userName,
                ],
                'messenger_messages',
                $locale = $supremeAdminLang
            );
            
            // Persist Message
            $this->message( $admin, 'alert',
                            $senderUser, $supremeAdmin,
                            $subject, null, $url,
                            $entity, $entityId,
                            null, $channel);
        }       
    }
    
    /**
     * Send Message alert to user
     * 
     * @param type $type        set by moderator validation - string
     * @param type $entity      set by moderator validation - string
     * @param type $data        set by moderator validation - object
     * @return Message
     * @throws \Exception
     */
    public function sendUserAlert($action, $entity, $data)
    {
        // Default
        $admin = false;
        $recipientUser = $data->getUser();
        $entityId = $data->getId();
        $url = null;
        // FLP default sender
        $senderUser = $this->em->getRepository(User::class)->find(0);
        
        // Default recipient Lang
        $lang = $recipientUser->getLang()->getLang();

        // Validation situ
        if($action === 'validation') {
            $channel = 'success';

            $subject = $this->translator->trans(
                'front.alert.'.$action.'.'.$entity, [
                    '%title%' => $data->getTitle(),
                ],
                'messenger_messages',
                $locale = $lang
            );
            
            if ($entity === 'situ') {
                $situ = $this->em->getRepository(Situ::class)->find($entityId);
                $url = $this->generateUrl->generate(
                    'read_situ',
                    [
                        'slug' => $situ->getSlug(),
                        '_locale' => $lang,
                    ]
                );
            }
        }
        
        // Persist Message
        $this->message( $admin, 'alert',
                        $senderUser, $recipientUser,
                        $subject, null, $url,
                        $entity, $entityId,
                        null, $channel);
    }
    
    /**
     * Send Message envelope when situ user is refused
     * 
     * @param type $action          string - can be situ_refuse, event_remove(todo)...
     * @param type $text
     * @param type $data            object - can be Situ or Event...
     * @return Message
     * @throws \Exception
     */
    public function sendUserEnvelope($action, $text, $data)
    {
        // Default
        $admin = false;
        $entityId = $data->getId();
        $channel = 'warning';
        $recipient = $data->getUser();
        
        // FLP moderator signing
        $sender = $this->em->getRepository(User::class)->find('-1');
        
        // Default recipient Lang
        $lang = $data->getUser()->getLang()->getLang();

        if ($action = 'situ_refuse') {
            
            $entity = 'situ';
            
            // Subject
            $subject = $this->translator->trans(
                'front.alert.refuse.subject', [],
                'messenger_messages',
                $locale = $lang
            );

            // Text
            $reason = $this->translator->trans(
                'front.alert.refuse.text', [
                    '%id%' => $entityId,
                ],
                'messenger_messages',
                $locale = $lang
            );

            $situLink = $this->generateUrl->generate(
                'create_situ',
                [
                    '_locale' => $lang,
                    'id' => $entityId,
                ]
            );
            $link = $this->translator->trans(
                'front.alert.refuse.link', [
                    '%link%' => $situLink,
                ],
                'messenger_messages',
                $locale = $lang
            );

            $text = $reason.PHP_EOL.$text.PHP_EOL.$link;
        }

        // Persist Message
        return $this->message( $admin, 'envelope',
                        $sender, $recipient,
                        $subject, $text, null,
                        $entity, $entityId,
                        null, $channel);
    }
    
    /**
     * Send Message envelope to report Moderator
     * 
     * @param type $_locale
     * @param type $text            set by user or anonymous
     * @param type $senderUser      can be user or anonymous
     * @param type $type            set by user or anonymous - string
     * @param type $entity          set by user or anonymous
     * @param type $data            set by user or anonymous, can be situ or user
     */
    public function reportEnvelope($text, $senderUser = null, $entity, $data)
    {
        // Default
        if (null === $senderUser) {
            // Anonymous (user not connected)
            $sender =   $this->em->getRepository(User::class)->find('-1');
        } else {
            $sender =   $senderUser;
        }
        $admin =        true;
        $reported =     true;
        $entityId =     $data->getId();
        $channel =      'warning';
        
        $langData = $data->getLang()->getLang();
        $params =       [
                            'id' => $entityId,
                            'back' => 'back',
                            '_locale' => $langData,
                        ];
        
        // FLP recipient(s)
        $moderators = $this->em->getRepository(User::class)
                            ->findRoleByLang('MODERATOR', $data->getLang());
        $supremeAdmin = $this->em->getRepository(User::class)
                            ->find($this->supremeAdminId);
        
        // Situ report
        if($entity = 'situ') $url = $this->generateUrl->generate('back_situ_read', $params);
        // User report
        elseif($entity = 'user') $url = $this->generateUrl->generate('back_user_read', $params);
        
        // Persist Message
        if ($moderators) {
            foreach ($moderators as $moderator) {
                
                $moderatorlang = $moderator->getLang()->getLang();
                
                $subject = $this->translator->trans(
                    'message.report.'. $entity .'.subject', [],
                    'messenger_messages',
                    $locale = $moderatorlang
                );

                $text = $this->translator->trans(
                    'message.report.'. $entity .'.text', [],
                    'messenger_messages',
                    $locale = $moderatorlang
                ).PHP_EOL.$text;
            
                $this->message( $admin, 'envelope',
                                $sender, $moderator,
                                $subject, $text, $url,
                                $entity, $entityId,
                                $reported, $channel);
            }
        } else {
            $supremeAdmin = $this->em->getRepository(User::class)->find($this->supremeAdminId);
            
            $this->message( $admin, 'envelope',
                            $sender, $supremeAdmin,
                            $subject, $content, $url,
                            $entity, $entityId,
                            $reported, $channel);
        }
    }
    
    /**
     * Persist Message
     * 
     * @param type $admin           boolean - depending on back or front office
     * @param type $type            string - alert or envelope
     * @param type $sender          object user
     * @param type $recipient       object user
     * @param type $subject         string
     * @param type $text            text - null with alert type
     * @param type $url             string - null with entity event or category, and alert type
     * @param type $entity          string entity name - can be user, situ, event...
     * @param type $entityId        objectId - object can be User, Situ, Event...
     * @param type $reported        boolean
     * @param type $channel         string
     * @return Message
     * @throws \Exception 
     */
    public function message($admin, $type,
                            $sender, $recipient,
                            $subject, $text = null, $url = null,
                            $entity, $entityId,
                            $reported = null, $channel = null)
    {
        $message = new Message();
        
        $message->setAdmin($admin);
        $message->setType($type);
        $message->setSenderUser($sender);
        $message->setRecipientUser($recipient);
        $message->setSubject($subject);
        $message->setText($text);
        $message->setUrl($url);
        $message->setEntity($entity);
        $message->setEntityId($entityId);
        $message->setReported($reported);
        $message->setScanned(false);
        $message->setChannel($channel);
        $this->em->persist($message);

        try {
            $this->em->flush();
            return ['success' => true, 'message' => $message];
        } catch (\Doctrine\DBAL\DBALException $e) {
            return ['success' => false, 'error' => $e->getMessage()];
        }
    }
}
