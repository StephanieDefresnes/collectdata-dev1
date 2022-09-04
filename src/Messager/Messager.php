<?php

namespace App\Messager;

use App\Entity\Message;
use App\Entity\Situ;
use App\Entity\User;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\DependencyInjection\ParameterBag\ParameterBagInterface;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;
use Symfony\Contracts\Translation\TranslatorInterface;

class Messager {
    
    private $em;
    private $generateUrl;
    private $supremeAdmin;
    private $translator;
    
    /**
     * Messenger constructor
     */
    public function __construct(EntityManagerInterface $em,
                                UrlGeneratorInterface $generateUrl,
                                ParameterBagInterface $parameters,
                                TranslatorInterface $translator)
    {
        $this->em = $em;
        $this->generateUrl = $generateUrl;
        $this->supremeAdmin = $em->getRepository(User::class)
                                    ->find($parameters->get('supreme_admin_id'));
        $this->translator = $translator;
    }
    
    /**
     * Send Message alert to Moderator
     * 
     * @param type $type        - string
     * @param type $data        - object
     * @throws \Exception
     */
    public function sendModeratorAlert( $action, $object)
    {
        $channel        = $action === 'submission' ? 'primary' : null;
        
        $classObject    = $this->em->getClassMetadata( get_class($object) )->getName();
        $classArray     = explode('\\', $classObject);
        $objectString   = strtolower( end($classArray) );
        
        $moderators     = $this->em->getRepository(User::class)
                                ->findRoleByLang('MODERATOR', $object->getLang());

        if ( $moderators ) {
            foreach ( $moderators as $moderator ) {

                $subject = $this->translator->trans(
                    'back.alert.'. $action .'.'. $objectString,
                    [
                        '%id%' => $object->getId(),
                        '%user%' => $object->getUser()->getName(),
                    ],
                    'messenger_messages',
                    $moderator->getLang()->getLang()
                );

                $this->addMessage(   true, 'alert',
                                    $object->getUser(), $moderator,
                                    $subject, null, null,
                                    $objectString, $object->getId(),
                                    null, $channel);
            }
            return;
        }
        
        $subject = $this->translator->trans(
            'back.alert.'. $type .'.'. $entity ,
            [
                '%id%' => $object->getId(),
                '%user%' => $object->getUser()->getName(),
            ],
            'messenger_messages',
            $this->supremeAdmin->getLang()->getLang()
        );

        $this->addMessage(   true, 'alert',
                            $object->getUser(), $this->supremeAdmin,
                            $subject, null, null,
                            $objectString, $object->getId(),
                            null, $channel);
    }
    
    /**
     * Send Message alert to user
     * 
     * @param type $action      set by moderator validation - string
     * @param type $object      set by moderator validation
     * @return Message
     * @throws \Exception
     */
    public function sendUserAlert( $action, $object )
    {
        $channel    = 'validation' == $action ? 'success' : null;
        $sender     = $this->em->getRepository(User::class)->find(intval('-1'));
        $url        = null;
        
        
        $classObject    = $this->em->getClassMetadata( get_class($object) )->getName();
        $classArray     = explode('\\', $classObject);
        $entityName     = strtolower( end($classArray) );
        
        $objectString   = 'category' === $entityName
                            ? $entityName . ( $object->getEvent() ? 'Level1' : 'Level2' )
                            : $entityName;

        // Validation situ
        if ( 'validation' == $action ) {
            
            $subject = $this->translator->trans(
                'front.alert.'. $action .'.'. $objectString, [
                    '%title%' => $object->getTitle(),
                ],
                'messenger_messages',
                $object->getUser()->getLang()->getLang()
            );
            
            if ( $object instanceof Situ ) {
                $url = $this->generateUrl->generate(
                    'read_situ',
                    [
                        'slug' => $object->getSlug(),
                        '_locale' => $object->getUser()->getLang()->getLang(),
                    ]
                );
            }
            
            if ( $object instanceof Category )
                $objectString = $category->getEvent() ? 'categoryLevel1' : 'categoryLevel2';
        }
        
        $this->addMessage(   false, 'alert',
                            $sender, $object->getUser(),
                            $subject, null, $url,
                            $objectString, $object->getId(),
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
    public function sendUserEnvelope( $action, $object, $text )
    {
        $channel = 'situ_refuse' === $action ? 'warning' : 'primary';
        
        $classObject    = $this->em->getClassMetadata( get_class($object) )->getName();
        $classArray     = explode('\\', $classObject);
        $objectString   = strtolower( end($classArray) );
        
        // FLP moderator signing
        $sender = $this->em->getRepository(User::class)->find('-1');        

        if ( 'situ_refuse' === $action ) {
            
            $lang = $object->getUser()->getLang()->getLang();
            
            // Subject
            $subject = $this->translator->trans(
                'front.alert.refuse.subject', [],
                'messenger_messages', $lang
            );

            // Content
            $reason = $this->translator->trans(
                'front.alert.refuse.text', [
                    '%id%' => $object->getId(),
                ],
                'messenger_messages', $lang
            );

            $urlSitu = $this->generateUrl->generate(
                'create_situ',
                [
                    '_locale' => $lang,
                    'id' => $object->getId(),
                ]
            );
            
            $link = $this->translator->trans(
                'front.alert.refuse.link', [
                    '%link%' => $urlSitu,
                ],
                'messenger_messages', $lang
            );

            $content = $reason ."\n". $text ."\n". $link;
        }

        return $this->addMessage(false, 'envelope',
                                $sender, $object->getUser(),
                                $subject, $content, null,
                                $objectString, $object->getId(),
                                null, $channel);
    }
    
    /**
     * Send report Message to moderator
     * 
     * @param type $text
     * @param type $senderUser      can be user or anonymous
     * @param type $object          can be situ or user
     */
    public function reportMessage( $text, $senderUser = null, $object )
    {
        // Sender can be anonymous
        $sender =   $this->em->getRepository(User::class)->find(0);
        if ( $senderUser ) $sender = $senderUser;
        
        $params     = [ 'id' => $object->getId(),
                        'back' => 'back',
                        '_locale' => $object->getLang()->getLang() ];
        $url        = null;
        
        $classObject    = $this->em->getClassMetadata( get_class($object) )->getName();
        $classArray     = explode('\\', $classObject);
        $objectString   = strtolower( end($classArray) );
                
        // Situ report
        if ( $object instanceof Situ )
                $url = $this->generateUrl->generate('back_situ_read', $params);
        // User report
        if ( $object instanceof User )
                $url = $this->generateUrl->generate('back_user_read', $params);
        
        $moderators = $this->em->getRepository(User::class)
                            ->findRoleByLang('MODERATOR', $object->getLang());
        
        if ( $moderators ) {
            foreach ( $moderators as $moderator ) {
                
                $subject = $this->translator->trans(
                    'message.report.'. $objectString .'.subject', [],
                    'messenger_messages',
                    $moderator->getLang()->getLang()
                );

                $content = $this->translator->trans(
                                'message.report.'. $objectString .'.text', [],
                                'messenger_messages',
                                $moderator->getLang()->getLang()
                            ) ."\n". $text;
            
                addMessage(   true, 'envelope',
                                    $sender, $moderator,
                                    $subject, $content, $url,
                                    $objectString, $object->getId(),
                                    true, 'warning');
            }
            return;
        }
                
        $subject = $this->translator->trans(
            'message.report.'. $objectString .'.subject', [],
            'messenger_messages',
            $this->supremeAdmin->getLang()->getLang()
        );
        
        $content = $this->translator->trans(
                        'message.report.'. $objectString .'.text', [],
                        'messenger_messages', $this->supremeAdmin->getLang()->getLang()
                    ) ."\n". $text;

        $this->addMessage(   true, 'envelope',
                            $sender, $this->supremeAdmin,
                            $subject, $content, $url,
                            $objectString, $objectId,
                            true, $channel);
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
     * @param type $entity          string object name - can be user, situ, event...
     * @param type $entityId        objectId - object can be User, Situ, Event...
     * @param type $reported        boolean
     * @param type $channel         string
     * @return Message
     * @throws \Exception 
     */
    public function addMessage( $admin, $type,
                                $sender, $recipient,
                                $subject, $text = null, $url = null,
                                $entity, $entityId,
                                $reported = null, $channel = null)
    {
        $message = new Message();
        
        $message->setAdmin( $admin );
        $message->setType( $type );
        $message->setSenderUser( $sender );
        $message->setRecipientUser( $recipient );
        $message->setSubject( $subject );
        $message->setText( $text );
        $message->setUrl( $url );
        $message->setEntity( $entity );
        $message->setEntityId( $entityId );
        $message->setReported( $reported );
        $message->setScanned( false );
        $message->setChannel( $channel );
        $this->em->persist( $message );

        try {
            $this->em->flush();
            return ['success' => true, 'message' => $message];
        } catch (\Doctrine\DBAL\DBALException $e) {
            return ['success' => false, 'error' => $e->getMessage()];
        }
    }
}
