<?php
/**
 * Created by PhpStorm.
 * User: NUIZ
 * Date: 14/1/2558
 * Time: 14:35
 */

namespace Main\CTL;
use Main\View\RedirectView;
use Main\Helper\URL;

/**
 * @Restful
 * @uri /
 */
class IndexCTL extends BaseCTL {
    /**
     * @GET
     */
    public function index(){
        return new RedirectView(URL::absolute('/marker'));
    }
}