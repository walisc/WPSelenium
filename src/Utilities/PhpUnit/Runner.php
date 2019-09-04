<?php

namespace WPSelenium\Utilities\PhpUnit;

class Runner{

    public function Run(){
        if (WPSeleniumConfig::Get()->IsWordPressSite()){
            $this->RunForWordPress();
        }else{
            $this->RunForSite();
        }
    }


    private function RunForWordPress(){
        if ($this->IsWordPressTestLinInstalled()){

        }
        else{
            system($this->wpSeleniumConfig->GetPhpUnitPath());
        }
    }

    //TODO: decouple this to helpers
    private function IsWordPressTestLinInstalled(){

    }
}