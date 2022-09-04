<?php

namespace App\Manager;

class MessageManager {
    
    public function getRoute( $entity )
    {
        switch ( $entity ) {
            case 'situ':
                $route = 'back_situ_verify';
                break;
            case 'event':
                $route = 'back_event_read';
                break;
            case 'categoryLevel1':
                $route = 'back_category_read';
                break;
            case 'categoryLevel2':
                $route = 'back_category_read';
                break;
        }
        return $route;
    }
    
}
