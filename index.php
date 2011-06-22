<?php
require_once('facebook.php');
require_once('top.php');
require_once('Zend/Loader.php');
require_once('functions.php');
Zend_Loader::loadClass('Zend_Gdata_YouTube');

$play = $_REQUEST["play"];
$groups_filter = $_REQUEST['groups_filter'];

// Display the homepage if I don't have a code or a play parameter.
if(empty($play)) {
  $c = new Smarty();
  $t = new Smarty();
  countSharedSongs($t);
  $t->assign('content', $c->fetch('home.tpl'));
  $t->display('page.tpl');
  return;
}

$authenticateOrDie = false;
include('authenticate.php');

// --------- finally I am done with this token shit -------------------

$access_token = $facebook->getAccessToken();

$PREFS = readUserPrefs($user['id']);

$t = new Smarty();

// The playing head is either specified in the URL or taken from the prefs.
// We take it from the prefs for when you come back.
$head = (int)$_GET["head"] > 0 ? (int)$_GET["head"] : $PREFS['head'];
setUserPref($user['id'], 'head', $head);

if ((int)$_GET["rehead"] > 0) {
  $head = time();
}

$current_friend = $PREFS['fid'];

$t->assign('access_token', $access_token);
$t->assign('app_id', $app_id);
$t->assign('code', $code);
$t->assign('my_url', $my_url);

$t->assign('current_friend', $current_friend);

$t->assign('user_name', $user['name']);
$t->assign('user_id', $user['id']);

assignPrefsToTemplate($t);

$songs = getNextSongs($user['id'], $head, 6,
    $PREFS['unplayed'], $PREFS['music'], $PREFS['shuffle']);

if (sizeof($songs) == 0) {
  // This is either the very first time this user has visited our application,
  // or when the user is rewinding to the very beginning. All other times (when
  // head is set in the url) we don't want to be fast.
  $results = getVideoObjects($access_token);
  putResultsInDatabase($results, $user['id'], $user['id'], $user['name']);

  $songs = getNextSongs($user['id'], $head, 6,
      $PREFS['unplayed'], $PREFS['music'], $PREFS['shuffle']);

  // If I still don't have a song, exit.
  if (sizeof($songs) == 0) return;
}

while (!renderCurrentSong($t, $songs[0], $head, $user['id'])) {
  $songs = getNextSongs($user['id'], $songs[1]['time'] + 1, 5,
      $PREFS['unplayed'], $PREFS['music'], $PREFS['shuffle']);
}

renderComingUpNext($t, array_slice($songs, 1, 5));

$t->assign('unplayed_count', getSongsCount($user['id'], false));
$t->assign('played_count', getSongsCount($user['id'], true));

$t->assign('groups_filter', $groups_filter);

$playlist = new Smarty();
$playlist->assign('code', $code);
$headplaylist = getNextSongs($user['id'], time(), 5, 'false', 'true', 'false');
$playlist->assign('head', $headplaylist);

$groups = new Smarty();

$t->assign('playlist', $playlist->fetch('playlist.tpl'));
$t->assign('groups', $groups->fetch('groups.tpl'));

$page = new Smarty();
countSharedSongs($page);
$page->assign('content', $t->fetch('play.tpl'));
$page->display('page.tpl');

include_once('bottom.php');

?>
