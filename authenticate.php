<?php

$session = $facebook->getSession();

if ($session) {
  try {
    $user = $facebook->api("/me");
  } catch (FacebookApiException $fae) {
    error_log($fae);
    die();
  }
} else {
  if ($authenticateOrDie) {
    die();
  }
  login();
}

function login() {
  global $facebook;
  global $scope;

  $loginUrl = $facebook->getLoginUrl(array('req_perms' => $scope));
  header("Location: " . $loginUrl);
}

?>