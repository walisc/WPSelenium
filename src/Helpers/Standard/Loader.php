<?php

namespace WPSelenium\Helpers\Standard;

use WPSelenium\Helpers\HelperInterface;

class Loader implements HelperInterface{

    function GetProvisionClasses()
    {
        return [];
    }

    function SetEnvVariables()
    {
        // TODO: Implement SetEnvVariables() method.
    }

    function SetConfig($WPSeleniumConfig)
    {
        // TODO: Implement SetConfig() method.
    }

    function PhpUnitRunner($phpUnitPath, $options = [])
    {
        system($phpUnitPath);
    }
}