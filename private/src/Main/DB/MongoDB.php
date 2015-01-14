<?php


namespace Main\DB;

class MongoDB {

    /** @var  \MongoClient $mongo */
    private static $mongo, $db;

    public static function getMongo(){
        if(is_null(self::$mongo)){
            self::$mongo = new \MongoClient(AppConfig::get("db.mongodb.host"));
        }
        return self::$mongo;
    }

    public static function getDB(){
        if(is_null(self::$db)){
            self::$db = self::getMongo()->{AppConfig::get("db.mongodb.name")};
        }
        return self::$db;
    }
}