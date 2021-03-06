<?php

namespace WPSelenium\Helpers\WordPress;

use WPSelenium\Helpers\HelperInterface;
use WPSelenium\Helpers\WordPress\PhpUnitRunner\Runner;
use WPSelenium\Helpers\WordPress\Provision\ProvisionTestPlugin;
use WPSelenium\Helpers\WordPress\Provision\ProvisionWordPressTestLib;

class Loader implements HelperInterface{

    function SetEnvVariables(){
        putenv('WPSELENIUM_WP_SITE_PATH=' .Config::Get()->GetWpSeleniumConfig()->GetSitePath() );
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

    function PhpUnitRunner($phpUnitPath, $options=[])
    {
        (new Runner())->Run($phpUnitPath, $options);
    }

    function SetConfig($WPSeleniumConfig){
         new Config($WPSeleniumConfig);
    }
}