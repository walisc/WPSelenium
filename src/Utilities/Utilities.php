<?php

namespace WPSelenium\Utilities;

class Utilities{
    private static function WriteHashFile($hashFilePath, $content){
        $hashFileHandler = fopen($hashFilePath, 'w');
        fwrite($hashFileHandler, json_encode($content) ); 
        fclose($hashFileHandler);
    }

    static function DownloadFileAndCheckHash($url, $filePath, $hash_key, $start_download_message=null, $end_download_message=null){
        
        $fileHash = hash("md5", $url);
        $hashFilePath = sprintf("%s%shash_file", dirname($filePath), DIRECTORY_SEPARATOR);
        
        $hashFileObj = null;
        $isDifferentFile = FALSE;

        if (!file_exists($hashFilePath)){
            $hashFileObj = [
                "$hash_key" => $fileHash
            ];
            self::WriteHashFile($hashFilePath, $hashFileObj);
            $isDifferentFile = TRUE;    
        }
        else{
            $hashFileObj = json_decode(file_get_contents($hashFilePath), true);
            $isDifferentFile = $hashFileObj[$hash_key] != $fileHash;
        }

        if ($isDifferentFile || !file_exists($filePath) ){
            $start_download_message == null ? "" : Logger::INFO($start_download_message);
            //TODO: Not giving feedback whenit fails 
            if (Requests::GetFile($url, $filePath) === FALSE){
                Logger::ERROR("Could not download file for $url.", TRUE);
            }

            $hashFileObj[$hash_key] = $fileHash;
            self::WriteHashFile($hashFilePath, $hashFileObj);

            $$end_download_message == null ? "" : Logger::INFO($end_download_message); 

            return [
                "status" => CONSTS::DOWNLOAD_FILE_DOWNLOADED
            ];
        }
        else{
            return [
                "status" => CONSTS::DOWNLOAD_FILE_ALREADY_EXISTS
            ];
        }
        
    }

    public static function GetOS(){
        switch (true) {
            case stristr(PHP_OS, 'DAR'): return "dar";
            case stristr(PHP_OS, 'WIN'): return "win";
            case stristr(PHP_OS, 'LINUX'): return "linux";
            default : return self::OS_UNKNOWN;
        }
    }

    public static function ChmodR($path, $filemode) { 
        if (!is_dir($path)) 
            return chmod($path, $filemode); 
    
        $dh = opendir($path); 
        while (($file = readdir($dh)) !== false) { 
            if($file != '.' && $file != '..') { 
                $fullpath = $path.DIRECTORY_SEPARATOR.$file; 
                if(is_link($fullpath)) 
                    return FALSE; 
                elseif(!is_dir($fullpath)) 
                    if (!chmod($fullpath, $filemode)) 
                        return FALSE; 
                elseif(!self::ChmodR($fullpath, $filemode)) 
                    return FALSE; 
            } 
        } 
    
        closedir($dh); 
    
        if(chmod($path, $filemode)) 
            return TRUE; 
        else 
            return FALSE; 
    } 

    public static function RecursiveCopy($src,$dst) { 
        $dir = opendir($src); 
        @mkdir($dst); 
        while(false !== ( $file = readdir($dir)) ) { 
            if (( $file != '.' ) && ( $file != '..' )) { 
                if ( is_dir($src . DIRECTORY_SEPARATOR . $file) ) { 
                    self::RecurseCopy($src . DIRECTORY_SEPARATOR . $file,$dst . DIRECTORY_SEPARATOR . $file); 
                } 
                else { 
                    copy($src .DIRECTORY_SEPARATOR . $file,$dst .DIRECTORY_SEPARATOR. $file); 
                } 
            } 
        } 
        closedir($dir); 
    } 
}