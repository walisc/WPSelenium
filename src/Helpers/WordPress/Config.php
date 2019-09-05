<?php

namespace WPSelenium\Helpers\WordPress;

use WPSelenium\Helpers\ConfigInterface;
use WPSelenium\Utilities\Logger;

class Config implements ConfigInterface {

    private static $instance;
    private $wpSeleniumConfig;

    function __construct($wpSeleniumConfig)
    {
        $this->wpSeleniumConfig = $wpSeleniumConfig;
        self::$instance = $this;
    }

    /**
     * @return Config
     */
    static function Get(){
        if (self::$instance == null){
            Logger::ERROR("Hmm...Thats strange. Are you tring to access the  config object without configuring it first", true);
        }
        return self::$instance ;
    }

    public function GetWPTestUsername(){
        return $this->wpSeleniumConfig->GetParsedConfig()->wpTestUsername;
    }

    public function GetWPTestPassword(){
        return $this->wpSeleniumConfig->GetParsedConfig()->wpTestPassword;
    }

}