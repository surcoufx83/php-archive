<?php

$Controller->get(array(
  'pattern' => '/login',
  'requiresAuthentication' => false,
  'fn' => 'ui_login'
));

$Controller->post(array(
  'pattern' => '/login',
  'requiresAuthentication' => false,
  'fn' => 'post_login'
));

$Controller->get(array(
  'pattern' => '/logout',
  'requiresAuthentication' => false,
  'fn' => 'ui_logout'
));

$Controller->get(array(
  'pattern' => '/settings',
  'fn' => 'ui_settings'
));

function ui_login() {
  global $OUT, $twig;
  $OUT['Page']['Scripts']['Custom'][] = 'auth-login';
  $OUT['Content'] = $twig->render('views/user/login.html.twig', $OUT);
} // ui_login()

function post_login() {
  global $Controller;

  $username = $password = null;
  $agreedStatement = false;
  $keepSession = false;
  $response = null;

  if (array_key_exists('loginUsername', $_POST)) {
    $username = $_POST['loginUsername'];
  }

  if (array_key_exists('loginPassword', $_POST)) {
    $password = $_POST['loginPassword'];
  }

  if (array_key_exists('keepSession', $_POST)) {
    $keepSession = ($_POST['keepSession'] == 'true');
  }

  if (array_key_exists('agreedStatement', $_POST)) {
    $agreedStatement = ($_POST['agreedStatement'] == 'true');
  }

  if ($password != null && $username != null) {
    if (!$Controller->loginWithPassword($username, $password, $keepSession, $agreedStatement, $response))
      $Controller->Dispatcher()->exitJson($response);
  }

  $response['isLoggedIn'] = $Controller->isAuthenticated();
  $Controller->Dispatcher()->exitJson($response);

} // post_login()

function ui_logout() {
  global $Controller;
  $Controller->logout();
  $Controller->Dispatcher()->forward('/');
} // ui_logout()

function ui_settings() {
  global $OUT, $twig;
  $OUT['Page']['Current'] = 'private:home';
  $OUT['Page']['CurrentSub'] = 'private:settings';
  $OUT['Page']['Heading1'] = 'Meine Einstellungen';
} // ui_settings()
