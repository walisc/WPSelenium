<?php

/*
Plugin Name: WPSelenium Test plugin
Description: Plugin used in conjunction with the wpselenium library to run Selenium tests on WordPress sites.
Author: Chido Warambwa
Author URI: http://devchid.com
*/

//Disallow someone accessing this file out the WordPress context
defined( 'ABSPATH') or die('Accessing this is disallowed');

add_action( 'wp_ajax_nopriv_wpselenium_testing_request', 'wpselenium_testing_request' );

function wpselenium_testing_request(){

    $wpselenium_linked = false;
    if (file_exists( sprintf("%s%s%s%s%s", __DIR__, DIRECTORY_SEPARATOR, "vendor", DIRECTORY_SEPARATOR, "autoload.php")))
    {
        include sprintf("%s%s%s%s%s", __DIR__, DIRECTORY_SEPARATOR, "vendor", DIRECTORY_SEPARATOR, "autoload.php");
        if (class_exists("WPSelenium\PrepareTests")){
            $wpselenium_linked = true;
        }
    }
    
    echo json_encode([
        "pluginPathDir" => __DIR__,
        "WPSeleniumLinked" => $wpselenium_linked 
    ]);

}

if (file_exists( sprintf("%s%s%s%s%s", __DIR__, DIRECTORY_SEPARATOR, "vendor", DIRECTORY_SEPARATOR, "autoload.php")))
{
    include sprintf("%s%s%s%s%s", __DIR__, DIRECTORY_SEPARATOR, "vendor", DIRECTORY_SEPARATOR, "autoload.php");
    if (class_exists("WPSelenium\PrepareTests")){
        include_once 'vendor/autoload.php';
        \WPSelenium\PrepareTests::CreateTestElements();
    }
}

