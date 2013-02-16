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

$wordsList = fopen('list.csv', 'r');


echo 'Мета Код: кол-во словоформ'."\n";
while ($row = fgetcsv($wordsList, 1000, ';')) {
    $inflects = $request->get($row[1]);
    $result = $db->proceed($row[0], $row[1], $inflects);
    if ($result) {
        echo $row[0] . ': ' . count($inflects)."\n";
    } else {
        echo $row[0] . ': Ошибка!'."\n";
        print_r($inflects);
        print_r($result);
        exit;
    }
    sleep(1);
}