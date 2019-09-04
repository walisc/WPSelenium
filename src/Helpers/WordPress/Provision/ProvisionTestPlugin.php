<?php

namespace WPSelenium\Helpers\WordPress\Provision;

use WPSelenium\Helpers\ProvisionInterface;
use WPSelenium\Utilities\Logger;
use WPSelenium\WPSeleniumConfig;
use WPSelenium\Utilities\Requests;
use WPSelenium\Utilities\Utilities;

class ProvisionTestPlugin implements ProvisionInterface {

    private $sitePath;
    private $siteUrl;
    private $wpSeleniumPluginPath;
    private $wpSeleniumPluginInstallPath;

    public static function Provision(){
        (new self());
    }
    function __construct()
    {
        
        $ds = DIRECTORY_SEPARATOR;
        $this->sitePath = WPSeleniumConfig::Get()->GetSitePath();
        $this->siteUrl = WPSeleniumConfig::Get()->GetSiteUrl();
        $this->wpSeleniumPathDir = WPSeleniumConfig::Get()->GetWPSeleniumWorkingDir();
        $this->wpSeleniumPluginPath = sprintf("%s{$ds}TestPlugin", __DIR__);
        $this->wpSeleniumPluginInstallPath = "{$this->sitePath}{$ds}wp-content{$ds}plugins{$ds}WPSeleniumTestPlugin";

        $this->CopyTestPlugin();
        $this->LinkToPlugin();

    }

    function LinkToPlugin(){
        $result = Requests::Post(sprintf('%s/wp-admin/admin-ajax.php', $this->siteUrl),  "action=wpselenium_testing_request");
        $resultDic = json_decode(substr($result,0,strlen($result)-1), true);

        if (!$resultDic["WPSeleniumLinked"]){
            if ($resultDic["pluginPathDir"]){
                Logger::INFO("WPSelenium not linked to your site. Linking now");
                $testPluginPath =sprintf("%s%s",$resultDic["pluginPathDir"], DIRECTORY_SEPARATOR);
                $testPluginDetails = json_decode(file_get_contents($testPluginPath. "composer.sample.json"), true);
                $testPluginDetails["repositories"][0]["url"] = realpath($this->wpSeleniumPathDir);
    
                fwrite(fopen($testPluginPath."composer.json", "w") , json_encode($testPluginDetails));
                echo "\n";
                Logger::INFO("WPSelenium linked to site successfully");
            }
            else{
                Logger::ERROR("Can't seem to be able to communicate with the WPSelenium Test Plugin. Please make sure your site is running and you have added and activated the WPSelenium Test Plugin.", true);
            }
        }
    
    }

    function CopyTestPlugin(){        
        if (file_exists($this->wpSeleniumPluginInstallPath)){
            return;
        }
        Logger::INFO("Copying WPSelenium Test Plugin to your site");
        Utilities::RecursiveCopy($this->wpSeleniumPluginPath, $this->wpSeleniumPluginInstallPath );

        Logger::INFO("We are getting there:) WPSelenium has just installed the WPSelenium Test Plugin to your site. This is needed to run your selenium tests succesfully. 
        For security reasons, the plugin has not been activated. Please login to your WordPress site and activate the WPSelenium Test Plugin. Once you have activated it re-run wpselenium.", true);
        
    }
}
