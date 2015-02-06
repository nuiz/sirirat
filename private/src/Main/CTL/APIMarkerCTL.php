<?php
/**
 * Created by PhpStorm.
 * User: NUIZ
 * Date: 14/1/2558
 * Time: 14:39
 */

namespace Main\CTL;
use Main\DB\Medoo\MedooFactory;
use Main\Exception\Service\ServiceException;
use Main\Helper\ImageHelper;
use Main\Helper\URL;
use Main\View\HtmlView;
use Main\View\JsonView;
use Main\View\RedirectView;
use Valitron\Validator;

/**
 * @Restful
 * @uri /api/marker
 */
class APIMarkerCTL extends BaseCTL {
    /**
     * @GET
     */
    public function index(){
        $v = new HtmlView('/index');
        $db = MedooFactory::getInstance();
        $items = $db->select('marker', '*');
        foreach($items as $key=> $item){
            $this->build($items[$key]);
        }
        $res = array('length'=> count($items), 'items'=> $items);
        return new JsonView($res);
    }

    /**
     * @GET
     * @uri /[i:id]
     */
    public function getById(){
        return $this->_get($this->reqInfo->urlParam('id'));
    }

    /**
     * @GET
     * @uri /by_name
     */
    public function getByName(){
        $masterDB = MedooFactory::getInstance();
        $result = $masterDB->select('marker', '*', array('name'=> $this->reqInfo->param('name'), "LIMIT"=> 1));
        if(isset($result[0])){
            $item = $result[0];
            $this->build($item);
            return new JsonView($item);
        }
        else {
            /** @noinspection PhpLanguageLevelInspection */
            return new JsonView([
                'error'=> 'not found.'
            ]);
        }
    }

    public function _get($id){
        // get ebook data
        $masterDB = MedooFactory::getInstance();
        $result = $masterDB->select('marker', '*', array('id'=> $id, "LIMIT"=> 1));
        if(isset($result[0])){
            $item = $result[0];
            $this->build($item);
            return $item;
        }
        else {
            return null;
        }
    }

    public function build(&$item){
        $item['thumbnail_url'] = URL::absolute('/public/image/'.$item['thumbnail_path']);
        $item['image_url'] = URL::absolute('/public/image/'.$item['image_path']);
        $item['ios_url'] = URL::absolute('/public/ios/'.$item['ios_path']);
        $item['android_url'] = URL::absolute('/public/android/'.$item['android_path']);
        $item['video_url'] = URL::absolute('/public/video/'.$item['video_path']);
    }
}