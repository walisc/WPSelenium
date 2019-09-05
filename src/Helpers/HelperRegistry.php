<?php

namespace WPSelenium\Helpers;

use WPSelenium\Utilities\Logger;

class HelperRegistry{

     private static $helpers = [
         'wordpress' => 'WPSelenium\Helpers\WordPress\Loader',
         'standard' => 'WPSelenium\Helpers\Standard\Loader'
     ];

     static function GetHelper($helperKey)
     {
         if (count($helperKey) > 0 && array_key_exists($helperKey[0], self::$helpers)){
             $helper = self::$helpers[$helperKey[0]];
         }
         else{
             Logger::WARN('Could not find the helper class %s. Using the default instead');
             $helper = self::$helpers['standard'];
         }

         if (class_exists($helper)){
             return new $helper();
         }
         else{
             //TODO: Auto install
             Logger::ERROR(sprintf('Could not find the helper class %s. Please make sure it is installed', $helper), true);
         }
     }
}