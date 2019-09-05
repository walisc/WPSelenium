<?php

namespace WPSelenium\Helpers\WordPress;

use WPSelenium\Helpers\ConfigBase;


class Config extends ConfigBase {

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
            include_once  sprintf("%s/wp-includes/version.php");
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