<?php

require_once 'vendor/autoload.php';


function googleClient()
{
    $google_client = new Google_Client();
    $google_client->setClientId('891292730897-315fn00uqjbq4ft530lr5jus2o9ol05g.apps.googleusercontent.com');
    $google_client->setClientSecret('WjvoFB_1RNXvBzBWiiz00uuR');
    $google_client->setRedirectUri('http://localhost/goauth-success');
    $google_client->addScope('email');
    $google_client->addScope('profile');
    session_start();
    return $google_client;
}

$google_client = googleClient();
$login_button = '';
if (isset($_GET['code'])) {
    $token = $google_client->fetchAccessTokenWithAuthCode(
        $_GET['code']
    );
    if (!isset($token['error'])) {
        $google_client->setAccessToken($token['access_token']);
        $_SESSION['access_token'] = $token['access_token'];
        $google_service = new Google_Service_Oauth2($google_client);
        $data = $google_service->userinfo->get();
        if (!empty($data['given_name'])) {
            $_SESSION['first_name'] = $data['given_name'];
        }
        if (!empty($data['family_name'])) {
            $_SESSION['last_name'] = $data['family_name'];
        }
        if (!empty($data['email'])) {
            $_SESSION['email'] = $data['email'];
        }
    }
}

if (!isset($_SESSION['access_token'])) {
    $login_button = '<a href="' . $google_client->createAuthUrl() . '">Google connexion</a>';
}


