# WP Selenium
### Selenium integration for test ui elements for WorPress Sites/Plugins/Themes

When creating WordPress sites/themes/plugins there are instance you might require to test the UI elements. This libray assists in this by intergrating and configuring selenium to run ui tests on your site. 

The tests themselves run on top of phpunit. This means you can also intergrate you logical tests with you ui tests. 

## Getting Started

1. Install the library through composer `composer require-dev devchid/wpselenium` . 
   
   **Note:-** the folder in which you run this command must be a composer project. As we are using this within a WordPress context, intialize the theme or plugin project you are testing as a composer project. This is simply done by running `composer init` or adding a composer.json file. To read more on getting started with composer please see https://getcomposer.org/doc/00-intro.md

2. Create a wpselenuim.xml file in the composer project (i.e in your theme or plugin ). This file will hold configuration needed by WPSelenium. Below is a sample configuration you can use. 

    **Note:-** The wpselenuim file extends the phpunit.xml file. This means any configuration you would put in there you can add them here

    ```xml
    <wpselenium>
        <siteUrl>localhost</siteUrl>
        <sitePath>/opt/lampp/htdocs/wpseleniumtestsite</sitePath>
        <testDirectory>/opt/lampp/htdocs/wpseleniumtestsite/wp-content/themes/wpseleniumtestsite<testDirectory>
        <!-- Only requiured it you need to do test through wp-admin -->
        <!-- Pass dont store production details here -->
        <wpusername>testuser</wpusername>
        <wppassword>testuser</wppassword>
        <phpunit bootstrap="vendor/autoload.php">
            <testsuites>
                <testsuite name="WPooW Tests">
                <directory>tests</directory>
                </testsuite>
            </testsuites>
        </phpunit>
    </wpselenium>
    ```

    Apart from these configuration option, you could also specify the selenium standalone server url using seleniumURL, and the broswer driver URL using browserDriverURL. This is useful if there is a particual version of the selenium server or driver you want to use. The library however uses the best setting it can find. Also note. If you do chose to do the you will need to delete the vendor/wpselenium/bin folder


3. Create Selenium Tests. This process is similar to creating test with phpunit with the caveat of that we extend the WPSelenium WPSTestCase class instead of the phpunit TestCase class. An example is below

```php

use WPSelenium\WPSTestCase;

class MyThemeTests extends WPSTestCase{

    function BeforeRun($WPooW)
    {
        //Any WordPress logic you might want to do goes here. e.g Create a custome PostType etc
    }

    function SignUpLinkWorkingTest(){
        $this->GoToSite();
        $this->GetDriver()->Find()
        //...
    }

    function ThemeColorPickerTest(){
        $this->LogInToWPAdmin()
        $this->GetDriver()->Click()
        // ...
    }
```

**Note on the WPSelenium TestCase class**

`GoToSite` and `LogInToWPAdmin` are both helper methods provided by WPSelenium TestCase class

`GetDriver` returns the Facebook Selenium Driver. This is important to note, as once you have this driver any other method availed by it can be uses. See for documentaion

4. From there you should be able to run composer/bin/wpselenium to run the test. If its your first time running this, the process will take longer as it need to download the required files and configure them accordingly. After the first run, it will run much faster. 