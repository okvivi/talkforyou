<?php
  date_default_timezone_set('America/New_York');
  $scope = "read_stream";

  if ($_SERVER['SERVER_NAME'] == 'talkforyou.local') {
    $app_id = "129592820452630";
    $secret = "e6de56db4a15fd4b6b01925eb7360e5a";
    $my_url = "http://talkforyou.local/";

    $dblink = mysql_connect("localhost", "root", "") or die("Could not connect");

  } else if ($_SERVER['SERVER_NAME'] == 'localhost' ||
             $_SERVER['SERVER_NAME'] == 'zen.local') {
    $app_id = "214031575282922";
    $secret = "066ef95af4a17fe0dff167ff9a9ae5f7";
    $my_url = "http://localhost/fbtunes/";

    $dblink = mysql_connect("localhost", "root", "") or die("Could not connect");

  } else {
    $app_id = "151610438237720";
    $secret = "e8a74fd367ed52adf9521c812b11e302";
    $my_url = "http://talkforyou.me/";

    $dblink = mysql_connect("localhost:/tmp/mysql5.sock", "vivi", "divertis") or die("Could not connect");
  }

  $facebook = new Facebook(array(
    'appId' => $app_id,
    'secret' => $secret,
    'cookie' => true,
  ));

  mysql_set_charset('UTF8', $dblink);
  mysql_select_db("talk_foryou", $dblink) or die("Could not select database");

  require_once("smarty/Smarty.class.php");
?>
