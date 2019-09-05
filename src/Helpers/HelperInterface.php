<?php


namespace WPSelenium\Helpers;

interface HelperInterface{
    function GetProvisionClasses();
    function PhpUnitRunner($phpUnitPath, $options=[]);
    function SetEnvVariables();
    function SetConfig($WPSeleniumConfig);
}