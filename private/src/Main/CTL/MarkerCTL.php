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
        $params = $this->reqInfo->params();
        $files = $this->reqInfo->files();
        try {
            $v = new Validator($params);
            $v->rule('required', array('name'));

            if(!$v->validate()){
                throw new ServiceException(null);
            }

            $v = new Validator($files);
            $v->rule('required', array('ios', 'android', 'marker'));

            if(!$v->validate()){
                throw new ServiceException(null);
            }

            if($files['marker']['type'] != 'image/jpeg'){
//                throw new ServiceException(null);
                echo "marker is not jpg/jpeg/png file";
                echo '<meta http-equiv="refresh" content="3; url='.URL::absolute("/marker/add").'" />';
                exit();
            }

            $db = MedooFactory::getInstance();
//            if($db->count('marker', null, null, []) > 0){
//
//            }

            // check is duplicate

            if($this->isDuplicateName($params['name'])){
                echo "duplicate name";
                echo '<meta http-equiv="refresh" content="3; url='.URL::absolute("/marker/add").'" />';
            }

            $insert = array(
                'name'=> $params['name']
            );
            $time = time();

            // marker

            $ext = array_pop(explode('.', $files['marker']['name']));
            $ext = strtolower($ext);

            if(!in_array($ext, array("jpg", "jpeg", "png"))){
                echo "marker is not jpg/jpeg/png file";
                echo '<meta http-equiv="refresh" content="3; url='.URL::absolute("/marker/add").'" />';
                exit();
            }

            $fileName = uniqid("marker").'.'.$ext;
            $des = 'public/marker/'.$fileName;
            move_uploaded_file($files['marker']['tmp_name'], $des);
            $insert['marker_path'] = $fileName;
            $insert['marker_updated_at'] = $time;


            // ios

            $ext = array_pop(explode('.', $files['ios']['name']));
            $ext = strtolower($ext);

//            if(!in_array($ext, array("jpg", "jpeg", "png"))){
//                echo "marker is not jpg/jpeg/png file";
//                echo '<meta http-equiv="refresh" content="3; url='.URL::absolute("/marker/add").'" />';
//                exit();
//            }

            $fileName = uniqid("ios").'.'.$ext;
            $des = 'public/ios/'.$fileName;
            move_uploaded_file($files['ios']['tmp_name'], $des);
            $insert['ios_path'] = $fileName;
            $insert['ios_updated_at'] = $time;

            // android

            $ext = array_pop(explode('.', $files['android']['name']));
            $ext = strtolower($ext);
//
//            if(!in_array($ext, array("jpg", "jpeg", "png"))){
//                echo "marker is not jpg/jpeg/png file";
//                echo '<meta http-equiv="refresh" content="3; url='.URL::absolute("/marker/add").'" />';
//                exit();
//            }

            $fileName = uniqid("android").'.'.$ext;
            $des = 'public/android/'.$fileName;
            move_uploaded_file($files['android']['tmp_name'], $des);
            $insert['android_path'] = $fileName;
            $insert['android_updated_at'] = $time;

            $id = $db->insert('marker', $insert);

            return new RedirectView(URL::absolute('/marker'));
        }
        catch (ServiceException $e){
            return new RedirectView(URL::absolute('/marker'));
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
//                $ext = strtolower($ext);
//
//                if(!in_array($ext, array("jpg", "jpeg", "png"))){
//                    echo "marker is not jpg/jpeg/png file";
//                    echo '<meta http-equiv="refresh" content="3; url='.URL::absolute("/marker/edit/".$id).'" />';
//                    exit();
//                }

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
        $item['marker_url'] = URL::absolute('/public/marker/'.$item['marker_path']);
        $item['ios_url'] = URL::absolute('/public/ios/'.$item['ios_path']);
        $item['android_url'] = URL::absolute('/public/android/'.$item['android_path']);
    }
}