<?php
/**
 * Created by PhpStorm.
 * User: p2
 * Date: 7/11/2557
 * Time: 16:40 น.
 */

namespace Main\View;


class RedirectView extends BaseView {
    protected $redirPath = "";

    public function __construct($redirPath){
        $this->redirPath = $redirPath;
    }

    public function render()
    {
        header("Location: ".$this->redirPath);
    }
}