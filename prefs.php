<?php
require_once('facebook.php');
require_once('top.php');
require_once('functions.php');

$pref = $_GET['pref'];
if ($pref != "shuffle" && $pref != "unplayed" && $pref != "music") {
  return;
}

$value = $_GET['value'];
if ($value != "true" && $value != "false") {
  return;
}

$authenticateOrDie = true;
require_once('authenticate.php');
$access_token = $facebook->getAccessToken();

setUserPref($user['id'], $pref, $value);

include_once('bottom.php');

?>