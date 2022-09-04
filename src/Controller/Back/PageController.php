<?php

namespace App\Controller\Back;

use App\Entity\Lang;
use App\Entity\Page;
use App\Entity\Situ;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\IsGranted;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;

/**
 * @IsGranted("IS_AUTHENTICATED_FULLY")
 */
class PageController extends AbstractController
{
    public function dashboard()
    {
        return $this->render('back/page/dashboard.html.twig');
    }
    
    public function allContents()
    {
        $pages = $this->getDoctrine()->getRepository(Page::class)->findAll();
        
        return $this->render('back/page/content/search.html.twig', [
            'pages' => $pages,
        ]);
    }
    
    public function contentRead($id)
    {
        $page = $this->getDoctrine()->getRepository(Page::class)->find($id);
        
        if ( ! $page ) {
            return $this->redirectToRoute('back_not_found', [
                '_locale' => locale_get_default()
            ]);
        }
        
        $referentPage   = $this->getDoctrine()
                                ->getRepository(Page::class)
                                            ->findOneBy([
                                                'type' => $page->getType(),
                                                'lang' => locale_get_default(),
                                            ]);
        
        return $this->render('back/page/content/read.html.twig', [
            'page' => $page,
            'referentPage' => $referentPage,
        ]);
    }
    
    /**
     * @return Response of data array to set Charts js
     */
    public function ajaxDataChart(): Response
    {
        /*
         *  Get data for Chart Area
         */
        $dates = [];
        $months = [];
        $years = [];
        $countMonth = [];
        
        $result = $this->getDoctrine()->getRepository(Situ::class)
                                ->findSitusCountByMonth();
        
        // Month format with 3 first characters
        $intl = new \IntlDateFormatter(
                            locale_get_default(),
                            \IntlDateFormatter::LONG,
                            \IntlDateFormatter::LONG,
                            null, null, 'MMM');
        foreach( $result as $r ) {
            array_push( $countMonth, $r['situs'] );
            array_push( $months, $intl->format( new \DateTime($r['situMonth']) ) );
            array_push( $years, $r['situYear'] );
        }
        $length = count($years);
        
        // Push first date concatenated
        array_push($dates, $months[0] .' '. $years[0]);
        
        for ( $i = 1; $i < $length; $i++ ) {

            $date = $months[$i];
            
            // Concate date if previous year is different or if last date
            if ( $years[$i-1] !== $years[$i] || $i === $length - 1 )
                $date = $months[$i] .' '. $years[$i];
            
            array_push($dates, $date);
        }
        
        $situsPerMonth['situs']     = $countMonth;
        $situsPerMonth['dates']     = $dates;
        $situsPerMonth['situsMax']  = max($countMonth);
        
        $dataChart['situsPerMonth'] = $situsPerMonth;
        
        /*
         *  Get data for Chart Donut
         */
        $langs = $this->getDoctrine()->getRepository(Lang::class)
                    ->findBy(['enabled' => true]);
        
        $langNames = $countSitus = $countUsers = [];
        
        foreach ( $langs as $lang ) {
            $names = explode(';', $lang->getEnglishName());
            array_push( $langNames, $names[0] );
            array_push( $countSitus, count($lang->getSitus()) );
            array_push( $countUsers, count($lang->getUsers()) );
        }
        
        $dataPerLang['langs']       = $langNames;
        $dataPerLang['situs']       = $countSitus;
        $dataPerLang['users']       = $countUsers;
        $dataPerLang['usersMax']    = max($countUsers);
        
        $dataChart['dataPerLang']   = $dataPerLang;
        
        
        /*
         *  Return data to set Charts
         */
        return $this->json($dataChart);
    }
    
}