<?php


namespace WPSelenium\Helpers;

interface HelperInterface{
    // Lifecycle SetConfig -> GetProvisionClasses ->  SetEnvVariables -> PhpUnitRunner
    function SetConfig($WPSeleniumConfig);
    function GetProvisionClasses();
    function SetEnvVariables();
    function PhpUnitRunner($phpUnitPath, $options=[]);
}