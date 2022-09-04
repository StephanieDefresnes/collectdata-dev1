<?php

namespace App\Service;

use App\Entity\Category;
use App\Entity\Event;
use App\Entity\Situ;
use App\Entity\Status;
use App\Mailer\Mailer;
use App\Messager\Messager;
use Doctrine\ORM\EntityManagerInterface;

class SituValidator {
    
    private $em;
    private $mailer;
    private $messager;
    
    public function __construct(EntityManagerInterface $em,
                                Mailer $mailer,
                                Messager $messager)
    {
        $this->em = $em;
        $this->mailer = $mailer;
        $this->messager = $messager;
    }
    
    public function situValidation( $data )
    {
        $situ = $this->em->getRepository(Situ::class)->find($data['id']);
        
        $situ->setStatus( $this->em->getRepository(Status::class)->find($data['statusId']) );
        
        $validation = [];
        
        $situ->setDateValidation( 'validation' === $data['action'] ? new \DateTime('now') : null );
        $this->em->persist($situ);
        
        // Default refuse
        $validation['situ'] = [
            'validation' => false,
            'situ' => $situ,
            'text' => $data['comment'],
        ];
        
        if ( 'validation' === $data['action'] ) {
            
            $event          = $this->dataValidation( $data, $situ->getEvent() );
            if ( '0' === $data['eventInitial'] )
                    $validation['event'] = $event;

            $categoryLevel1 = $this->dataValidation( $data, $situ->getCategoryLevel1() );
            if ( '0' === $data['categoryLevel1Initial'] )
                    $validation['categoryLevel1'] = $categoryLevel1;
            
            $categoryLevel2 = $this->dataValidation( $data, $situ->getCategoryLevel2() );
            if ( '0' === $data['categoryLevel2Initial'] )
                    $validation['categoryLevel2'] = $categoryLevel2;
            
            // message validation
            $validation['situ'] = [ 'validation' => true ];
        }
            
        try {
            
            $this->em->flush();

            $error = null;
            
            if ( array_key_exists('event', $validation) )
                $error .= $this->tryToSend( 'alert', $event );

            if ( array_key_exists('categoryLevel1', $validation) )
                $error .= $this->tryToSend( 'alert', $categoryLevel1 ); 

            if ( array_key_exists('categoryLevel2', $validation) )
                $error .= $this->tryToSend( 'alert', $categoryLevel2 );
            
            switch ( $validation['situ']['validation'] ) {
                case true:
                    $error .= $this->tryToSend( 'alert', $situ );
                    // commented in localhost
                    $error .= $this->tryToSend( 'validation', $situ );
                    break;
                default:
                    $error .= $this->tryToSend( 'refuse', $situ, $validation['situ']['text'] );
                    // commented in localhost
                    $error .= $this->tryToSend( 'validation', $situ );
            }

            $result = [
                'success' => true,
                'msg' => $error,
            ];
            
        } catch ( \Doctrine\DBAL\DBALException $e ) {
            $result = [
                'success' => false,
                'msg' => $e->getMessage(),
            ];
        }
        
        return $result;
    }
    
    private function dataValidation( $data, $object )
    {
        $classObject    = $this->em->getClassMetadata( get_class($object) )->getName();
        $classArray     = explode('\\', $classObject);
        $entityName     = strtolower( end($classArray) );
        $dataName       = 'event' === $entityName
                            ? $entityName
                            : ( $object->getEvent() 
                                ? 'categoryLevel1' : 'categoryLevel2' );
        
        if ( '0' === $data[$dataName .'Initial'] && ! $object->getValidated() )
        {
            $object->setValidated( true );
        }
        return $object;
    }
    
    private function tryToSend( $action, $object, $text = null ) {
        try {
            switch ( $action ) {
                case 'alert':
                    $this->messager->sendUserAlert( 'validation', $object );
                    break;
                case 'validation':
                    // commented in localhost
                    $this->mailer->sendSituValidationMail( $object );
                    break;
                case 'refuse':
                    $this->messager->sendUserEnvelope( 'situ_refuse', $object, $text );
                    break;
            }
            return null;
        } catch ( \Exception $e ) {
            return $e->getMessage().PHP_EOL;
        }
    }
    
}