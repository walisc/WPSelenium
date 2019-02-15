<?php

namespace WPSelenium\Provision;
use WPSelenium\Utilities\Logger;
use WPSelenium\WPSeleniumConfig;
use WPSelenium\Utilities\Requests;

class ProvisionTestPlugin{

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
        $this->wpSeleniumPluginPath = sprintf("%s{$ds}TestPlugin", __DIR__);
        $this->wpSeleniumPluginInstallPath = "{$this->sitePath}{$ds}wp-content{$ds}plugins{$ds}WPSeleniumTestPlugin";

        $this->CopyTestPlugin();
        $this->LinkToPlugin();

    }

    function LinkToPlugin(){
        $result = Requests::Post(sprintf('http://%s/wp-admin/admin-ajax.php', $this->siteUrl),  "action=wpoow_testing_request");
        $resultDic = json_decode(substr($result,0,strlen($result)-1), true);
    
        if (!$resultDic["WPooWLinked"]){
            if ($resultDic["pluginPathDir"]){
                Logger::INFO("WPooW project not linked to WPooW test Plugin. Linking now");
                $testPluginPath =sprintf("%s%s",$resultDic["pluginPathDir"], DIRECTORY_SEPARATOR);
                $testPluginDetails = json_decode(file_get_contents($testPluginPath. "composer.sample.json"), true);
                $testPluginDetails["repositories"][0]["url"] = realpath(sprintf("%s%s%s",__DIR__, DIRECTORY_SEPARATOR, "../"));
    
                fwrite(fopen($testPluginPath."composer.json", "w") , json_encode($testPluginDetails));
                echo "\n";
                exec("composer install -d ". $resultDic["pluginPathDir"]);
                Logger::INFO("WPooW project linked successfully");
            }
            else{
                Logger::ERROR("Cant seem to be able to communicate WPooW Test Plugin. Make sure your site is running and you have add and activated it the WPooW test Plugin.");
                Quit();
            }
        }
    
    }

    function CopyTestPlugin(){        
        if (file_exists($this->wpSeleniumPluginInstallPath)){
            return;
        }
        Logger::INFO("Copying WPSelenium Test Plugin to your site");
        recurse_copy($this->wpSeleniumPluginPath, $this->wpSeleniumPluginInstallPath );

        Logger::INFO("We are getting there:) WPSelenium has just installed the WPSelenium Test Plugin to your site. This is needed to run your selenium test succefully. 
                      The plugin has not been activate however, for security reasons. Please login to your WordPress site and 
                      activate the WPSelenium Test Plugin. Once you have activated it re-run wpselenium.");
        Quit();
    }
}

// From http://php.net/manual/en/function.copy.php#91010
function recurse_copy($src,$dst) { 
    $dir = opendir($src); 
    @mkdir($dst); 
    while(false !== ( $file = readdir($dir)) ) { 
        if (( $file != '.' ) && ( $file != '..' )) { 
            if ( is_dir($src . '/' . $file) ) { 
                recurse_copy($src . '/' . $file,$dst . '/' . $file); 
            } 
            else { 
                copy($src . '/' . $file,$dst . '/' . $file); 
            } 
        } 
    } 
    closedir($dir); 
} 