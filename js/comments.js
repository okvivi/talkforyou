// This file contains all the functionality that is related to likes and
// comments at the bottom of the file. I guess it's rather all the Javascript
// related to the facebook API.


/**
 * The entry point for showing comments under the video, this method is called
 * right after load.
 */
function maybeShowComments() {
  var target = document.getElementById('interactions');

  // fb_id is the id of the current video on facebook. It's undefined if we
  // have not crawled it already.
  if (!fb_id) {
    target.style.display = "none";
    var likeBar = document.getElementById("like_bar");
    likeBar.style.display = "none";
    return;
  }

  FB.api("/" + fb_id, function(response) {
    log('Getting object from facebook response: ');
    log(response);

    var commentLink = getActionFromItem('Comment', response);
    var likeLink = getActionFromItem('Like', response);

    if (response.likes && likeLink) {
      likeArray = response.likes.data;
      renderLikes(likeArray);
    }
    if (response.comments && commentLink) {
      renderComments(response.comments.data);
    }

    maybeUpdateShareMessage(response);
  });
}


/**
 * If a sharing message is present in the object, update it on the page.
 * @param response
 */
function maybeUpdateShareMessage(response) {
  var el = document.getElementById('share_message');
  if (!el) return;

  var message = response.message || response.status;
  if (message && message.indexOf('youtube.com') < 0) {
    el.innerHTML = ": \"" + message + "\"";
  }
}


/**
 * Extracts the link for a specific action from an item. I'm really not sure
 * what this is used for, I'm guessing that it's the signal that you or cannot
 * comment on an item.
 *
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
 * Renders the likes array in the comments section. The array passed in as a
 * parameter is the one we fetched from the Facebook API.
 *
 * @param likesArray
 */
function renderLikes(likesArray) {
  var target = document.getElementById('likes');
  if (likesArray.length == 0) {
    target.style.display = "none";
    return;
  }
  target.style.display = "";

  maybeUpdateLikeButtonText(likesArray);

  // Put the html in this array.
  var out = [];
  for (var i = 0; i < likesArray.length; i++) {
    out.push("<span class='comment_name'>" +
        "<a href='http://www.facebook.com/profile.php?id=" +
        likesArray[i].id + "' target=_blank>" + likesArray[i].name +
        "</a></span>");
  }
  var suffix = likesArray.length == 1 ? " likes this" : " like this.";
  target.innerHTML = out.join(", ") + suffix;
}


/**
 * Checks if the current user is in the likes array, and if yes it changes the
 * text of the Like button to say "Unlike".
 * @param likesArray
 */
function maybeUpdateLikeButtonText(likesArray) {
  for (var i = 0; i < likesArray.length; i++) {
    // If the current user is in the array, change what the "Like" button says.
    if (likesArray[i].id == session.uid) {
      var likeButton = document.getElementById('like_button');
      likeButton.innerHTML = '<a href="javascript:like();">Unlike</a>';
    }
  }
}


/**
 * Renders the likes array in the comments section.
 * @param commentsArray
 */
function renderComments(commentsArray) {
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
    out.push("<span class='comment_name'>" +
        "<a href='http://www.facebook.com/profile.php?id=" +
        commentsArray[i].from.id + "' target=_blank>" +
        commentsArray[i].from.name +
        "</a></span> ");
    out.push(commentsArray[i].message);
    out.push("</div>");
  }
  target.innerHTML = out.join("");
}


/**
 * Handles the user clicking the like button. If the user is not logged in
 * anymore it prompts the user to log in, otherwise it just calls
 * doLikeAction()
 */
function like() {
  // Just log stuff for when I find that it doesn't work.
  log('The current session object:');
  log(session);

  // TODO(vivi): Add this check for the session in comments too.
  // We should understand if this is indeed why Likes does not work, I was not
  // able to reproduce.
  if (!session) {
    FB.login(function(response) {
      if (response.session) {
        doLikeAction();
      }
    });
  } else {
    doLikeAction();
  }
}


/**
 * Once we are sure that the user is logged in, actually do the like action.
 */
function doLikeAction() {
  var likeButton = document.getElementById('like_button');
  var method = null;
  if (likeButton.innerHTML == '<a href="javascript:like();">Unlike</a>') {
    method = 'delete';
  } else {
    method = 'post';
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
 */
function publishLike(method) {
  log('posting like');

  FB.api('/' + fb_id + '/likes', method, function(response) {
    if (response.error) {
      return;
    }

    var likeButton = document.getElementById('like_button');
    likeButton.innerHTML = method == 'delete' ?
        '<a href="javascript:like();">Like</a>' :
        '<a href="javascript:like();">Unlike</a>';

    if (method == 'delete') {
      // I just deleted a like.
      removeMeFromLikeArray();
    } else if (method == 'post') {
      addMeToLikeArray();
    }
    // Update the likes list so that I also show up in there.
    renderLikes(likeArray, null);
  });
}


/**
 * Goes through the likes array and removes the current user.
 */
function removeMeFromLikeArray() {
  var newArray = [];
  for (var i = 0; i < likeArray.length; i++) {
    if (likeArray[i].id != session.uid) {
      newArray.push(likeArray[i]);
    }
  }
  likeArray = newArray;
}


/**
 * Adds the current user to the likes array so we can render an updated list.
 */
function addMeToLikeArray() {
  likeArray.push({
        id: session.uid,
        name: user_name
      });
}


/**
 * Publishes a comment to facebook. We currently do not support deleting a
 * comment.
 * @param value
 */
function publishComment(value) {
  // Make sure double-click does not result in publishing two reviews.
  if (commentPending) return;
  commentPending = true;

  FB.api('/' + fb_id + '/comments', 'post', { message: value},
      function(response) {
    if (response.error) {
      return;
    }

    // Replace the comment area with the html for the comment I have just
    // pushed to facebook.
    var comment = document.getElementById('comment_area');
    var parent = comment.parentNode;

    parent.innerHTML = '<div class=fb_comment>' +
        '<img src="http://graph.facebook.com/' + session.uid +
        '/picture?type=square" ' +
        'width="25" heigth="25" class="profile_photo">' +
        '<span class=comment_name>' +
        '<a href="http://www.facebook.com/" target=_blank>' + user_name +
        '</a></span> ' +
        value + '</div>';

    commentPending = false;
  });
}


/**
 * Opens a popup to request write permission from the user.
 *
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


/**
 * Listener for when the text area for comments gets focus. Used to delete
 * the text in there.
 */
function commentAreaFocus() {
  var area = document.getElementById("comment_area");
  area.value = "";
}


/**
 * Listener for each key a user types in the comments area. Used to determine
 * when the user hits enter, which will result in posting the comment.
 */
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