<?php

function discordConnect()
{
    ini_set('display_errors', 1);
    ini_set('display_startup_errors', 1);
    ini_set('max_execution_time', 300);

    error_reporting(E_ALL);

    define('OAUTH2_CLIENT_ID', '865885940572094465');
    define('OAUTH2_CLIENT_SECRET', 'PXB0yiwxBKSzjuJ5UI4HTvBdljDtAbch');

    $authorizeURL = 'https://discord.com/api/oauth2/authorize';
    $tokenURL = 'https://discord.com/api/oauth2/token';
    $apiURLBase = 'https://discord.com/api/users/@me';

    //session_start();

// Start the login process by sending the user to Discord's authorization page
    if (get('action') == 'login') {

        $params = array(
            'client_id' => OAUTH2_CLIENT_ID,
            'redirect_uri' => 'http://localhost/discauth-success',
            'response_type' => 'code',
            'scope' => 'identify guilds'
        );

        // Redirect the user to Discord's authorization page
        header('Location: https://discord.com/api/oauth2/authorize' . '?' . http_build_query($params));
        die();
    }


// When Discord redirects the user back here, there will be a "code" and "state" parameter in the query string
    if (get('code') ) {
        // Exchange the auth code for a token
        $token = apiRequest($tokenURL, array(
            "grant_type" => "authorization_code",
            'client_id' => OAUTH2_CLIENT_ID,
            'client_secret' => OAUTH2_CLIENT_SECRET,
            'redirect_uri' => 'http://localhost/discauth-success',
            'code' => get('code')
        ));
        $logout_token = $token->access_token;
        $_SESSION['access_token'] = $token->access_token;

    }

    if (session('access_token')) {

        $user = apiRequest($apiURLBase);
        $_SESSION['first_name'] = $user->username;
        $_SESSION['email'] = $user->email;

        header('Location: /login');

        echo '<h4>Bienvenue ' . $_SESSION['first_name'] . '</h4>';
        echo '<br><p>Email : ' . $_SESSION['email'] . '</p>';

    } else {
        echo '<h3>Not logged in</h3>';
        echo '<p><a href="?action=login">Log In</a></p>';
    }

}

function get($key, $default = NULL)
{
    return array_key_exists($key, $_GET) ? $_GET[$key] : $default;
}

function session($key, $default = NULL)
{
    return array_key_exists($key, $_SESSION) ? $_SESSION[$key] : $default;
}

function apiRequest($url, $post = FALSE, $headers = array())
{
    $ch = curl_init($url);
    curl_setopt($ch, CURLOPT_IPRESOLVE, CURL_IPRESOLVE_V4);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, TRUE);
    $response = curl_exec($ch);
    if ($post)
        curl_setopt($ch, CURLOPT_POSTFIELDS, http_build_query($post));

    $headers[] = 'Accept: application/json';

    if (session('access_token'))
        $headers[] = 'Authorization: Bearer ' . session('access_token');

    curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);

    $response = curl_exec($ch);

    return json_decode($response);
}

?>
