<?php
/**
 * Created by PhpStorm.
 * User: NUIZ
 * Date: 14/1/2558
 * Time: 14:39
 */

namespace Main\CTL;
use Main\DB\Medoo\MedooFactory;
use Main\Event\Event;
use Main\Exception\Service\ServiceException;
use Main\Helper\ArrayHelper;
use Main\Helper\ImageHelper;
use Main\Helper\URL;
use Main\View\HtmlView;
use Main\View\RedirectView;
use Valitron\Validator;

/**
 * @Restful
 * @uri /marker
 */
class MarkerCTL extends BaseCTL {
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
        $v->setParams(array('items'=> $items));
        return $v;
    }

    /**
     * @GET
     * @uri /add
     */
    public function getAdd(){
        $v = new HtmlView('/add');
        $v->setParams(array(
            'action'=> URL::absolute('/marker/add'),
            'title'=> 'Add'
        ));
        return $v;
    }

    /**
     * @GET
     * @uri /form
     */
    public function form(){
        $v = new HtmlView('/form/model');
        $v->setParams(array(
            'action'=> URL::absolute('/form/model'),
        ));
        return $v;
    }

    /**
     * @POST
     * @uri /add
     */
    public function postAdd(){
        if($_POST["type"] == "image"){
            return $this->_addImage();
        }
        else if($_POST["type"] == "video"){
            return $this->_addVideo();
        }
        else if($_POST["type"] == "model"){
            return $this->_addModel();
        }
    }

    /**
     * @GET
     * @uri /edit/[:id]
     */
    public function getEdit(){
        $v = new HtmlView('/add');
        $id = $this->reqInfo->urlParam('id');
        $item = $this->_get($id);
        $v->setParams(array(
            'action'=> URL::absolute('/marker/edit/'.$id),
            'title'=> 'Edit Maker',
            'data'=> $item
        ));
        return $v;
    }

    /**
     * @POST
     * @uri /edit/[:id]
     */
    public function postEdit(){
        $params = $this->reqInfo->params();
        $files = $this->reqInfo->files();
        $remove_file = array();

        // check file .unity

        try {
            $id = $this->reqInfo->urlParam('id');
            $time = time();
            $update = array();

            $old = $this->_get($id);

            if(isset($params['name'])){
                if($this->isDuplicateName($params['name']) && $params['name'] != $old['name']){
                    echo "duplicate name";
                    echo '<meta http-equiv="refresh" content="3; url='.URL::absolute("/marker/edit/".$id).'" />';
                    exit();
                }
                $update['name'] = $params['name'];
            }

            if(isset($files['marker']) && is_uploaded_file($files['marker']['tmp_name'])){
                $ext = array_pop(explode('.', $files['marker']['name']));
                $ext = strtolower($ext);

                if(!in_array($ext, array("jpg", "jpeg", "png"))){
                    echo "marker is not jpg/jpeg/png file";
                    echo '<meta http-equiv="refresh" content="3; url='.URL::absolute("/marker/edit/".$id).'" />';
                    exit();
                }

                $fileName = uniqid("marker").'.'.$ext;
                $des = 'public/marker/'.$fileName;
                move_uploaded_file($files['marker']['tmp_name'], $des);
                $update['marker_path'] = $fileName;
                $update['marker_updated_at'] = $time;
                $remove_file[] = 'public/marker/'.$old['marker_path'];
            }

            if(isset($files['ios']) && is_uploaded_file($files['ios']['tmp_name'])){
                $ext = array_pop(explode('.', $files['ios']['name']));
                $ext = strtolower($ext);

//                if(!in_array($ext, array("jpg", "jpeg", "png"))){
//                    echo "marker is not jpg/jpeg/png file";
//                    echo '<meta http-equiv="refresh" content="3; url='.URL::absolute("/marker/edit/".$id).'" />';
//                    exit();
//                }

                $fileName = uniqid("ios").'.'.$ext;
                $des = 'public/ios/'.$fileName;
                move_uploaded_file($files['ios']['tmp_name'], $des);
                $update['ios_path'] = $fileName;
                $update['ios_updated_at'] = $time;
                $remove_file[] = 'public/ios/'.$old['ios_path'];
            }

            if(isset($files['android']) && is_uploaded_file($files['android']['tmp_name'])){
                $ext = array_pop(explode('.', $files['android']['name']));

                $fileName = uniqid("android").'.'.$ext;
                $des = 'public/android/'.$fileName;
                move_uploaded_file($files['android']['tmp_name'], $des);
                $update['android_path'] = $fileName;
                $update['android_updated_at'] = $time;
                $remove_file[] = 'public/android/'.$old['android_path'];
            }

            $db = MedooFactory::getInstance();
            $id = $db->update('marker', $update, array('id'=> $id));

            foreach($remove_file as $value){
                unlink(@$value);
            }

            return new RedirectView(URL::absolute('/marker'));
        }
        catch (ServiceException $e){
            return new RedirectView(URL::absolute('/marker'));
        }
    }

    /**
     * @GET
     * @uri /delete/[:id]
     */
    public function delete(){
        $db = MedooFactory::getInstance();
        $db->delete('marker', array('id'=> $this->reqInfo->urlParam('id')));

        return new RedirectView(URL::absolute('/marker'));
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

    public function isDuplicateName($name){
        $masterDB = MedooFactory::getInstance();
        return count($masterDB->select('marker', '*', array('name'=> $name))) > 0;
    }

    public function build(&$item){
        $item['thumbnail_url'] = URL::absolute('/public/image/'.$item['thumbnail_path']);
        $item['ios_url'] = URL::absolute('/public/ios/'.$item['ios_path']);
        $item['android_url'] = URL::absolute('/public/android/'.$item['android_path']);
        $item['video_url'] = URL::absolute('/public/video/'.$item['video_path']);
    }

    public function _getExt($fileName){
        return array_pop(explode(".", $fileName));
    }

    public function _addImage(){
        $back_url = URL::absolute("/marker/add?type=image");
        $next_url = URL::absolute("/marker");

        $db = MedooFactory::getInstance();

        // check image
        $image = $this->reqInfo->file("image");
        if(!is_uploaded_file($image["tmp_name"])){
            $this->errorRedirect("required image", $back_url);
        }

        // check type image
        $ext = $this->_getExt($image["name"]);
        if(!in_array($ext, array("jpg", "jpeg", "png"))){
            $this->errorRedirect("image only extension jpg,jpeg,png", $back_url);
        }

        // validate parameter
        $params = $this->reqInfo->params();
        $v = new Validator($params);
        $v->rule("required", array("name", "type"));
        if(!$v->validate()){
            $this->errorRedirect(print_r($v->errors(), true), $back_url);
        }

        // check duplicate name
        if($this->isDuplicateName($params['name'])){
            $this->errorRedirect("duplicate name", $back_url);
        }

        $fileName = uniqid("image").'.'.$ext;
        $des = 'public/image/'.$fileName;
        move_uploaded_file($image['tmp_name'], $des);
        $insert = ArrayHelper::filterKey(array("name", "type"), $params);
        $insert['image_path'] = $fileName;
        $insert['thumbnail_path'] = $fileName;
        $insert['type'] = "image";
        $insert['version'] = 1;

        Event::add("when_add_image_fail", function() use($des) {
            @unlink($des);
        });

        $id = $db->insert("marker", $insert);
        if(!$id){
            Event::trigger("when_add_image_fail");
            $this->errorRedirect(print_r($v->errors(), true), $back_url);
        }

        return new RedirectView(URL::absolute("/marker"));
    }

    public function _addVideo(){
        $back_url = URL::absolute("/marker/add?type=video");
        $next_url = URL::absolute("/marker");

        $db = MedooFactory::getInstance();

        // validate parameter
        $params = $this->reqInfo->params();
        $v = new Validator($params);
        $v->rule("required", array("name", "type"));
        if(!$v->validate()){
            $this->errorRedirect(print_r($v->errors(), true), $back_url);
        }
        $insert = ArrayHelper::filterKey(array("name", "type"), $params);

        // check duplicate name
        if($this->isDuplicateName($params['name'])){
            $this->errorRedirect("duplicate name", $back_url);
        }

        // check image
        $image = $this->reqInfo->file("thumbnail");
        if(!is_uploaded_file($image["tmp_name"])){
            $this->errorRedirect("required thumbnail", $back_url);
        }

        // check type image
        $ext = $this->_getExt($image["name"]);
        if(!in_array($ext, array("jpg", "jpeg", "png"))){
            $this->errorRedirect("image only extension jpg,jpeg,png", $back_url);
        }

        $fileName = uniqid("image").'.'.$ext;
        $desImg = 'public/image/'.$fileName;
        move_uploaded_file($image['tmp_name'], $desImg);
        Event::add("when_add_video_fail", function() use($desImg) {
            @unlink($desImg);
        });
        $insert['thumbnail_path'] = $fileName;

        // check video
        $video = $this->reqInfo->file("video");
        if(!is_uploaded_file($video["tmp_name"])){
            $this->errorRedirect("required video", $back_url);
        }

        // check type video
        $ext = $this->_getExt($video["name"]);
        if(!in_array($ext, array("mp4"))){
            $this->errorRedirect("video only extension mp4", $back_url);
        }

        $fileName = uniqid("image").'.'.$ext;
        $desVideo = 'public/video/'.$fileName;
        move_uploaded_file($video['tmp_name'], $desVideo);
        Event::add("when_add_video_fail", function() use($desVideo) {
            @unlink($desVideo);
        });
        $insert['video_path'] = $fileName;

        $insert['type'] = "video";
        $insert['version'] = 1;

        $id = $db->insert("marker", $insert);
        if(!$id){
            Event::trigger("when_add_video_fail");
            $this->errorRedirect(print_r($v->errors(), true), $back_url);
        }

        return new RedirectView(URL::absolute("/marker"));
    }

    public function _addModel(){
        $back_url = URL::absolute("/marker/add?type=model");
        $next_url = URL::absolute("/marker");

        $db = MedooFactory::getInstance();

        // validate parameter
        $params = $this->reqInfo->params();
        $v = new Validator($params);
        $v->rule("required", array("name", "type"));
        if(!$v->validate()){
            $this->errorRedirect(print_r($v->errors(), true), $back_url);
        }
        $insert = ArrayHelper::filterKey(array("name", "type"), $params);

        // check duplicate name
        if($this->isDuplicateName($params['name'])){
            $this->errorRedirect("duplicate name", $back_url);
        }

        // check image
        $image = $this->reqInfo->file("thumbnail");
        if(!is_uploaded_file($image["tmp_name"])){
            $this->errorRedirect("required thumbnail", $back_url);
        }

        // check type image
        $ext = $this->_getExt($image["name"]);
        if(!in_array($ext, array("jpg", "jpeg", "png"))){
            $this->errorRedirect("image only extension jpg,jpeg,png", $back_url);
        }

        $fileName = uniqid("image").'.'.$ext;
        $desImg = 'public/image/'.$fileName;
        move_uploaded_file($image['tmp_name'], $desImg);
        Event::add("when_add_model_fail", function() use($desImg) {
            @unlink($desImg);
        });
        $insert['thumbnail_path'] = $fileName;

        // check ios
        $ios = $this->reqInfo->file("ios");
        if(!is_uploaded_file($ios["tmp_name"])){
            $this->errorRedirect("required ios", $back_url);
        }

        // check type ios
        $ext = $this->_getExt($ios["name"]);
        if(!in_array($ext, array("unity"))){
            $this->errorRedirect("ios only extension .unity", $back_url);
        }

        $fileName = uniqid("ios").'.'.$ext;
        $desIOS = 'public/ios/'.$fileName;
        move_uploaded_file($ios['tmp_name'], $desIOS);
        Event::add("when_add_model_fail", function() use($desIOS) {
            @unlink($desIOS);
        });
        $insert['ios_path'] = $fileName;

        // check android
        $android = $this->reqInfo->file("android");
        if(!is_uploaded_file($android["tmp_name"])){
            $this->errorRedirect("required android", $back_url);
        }

        // check type android
        $ext = $this->_getExt($android["name"]);
        if(!in_array($ext, array("unity"))){
            $this->errorRedirect("android only extension .unity", $back_url);
        }

        $fileName = uniqid("android").'.'.$ext;
        $desAndroid = 'public/android/'.$fileName;
        move_uploaded_file($android['tmp_name'], $desAndroid);
        Event::add("when_add_model_fail", function() use($desAndroid) {
            @unlink($desAndroid);
        });
        $insert['android_path'] = $fileName;

        $insert['type'] = "model";
        $insert['version'] = 1;

        $id = $db->insert("marker", $insert);
        if(!$id){
            Event::trigger("when_add_model_fail");
            $this->errorRedirect(print_r($v->errors(), true), $back_url);
        }

        return new RedirectView(URL::absolute("/marker"));
    }

    public function errorRedirect($message, $url){
        echo $message;
        echo '<meta http-equiv="refresh" content="3; url='.$url.'" />';
        exit();
    }
}