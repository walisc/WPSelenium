<?php

use WPSelenium\WPSTestCase;

 class SampleTest extends WPSTestCase{


    protected function setUp()
    {  
        $this->GetSeleniumDriver()->get($this->GetTestSite());
    }

}