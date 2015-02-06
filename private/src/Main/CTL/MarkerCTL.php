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
        $v = new HtmlView('/edit');
        $id = $this->reqInfo->urlParam('id');
        $item = $this->_get($id);
        $v->setParams(array(
            'old'=> $item,
        ));
        return $v;
    }

    /**
     * @POST
     * @uri /edit/[:id]
     */
    public function postEdit(){
        if($_POST["type"] == "image"){
            return $this->_editImage();
        }
        else if($_POST["type"] == "video"){
            return $this->_editVideo();
        }
        else if($_POST["type"] == "model"){
            return $this->_editModel();
        }
    }

    /**
     * @GET
     * @uri /delete/[:id]
     */
    public function delete(){
        $db = MedooFactory::getInstance();
        $id = $this->reqInfo->urlParam('id');
        $old = $this->_get($id);
        $db->delete('marker', array('id'=> $id));

        if(!is_null($old)){
            @unlink("public/image/".$old["thumbnail_path"]);
            @unlink("public/image/".$old["image_path"]);
            @unlink("public/ios/".$old["ios_path"]);
            @unlink("public/android/".$old["android_path"]);
            @unlink("public/video/".$old["video_path"]);
        }

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
        $item['image_url'] = URL::absolute('/public/image/'.$item['image_path']);
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
        $insert = ArrayHelper::filterKey(array("name", "type", "click_url"), $params);
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
            $this->errorRedirect(print_r($db->error(), true), $back_url);
        }

        return new RedirectView(URL::absolute("/marker"));
    }

    public function _editImage(){
        $id = $this->reqInfo->urlParam("id");
        $old = $this->_get($id);
        $params = $this->reqInfo->params();

        $back_url = URL::absolute("/marker/edit/".$id."?type=image");
        $next_url = URL::absolute("/marker");

        $db = MedooFactory::getInstance();

        $update = ArrayHelper::filterKey(array("name", "type", "click_url"), $params);
        $update['type'] = "image";
        $update['version'] = 1;
        $update['android_path'] = "";
        $update['ios_path'] = "";
        $update['video_path'] = "";
        $update['version'] = $old["version"] + 1;

        // check duplicate name
        if($old["name"] != $params["name"] && $this->isDuplicateName($params['name'])){
            $this->errorRedirect("duplicate name", $back_url);
        }

        $image = $this->reqInfo->file("image");
        if(!is_null($image) && is_uploaded_file($image["tmp_name"])){
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

            $fileName = uniqid("image").'.'.$ext;
            $des = 'public/image/'.$fileName;
            move_uploaded_file($image['tmp_name'], $des);
            $update['image_path'] = $fileName;
            $update['thumbnail_path'] = $fileName;

            Event::add("when_update_image_fail", function() use($des) {
                @unlink($des);
            });
        }

        $success = $db->update("marker", $update, array("id"=> $id));
        if(!$success){
            Event::trigger("when_update_image_fail");
            $this->errorRedirect(print_r($db->error(), true), $back_url);
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
            $this->errorRedirect(print_r($db->error(), true), $back_url);
        }

        return new RedirectView(URL::absolute("/marker"));
    }

    public function _editVideo(){
        $id = $this->reqInfo->urlParam("id");
        $old = $this->_get($id);
        $params = $this->reqInfo->params();

        $back_url = URL::absolute("/marker/edit/".$id."?type=video");
        $next_url = URL::absolute("/marker");

        $update = ArrayHelper::filterKey(array("name", "type"), $params);
        $update['type'] = "video";
        $update['android_path'] = "";
        $update['ios_path'] = "";
        $update['image_path'] = "";
        $update['click_url'] = "";
        $update['version'] = $old["version"] + 1;

        $db = MedooFactory::getInstance();

        // check duplicate name
        if($old["name"] != $params["name"] && $this->isDuplicateName($params['name'])){
            $this->errorRedirect("duplicate name", $back_url);
        }

        $thumbnail = $this->reqInfo->file("thumbnail");
        if(!is_null($thumbnail) && is_uploaded_file($thumbnail["tmp_name"])){
            // check thumbnail
            $thumbnail = $this->reqInfo->file("thumbnail");
            if(!is_uploaded_file($thumbnail["tmp_name"])){
                $this->errorRedirect("required thumbnail", $back_url);
            }

            // check type thumbnail
            $ext = $this->_getExt($thumbnail["name"]);
            if(!in_array($ext, array("jpg", "jpeg", "png"))){
                $this->errorRedirect("thumbnail only extension jpg,jpeg,png", $back_url);
            }

            $fileName = uniqid("thumbnail").'.'.$ext;
            $desThumbnail = 'public/image/'.$fileName;
            move_uploaded_file($thumbnail['tmp_name'], $desThumbnail);
            Event::add("when_update_video_fail", function() use($desThumbnail) {
                @unlink($desThumbnail);
            });
            $update['thumbnail_path'] = $fileName;
        }

        $video = $this->reqInfo->file("video");
        if(!is_null($video) && is_uploaded_file($video["tmp_name"])){
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

            $fileName = uniqid("video").'.'.$ext;
            $desVideo = 'public/video/'.$fileName;
            move_uploaded_file($video['tmp_name'], $desVideo);
            Event::add("when_update_video_fail", function() use($desVideo) {
                @unlink($desVideo);
            });
            $update['video_path'] = $fileName;
        }

        $success = $db->update("marker", $update, array("id"=> $id));
        if(!$success){
            Event::trigger("when_update_video_fail");
            $this->errorRedirect(print_r($db->error(), true), $back_url);
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
            $this->errorRedirect($db->error(), $back_url);
        }

        return new RedirectView(URL::absolute("/marker"));
    }

    public function _editModel(){
        $id = $this->reqInfo->urlParam("id");
        $old = $this->_get($id);
        $params = $this->reqInfo->params();

        $back_url = URL::absolute("/marker/edit/".$id."?type=model");
        $next_url = URL::absolute("/marker");

        $update = ArrayHelper::filterKey(array("name", "type"), $params);
        $update['type'] = "model";
        $update['image_path'] = "";
        $update['video_path'] = "";
        $update['click_url'] = "";
        $update['version'] = $old["version"] + 1;

        $db = MedooFactory::getInstance();

        // check duplicate name
        if($old["name"] != $params["name"] && $this->isDuplicateName($params['name'])){
            $this->errorRedirect("duplicate name", $back_url);
        }

        $thumbnail = $this->reqInfo->file("thumbnail");
        if(!is_null($thumbnail) && is_uploaded_file($thumbnail["tmp_name"])){
            // check thumbnail
            $thumbnail = $this->reqInfo->file("thumbnail");
            if(!is_uploaded_file($thumbnail["tmp_name"])){
                $this->errorRedirect("required thumbnail", $back_url);
            }

            // check type thumbnail
            $ext = $this->_getExt($thumbnail["name"]);
            if(!in_array($ext, array("jpg", "jpeg", "png"))){
                $this->errorRedirect("thumbnail only extension jpg,jpeg,png", $back_url);
            }

            $fileName = uniqid("thumbnail").'.'.$ext;
            $desThumbnail = 'public/image/'.$fileName;
            move_uploaded_file($thumbnail['tmp_name'], $desThumbnail);
            Event::add("when_update_model_fail", function() use($desThumbnail) {
                @unlink($desThumbnail);
            });
            $update['thumbnail_path'] = $fileName;
        }

        $ios = $this->reqInfo->file("ios");
        if(!is_null($ios) && is_uploaded_file($ios["tmp_name"])){
            // check ios
            $ios = $this->reqInfo->file("ios");
            if(!is_uploaded_file($ios["tmp_name"])){
                $this->errorRedirect("required ios", $back_url);
            }

            // check type ios
            $ext = $this->_getExt($ios["name"]);
            if(!in_array($ext, array("unity"))){
                $this->errorRedirect("ios only extension unity", $back_url);
            }

            $fileName = uniqid("ios").'.'.$ext;
            $desIos = 'public/ios/'.$fileName;
            move_uploaded_file($ios['tmp_name'], $desIos);
            Event::add("when_update_model_fail", function() use($desIos) {
                @unlink($desIos);
            });
            $update['ios_path'] = $fileName;
        }

        $android = $this->reqInfo->file("android");
        if(!is_null($android) && is_uploaded_file($android["tmp_name"])){
            // check android
            $android = $this->reqInfo->file("android");
            if(!is_uploaded_file($android["tmp_name"])){
                $this->errorRedirect("required android", $back_url);
            }

            // check type android
            $ext = $this->_getExt($android["name"]);
            if(!in_array($ext, array("unity"))){
                $this->errorRedirect("android only extension unity", $back_url);
            }

            $fileName = uniqid("android").'.'.$ext;
            $desAndroid = 'public/android/'.$fileName;
            move_uploaded_file($android['tmp_name'], $desAndroid);
            Event::add("when_update_model_fail", function() use($desAndroid) {
                @unlink($desAndroid);
            });
            $update['android_path'] = $fileName;
        }

        $success = $db->update("marker", $update, array("id"=> $id));
        if(!$success){
            Event::trigger("when_update_video_fail");
            $this->errorRedirect(print_r($db->error(), true), $back_url);
        }

        return new RedirectView(URL::absolute("/marker"));
    }

    public function errorRedirect($message, $url){
        echo $message;
        echo '<meta http-equiv="refresh" content="3; url='.$url.'" />';
        exit();
    }
}