<?php
/**
 * User: Stanislav Vetlovskiy
 * Date: 16.02.2013
 */

error_reporting(7);
error_reporting(E_ALL);

include_once('classes/autoloader.php');
Autoloader::init();

ini_set('default_charset', 'UTF-8');

$ini = parse_ini_file('conf.ini', true);
$dbConf = $ini['data_base'];
$proxyConf = $ini['data_base'];
Registry::$db = new PDO('mysql:host=' . $dbConf['host'] . ';dbname=' . $dbConf['database'], $dbConf['user'], $dbConf['passwd']);
Registry::$db->exec('SET CHARACTER SET ' . $dbConf['charset']);
Registry::$db->exec('SET NAMES ' . $dbConf['charset']);

// init Request service
$request = new Request();
if (!empty($proxyConf) && !empty($proxyConf['ip'])) {
    $ip = $proxyConf['ip'];
    if (!empty($proxyConf['port'])) {
        $ip .= ':' . $proxyConf['port'];
    }
    $user = null;
    if (!empty($proxyConf['user'])) {
        $user = $proxyConf['user'];
    }
    if (!empty($proxyConf['passwd'])) {
        $user .= ':' . $proxyConf['passwd'];
    }
    $request->setProxy($ip, $user);
}

// init DB service
$db = new Save(Registry::$db);
$inflect = new Inflect($db, $request);
echo "Мета Код: кол-во словоформ\n";
$files = glob('lists/*.csv');
while ($file = array_shift($files)) {
    $inflect->processFile($file);
}

echo 'All is complete!';
