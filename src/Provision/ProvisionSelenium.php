<?php

namespace WPSelenium\Provision;
use WPSelenium\WPSeleniumConfig;
use WPSelenium\Utilities\Logger;
use WPSelenium\Utilities\Requests;
use WPSelenium\Utilities\Utilities;
use WPSelenium\Utilities\CONSTS;

class ProvisionSelenium{

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
        Utilities::ChmodR($this->wpSeleniumBinPath, 0755);
    }

    function DownloadSelenium(){
        Utilities::DownloadFileAndCheckHash($this->wpSeleniumProvisionConfig->GetSeleniumDownloadUrl(), 
                                            $this->seleniumServerServerPath, 
                                            "seleniumServer",
                                            "Selenium Server is not installed. Installing now");
    
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

        
        $downloadFileResults = Utilities::DownloadFileAndCheckHash($driverUrl, 
                                                                   $saveDriverPath, 
                                                                   sprintf("driver.%s.%s", Utilities::GetOS(),$this->selectedBrowserDriver),
                                                                   "Installing Selenium {$this->selectedBrowserDriver} drivers");
                                                                   
        if ($downloadFileResults == CONSTS::DOWNLOAD_FILE_DOWNLOADED){            
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