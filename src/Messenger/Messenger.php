<?php

namespace App\Messenger;

use App\Entity\Lang;
use App\Entity\Message;
use App\Entity\Situ;
use App\Entity\User;
use App\Service\MessageService;
use App\Service\UserService;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\DependencyInjection\ParameterBag\ParameterBagInterface;
use Symfony\Contracts\Translation\TranslatorInterface;

class Messenger {
    
    private $em;
    private $messageService;
    private $parameters;
    private $translator;
    private $userService;
    
    /**
     * Messenger constructor.
     *
     */
    public function __construct(EntityManagerInterface $em,
                                MessageService $messageService,
                                ParameterBagInterface $parameters,
                                TranslatorInterface $translator,
                                UserService $userService)
    {
        $this->em = $em;
        $this->messageService = $messageService;
        $this->parameters = $parameters;
        $this->translator = $translator;
        $this->userService = $userService;
    }
    
    public function sendModeratorAlert($entity, $data) {
        
        $moderators = $this->userService->getRoleByLang('MODERATOR', $data->getLang());
        
        $author = $this->em->getRepository(User::class)->find($data->getUser()->getId());

        if (!$moderators) {
            $moderators = $this->em->getRepository(User::class)->findBy(['id' => intval('-1')]);
        }
        
        foreach ($moderators as $moderator) {

            $moderatorLang = $this->em->getRepository(Lang::class)->find($moderator->getLang());

            $subject = $this->translator->trans(
                'back.alert.'.$entity, [
                    '%user%' => $author->getName(),
                    '%id%' => $data->getId(),
                ],
                'messenger_messages',
                $locale = $moderatorLang->getLang()
            );

            $message = new Message();
            $message->setChannel('primary');
            $message->setType('alert');
            $message->setSubject($subject);
            $message->setSenderUser($moderator);
            $message->setRecipientUser($moderator);
            $message->setEntity($entity);
            $message->setEntityId($data->getId());
            $message->setReported(false);
            $message->setScanned(false);
            $this->em->persist($message);

            try {
                $this->em->flush();
            } catch (\Doctrine\DBAL\DBALException $e) {
                throw new \Exception($e->getMessage());
            }
        }
        
    }
    
    public function sendMessageEnvelope($param) {
        
    }
    
    
    
}
