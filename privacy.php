<?php
require_once('facebook.php');
require_once('top.php');
require_once('functions.php');


$c = new Smarty();
$t = new Smarty();
$t->assign('content', $c->fetch('privacy.tpl'));
$t->display('page.tpl');

require_once('bottom.php');

?>