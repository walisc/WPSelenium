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
                                                                   
        if ($downloadFileResults["status"] == CONSTS::DOWNLOAD_FILE_DOWNLOADED){            
            switch($fileExtension){
                case "zip":
                    $zip = new \ZipArchive;
                    $res = $zip->open($saveDriverPath);
                    if ($res == TRUE){
                        for($i = 0; $i < $zip->numFiles; $i++) {
                            $filename = $zip->getNameIndex($i);
                            $potential_file_path =  sprintf("%s%s%s",$this->wpSeleniumBinPath, DIRECTORY_SEPARATOR, $filename );
                            if (file_exists($potential_file_path)){
                                unlink($potential_file_path);
                            }
                        }
                    }
                    $zip->extractTo($this->wpSeleniumBinPath);
                    $zip->close();
                    break;
                case "tar.gz":
                    $saveDriverPathTar = str_replace(".gz","",$saveDriverPath);
                    if (file_exists($saveDriverPathTar)){
                        unlink($saveDriverPathTar);
                    }
                    $p = new \PharData($saveDriverPath);
                    $p->decompress(); 
                    $phar = new \PharData($saveDriverPathTar);
                    $phar->extractTo($this->wpSeleniumBinPath, null, TRUE);
                    break;
            }
        
        }
    } 
}