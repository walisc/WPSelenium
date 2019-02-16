<?php

namespace WPSelenium\Provision;
use WPSelenium\WPSeleniumConfig;
use WPSelenium\Utilities\Logger;
use WPSelenium\Utilities\Requests;

class ProvisionSelenium{

    private $wpSeleniumPathDir;
    private $wpSeleniumBinPath;
    private $seleniumServerDownloadPath; 
    private $seleniumDriverDownloadPath; 
    private $selectedBrowserDriver;

    private $wpSeleniumProvisionConfig;

    public static function Provision(){
        (new self());
    }
    
    function __construct()
    {
        $wpSeleniumConfig = WPSeleniumConfig::Get();
        $this->wpSeleniumPathDir = $wpSeleniumConfig->GetWPSeleniumDir();
        $this->wpSeleniumProvisionConfig = $wpSeleniumConfig->GetWPSeleniumProvisionConfig();
        $this->selectedBrowserDriver = $wpSeleniumConfig->GetBroswerDriver();

        $this->wpSeleniumBinPath = sprintf("%s%s%s", $this->wpSeleniumPathDir, DIRECTORY_SEPARATOR, "bin");

        $this->seleniumServerDownloadPath = sprintf("%s%s%s", $this->wpSeleniumBinPath , DIRECTORY_SEPARATOR, "seleniumServer.jar");
        $this->seleniumDriverDownloadPath = sprintf("%s%s%s", $this->wpSeleniumBinPath , DIRECTORY_SEPARATOR, sprintf("%sDriverCompressed",  $this->selectedBrowserDriver ));

        

        if (!file_exists($this->wpSeleniumBinPath))
        {
            mkdir($this->wpSeleniumBinPath);
        }
        $this->DownloadSelenium();
        $this->DownloadSeleniumDrivers();
    }

    function DownloadSelenium(){
        if (!file_exists($this->seleniumServerDownloadPath )){
            Logger::INFO("Selenium Server is not installed. Installing now");
            Requests::GetFile($this->wpSeleniumProvisionConfig->GetSeleniumDownloadUrl(), fopen($this->seleniumServerDownloadPath, "w+"));
        }
    }

    function DownloadSeleniumDrivers(){
        if (!file_exists($this->seleniumDriverDownloadPath)){
            Logger::INFO("Installing Selenium {$this->selectedBrowserDriver} drivers");
            
            Requests::GetFile($this->wpSeleniumProvisionConfig->GetDriverDownloadUrl($this->selectedBrowserDriver), fopen($this->seleniumDriverDownloadPath, "w+") );
            
            $zip = new \ZipArchive;
            $res = $zip->open($this->seleniumDriverDownloadPath);
            $zip->extractTo($this->wpSeleniumBinPath);
            $zip->close();
    
            
        }
    }
}



function InstallSeleniumDependencies(){

    global $project_dir;
    global $phpunitloc;
    global $binPath;
    global $driverPath;
    global $seleniumPath;

    $seleniumUrl = "http://selenium-release.storage.googleapis.com/3.9/selenium-server-standalone-3.9.1.jar";
    $driverUrl = "https://chromedriver.storage.googleapis.com/2.45/chromedriver_%s.zip";

    if (!file_exists($binPath))
    {
        mkdir($binPath);
    }

    if (!file_exists($seleniumPath)){
        Logger::INFO("Selenium Server is not installed. Installing now");
        ProcessFileRequest($seleniumUrl, fopen($seleniumPath, "w+"));
    }


    if (!file_exists($driverPath)){
        Logger::INFO("Installing Selenium Chrome drivers");

        $dirveType = "";
        if (stristr(PHP_OS, 'DAR')){
            ProcessFileRequest(sprintf($driverUrl,"mac64"),fopen($driverPath, "w+"));
        }else if (stristr(PHP_OS, 'WIN')){
            ProcessFileRequest(sprintf($driverUrl,"win32"), fopen($driverPath, "w+"));
        }else if(stristr(PHP_OS, 'LINUX')){
            ProcessFileRequest(sprintf($driverUrl,"linux64"),fopen($driverPath, "w+"));
        }
        
        $zip = new ZipArchive;
        $res = $zip->open($driverPath);
        $zip->extractTo($binPath);
        $zip->close();

        
    }
}