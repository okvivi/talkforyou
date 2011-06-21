<?php
require_once('facebook.php');
require_once('top.php');

require_once('Zend/Loader.php');
Zend_Loader::loadClass('Zend_Gdata_YouTube');

require_once('functions.php');

$authenticateOrDie = true;
require_once('authenticate.php');
$access_token = $facebook->getAccessToken();

$fid = (int)$_GET['fid'];

if ($_GET['id'] == "-1") {
  $results = getVideoObjects($access_token, "me", 1);

  echo "({video_count: '" . sizeof($results)
      . "', name: '" . $user->name . "', "
      . "friend_id: 'me'})";

} else {
  if (!preg_match('/^([0-9]+)$/', $_GET['id'])) {
    return;
  }
  $friend = getUser($access_token, $_GET['id']);
  // Always get the first page on the stream in case something showed up.
  $results = getVideoObjects($access_token, $friend->id, 1);

  setUserPref($user['id'], 'fid', $fid);

  echo "({video_count: '" . sizeof($results)
      . "', name: '" . $friend->name . "', "
      . "friend_id: '" . $friend->id . "'})";

}

putResultsInDatabase($results, $friend->id, $friend->name);

include_once('bottom.php');

?>
