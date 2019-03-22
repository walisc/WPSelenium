<?php
namespace WPSelenium;
use WPSelenium\Utilities\CONSTS;

class PrepareTests {
    public static function CreateTestElements(){

        function setFileForTests($file){
            if(!$file->isDir()){
                if (preg_match('/Test\.php/', $file->getFileName() )){
                    include $file->getPathname();
                    (str_replace(".php","", $file->getFileName()))::setUpWPSite();
                }
            }
        }
        $decodedTestFile = json_decode(file_get_contents(sprintf("%s%s%s", __DIR__, DIRECTORY_SEPARATOR,CONSTS::WPSELENIUM_TEMP_TEST_FILE)), true);

        foreach($decodedTestFile[CONSTS::WPSELENIUM_TEMP_TEST_DIR_KEY] as $decodedTestDir){
            if (file_exists($decodedTestDir)){
                $testFilesItre = new \RecursiveIteratorIterator(new \RecursiveDirectoryIterator($decodedTestDir));

                foreach($testFilesItre as $file){
                    setFileForTests($file);  
                }
            }
        }

        foreach($decodedTestFile[CONSTS::WPSELENIUM_TEMP_TEST_FILE_KEY] as $decodedTestFile){
            if (file_exists( $decodedTestFile)){
                setFileForTests(new \SplFileInfo($decodedTestFile));
            }
        }
    }
}
