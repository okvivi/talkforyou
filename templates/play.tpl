{* Smarty *}
<script language='javascript'>
    var currentFriend = {$current_friend};
    var myUrl = '{$my_url}';
    var next_head = '{$next_head}';
    var globalYtState = -2;
    var accessToken = '{$access_token}';
    var fb_id = '{$fb_id}';
    var app_id = '{$app_id}';
    var user_name = '{$user_name}';
    var unplayed_count = {$unplayed_count};
</script>

<div id="fb-root"></div>
<script src="http://connect.facebook.net/en_US/all.js"></script>
<script>
  {literal}
  FB.init({
  {/literal}
    appId  : '{$app_id}',
    status : true, // check login status
    cookie : true, // enable cookies to allow the server to access the session
    xfbml  : true  // parse XFBML
  {literal}
  });
  {/literal}
</script>

<div class="playlist_header">
  <div class="profile_photo">
    <img src="http://graph.facebook.com/{$user_id}/picture?type=square"
        width="50" height="50">
  </div>
  Hello <b>{$user_name}</b><br>
  <div class="explanation">
    You're listening to the videos and music that your friends shared on
    facebook. If you leave it open it plays whatever is most recent and
    tries to find more shared songs from your friends.
  </div>
</div>

<table width="100%"><td>
<div class="video_title">
  {$video_title}
</div>
</td><td align="right">
    <div class="controls">
      <a onclick="javascript:controlRewind();">|&lt;&lt;</a> /
      <a onclick="javascript:controlNext();">Next song</a>
    </div>
</table>

<table width="680" cellspacing=0 cellpadding=0>
  <td width="450" valign="top">
    <div class="video_container">
      <div id="ytapiplayer">
        You need Flash player 8+ and JavaScript enabled to view this video.
      </div>
      <script type="text/javascript">
      {literal}
        var params = { allowScriptAccess: "always" };
        var atts = { id: "myytplayer" };
      {/literal}
        swfobject.embedSWF(
            "http://www.youtube.com/e/{$video_id}?enablejsapi=1&playerapiid=ytplayer",
            "ytapiplayer", "450", "330", "8", null, null, params, atts);
      </script>
    </div>

    <div class="like_bar" id="like_bar">
      Posted a while ago -
      <span id="like_button">
        <a href="javascript:like();">Like</a>
      </span>
    </div>
    <div id="interactions">
      <div id="likes" class="likes"></div>
      <div id="comments"></div>
      <div id="icomment" class="icomment">
        <textarea rows=1 cols=50 id='comment_area'
          onFocus="javascript:commentAreaFocus();"
          onKeyUp="javascript:commentAreaKeyUp();">Write a comment...</textarea>
        <div class="small" style="color:#CCC">Enter will submit</div>
      </div>
    </div>

  </td>
  <td valign="top">
    <div class="right_column">
      <div class="shared_by">
        <img src="http://graph.facebook.com/{$shared_by_id}/picture?type=square"
          width="50" heigth="50" class="profile_photo">
        From {$shared_by_name}
        <br>{$shared_time|date_format:"%d %b %H:%M"}
        <div class="play_count">play count: {$play_count}</div>
      </div>
      <div class="separator"></div>
      <div class="coming_up_next">
        <b>The next few songs</b>

        {section name=song loop=$coming_up max=4}
          <div class="coming_up_song small">
            <a href="./?head={$coming_up[song].next_time}&play=1">
            <img src="http://img.youtube.com/vi/{$coming_up[song].video_id}/2.jpg"
                width="50" height="30" style="float:left;margin-right:10px;" border=0>
            </a>
            from {$coming_up[song].shared_by_name}
            <div class="play_count">play count: {$coming_up[song].play_count}</div>
          </div>
          <div class="separator"></div>
        {/section}
      </div>

      <div class="playlist_stats">
        <b>Current stats (always changing)</b><br>
        Songs listened to: {$played_count}<br>
        Unplayed songs: {$unplayed_count}<br>
      </div>

      <div class="settings">
        <b>Settings (click to change):</b>
        <br><a id="shuffle" onclick="javascript:togglePref('shuffle');">{$shuffle_text}</a>
        <br><a id="unplayed" onclick="javascript:togglePref('unplayed');">{$unplayed_text}</a>
        <br><a id="music" onclick="javascript:togglePref('music');">{$music_text}</a>
      </div>
    </div>
  </td>
</table>

<div id="friends_status">&nbsp;</div>

{$playlist}
