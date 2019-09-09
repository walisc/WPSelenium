<?php

namespace WPSelenium\Helpers\WordPress;

use WPSelenium\Helpers\ConfigBase;
use WPSelenium\Utilities\Logger;

class Config extends ConfigBase {

    function __construct($wpSeleniumConfig)
    {
        parent::__construct($wpSeleniumConfig);
        if (!$this->IsWordPressSite()){
            Logger::ERROR("The site you are testing doesnt seem to be a WordPress site. Please make sure the 'sitePath' you have specified points to a WordPress site. ", true);
        }
    }

    public function GetWPTestUsername(){
        return $this->wpSeleniumConfig->GetParsedConfig()->wpTestUsername;
    }

    public function GetWPTestPassword(){
        return $this->wpSeleniumConfig->GetParsedConfig()->wpTestPassword;
    }

    public function IsWordPressSite(){
        return file_exists(sprintf("%s/wp-includes/version.php", $this->GetWpSeleniumConfig()->GetSitePath()));
    }

    public function GetWordPressVersion(){
        if ($this->IsWordPressSite()){
            include_once  sprintf("%s/wp-includes/version.php",$this->GetWpSeleniumConfig()->GetSitePath());
            global $wp_version;
            return $wp_version;
        }
        return null;
    }

    public function IsWordPressTestLibInstalled(){
        $tempDir = $this->GetWpSeleniumConfig()->GetTempDirectory();
        return file_exists(sprintf("%s/wordpress-tests-lib/version.php", $tempDir));

    }

}