<?php

namespace Hcode\Page;

use Rain\Tpl;

class Page
{
    private $tpl;
    private $options = [];
    private $defaults = [
        "data" => [],
        "header" => true,
        "footer" => true
    ];

    public function __construct($opt = array(), $tpl_dir = "/views/")
    {
        $this->options = array_merge($this->defaults, $opt);
        // config
        $config = array(
            "tpl_dir"       => $_SERVER["DOCUMENT_ROOT"] . $tpl_dir,
            "cache_dir"     => $_SERVER["DOCUMENT_ROOT"] . "/views-cache/",
            "debug"         => false // set to false to improve the speed
        );

        Tpl::configure($config);

        $this->tpl = new Tpl();
        $this->setData($this->options["data"]);
        $this->options["header"] === true ? $this->tpl->draw("header") : '';
    }
    
    private function setData($data =  array()){
        foreach ($data as $key => $value) {
            $this->tpl->assign($key, $value);
        }
    }

    public function setTpl($name, $data = array(), $returnHTML = false)
    {
        $this->setData($data);
        
        return $this->tpl->draw($name, $returnHTML);
    }

    public function __destruct()
    {
        $this->options["footer"] === true ? $this->tpl->draw("footer") : '';
    }
}

?>