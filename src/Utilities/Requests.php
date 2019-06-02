<?php

namespace WPSelenium\Utilities;

class Requests{

    static function GetFile($url, $filePath){

        $fp = fopen($filePath, "w+");
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_FOLLOWLOCATION, 1);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, 30);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, 0);    
        curl_setopt($ch, CURLOPT_FILE, $fp);
    
        $response = curl_exec($ch);
        fclose($fp);
        curl_close($ch);
        return $response;
    }

    static function SiteUp($url) {
        $curlInit = curl_init($url);
        curl_setopt($curlInit,CURLOPT_CONNECTTIMEOUT,10);
        curl_setopt($curlInit,CURLOPT_HEADER,true);
        curl_setopt($curlInit,CURLOPT_NOBODY,true);
        curl_setopt($curlInit,CURLOPT_RETURNTRANSFER,true);

        $response = curl_exec($curlInit);
     
        curl_close($curlInit);
        if ($response) return true;
        return false;
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