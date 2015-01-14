<?php
/**
 * Created by PhpStorm.
 * User: MRG
 * Date: 11/5/14 AD
 * Time: 4:22 PM
 */
namespace Main;

use Main\Helper\ArrayHelper;

Class AppConfig {

    static public $setting = null;

    public static function get($name) {
        if (is_null(self::$setting)) {
            self::$setting = include(dirname(__FILE__)."/../../AppConfig.php");
            self::$setting = ArrayHelper::ArrayGetPath(self::$setting);
        }
        return self::$setting[$name];
    }
}