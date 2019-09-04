<?php

namespace WPSelenium\Helpers\WordPress\Provision;

use WPSelenium\Helpers\ProvisionInterface;
use WPSelenium\WPSeleniumConfig;
use WPSelenium\Utilities\Svn;
use WPSelenium\Utilities\Logger;

class ProvisionWordPressTestLib implements ProvisionInterface{

    public static function Provision(){
        (new self());
    }

    public function __construct()
    {
//        if (!Svn::CheckIfInstalled()){
//            Logger::WARN("Svn doesnt seem to be installed. This mean we can not download the WordPress Test Library which add extra functionality. You should still be able to run you though");
//            return;
//        }
    }
}