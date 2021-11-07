<?php

$config = dirname(__FILE__) . '/../../../videos/configuration.php';
require_once $config;

if (!isCommandLineInterface()) {
    return die('Command Line only');
}

$isCDNEnabled = AVideoPlugin::isEnabledByName('CDN');

if (empty($isCDNEnabled)) {
    return die('Plugin disabled');
}

require_once './functions.php';

set_time_limit(300);
ini_set('max_execution_time', 300);

getConnID(0);

$list = ftp_rawlist($conn_id[0], "/{$CDNObj->storage_username}/", true);

foreach ($list as $value) {
    $parts = explode(' ', $value);
    $dir = end($parts);
    $files = ftp_rawlist($conn_id[0], "/{$CDNObj->storage_username}/{$dir}/", true);
    foreach ($files as $file) {
        echo 'Searching '.$file.PHP_EOL;
        if(preg_match('/enc_[0-9a-z].key$/i', $file)){
            echo '******** '.$file.PHP_EOL;
        }
    }
}