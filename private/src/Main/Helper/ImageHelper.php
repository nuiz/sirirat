<?php
/**
 * Created by PhpStorm.
 * User: NUIZ
 * Date: 11/1/2558
 * Time: 22:59
 */

namespace Main\Helper;


use Main\DB\Medoo\MedooFactory;

class ImageHelper {
    public static function makeResponse($id){
        $masterDB = MedooFactory::getInstance();
        $result = $masterDB->select("images", '*', ['id'=> $id, "LIMIT"=> 1]);
        if(isset($result[0])){
            $result[0]['url'] = URL::absolute('/image/'.$id);
            return $result[0];
        }
        else {
            return null;
        }
    }
}