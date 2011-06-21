<?php
require_once('facebook.php');
require_once('top.php');

require_once('Zend/Loader.php');
Zend_Loader::loadClass('Zend_Gdata_YouTube');

require_once('functions.php');

$authenticateOrDie = true;
require_once('authenticate.php');
$access_token = $facebook->getAccessToken();

$group_id = $_GET['id'];
if (!preg_match('/^([0-9]+)$/', $group_id)) {
  return;
}

// Always get the first page on the stream in case something showed up.
$results = getVideoObjects($access_token, $group_id, 1);

echo "({video_count: '" . sizeof($results)
    . "', name: '" . $group_id . "', "
    . "friend_id: '" . $group_id . "'})";

$group = getUser($access_token, $group_id);
putResultsInDatabase($results, $group_id, $group->name);

include_once('bottom.php');

?>
