{* Smarty *}

<div style="margin:5px">The top of your playlist (most recently shared songs):</div>
<table>
{section name=h loop=$head}
<tr>
  <td>
    <img src="http://img.youtube.com/vi/{$head[h].video_id}/2.jpg" width="35" height="20">
  </td>
  <td>
    <div class="small">
    <a href="./?head={$head[h].time+3}&play=1">{$head[h].title}</a>
    </div>
  </td>
  <td>
    <div class="small">
    {$head[h].minutes}:{$head[h].seconds}
    </div>
  </td>
  <td>
    <div class="small">
    by {$head[h].shared_by_name}
    </div>
  </td>
  <td>
    <div class="small">
    {$head[h].time|date_format:"%d %b, %l:%M %p"}
    </div>
  </td>
</tr>
{/section}
</table>