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
         $setHelperKey = 'standard';

         if (count($helperKey) > 0){
             if (array_key_exists($helperKey[0], self::$helpers)) {
                 $setHelperKey = $helperKey[0];
             }
             else{
                 Logger::WARN('Could not find the helper class %s. Using the default instead', $helperKey[0] );
             }
         }

         $helper = self::$helpers[$setHelperKey];

         if (class_exists($helper)){
             return new $helper();
         }
         else{
             //TODO: Auto install
             Logger::ERROR(sprintf('Could not find the helper class %s. Please make sure it is installed', $helper), true);
         }
     }
}