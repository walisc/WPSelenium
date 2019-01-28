<?php

echo "--- WPooW Tests Runner --- \n";

$testSite = "";
$project_dir = realpath(sprintf("%s%s%s", __DIR__, DIRECTORY_SEPARATOR, "../"));
$phpunitloc = sprintf("%s%s%s%s%s%s%s", $project_dir, DIRECTORY_SEPARATOR, "vendor", DIRECTORY_SEPARATOR, "bin", DIRECTORY_SEPARATOR, "phpunit" );
$binPath = sprintf("%s%s%s",__DIR__, DIRECTORY_SEPARATOR, "bin");
$driverPath = sprintf("%s%s%s", $binPath, DIRECTORY_SEPARATOR, "chromeDrivers.zip");
$seleniumPath = sprintf("%s%s%s", $binPath, DIRECTORY_SEPARATOR, "seleniumServer.jar");

ParseArgs($argv,$argc);
LinkToTestPlugin($argv);
InstallSeleniumDependencies();
StartSeleniumServer();
StartWPooWTests();

echo "--- WPooW Tests Runner Completed--- \n";

function ParseArgs($argv,$argc){
    if ($argc != 2)
    {
        Logger::ERROR("You need to specify the site URL for the site");
        Logger::ERROR("Usuage:- php WPooWTestRunner.php [url]");
        Logger::ERROR("Example:- php WPooWTestRunner.php localhost");
        Quit();
    }
    
}

function LinkToTestPlugin($argv){
    global $testSite;
    $testSite = $argv[1];
    $result = ProcessPostRequest(sprintf('http://%s/wp-admin/admin-ajax.php', $testSite),  "action=wpoow_testing_request");
    $resultDic = json_decode(substr($result,0,strlen($result)-1), true);

    if (!$resultDic["WPooWLinked"]){
        if ($resultDic["pluginPathDir"]){
            Logger::INFO("WPooW project not linked to WPooW test Plugin. Linking now");
            $testPluginPath =sprintf("%s%s",$resultDic["pluginPathDir"], DIRECTORY_SEPARATOR);
            $testPluginDetails = json_decode(file_get_contents($testPluginPath. "composer.sample.json"), true);
            $testPluginDetails["repositories"][0]["url"] = realpath(sprintf("%s%s%s",__DIR__, DIRECTORY_SEPARATOR, "../"));

            fwrite(fopen($testPluginPath."composer.json", "w") , json_encode($testPluginDetails));
            echo "\n";
            exec("composer install -d ". $resultDic["pluginPathDir"]);
            Logger::INFO("WPooW project linked successfully");
        }
        else{
            Logger::ERROR("Cant seem to be able to communicate WPooW Test Plugin. Make sure your site is running and you have add and activated it the WPooW test Plugin.");
            Quit();
        }
    }

}

function InstallSeleniumDependencies(){

    global $project_dir;
    global $phpunitloc;
    global $binPath;
    global $driverPath;
    global $seleniumPath;

    $seleniumUrl = "http://selenium-release.storage.googleapis.com/3.9/selenium-server-standalone-3.9.1.jar";
    $driverUrl = "https://chromedriver.storage.googleapis.com/2.45/chromedriver_%s.zip";

    if (!file_exists($binPath))
    {
        mkdir($binPath);
    }

    if (!file_exists($seleniumPath)){
        Logger::INFO("Selenium Server is not installed. Installing now");
        ProcessFileRequest($seleniumUrl, fopen($seleniumPath, "w+"));
    }


    if (!file_exists($driverPath)){
        Logger::INFO("Installing Selenium Chrome drivers");

        $dirveType = "";
        if (stristr(PHP_OS, 'DAR')){
            ProcessFileRequest(sprintf($driverUrl,"mac64"),fopen($driverPath, "w+"));
        }else if (stristr(PHP_OS, 'WIN')){
            ProcessFileRequest(sprintf($driverUrl,"win32"), fopen($driverPath, "w+"));
        }else if(stristr(PHP_OS, 'LINUX')){
            ProcessFileRequest(sprintf($driverUrl,"linux64"),fopen($driverPath, "w+"));
        }
        
        $zip = new ZipArchive;
        $res = $zip->open($driverPath);
        $zip->extractTo($binPath);
        $zip->close();

        
    }
}

function StartSeleniumServer(){
    global $seleniumPath;
    global $binPath;
    global $testSite;

    Logger::INFO("Setting Enviroment variables");
    putenv('PATH=' . getenv('PATH') . PATH_SEPARATOR . $binPath);
    putenv('WPOOW_TEST_SITE=' . $testSite );
    //adding sleep command to give time for the command to full run/programs exceute
    sleep(1);
    # 3. Starting Selenium
    Logger::INFO("---- Starting Selenium----- \n\n");

    $fp = fsockopen("localhost",4444, $errno, $errstr,1);
    if($errstr == "" || $errno == 0){   
        ProcessGetRequest(sprintf("http://localhost:4444/extra/LifecycleServlet?action=shutdown"));
        sleep(1);
    } 
    exec(sprintf("java -jar %s -role node -servlet org.openqa.grid.web.servlet.LifecycleServlet -registerCycle 0 -port 4444  > /dev/null 2>&1 &", $seleniumPath));   
    sleep(1);
    fclose($fp);
}

function StartWPooWTests(){
    global $phpunitloc;
    
    Logger::INFO("---- Running WPooW Tests---- \n\n");
    system($phpunitloc);
    //Kill Selenium
    //sleep 1
}



########## Helper Functions ##########
function ProcessFileRequest($url, $filePath){

    $ch = curl_init($url);
    curl_setopt($ch, CURLOPT_FILE, $filePath);
    curl_exec($ch);
    curl_close($ch);
}

function ProcessPostRequest($url, $postFields){
    $ch=curl_init($url);
    curl_setopt($ch, CURLOPT_CUSTOMREQUEST, "POST");
    curl_setopt($ch, CURLOPT_RETURNTRANSFER , true);
    curl_setopt($ch, CURLOPT_POSTFIELDS, $postFields);
    
    $result = curl_exec($ch);
    curl_close($ch);
    return $result;
}

function ProcessGetRequest($url){
    $ch=curl_init($url);
    curl_setopt($ch, CURLOPT_CUSTOMREQUEST, "GET");

    $result = curl_exec($ch);
    curl_close($ch);
    return $result;
}

function Quit(){
    echo("\n\n");
    exit(0);
}

Class Logger{

    static function INFO($msg){
        echo sprintf("\n\033[32m %s INFO:\033[0m %s\n",  date("Y-m-d H:i:s"), $msg); 
    }
    static function WARN($msg){
        echo sprintf("\n\033[33m %s WARN:\033[0m %s\n",  date("Y-m-d H:i:s"), $msg); 
    }
    static function ERROR($msg){
        echo sprintf("\n\033[31m %s ERROR:\033[0m %s\n",  date("Y-m-d H:i:s"), $msg);     
    }
    static function FATAL($msg){
        echo sprintf("\n\033[31m %s FATAL:\033[0m %s\n",  date("Y-m-d H:i:s"), $msg);     
    }

}