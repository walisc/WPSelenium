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

$composerFile = sprintf("%s%scomposer.json", __DIR__ , DIRECTORY_SEPARATOR);

function get_test_project_location(){

    global $composerFile;
    if (file_exists($composerFile)) {
        $composerJSONFile = json_decode(file_get_contents($composerFile, true), true);

        if (array_key_exists('repositories', $composerJSONFile)) {
            return $composerJSONFile['repositories'][0]['url'];
        }
    }
    return null;
}

function check_wpselenium_can_load(){
    $testProjectLoc = get_test_project_location();
    if ($testProjectLoc != null) {
        include_once sprintf("%s%s%s%s%s", $testProjectLoc, DIRECTORY_SEPARATOR, "vendor", DIRECTORY_SEPARATOR, "autoload.php");
        if (class_exists("WPSelenium\Helpers\WordPress\WPSupport")) {
            return true;
        }
    }
    return false;
}

function wpselenium_testing_request(){

    echo json_encode([
        "pluginPathDir" => __DIR__,
        "WPSeleniumLinked" =>  check_wpselenium_can_load()
    ]);

}



if (check_wpselenium_can_load())
{
    \WPSelenium\Helpers\WordPress\WPSupport::RunBeforeTestsFunctions();
}

