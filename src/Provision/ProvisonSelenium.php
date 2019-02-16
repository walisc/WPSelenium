<?php

namespace WPSelenium\Provision;

class ProvisionSelenium{

    private $wpSeleniumPathDir;
    private $wpSeleniumBinPath;
    private $binSeleniumPath;

    private $wpSeleniumProvisionConfig;


    function __construct()
    {
        $wpSeleniumConfig = WPSeleniumConfig::Get();
        $this->wpSeleniumPathDir = $wpSeleniumConfig->GetWPSeleniumDir();
        $this->wpSeleniumProvisionConfig = new ProvisionSeleniumConfig();
        $this->wpSeleniumBinPath = sprintf("%s%s%s", $this->wpSeleniumPathDir, DIRECTORY_SEPARATOR, "bin");
        $this->binSeleniumPath = sprintf("%s%s%s", $binPath, DIRECTORY_SEPARATOR, "seleniumServer.jar");

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

    
}