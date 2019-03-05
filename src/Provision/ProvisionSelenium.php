<?php

namespace WPSelenium\Provision;
use WPSelenium\WPSeleniumConfig;
use WPSelenium\Utilities\Logger;
use WPSelenium\Utilities\Requests;

class ProvisionSelenium{

    private $wpSeleniumPathDir;
    private $wpSeleniumBinPath;
    private $seleniumServerServerPath; 
    private $seleniumCompressedDriverPath; 
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

        $this->wpSeleniumBinPath = $wpSeleniumConfig->GetBinDirectory();
        $this->seleniumServerServerPath =  $wpSeleniumConfig->GetSeleniumServerPath();
        $this->seleniumCompressedDriverPath =  $wpSeleniumConfig->GetSeleniumCompressedDriverPath();

        if (!file_exists($this->wpSeleniumBinPath))
        {
            mkdir($this->wpSeleniumBinPath);
        }
        $this->DownloadSelenium();
        $this->DownloadSeleniumDrivers();
        chmod_R($this->wpSeleniumBinPath, 0755);
    }

    function DownloadSelenium(){
        if (!file_exists($this->seleniumServerServerPath )){
            Logger::INFO("Selenium Server is not installed. Installing now");
            Requests::GetFile($this->wpSeleniumProvisionConfig->GetSeleniumDownloadUrl(), fopen($this->seleniumServerServerPath, "w+"));
        }
    }

    function DownloadSeleniumDrivers(){
        $driverUrl = $this->wpSeleniumProvisionConfig->GetDriverDownloadUrl($this->selectedBrowserDriver);
        $driverUrlExploded = explode('.',$driverUrl);

        if (count($driverUrlExploded)> 2 && $driverUrlExploded[count($driverUrlExploded)-1] == "gz" && $driverUrlExploded[count($driverUrlExploded)-2] == "tar" ){
            $fileExtension = "tar.gz";
        }
        else{ 
        $fileExtension = pathinfo($driverUrl, PATHINFO_EXTENSION ); //NOTE:- File extension does not recognize tar.gz. 
        }

        $saveDriverPath = "$this->seleniumCompressedDriverPath.$fileExtension";

        if (!file_exists($saveDriverPath)){
            Logger::INFO("Installing Selenium {$this->selectedBrowserDriver} drivers");
            Requests::GetFile($driverUrl, fopen($saveDriverPath, "w+") );
            
            switch($fileExtension){
                case "zip":
                    $zip = new \ZipArchive;
                    $res = $zip->open($saveDriverPath);
                    $zip->extractTo($this->wpSeleniumBinPath);
                    $zip->close();
                    break;
                case "tar.gz":
                    $p = new \PharData($saveDriverPath);
                    $p->decompress(); 
                    $phar = new \PharData(str_replace(".gz","",$saveDriverPath));
                    $phar->extractTo($this->wpSeleniumBinPath);
            }
            
            
    
            
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

// From http://php.net/manual/en/function.chmod.php#84273
function chmod_R($path, $filemode) { 
    if (!is_dir($path)) 
        return chmod($path, $filemode); 

    $dh = opendir($path); 
    while (($file = readdir($dh)) !== false) { 
        if($file != '.' && $file != '..') { 
            $fullpath = $path.'/'.$file; 
            if(is_link($fullpath)) 
                return FALSE; 
            elseif(!is_dir($fullpath)) 
                if (!chmod($fullpath, $filemode)) 
                    return FALSE; 
            elseif(!chmod_R($fullpath, $filemode)) 
                return FALSE; 
        } 
    } 

    closedir($dh); 

    if(chmod($path, $filemode)) 
        return TRUE; 
    else 
        return FALSE; 
} 