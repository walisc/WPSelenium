<?php

namespace WPSelenium\Helpers\WordPress;

use WPSelenium\Helpers\HelperInterface;
use WPSelenium\Helpers\WordPress\Provision\ProvisionTestPlugin;
use WPSelenium\Helpers\WordPress\Provision\ProvisionWordPressTestLib;
use WPSelenium\Utilities\PhpUnit\Runner;

class Loader implements HelperInterface{

    function GetProvisionClasses()
    {
        return [
            ProvisionTestPlugin::class,
            ProvisionWordPressTestLib::class
        ];
    }

    function PhpRunner(Runner $PhpRunner)
    {
        // TODO: Implement PhpRunner() method.
    }

    function SetConfig($WPSeleniumConfig){
         new Config($WPSeleniumConfig);
    }
}