<?php /* Smarty version 2.6.26, created on 2011-05-17 21:42:52
         compiled from playlist.tpl */ ?>
<?php require_once(SMARTY_CORE_DIR . 'core.load_plugins.php');
smarty_core_load_plugins(array('plugins' => array(array('modifier', 'date_format', 'playlist.tpl', 27, false),)), $this); ?>

<div style="margin:5px">The top of your playlist (most recently shared songs):</div>
<table>
<?php unset($this->_sections['h']);
$this->_sections['h']['name'] = 'h';
$this->_sections['h']['loop'] = is_array($_loop=$this->_tpl_vars['head']) ? count($_loop) : max(0, (int)$_loop); unset($_loop);
$this->_sections['h']['show'] = true;
$this->_sections['h']['max'] = $this->_sections['h']['loop'];
$this->_sections['h']['step'] = 1;
$this->_sections['h']['start'] = $this->_sections['h']['step'] > 0 ? 0 : $this->_sections['h']['loop']-1;
if ($this->_sections['h']['show']) {
    $this->_sections['h']['total'] = $this->_sections['h']['loop'];
    if ($this->_sections['h']['total'] == 0)
        $this->_sections['h']['show'] = false;
} else
    $this->_sections['h']['total'] = 0;
if ($this->_sections['h']['show']):

            for ($this->_sections['h']['index'] = $this->_sections['h']['start'], $this->_sections['h']['iteration'] = 1;
                 $this->_sections['h']['iteration'] <= $this->_sections['h']['total'];
                 $this->_sections['h']['index'] += $this->_sections['h']['step'], $this->_sections['h']['iteration']++):
$this->_sections['h']['rownum'] = $this->_sections['h']['iteration'];
$this->_sections['h']['index_prev'] = $this->_sections['h']['index'] - $this->_sections['h']['step'];
$this->_sections['h']['index_next'] = $this->_sections['h']['index'] + $this->_sections['h']['step'];
$this->_sections['h']['first']      = ($this->_sections['h']['iteration'] == 1);
$this->_sections['h']['last']       = ($this->_sections['h']['iteration'] == $this->_sections['h']['total']);
?>
<tr>
  <td>
    <img src="http://img.youtube.com/vi/<?php echo $this->_tpl_vars['head'][$this->_sections['h']['index']]['video_id']; ?>
/2.jpg" width="35" height="20">
  </td>
  <td>
    <div class="small">
    <a href="./?head=<?php echo $this->_tpl_vars['head'][$this->_sections['h']['index']]['time']+3; ?>
&play=1"><?php echo $this->_tpl_vars['head'][$this->_sections['h']['index']]['title']; ?>
</a>
    </div>
  </td>
  <td>
    <div class="small">
    <?php echo $this->_tpl_vars['head'][$this->_sections['h']['index']]['minutes']; ?>
:<?php echo $this->_tpl_vars['head'][$this->_sections['h']['index']]['seconds']; ?>

    </div>
  </td>
  <td>
    <div class="small">
    by <?php echo $this->_tpl_vars['head'][$this->_sections['h']['index']]['shared_by_name']; ?>

    </div>
  </td>
  <td>
    <div class="small">
    <?php echo ((is_array($_tmp=$this->_tpl_vars['head'][$this->_sections['h']['index']]['time'])) ? $this->_run_mod_handler('date_format', true, $_tmp, "%d %b, %l:%M %p") : smarty_modifier_date_format($_tmp, "%d %b, %l:%M %p")); ?>

    </div>
  </td>
</tr>
<?php endfor; endif; ?>
</table>