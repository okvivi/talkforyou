
var FRIENDS_TIMEOUT = 10000;
var friendsArray = null;
var session = null;
var permissions = null;
var commentPending = false;
var likeArray = [];
var groupsArray = [];

/**
 * Starts the background processes after the page loads. This is the parsing
 * of the friends data and showing comments on the video.
 */
function runOnLoad() {
  if (window.accessToken === undefined) return;

  FRIENDS_TIMEOUT = unplayed_count > 30 ? 20000 : 10000;

  // Init the facebook API here, after everything has loaded.
  FB.init({
    appId  : app_id,
    status : true, // check login status
    cookie : true, // enable cookies to allow the server to access the session
    xfbml  : true  // parse XFBML
  });

  FB.getLoginStatus(function(response) {
    log('Login status response:');
    log(response);

    session = response.session;
    log('Got login status:');
    log(session);

    FB.api("/me/friends", handleFriendsListBack);
    FB.api("/me/groups", handleGroupsListBack);
    FB.api("/me/permissions", function(r) {
      permissions = r.data[0];
    });

    maybeShowComments();
  });
}


function handleCrawlFriend(response) {
  var r = eval(response);
  var el = document.getElementById('friends_status');

  if (r.friend_id == "me") {
    setTimeout('triggerCrawlFriend()', 3000);
    return;
  }

  out = [];
  out.push('<img src="http://graph.facebook.com/' +
      r.friend_id + '/picture?type=square" width="25" height="25" hspace="5" ' +
      'align="absmiddle">');
  if (r.video_count == 0) {
    out.push(r.name + ' didn\'t share any videos recently.');
  } else {
    out.push(r.name + ' recently shared ' + r.video_count + ' videos.');
  }

  el.innerHTML = out.join('');

  currentFriend++;
  if (currentFriend >= friendsArray.length) {
    currentFriend = 0;
  }

  setTimeout('triggerCrawlFriend()', FRIENDS_TIMEOUT);
}


function togglePref(pref) {
  var value;
  var el = document.getElementById(pref);

  if (el.innerHTML == 'Shuffle is off') {
    value = 'true';
    el.innerHTML = 'Shuffle is on';
  } else if (el.innerHTML == 'Shuffle is on') {
    value = 'false';
    el.innerHTML = 'Shuffle is off';
  }

  if (el.innerHTML == 'Unplayed only') {
    value = 'false';
    el.innerHTML = 'Everything';
  } else if (el.innerHTML == 'Everything') {
    value = 'true';
    el.innerHTML = 'Unplayed only';
  }

  if (el.innerHTML == 'Music only') {
    value = 'false';
    el.innerHTML = 'All clips';
  } else if (el.innerHTML == 'All clips') {
    value = 'true';
    el.innerHTML = 'Music only';
  }

  sendPayload_('prefs.php?pref=' + pref + '&value=' + value, function() {});
}


function triggerCrawlFriend(opt_id) {
  var id = opt_id || friendsArray[currentFriend].id;
  sendPayload_('friend.php?id=' + id + '&fid=' + currentFriend,
      handleCrawlFriend);
}


function handleFriendsListBack(response) {
  if (response.error) {
    log('Error getting friends!');
    log(response);
    return;
  }
  friendsArray = response.data;
  // The first user we are crawling after the friends list is back is ME.
  setTimeout('triggerCrawlFriend(-1)', 500);
}


function handleGroupsListBack(response) {
  if (response.error) {
    window.console.log('Error getting groups');
    return;
  }

  var out = [];

  for (var i = 0; i < response.data.length; i++) {
    var group = response.data[i];
    if (group.version == 1) {
      sendPayload_('group.php?id=' + response.data[i].id, function(response) {
        // do nothing.
      });
      // Make pretty checkboxes here.
      var checked = document.location.href.indexOf(group.id) > 0;

      out.push('<div class="groups_item">')
      out.push('<input id="' + group.id + '" type="checkbox" name="' +
               group.id + '" ');
      if (checked) {
        out.push(' checked');
      }
      out.push('>');
      out.push('<label for="' + group.id + '">' + group.name + '</label>');
      out.push('</div>');

      groupsArray.push(group);
    }
  }
  var list = document.getElementById('groups_list');
  list.innerHTML = out.join('');
}


/**
 * When the user clicks the button to filter the playlist by a certain list of
 * groups, we call this method.
 */
function filterByGroups() {
  var param_value = [];
  for (var i = 0; i < groupsArray.length; i++) {
    var cb = document.getElementById(groupsArray[i].id);
    if (cb.checked) {
      param_value.push(groupsArray[i].id);
    }
  }
  if (param_value.length > 0) {
    document.location.href = myUrl + '?play=1&rehead=1' +
        '&fid=' + currentFriend + '&groups_filter=' + param_value.join(',');
  } else {
    document.location.href = myUrl + '?play=1&rehead=1' +
        '&fid=' + currentFriend;
  }
}


/**
 * Handler called by the Youtube Javascript API when the player is ready.
 * @param playerId
 */
function onYouTubePlayerReady(playerId) {
  var ytplayer = document.getElementById("myytplayer");
  if (ytplayer) {
    if (!isDeb()) {
      ytplayer.playVideo();
    }
    ytplayer.addEventListener("onStateChange", "onytplayerStateChange");
  }
}


/**
 * Event listener for changes in the state of the youtube player. We add this
 * listener and use it to figure out that the song is indeed playing.
 * @param newState
 */
function onytplayerStateChange(newState) {
  globalYtState = newState;
  if (newState == 0) {
    document.location.href = myUrl + '?play=1&head=' + next_head
        + '&fid=' + currentFriend + '&groups_filter=' + groups_filter.join(',');
  }
  if (newState == -1) {
    // If a song doesn't start playing in 5 seconds.
    if (!isDeb()) {
      setTimeout("checkPlaying()", 7000);
    }
  }
}

function checkPlaying() {
  if (globalYtState == -1) {
    controlNext();
  }
}

function controlRewind() {
  document.location.href = myUrl + '?play=1&rehead=1&groups_filter=' +
      groups_filter.join(',');
}

function controlNext() {
  document.location.href = myUrl + '?play=1&head=' + next_head
      + '&groups_filter=' + groups_filter.join(',');
}


function isDeb() {
  return document.location.href.indexOf("deb=1") > 0;
}
