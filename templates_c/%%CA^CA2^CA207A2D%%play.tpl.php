<?php /* Smarty version 2.6.26, created on 2011-05-18 00:13:45
         compiled from play.tpl */ ?>
<?php require_once(SMARTY_CORE_DIR . 'core.load_plugins.php');
smarty_core_load_plugins(array('plugins' => array(array('modifier', 'date_format', 'play.tpl', 93, false),)), $this); ?>
<script language='javascript'>
    var currentFriend = <?php echo $this->_tpl_vars['current_friend']; ?>
;
    var myUrl = '<?php echo $this->_tpl_vars['my_url']; ?>
';
    var next_head = '<?php echo $this->_tpl_vars['next_head']; ?>
';
    var globalYtState = -2;
    var accessToken = '<?php echo $this->_tpl_vars['access_token']; ?>
';
    var fb_id = '<?php echo $this->_tpl_vars['fb_id']; ?>
';
    var app_id = '<?php echo $this->_tpl_vars['app_id']; ?>
';
    var user_name = '<?php echo $this->_tpl_vars['user_name']; ?>
';
</script>

<div id="fb-root"></div>
<script src="http://connect.facebook.net/en_US/all.js"></script>
<script>
  <?php echo '
  FB.init({
  '; ?>

    appId  : '<?php echo $this->_tpl_vars['app_id']; ?>
',
    status : true, // check login status
    cookie : true, // enable cookies to allow the server to access the session
    xfbml  : true  // parse XFBML
  <?php echo '
  });
  '; ?>

</script>

<div class="playlist_header">
  <div class="profile_photo">
    <img src="http://graph.facebook.com/<?php echo $this->_tpl_vars['user_id']; ?>
/picture?type=square"
        width="50" height="50">
  </div>
  Hello <b><?php echo $this->_tpl_vars['user_name']; ?>
</b><br>
  <div class="explanation">
    You're listening to the videos and music that your friends shared on
    facebook. If you leave it open it plays whatever is most recent and
    tries to find more shared songs from your friends.
  </div>
</div>

<table width="100%"><td>
<div class="video_title">
  <?php echo $this->_tpl_vars['video_title']; ?>

</div>
</td><td align="right">
    <div class="controls">
      <a onclick="javascript:controlRewind();">|&lt;&lt;</a> /
      <a onclick="javascript:controlNext();">Next song</a>
    </div>
</table>

<table width="680" cellspacing=0 cellpadding=0>
  <td width="450">
    <div class="video_container">
      <div id="ytapiplayer">
        You need Flash player 8+ and JavaScript enabled to view this video.
      </div>
      <script type="text/javascript">
      <?php echo '
        var params = { allowScriptAccess: "always" };
        var atts = { id: "myytplayer" };
      '; ?>

        swfobject.embedSWF(
            "http://www.youtube.com/e/<?php echo $this->_tpl_vars['video_id']; ?>
?enablejsapi=1&playerapiid=ytplayer",
            "ytapiplayer", "450", "330", "8", null, null, params, atts);
      </script>
    </div>

    <div class="like_bar">
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
        <img src="http://graph.facebook.com/<?php echo $this->_tpl_vars['shared_by_id']; ?>
/picture?type=square"
          width="50" heigth="50" class="profile_photo">
        From <?php echo $this->_tpl_vars['shared_by_name']; ?>

        <br><?php echo ((is_array($_tmp=$this->_tpl_vars['shared_time'])) ? $this->_run_mod_handler('date_format', true, $_tmp, "%d %b %H:%M") : smarty_modifier_date_format($_tmp, "%d %b %H:%M")); ?>

        <div class="play_count">play count: <?php echo $this->_tpl_vars['play_count']; ?>
</div>
      </div>
      <div class="separator"></div>
      <div class="coming_up_next">
        <b>The next few songs</b>

        <?php unset($this->_sections['song']);
$this->_sections['song']['name'] = 'song';
$this->_sections['song']['loop'] = is_array($_loop=$this->_tpl_vars['coming_up']) ? count($_loop) : max(0, (int)$_loop); unset($_loop);
$this->_sections['song']['max'] = (int)4;
$this->_sections['song']['show'] = true;
if ($this->_sections['song']['max'] < 0)
    $this->_sections['song']['max'] = $this->_sections['song']['loop'];
$this->_sections['song']['step'] = 1;
$this->_sections['song']['start'] = $this->_sections['song']['step'] > 0 ? 0 : $this->_sections['song']['loop']-1;
if ($this->_sections['song']['show']) {
    $this->_sections['song']['total'] = min(ceil(($this->_sections['song']['step'] > 0 ? $this->_sections['song']['loop'] - $this->_sections['song']['start'] : $this->_sections['song']['start']+1)/abs($this->_sections['song']['step'])), $this->_sections['song']['max']);
    if ($this->_sections['song']['total'] == 0)
        $this->_sections['song']['show'] = false;
} else
    $this->_sections['song']['total'] = 0;
if ($this->_sections['song']['show']):

            for ($this->_sections['song']['index'] = $this->_sections['song']['start'], $this->_sections['song']['iteration'] = 1;
                 $this->_sections['song']['iteration'] <= $this->_sections['song']['total'];
                 $this->_sections['song']['index'] += $this->_sections['song']['step'], $this->_sections['song']['iteration']++):
$this->_sections['song']['rownum'] = $this->_sections['song']['iteration'];
$this->_sections['song']['index_prev'] = $this->_sections['song']['index'] - $this->_sections['song']['step'];
$this->_sections['song']['index_next'] = $this->_sections['song']['index'] + $this->_sections['song']['step'];
$this->_sections['song']['first']      = ($this->_sections['song']['iteration'] == 1);
$this->_sections['song']['last']       = ($this->_sections['song']['iteration'] == $this->_sections['song']['total']);
?>
          <div class="coming_up_song small">
            <a href="./?head=<?php echo $this->_tpl_vars['coming_up'][$this->_sections['song']['index']]['next_time']; ?>
&play=1">
            <img src="http://img.youtube.com/vi/<?php echo $this->_tpl_vars['coming_up'][$this->_sections['song']['index']]['video_id']; ?>
/2.jpg"
                width="50" height="30" style="float:left;margin-right:10px;" border=0>
            </a>
            from <?php echo $this->_tpl_vars['coming_up'][$this->_sections['song']['index']]['shared_by_name']; ?>

            <div class="play_count">play count: <?php echo $this->_tpl_vars['coming_up'][$this->_sections['song']['index']]['play_count']; ?>
</div>
          </div>
          <div class="separator"></div>
        <?php endfor; endif; ?>
      </div>

      <div class="playlist_stats">
        <b>Current stats (always changing)</b><br>
        Songs listened to: <?php echo $this->_tpl_vars['played_count']; ?>
<br>
        Unplayed songs: <?php echo $this->_tpl_vars['unplayed_count']; ?>
<br>
      </div>

      <div class="settings">
        <b>Settings (click to change):</b>
        <br><a id="shuffle" onclick="javascript:togglePref('shuffle');"><?php echo $this->_tpl_vars['shuffle_text']; ?>
</a>
        <br><a id="unplayed" onclick="javascript:togglePref('unplayed');"><?php echo $this->_tpl_vars['unplayed_text']; ?>
</a>
        <br><a id="music" onclick="javascript:togglePref('music');"><?php echo $this->_tpl_vars['music_text']; ?>
</a>
      </div>
    </div>
  </td>
</table>

<div id="friends_status">&nbsp;</div>

<?php echo $this->_tpl_vars['playlist']; ?>
