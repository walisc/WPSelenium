<?php

use WPSelenium\WPSTestCase;

 class SampleTest extends WPSTestCase{

    /**
     * @doesNotPerformAssertions
     */
    function testCanOpenSite()
    {  
        $this->GetSeleniumDriver()->get($this->GetTestSite());
	    sleep(10);
    }


}
