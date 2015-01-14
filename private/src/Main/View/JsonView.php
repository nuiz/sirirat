<?php
/**
 * Created by PhpStorm.
 * User: p2
 * Date: 7/11/2557
 * Time: 13:54 น.
 */

namespace Main\View;


class JsonView extends BaseView {
    protected $val = null, $options = [];
    public function __construct($val, $options = [])
    {
        $default = [
            'connection_close'=> true
        ];
        $this->options = array_merge($default, $options);
        $this->val = $val;
    }

    public function render()
    {
        header("Content-type: application/json");
        if($this->options['connection_close']){
            ob_end_clean();
            header("Connection: close");
            ignore_user_abort(); // optional
            ob_start();

            echo json_encode($this->val);

            $size = ob_get_length();
            header("Content-Length: $size");
            ob_end_flush(); // Strange behaviour, will not work
            flush();            // Unless both are called !
        }
        else {
            echo json_encode($this->val);
        }
    }
}