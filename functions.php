<?php

function getAccessToken($app_id, $my_url, $app_secret, $code) {
  $token_url = "https://graph.facebook.com/oauth/access_token?"
      . "client_id=" . $app_id
      . "&redirect_uri=" . urlencode($my_url)
      . "&client_secret=" . $app_secret
      . "&code=" . $code;

  return @file_get_contents($token_url);
}


function getUser($access_token, $id) {
  if (!$id) return;
  $graph_url = "https://graph.facebook.com/{$id}?" . $access_token;
  return json_decode(file_get_contents($graph_url));
}


function readUserPrefs($user_id) {
  $prefs = array();
  $s = mysql_query("
    SELECT pref, value FROM prefs WHERE user_id = '{$user_id}'
  ");
  while ($r = mysql_fetch_array($s)) {
    $prefs[$r['pref']] = $r['value'];
  }

  // Set some defaults.
  if (!array_key_exists('shuffle', $prefs)) $prefs['shuffle'] = 'false';
  if (!array_key_exists('head', $prefs)) $prefs['head'] = '' . time();
  if (!array_key_exists('unplayed', $prefs)) $prefs['unplayed'] = 'true';
  if (!array_key_exists('music', $prefs)) $prefs['music'] = 'true';
  if (!array_key_exists('fid', $prefs)) $prefs['fid'] = '0';

  return $prefs;
}


function assignPrefsToTemplate($t) {
  global $PREFS;

  if ($PREFS['shuffle'] == 'true') $t->assign('shuffle_text', 'Shuffle is on');
  if ($PREFS['shuffle'] == 'false') $t->assign('shuffle_text', 'Shuffle is off');

  if ($PREFS['unplayed'] == 'true') $t->assign('unplayed_text', 'Unplayed only');
  if ($PREFS['unplayed'] == 'false') $t->assign('unplayed_text', 'Everything');

  if ($PREFS['music'] == 'true') $t->assign('music_text', 'Music only');
  if ($PREFS['music'] == 'false') $t->assign('music_text', 'All clips');
}


function setUserPref($user_id, $pref, $value) {
  // Delete the old value.
  mysql_query("
    DELETE FROM prefs
    WHERE user_id = '{$user_id}' AND pref = '{$pref}'
  ");
  mysql_query("
    INSERT INTO prefs(user_id, pref, value)
    values('{$user_id}', '{$pref}', '{$value}')
  ");
}


function getVideoObjects($access_token, $id="me", $pages=5) {
  $results = array();

  $graph_url = "https://graph.facebook.com/" . $id . "/feed?access_token=" . $access_token;
  if ($id == "me") {
    $graph_url = "https://graph.facebook.com/" . $id . "/home?access_token=" . $access_token;
  }
  $totalStream = 0;

  for ($i = 0; $i < $pages; $i++) {
    $cont = @file_get_contents($graph_url);
    if ($cont === FALSE) {
      return $results;
    }

    $stream = json_decode($cont);
    $totalStream += sizeof($stream->data);

    for ($j = 0; $j < sizeof($stream->data); $j++) {
      if (strpos($stream->data[$j]->message, "www.youtube.com") !== FALSE) {
        $results[] = $stream->data[$j];
      } else if (strpos($stream->data[$j]->link, "www.youtube.com") !== FALSE) {
        $results[] = $stream->data[$j];
      } else if (strpos($stream->data[$j]->link, "http://youtu.be") !== FALSE) {
        $results[] = $stream->data[$j];
      } else if (strpos($stream->data[$j]->source, "www.youtube.com") !== FALSE) {
        $results[] = $stream->data[$j];
      }
    }
    $graph_url = $stream->paging->next;
    if (!$graph_url) {
      break;
    }
  }

  return $results;
}


function putResultsInDatabase($user_id, $results) {
  $youtubeService = new Zend_Gdata_YouTube();

  for ($i = 0; $i < sizeof($results); $i++) {
    $r = $results[$i];
    $time = strtotime($r->created_time);
    $link = $r->link;
    if (!$r->link || strpos($r->link, "youtu") === FALSE) {
      $link = $r->message;
      if (!$r->message || strpos($r->message, "youtu") === FALSE) {
        $link = $r->source;
      }
    }

    $videoId = getVideoId($link);
    if ($videoId == '') {
      continue;
    }
    $cat = '';

    try {
      $entry = $youtubeService->getVideoEntry($videoId);

      $cat = $entry->getVideoCategory();
      $title = $entry->getVideoTitle();
      $duration = $entry->getVideoDuration();

      updatePlaylistField($user_id, $time, 'cat', $cat);
      updatePlaylistField($user_id, $time, 'title', $title);
      updatePlaylistField($user_id, $time, 'duration', $duration);

    } catch (Zend_Gdata_App_HttpException $ex) {
    } catch (Zend_Uri_Exception $zue) {
      $cat = 'invalid';
    }

    mysql_query("
      INSERT IGNORE INTO
      playlist(user_id, time, link, source_id, cat, title,
               duration, shared_by_name, fb_id)
      VALUES('{$user_id}', $time, '$link', '{$r->from->id}', '{$cat}',
             '{$title}', '{$duration}', '{$r->from->name}', '{$r->id}')
    ");
    updatePlaylistField($user_id, $time, 'fb_id', $r->id);
  }
}

function getSongsCount($user_id, $played) {
  if ($played) {
    $s = mysql_query("
      SELECT count(*) AS num FROM playlist
      WHERE user_id = '{$user_id}' AND play_count > 0
    ");
  } else {
    $s = mysql_query("
      SELECT count(*) AS num FROM playlist
      WHERE user_id = '{$user_id}' AND play_count = 0
    ");
  }
  if ($r = mysql_fetch_array($s)) {
    return $r['num'];
  }
  return 0;
}

function updateCategory($user_id, $time, $cat) {
  mysql_query("
    UPDATE playlist SET cat='{$cat}'
    WHERE user_id = '{$user_id}' AND time = '{$time}'
  ");
}

function updatePlaylistField($user_id, $time, $field, $value) {
  mysql_query("
    UPDATE playlist SET {$field}='{$value}'
    WHERE user_id = '{$user_id}' AND time = '{$time}'
  ");
}

function getNextSongs($user_id, $head, $count, $unplayed, $music, $shuffle) {
  global $access_token;

  // When chosing the next song, always respect head.
  $cond = $unplayed == 'true' ?
    $cond = "AND (play_count = 0 OR time < '{$head}') " :
    $cond = "AND time < '{$head}' ";

  if ($shuffle == 'true') {
    $order = 'rand(' . time() . ')';
    // Delete the condition with the head in this case, we want to randomize
    // over everything.
    $cond = $unplayed == 'true' ? 'AND play_count = 0' : '';
  } else {
    $order = 'time';
  }

  if ($music == 'true')  $cond = $cond . " AND cat = 'Music' ";

  $s = mysql_query("
    SELECT * FROM playlist
    WHERE user_id = '{$user_id}' {$cond}
      AND link != ''
      AND link not like '%vimeo%'
    GROUP BY link
    ORDER BY {$order} DESC
    LIMIT 0, {$count}
  ");
  $results = array();

  if (mysql_num_rows($s) == 0) {
    // it means we have no more unplayed songs.
    $s = mysql_query("
      SELECT * FROM playlist
      WHERE user_id = '{$user_id}'
      GROUP BY link
      ORDER BY {$order} DESC
      LIMIT 0, {$count}
    ");
  }

  $youtubeService = new Zend_Gdata_YouTube();

  while ($r = mysql_fetch_array($s)) {
    $videoId = getVideoId($r['link']);
    if (!$videoId || $videoId == '') {
      continue;
    }
    $r['video_id'] = $videoId;

    if ($r['shared_by_name'] == '') {
      $name = getUser($access_token, $r['source_id'])->name;
      $r['shared_by_name'] = $name;
      updatePlaylistField($r['user_id'], $r['time'], 'shared_by_name', $name);
    }

    // Update the categories.
    if ($r['cat'] == '' || $r['title'] == '') {
      try {
        $entry = $youtubeService->getVideoEntry($videoId);

        $cat = $entry->getVideoCategory();
        $title = $entry->getVideoTitle();
        $duration = $entry->getVideoDuration();

        updatePlaylistField($r['user_id'], $r['time'], 'cat', $cat);
        updatePlaylistField($r['user_id'], $r['time'], 'title', $title);
        updatePlaylistField($r['user_id'], $r['time'], 'duration', $duration);

        $r['title'] = $title;
        $r['duration'] = $duration;

      } catch (Zend_Gdata_App_HttpException $ex) {
      } catch (Zend_Uri_Exception $zue) {
        updatePlaylistField($r['user_id'], $r['time'], 'cat', 'invalid');
      }
    }

    $r['minutes'] = (int)($r['duration'] / 60);
    $r['seconds'] = (int)($r['duration'] % 60);
    $results[] = $r;
  }
  return $results;
}


function getVideoId($url) {
  if (preg_match("/http:\\/\\/www\\.youtube\\.com\\/v\\/([a-zA-Z0-9]*)\?(.*)/",
                 $url, $matches) > 0) {
    return $matches[1];
  }
  if (strpos($url, "www.youtube.com") !== FALSE) {
    $q = parse_url($url, PHP_URL_QUERY);
    parse_str($q, $out);
    return $out['v'];
  }

  if (strpos($url, "youtu.be") !== FALSE) {
    $pos = strpos($url, "youtu.be") + 9;
    return substr($url, $pos, strlen($url) - $pos);
  }
  return '';
}


function getNextHead($user_id, $current_head) {
  global $PREFS;

  if ($PREFS['unplayed'] == 'true') {
    // When chosing the next song, always respect head.
    $cond = "AND play_count = 0";
  } else {
    $cond = "AND time < '{$current_head}'";
  }
  if ($PREFS['music'] == 'true') {
    // When chosing the next song, always respect head.
    $cond = $cond . " AND cat = 'Music' ";
  }
  if ($PREFS['shuffle'] == 'true') {
    $order = 'rand(' . time() . ')';
  } else {
    $order = 'time';
  }
  $s = mysql_query("
    SELECT * FROM playlist
    WHERE user_id = '{$user_id}' {$cond}
    GROUP BY link
    ORDER BY {$order} DESC
    LIMIT 0, 1
  ");
  if (mysql_num_rows($s) == 0) {
    // it means we have no more unplayed songs.
    $s = mysql_query("
      SELECT * FROM playlist
      WHERE user_id = '{$user_id}'
      GROUP BY link
      ORDER BY {$order} DESC
      LIMIT 0, 1
    ");
  }
  if ($r = mysql_fetch_array($s)) {
    return $r['time'] - 1;
  }
}


function renderCurrentSong($t, $song, $head, $user_id) {
  global $access_token;

  if (getVideoId($song['link']) == '') {
    return false;
  }

  mysql_query("
    UPDATE playlist
    SET play_count = play_count + 1
    WHERE user_id = '{$song['user_id']}' AND time = '{$song['time']}'
  ");

  $t->assign('video_id', getVideoId($song['link']));
  $t->assign('video_title', $song['title']);
  $t->assign('object_id', $song['object_id']);
  $t->assign('next_head', getNextHead($user_id, $head));
  $t->assign('shared_by_name',
      getUser($access_token, $song['source_id'])->name);
  $t->assign('shared_by_id', $song['source_id']);
  $t->assign('shared_time', $song['time']);
  $t->assign('play_count', $song['play_count']);
  $t->assign('fb_id', $song['fb_id']);

  return true;
}


function renderComingUpNext($t, $songs) {
  $templateObjects = array();
  for ($i = 0; $i < sizeof($songs); $i++) {
    $song = $songs[$i];
    $songObject = array();
    $songObject['video_id'] = getVideoId($song['link']);
    $songObject['next_time'] = (int)$song['time'] + 1;
    $songObject['shared_by_name'] = $song['shared_by_name'];
    $songObject['shared_by_id'] = $song['source_id'];
    $songObject['shared_time'] = $song['time'];
    $songObject['play_count'] = $song['play_count'];
    $songObject['title'] = $song['title'];

    $templateObjects[] = $songObject;
  }
  $t->assign('coming_up', $templateObjects);
}

?>