
var FRIENDS_TIMEOUT = 10000;
var friendsArray = null;
var session = null;
var permissions = null;
var firstFocus = true;
var commentPending = false;
var likeArray = [];

/**
 * Starts the background processes after the page loads. This is the parsing
 * of the friends data and showing comments on the video.
 */
function runOnLoad() {
  if (window.accessToken === undefined) return;

  FRIENDS_TIMEOUT = unplayed_count > 30 ? 20000 : 10000;

  FB.getLoginStatus(function(status) {
    session = status.session;
    window.console.log(session);

    FB.api("/me/friends", handleFriendsListBack);
    FB.api("/me/permissions", function(response) {
      permissions = response.data[0];
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
    window.console.log('Error getting friends');
    return;
  }
  friendsArray = response.data;
  // The first user we are crawling after the friends list is back is ME.
  setTimeout('triggerCrawlFriend(-1)', 500);
}

function onYouTubePlayerReady(playerId) {
  var ytplayer = document.getElementById("myytplayer");
  if (ytplayer) {
    if (!isDeb()) {
      ytplayer.playVideo();
    }
    ytplayer.addEventListener("onStateChange", "onytplayerStateChange");
  }
}

function onytplayerStateChange(newState) {
  globalYtState = newState;
  if (newState == 0) {
    document.location.href = myUrl + '?play=1&head=' + next_head
        + '&fid=' + currentFriend;
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
  document.location.href = myUrl + '?play=1&rehead=1';
}

function controlNext() {
  document.location.href = myUrl + '?play=1&head=' + next_head;
}


function isDeb() {
  return document.location.href.indexOf("deb=1") > 0;
}

// ------------ some utility functions

function sendPayload_(url, opt_callback, opt_method, opt_payload) {
  var method = opt_method || "GET";

  var xmlhttp = null;
  if (window.XMLHttpRequest) {// code for all new browsers
    xmlhttp = new XMLHttpRequest();
  } else if (window.ActiveXObject) {// code for IE5 and IE6
    xmlhttp = new ActiveXObject("Microsoft.XMLHTTP");
  }

  if (xmlhttp != null) {
    xmlhttp.onreadystatechange = onPayloadResponse_(xmlhttp, opt_callback);
    xmlhttp.open(method, url, true);
  if (opt_method == "POST") {
    xmlhttp.setRequestHeader("Content-type",
                             "application/x-www-form-urlencoded");
  }

    xmlhttp.send(opt_payload);
  }
}


function onPayloadResponse_(xmlhttp, opt_callback, opt_err) {
  return function() {
    if (xmlhttp.readyState == 4) {// 4 = "loaded"
      if (xmlhttp.status == 200) {// 200 = OK
        if (opt_callback) {
          opt_callback(xmlhttp.responseText);
        }
      } else {
        if (opt_err) {
          opt_err(xmlhttp);
        }
      }
    }
  }
}


// ------------ Like functionality ------------------

/**
 * Attempts to show comments on the video. By this point we know for sure that
 * the FB api is loaded and authenticated. all we need to do is call.
 */
function maybeShowComments() {
  var target = document.getElementById('interactions');
  if (!fb_id) {
    target.style.display = "none";
    var likeBar = document.getElementById("like_bar");
    likeBar.style.display = "none";
    return;
  }

  FB.api("/" + fb_id, function(response) {
    var commentLink = getActionFromItem('Comment', response);
    var likeLink = getActionFromItem('Like', response);

    if (response.likes && likeLink) {
      likeArray = response.likes.data;
      renderLikes(likeArray, likeLink);
    }
    if (response.comments && commentLink) {
      renderComments(response.comments.data, commentLink);
    }

    var likeButton = document.getElementById('like_button');
    if (!likeLink) {
      likeButton.style.display = "hidden";
    } else {
      likeButton.likeLink = likeLink;
    }
  });
}


/**
 * Extracts the link for a specific action from an item.
 * @param {string} action The action, Comment or Like.
 * @param {Object} item A facebook object gotten from the graph api.
 */
function getActionFromItem(action, item) {
  for (var i = 0; i < item.actions.length; i++) {
    if (item.actions[i].name == action) {
      return item.actions[i].link;
    }
  }
  return null;
}


/**
 * Renders the likes array in the comments section.
 * @param likesArray
 */
function renderLikes(likesArray, likeLink) {
  var target = document.getElementById('likes');
  if (likesArray.length == 0) {
    target.style.display = "none";
    return;
  }

  var out = [];
  for (var i = 0; i < likesArray.length; i++) {
    out.push("<span class='comment_name'>" + likesArray[i].name + "</span>");

    if (likesArray[i].id == session.uid) {
      var likeButton = document.getElementById('like_button');
      likeButton.innerHTML = '<a href="javascript:like();">Unlike</a>';
    }
  }

  var suffix = " like this.";
  if (likesArray.length == 1) {
    suffix = " likes this.";
  }

  target.innerHTML = out.join(", ") + suffix;
}

/**
 * Renders the likes array in the comments section.
 * @param commentsArray
 */
function renderComments(commentsArray, commentLink) {
  var target = document.getElementById('comments');
  if (commentsArray.length == 0) {
    target.style.display = "none";
    return;
  }

  var out = [];
  for (var i = 0; i < commentsArray.length; i++) {
    out.push("<div class=fb_comment>");
    out.push('<img src="http://graph.facebook.com/' +
        commentsArray[i].from.id + '/picture?type=square" ' +
        'width="25" heigth="25" class="profile_photo">');
    out.push("<span class='comment_name'>" + commentsArray[i].from.name +
        "</span> ");
    out.push(commentsArray[i].message);
    out.push("</div>");
  }

  target.innerHTML = out.join("");
}

/**
 * Handles the user clicking the like button.
 */
function like() {
  var likeButton = document.getElementById('like_button');
  var method = null;
  if (likeButton.innerHTML == '<a href="javascript:like();">Unlike</a>') {
    method = 'delete';
  }

  if (!permissions['publish_stream']) {
    requestWritePermission(function(response) {
      publishLike(method);
    });
  } else {
    publishLike(method);
  }
}

/**
 * Makes the API call for a like (or for deleting a like).
 * @param opt_method
 */
function publishLike(opt_method) {
  window.console.log('posting');
  var method = opt_method ? opt_method : 'post';
  FB.api('/' + fb_id + '/likes', method, function(response) {
    if (response.error) {
      return;
    }

    var likeButton = document.getElementById('like_button');
    likeButton.innerHTML = opt_method ?
        '<a href="javascript:like();">Like</a>' :
        '<a href="javascript:like();">Unlike</a>';

    if (opt_method) {
      // I just deleted a like.
      removeMeFromLikeArray();
    } else {
      addMeToLikeArray();
    }
    renderLikes(likeArray, null);
  });
}

function removeMeFromLikeArray() {
  var newArray = [];
  for (var i = 0; i < likeArray.length; i++) {
    if (likeArray[i].id != session.uid) {
      newArray.push(likeArray[i]);
    }
  }
  likeArray = newArray;
}

function addMeToLikeArray() {
  likeArray.push({
        id: session.uid,
        name: user_name
      });
}

function publishComment(value, opt_method) {
  if (commentPending) return;
  commentPending = true;

  var method = opt_method ? opt_method : 'post';
  FB.api('/' + fb_id + '/comments', method, {
        message: value
      }, function(response) {
    if (response.error) {
      return;
    }

    var comment = document.getElementById('comment_area');
    var parent = comment.parentNode;

    parent.innerHTML = '<div class=fb_comment>' +
        '<img src="http://graph.facebook.com/' + session.uid +
        '/picture?type=square" ' +
        'width="25" heigth="25" class="profile_photo">' +
        '<span class=comment_name>' + user_name + '</span> ' +
        value + '</div>';

    commentPending = false;
  });
}

/**
 * Opens a popup to request write permission from the user.
 * @param callback
 */
function requestWritePermission(callback) {
  FB.ui({
    method: 'oauth',
    display: 'popup',
    scope: 'publish_stream',
    client_id: app_id,
    redirect_uri: myUrl + '/close.php'
  }, function(response) {
    callback(response);
  });
}

function commentAreaFocus() {
  var area = document.getElementById("comment_area");
  area.value = "";
}

function commentAreaKeyUp() {
  var area = document.getElementById("comment_area");
  var value = area.value;
  if (value.indexOf("\n") <= 0) {
    return;
  }
  value = value.substr(0, value.indexOf("\n"));
  value += " (\u2710http://talkforyou.me)";

  if (!permissions['publish_stream']) {
    requestWritePermission(function(response) {
      publishComment(value);
    });
  } else {
    publishComment(value);
  }
}