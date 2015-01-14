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
        $v->setParams(['items'=> $items]);
        return $v;
    }

    /**
     * @GET
     * @uri /add
     */
    public function getAdd(){
        $v = new HtmlView('/add');
        $v->setParams([
            'action'=> URL::absolute('/marker/add'),
            'title'=> 'Add Maker'
        ]);
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
            $v->rule('required', ['name']);

            if(!$v->validate()){
                throw new ServiceException(null);
            }

            $v = new Validator($files);
            $v->rule('required', ['ios', 'android', 'marker']);

            if(!$v->validate()){
                throw new ServiceException(null);
            }

            if($files['marker']['type'] != 'image/jpeg'){
                throw new ServiceException(null);
            }

            $db = MedooFactory::getInstance();
            if($db->count('marker', null, null, []) > 0){

            }

            $insert = [
                'name'=> $params['name']
            ];
            $time = time();

            $fileName = uniqid("marker").'.jpeg';
            $des = 'public/marker/'.$fileName;
            move_uploaded_file($files['marker']['tmp_name'], $des);
            $insert['marker_path'] = $fileName;
            $insert['marker_updated_at'] = $time;

            $fileName = uniqid("ios").'.jpeg';
            $des = 'public/ios/'.$fileName;
            move_uploaded_file($files['ios']['tmp_name'], $des);
            $insert['ios_path'] = $fileName;
            $insert['ios_updated_at'] = $time;

            $fileName = uniqid("android").'.jpeg';
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
        $v->setParams([
            'action'=> URL::absolute('/marker/edit/'.$id),
            'title'=> 'Edit Maker',
            'data'=> $item
        ]);
        return $v;
    }

    /**
     * @POST
     * @uri /edit/[:id]
     */
    public function postEdit(){
        $params = $this->reqInfo->params();
        $files = $this->reqInfo->files();
        $remove_file = [];


        try {
            $id = $this->reqInfo->urlParam('id');
            $time = time();
            $update = [];

            $old = $this->_get($id);

            if(isset($params['name'])){
                $update['name'] = $params['name'];
            }

            if(isset($files['marker'])){
                $ext = array_pop(explode('.', $files['marker']['name']));
                $fileName = uniqid("marker").'.'.$ext;
                $des = 'public/marker/'.$fileName;
                move_uploaded_file($files['marker']['tmp_name'], $des);
                $update['marker_path'] = $fileName;
                $update['marker_updated_at'] = $time;
                $remove_file[] = 'public/marker/'.$old['marker_path'];
            }

            if(isset($files['ios'])){
                $ext = array_pop(explode('.', $files['ios']['name']));
                $fileName = uniqid("ios").'.'.$ext;;
                $des = 'public/ios/'.$fileName;
                move_uploaded_file($files['ios']['tmp_name'], $des);
                $update['ios_path'] = $fileName;
                $update['ios_updated_at'] = $time;
                $remove_file[] = 'public/ios/'.$old['ios_path'];
            }

            if(isset($files['android'])){
                $ext = array_pop(explode('.', $files['android']['name']));
                $fileName = uniqid("android").'.'.$ext;;
                $des = 'public/android/'.$fileName;
                move_uploaded_file($files['android']['tmp_name'], $des);
                $update['android_path'] = $fileName;
                $update['android_updated_at'] = $time;
                $remove_file[] = 'public/android/'.$old['android_path'];
            }

            $db = MedooFactory::getInstance();
            $id = $db->update('marker', $update, ['id'=> $id]);

            return new RedirectView(URL::absolute('/marker'));
        }
        catch (ServiceException $e){
            return new RedirectView(URL::absolute('/marker'));
        }
        finally {
            foreach($remove_file as $value){
                unlink(@$value);
            }
        }
    }

    /**
     * @GET
     * @uri /delete/[:id]
     */
    public function delete(){
        $db = MedooFactory::getInstance();
        $db->delete('marker', ['id'=> $this->reqInfo->urlParam('id')]);

        return new RedirectView(URL::absolute('/marker'));
    }

    public function _get($id){
        // get ebook data
        $masterDB = MedooFactory::getInstance();
        $result = $masterDB->select('marker', '*', ['id'=> $id, "LIMIT"=> 1]);
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
        $item['marker_url'] = URL::absolute('/public/marker/'.$item['marker_path']);
        $item['ios_url'] = URL::absolute('/public/ios/'.$item['ios_path']);
        $item['android_url'] = URL::absolute('/public/android/'.$item['android_path']);
    }
}