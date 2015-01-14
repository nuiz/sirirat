<?php
/**
 * Created by PhpStorm.
 * User: p2
 * Date: 7/15/14
 * Time: 11:27 AM
 */

namespace Main\CTL;


use Main\Context\Context;
use Main\Http\RequestInfo;

class BaseCTL {
    /**
     * @var Context $ctx;
     */
    public $reqInfo, $ctx;
    public function __construct(RequestInfo $reqInfo){
        $this->reqInfo = $reqInfo;
        $this->ctx = new Context();

    }

    public function getCtx(){
        return $this->ctx;
    }
}