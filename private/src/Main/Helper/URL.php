<?php
/**
 * Created by PhpStorm.
 * User: p2
 * Date: 7/19/14
 * Time: 4:47 PM
 */

namespace Main\Helper;

use Main\AppConfig;

class URL {
    public static function absolute($url){
        return AppConfig::get("application.base_url").$url;
    }

    public static function share($url){
        return AppConfig::get("application.share_url").$url;
    }
}