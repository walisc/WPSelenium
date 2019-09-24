<?php

namespace WPSelenium\Helpers\WordPress\PhpUnitRunner;

use WPSelenium\Helpers\WordPress\Config;
use WPSelenium\Utilities\Logger;

class Runner{

    private $wpBootStrapNoTestLib = 'WPBootStrapNoTestLib.php';
    private $wpBootStrapWithTestLib = 'WPBootStrapWithTestLib.php';

    private $defaultOptions = [];

    private function GetBootStrapFile(){
        return sprintf("%s/%s", dirname(__FILE__), Config::Get()->IsWordPressTestLibInstalled() ? $this->wpBootStrapWithTestLib : $this->wpBootStrapNoTestLib);
    }

    public function Run($phpUnitPath, $options=[]){
        if (!Config::Get()->IsWordPressSite()){
            Logger::ERROR('The site your are testing doesnt seem to be a WordPress site. When using --type wordpress, please make sure your sitePath is set to the root of your WordPress site. ', true);
        }

        $this->defaultOptions['bootstrap'] = $this->GetBootStrapFile();
        $this->_Run($phpUnitPath, array_merge($this->defaultOptions, $options));
    }

    private function _Run($phpUnitPath, $options)
    {
        $optionsCommand = '';
        foreach ($options as $option => $value){
            $optionsCommand .= sprintf("--%s \"%s\"", $option, $value);
        }

        system(sprintf("\"%s\" %s",$phpUnitPath, $optionsCommand ));
    }

}