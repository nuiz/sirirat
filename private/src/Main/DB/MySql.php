<?php
/**
 * Created by PhpStorm.
 * User: MRG
 * Date: 11/5/14 AD
 * Time: 4:57 PM
 */

namespace Main\DB;


class MySql {

    private static $mysql;

    /**
     * @return \Doctrine\ORM\EntityManager
     */
    public static function getMySql()
    {
        if(is_null(self::$mysql)){
            $paths = array('src/Main/Entity');
            $config = \Doctrine\ORM\Tools\Setup::createAnnotationMetadataConfiguration($paths, true);
            self::$mysql = \Doctrine\ORM\EntityManager::create(array(
                'driver'   => 'pdo_mysql',
                'user'     => AppConfig::get("db.mysql.host"),
                'password' => AppConfig::get("db.mysql.password"),
                'dbname'   => AppConfig::get("db.mysql.name"),
                'charset'  => 'utf8',
                'driverOptions' => array(
                    1002=>'SET NAMES utf8'
                )
            ), $config);
        }
        return self::$mysql;
    }
} 