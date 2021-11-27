<?php

namespace App\Service;

/**
 * Used when calling trait Referer function
 * from MessageController with followMessage() type 'alert'
 */
class RefererService {

    /**
     * Return array of url params to reload current page
     * 
     * @param type $refererParams
     * @return type
     */
    public function getParamsArray($refererParams)
    {
        switch ($refererParams['_route']) {
            
            // Back : case of DBALException
            case 'back_content_edit':
                $params = [
                    'back' => $refererParams['back'],
                    'id' => $refererParams['id'],
                    '_locale' => locale_get_default()
                ];
                break;
            case 'back_envelope_read':
                $params = [
                    'back' => $refererParams['back'],
                    'id' => $refererParams['id'],
                    '_locale' => locale_get_default()
                ];
                break;
            case 'back_event_read':
                $params = [
                    'event' => $refererParams['event'],
                    '_locale' => locale_get_default()
                ];
                break;
            case 'back_translation_create':
                $params = [
                    'back' => $refererParams['back'],
                    'referentId' => $refererParams['referentId'],
                    'langId' => $refererParams['langId'],
                    'id' => $refererParams['id'],
                    '_locale' => locale_get_default()
                ];
                break;
            case 'back_translation_form':
                $params = [
                    'id' => $refererParams['id'],
                    '_locale' => locale_get_default()
                ];
                break;
            case 'back_translation_verify':
                $params = [
                    'translation' => $refererParams['translation'],
                    '_locale' => locale_get_default()
                ];
                break;
            case 'back_situ_read':
                $params = [
                    'id' => $refererParams['id'],
                    '_locale' => locale_get_default()
                ];
                break;
            case 'back_situ_verify':
                $params = [
                    'id' => $refererParams['id'],
                    '_locale' => locale_get_default()
                ];
                break;
            case 'back_user_read':
                $params = [
                    'id' => $refererParams['id'],
                    '_locale' => locale_get_default()
                ];
                break;
            case 'back_user_update':
                $params = [
                    'id' => $refererParams['id'],
                    '_locale' => locale_get_default()
                ];
                break;
            
            // Front : case of DBALException or follow-message type alert
            case 'front_content_edit':
                $params = [
                    'id' => $refererParams['id'],
                    '_locale' => locale_get_default()
                ];
                break;
            case 'front_envelope_read':
                $params = [
                    'id' => $refererParams['id'],
                    '_locale' => locale_get_default()
                ];
                break;
            case 'front_translation_create':
                $params = [
                    'referentId' => $refererParams['referentId'],
                    'langId' => $refererParams['langId'],
                    'id' => $refererParams['id'],
                    '_locale' => locale_get_default()
                ];
                break;
            case 'create_situ':
                $params = [
                    'id' => $refererParams['id'],
                    '_locale' => locale_get_default()
                ];
                break;
            case 'read_situ':
                $params = [
                    'slug' => $refererParams['slug'],
                    '_locale' => locale_get_default()
                ];
                break;
            case 'translate_situ':
                $params = [
                    'situId' => $refererParams['situId'],
                    'langId' => $refererParams['langId'],
                    '_locale' => locale_get_default()
                ];
                break;
            case 'user_visit':
                $params = [
                    'slug' => $refererParams['slug'],
                    '_locale' => locale_get_default()
                ];
                break;
            
            default:
                $params = ['_locale' => locale_get_default()];
        }
        
        return $params;
    }
    
}
