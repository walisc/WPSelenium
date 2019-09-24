<?php
namespace WPSelenium\Helpers\WordPress;

use WPSelenium\Utilities\CONSTS;
use WPSelenium\WPSeleniumConfig;
use PHPUnit\Util\Test;


class WPSupport {
    public static function RunBeforeTestsFunctions(){

        $currentTestPath = sprintf('%s%s%s', WPSeleniumConfig::GetTempDirectory(), DIRECTORY_SEPARATOR, 'wp_selenium_current_test_file');
        if (file_exists($currentTestPath))
        {
            $myfile = fopen($currentTestPath, 'r');
            $current_wp_selenium_test_dic =  explode(';',fgets($myfile));

            $current_wp_selenium_test_path = $current_wp_selenium_test_dic[0];
            $current_wp_selenium_test_classname = $current_wp_selenium_test_dic[1];
            $current_wp_selenium_test_name = $current_wp_selenium_test_dic[2];


            if (file_exists($current_wp_selenium_test_path))
            {
                include $current_wp_selenium_test_path;

                if (class_exists($current_wp_selenium_test_classname))
                {

                    //TODO: Docment of erro dont use test on WP_Before method
                    $MethodAnnotations = Test::parseTestMethodAnnotations($current_wp_selenium_test_classname, $current_wp_selenium_test_name)['method'];
                    if (array_key_exists(CONSTS::ANNOTATION_WP_BEFORE_RUN, $MethodAnnotations)){
                        if (method_exists($current_wp_selenium_test_classname,$MethodAnnotations[CONSTS::ANNOTATION_WP_BEFORE_RUN][0] )){
                            $current_wp_selenium_test_classname::{$MethodAnnotations[CONSTS::ANNOTATION_WP_BEFORE_RUN][0]}();
                        }

                    }
                }

            }

            fclose($myfile);
        }


    }
}
