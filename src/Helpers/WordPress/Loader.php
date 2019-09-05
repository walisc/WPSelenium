<?php

namespace WPSelenium\Helpers\WordPress;

use WPSelenium\Helpers\HelperInterface;
use WPSelenium\Helpers\WordPress\Provision\ProvisionTestPlugin;
use WPSelenium\Helpers\WordPress\Provision\ProvisionWordPressTestLib;

class Loader implements HelperInterface{

    function SetEnvVariables(){
        putenv('WPSELENIUM_WP_TEST_USERNAME=' . Config::Get()->GetWPTestUsername() );
        putenv('WPSELENIUM_WP_TEST_PASSWORD=' .  Config::Get()->GetWPTestPassword());
    }

    function GetProvisionClasses()
    {
        return [
            ProvisionTestPlugin::class,
            ProvisionWordPressTestLib::class
        ];
    }

    function PhpUnitRunner($phpUnitPath)
    {
        system($phpUnitPath);
    }

    function SetConfig($WPSeleniumConfig){
         new Config($WPSeleniumConfig);
    }
}