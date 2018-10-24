<?php
function isolateDomain($url) {
    return parse_url($url)['host'];
}
function newModel() {
    if(isset($_COOKIE['uid'])) {
        return $_COOKIE['uid'];
    } else {
        $uid = uniqid();
        setcookie("uid", $uid, time()+631139040);
        $dir = realpath(__DIR__ . '/../keys/models') . "/";
        $file = fopen($dir.$uid.".txt", "w+");
        $array = array();
        fwrite($file, serialize($array));
        return $uid;
    }
}
function addNewCategory($catName) {
    $dir = realpath(__DIR__ . '/../keys/models') . "/";
    $model = fopen($dir . $_COOKIE['uid'] . ".txt", "r");
    $array = unserialize(fgets($model));
    fclose($model);
    if(!array_key_exists($catName, $array)) {
        $array[$catName]=array(array());
    }
    unset($array[$catName][0]);
    $model = fopen($dir . $_COOKIE['uid'] . ".txt", "w+");
    fwrite($model, serialize($array));
    fclose($model);
    return $_COOKIE['uid'];
}
function addNewDomain($catName, $domName) {
    $dir = realpath(__DIR__ . '/../keys/models') . "/";
    $model = fopen($dir . $_COOKIE['uid'] . ".txt", "r");
    $array = unserialize(fgets($model));
    fclose($model);
    if(!array_key_exists($catName, $array)) {
        addNewCategory($catName);
    }
    $array[$catName][$domName] = 0;
    $model = fopen($dir . $_COOKIE['uid'] . ".txt", "w+");
    fwrite($model, serialize($array));
    fclose($model);
    return $_COOKIE['uid'];
}
function increment($catName, $domName) {
    $dir = realpath(__DIR__ . '/../keys/models') . "/";
    $model = fopen($dir . $_COOKIE['uid'] . ".txt", "r");
    $array = unserialize(fgets($model));
    fclose($model);
    if(!array_key_exists($domName, $array[$catName])) {
        addNewDomain($catName, $domName);
    }
    $array[$catName][$domName] += 1;
    $model = fopen($dir . $_COOKIE['uid'] . ".txt", "w+");
    fwrite($model, serialize($array));
    fclose($model);
    return $_COOKIE['uid'];
}
function returnArray() {
    $dir = realpath(__DIR__ . '/../keys/models') . "/";
    $model = fopen($dir . $_COOKIE['uid'] . ".txt", "r");
    $array = unserialize(fgets($model));
    fclose($model);
    return $array;
}
?>
