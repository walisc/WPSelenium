<?php

namespace WPSelenium\Utilities;

class Requests{
    static function GetFile($url, $filePath){

        $ch = curl_init($url);
        curl_setopt($ch, CURLOPT_FILE, $filePath);
        curl_exec($ch);
        curl_close($ch);
    }
    
    static function Post($url, $postFields){
        $ch=curl_init($url);
        curl_setopt($ch, CURLOPT_CUSTOMREQUEST, "POST");
        curl_setopt($ch, CURLOPT_RETURNTRANSFER , true);
        curl_setopt($ch, CURLOPT_POSTFIELDS, $postFields);
        
        $result = curl_exec($ch);
        curl_close($ch);
        return $result;
    }
    
    static function Get($url){
        $ch=curl_init($url);
        curl_setopt($ch, CURLOPT_CUSTOMREQUEST, "GET");
    
        $result = curl_exec($ch);
        curl_close($ch);
        return $result;
    }
    
}