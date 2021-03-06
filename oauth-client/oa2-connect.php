<?php
require 'vendor/autoload.php';

use GuzzleHttp\Client;


function handleGoogleSuccess()
{
    $client = new Client([
        'timeout' => 2.0,
        'verify' => __DIR__ . '/usr/local/share/ca-certificates/cacert/'
    ]);
    try {
        $response = $client->request('GET', 'https://accounts.google.com/.well-known/openid-configuration');
        $discoveryJSON = json_decode((string)$response->getBody());
        $tokenEndpoint = $discoveryJSON->token_endpoint;
        $userinfoEndpoint = $discoveryJSON->userinfo_endpoint;
        $response = $client->request('POST', $tokenEndpoint, [
            'form_params' => [
                'code' => $_GET['code'],
                'client_id' => GOOGLE_ID,
                'client_secret' => GOOGLE_SECRET,
                'redirect_uri' => 'http://localhost:8081/oa2-connect.php',
                'grant_type' => 'authorization_code'
            ]
        ]);
        $accessToken = json_decode($response->getBody())->access_token;
        $response = $client->request('GET', $userinfoEndpoint, [
            'headers' => [
                'Authorization' => 'Bearer' . $accessToken
            ]
        ]);
        $response = json_decode($response);
        if ($response->email_verified === true) {
            session_start();
            $_SESSION['email'] = $response->email;
            header('Location: /secret.php');
            exit();
        }

    } catch (\GuzzleHttp\Exception\ClientException $exception) {
        dd($exception->getMessage());
    }
    dd((string)$response->getBody());

}
