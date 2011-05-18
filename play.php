<?php 
require_once('top.php');
require_once('functions.php');

$scope = "read_stream";

if ((int)$_GET["next_time"] > 0) {
  $next_time = (int)$_GET["next_time"];
} else {
  $next_time = time();
}
$current_friend = (int)$_GET['fid'];

// This is the code that does the redirection to the right place.
// This should not be on the homepage, but in the background, figure
// out if I need to prompt for "log into facebook" or not.
$code = $_REQUEST["code"];
$play = $_REQUEST["play"];

if(empty($code) && !empty($play)) {
  $dialog_url = "http://www.facebook.com/dialog/oauth?"
      . "client_id=" . $app_id 
      . "&redirect_uri=" . urlencode($my_url)      
      . "&scope=" . $scope;
  echo("<script> location.href='" . $dialog_url . "'</script>");
} else if (empty($code)) {  
  $t = new Smarty();
  $t->display('home.tpl');
  return;
}


$t = new Smarty();

$access_token = getAccessToken($app_id, $my_url, $app_secret, $code);
// TODO(vivi): If the access token expires (in about 1h), do all of this
// all over again.

$t->assign('access_token', $access_token);
$t->assign('app_id', $app_id);
$t->assign('code', $code);
$t->assign('my_url', $my_url);

$t->assign('current_friend', $current_friend);

$user = getUser($access_token, "me");
$t->assign('user_name', $user->name);
$t->assign('user_id', $user->id);

$songs = getNextSongs($user->id, $next_time, 4);
if (sizeof($songs) == 0) {
  // For now this is actually just the first few videos or something.
  $results = getVideoObjects($access_token);
  putResultsInDatabase($user->id, $results);
  $songs = getNextSongs($user->id, $next_time, 4);
  
  // If I still don't have a song, exit.
  if (sizeof($songs) == 0) return;
} else {
  // Always get the first page on the stream in case something showed up.
  $results = getVideoObjects($access_token, "me", 1);
  putResultsInDatabase($user->id, $results);
  $songs = getNextSongs($user->id, $next_time, 4);
}

renderCurrentSong($t, $songs[0], $next_time);
renderComingUpNext($t, array_slice($songs, 1, 3));

$t->assign('unplayed_count', getSongsCount($user->id, false));
$t->assign('played_count', getSongsCount($user->id, true));

$t->display('play.tpl');

include_once('bottom.php');

?>