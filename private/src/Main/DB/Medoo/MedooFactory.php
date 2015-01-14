<?php
/**
 * Created by PhpStorm.
 * User: p2
 * Date: 18/12/2557
 * Time: 14:26 น.
 */

namespace Main\DB\Medoo;


use Main\AppConfig;

class MedooFactory {
    /**
     * @param \medoo[] $instances;
     */
    protected static $instances = [];

    /**
     * @param string $name
     * @return \medoo
     */
    public static function getInstance($name = 'master'){
        if(!isset(self::$instances[$name])){
            $paramPath = 'db.medoo.'.$name;
            self::$instances[$name] = new \medoo([
                // required
                'database_type' => AppConfig::get($paramPath.'.database_type'),
                'database_name' => AppConfig::get($paramPath.'.database_name'),
                'server' => AppConfig::get($paramPath.'.server'),
                'username' => AppConfig::get($paramPath.'.username'),
                'password' => AppConfig::get($paramPath.'.password'),
                'port' => AppConfig::get($paramPath.'.port'),
                'charset' => AppConfig::get($paramPath.'.charset'),
                'option' => [
                    \PDO::ATTR_CASE => \PDO::CASE_NATURAL
                ]
            ]);
        }
        return self::$instances[$name];
    }
}