<?php 

namespace WPSelenium;


class PrepareTests {
    //TODO: Specify test, instead of running them all
    public static function CreateTestElements(){
        $testFilesItre = new \RecursiveIteratorIterator(new \RecursiveDirectoryIterator(sprintf("%s%s", __DIR__, DIRECTORY_SEPARATOR)));
        $testFile = [];

        foreach($testFilesItre as $file){

            if(!$file->isDir()){
                //Maybe base this on instance trype
                if (preg_match('/Test\.php/', $file->getFileName() )){
                    include $file->getPathname();
                    $testsClass = str_replace(".php","", $file->getFileName());
                    $testsClass::BeforeRun();
                }
            }
        }
    }
}
