<?php

/*
Plugin Name: WPooW Test plugin
Description: Plugin used to test the WPooW application. Read the README.md for more details
Author: Chido Warambwa
Author URI: http://wpoow.devchid.com
*/

//Disallow someone accessing this file out the WordPress context
defined( 'ABSPATH') or die('Accessing this is disallowed');

add_action( 'wp_ajax_nopriv_wpoow_testing_request', 'wpoow_testing_request' );

function wpoow_testing_request(){

    $wpoow_linked = false;
    if (file_exists( sprintf("%s%s%s%s%s", __DIR__, DIRECTORY_SEPARATOR, "vendor", DIRECTORY_SEPARATOR, "autoload.php")))
    {
        include sprintf("%s%s%s%s%s", __DIR__, DIRECTORY_SEPARATOR, "vendor", DIRECTORY_SEPARATOR, "autoload.php");
        if (class_exists("WPooW\WPooW")){
            $wpoow_linked = true;
        }
    }

    
    $plugin_details = json_decode(file_get_contents( sprintf("%s%s%s", __DIR__, DIRECTORY_SEPARATOR, "composer.json")), true);
    
    echo json_encode([
        "pluginPathDir" => __DIR__,
        "WPooWLinked" => $wpoow_linked 
    ]);

}

if (file_exists( sprintf("%s%s%s%s%s", __DIR__, DIRECTORY_SEPARATOR, "vendor", DIRECTORY_SEPARATOR, "autoload.php")))
{
    include sprintf("%s%s%s%s%s", __DIR__, DIRECTORY_SEPARATOR, "vendor", DIRECTORY_SEPARATOR, "autoload.php");
    if (class_exists("WPooW\WPooW")){
        include_once 'vendor/autoload.php';
        \WPooWTests\TestCases\ElementCreator::CreateTestElements();
    }
}

