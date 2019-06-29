<?php
namespace WPSelenium;
use WPSelenium\Utilities\CONSTS;
use WPSelenium\Utilities\Utilities;

class PrepareTests {
    public static function CreateTestElements(){

        function setFileForTests($file){
            if(!$file->isDir()){
                if (preg_match('/Test\.php/', $file->getFileName() )){
                    include $file->getPathname();
                    $potentialTestClass = str_replace(".php","", $file->getFileName());
                    if (class_exists($potentialTestClass))
                    {
                        $potentialTestClass::setUpWPSite();
                    }
                    
                }
            }
        }

        $composerPath = dirname(Utilities::GetCallingMethod()['file']) . DIRECTORY_SEPARATOR .  "composer.json";
        
 
        if (file_exists($composerPath)){
            $composerJSONFile = json_decode(file_get_contents($composerPath, true), true);

            if (array_key_exists('repositories',$composerJSONFile ))
            {
                $decodedTestFile = json_decode(file_get_contents($composerJSONFile["repositories"][0]["url"] . DIRECTORY_SEPARATOR .  CONSTS::WPSELENIUM_TEMP_TEST_FILE, true), true);
               
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
    }
}
