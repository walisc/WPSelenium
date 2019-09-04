<?php

namespace WPSelenium\Utilities;

class Svn{

    CONST COMMAND_CHECKOUT = 'co';


    private $executionProperties = [
        'quiet' => true
    ];

    public static function CheckIfInstalled(){

        if (Utilities::GetOS() == 'linux'){
            return exec(sprintf('which %s', self::GetSvnCommand())) != null ;
        }
        else if(Utilities::GetOS() == 'linux'){
            //TODO: tests on Windows
            return exec(sprintf('where %s', self::GetSvnCommand())) != null ;
        }

        Logger::ERROR('Hmm...Svn doesnt seem to be installed on your machine.', true);

    }

    private static function GetSvnCommand(){
        if (Utilities::GetOS() == 'linux'){
            return 'svn';
        }
        else if(Utilities::GetOS() == 'linux'){
            return 'svn.exe';
        }
    }

    public function CheckOut($url, $path){

        if ($this->CheckIfInstalled()){
            $this->execute(Svn::COMMAND_CHECKOUT, [$url, $path]);
        }
    }

    public function SetExecutionProperties($property, $trueOrFalse=true){
        $this->executionProperties[$property] = $trueOrFalse;

    }

    private function execute($cmd, $params){
        exec(sprintf("%s %s %s %s",
                            $this->GetSvnCommand(),
                            $cmd,
                            implode(' --', array_keys(array_filter($params, function($param){ return $param;}))),
                            implode(' ', $params)));
    }
}