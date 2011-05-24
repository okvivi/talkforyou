<html>
  <title>A seamless playlist of all the songs that my friends share on Facebook</title>
  <script type="text/javascript" src="swfobject.js"></script>
  <script src='main.js?id=4'></script>
  <link rel="stylesheet" href="main.css?id=3" />
  <script type="text/javascript">
  {literal}
  var _gaq = _gaq || [];
  _gaq.push(['_setAccount', 'UA-71349-3']);
  _gaq.push(['_trackPageview']);

  (function() {
    var ga = document.createElement('script'); ga.type = 'text/javascript'; ga.async = true;
    ga.src = ('https:' == document.location.protocol ? 'https://ssl' : 'http://www') + '.google-analytics.com/ga.js';
    var s = document.getElementsByTagName('script')[0]; s.parentNode.insertBefore(ga, s);
  })();
  {/literal}
</script>
<meta http-equiv="Content-Type" content="text/html; charset=utf-8">
<body onLoad='javascript:runOnLoad()'>
<center>
<div class="playlist_container">
  <div class="playlist_header">
    <table width="100%">
      <td>
      <a href="http://www.facebook.com/apps/application.php?id=151610438237720" target="_blank">Leave feedback</a>
      </td><td align="right">
      <div id="fb-root"></div><script src="http://connect.facebook.net/en_US/all.js#appId=129592820452630&amp;xfbml=1"></script><fb:like href="http://talkforyou.me/" send="true" layout="button_count" width="170" show_faces="true" font=""></fb:like>
      </td>
    </table>
  </div>

  <div class="elevator_pitch">
  I wanted a seamless playlist with all the songs that my friends share on
  Facebook. So I've built this site.
  </div>

  {$content}

  {include file="footer.tpl"}

</div>
</center>
</body>
</html>