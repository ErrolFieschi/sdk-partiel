<?php
include "disc.php";
include "goog.php";
require __DIR__ . '/vendor/autoload.php';
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
ini_set('max_execution_time', 300);

use GuzzleHttp\Client;

const CLIENT_ID = "client_6070546c6aba63.16480463";
const CLIENT_SECRET_ = "38201ad253c323a79d9108f4588bbc62d2e1a5c6";


const CLIENT_FBID = "1117408915453002";
const CLIENT_FBSECRET = "24612481e62ba69376f162f828ca4d1e";


const CLIENT_FBID_ = "280112260228105";
const CLIENT_FBSECRET_ = "3e5833b07ed52a57c0cad5c745cd1061";

//session_start();

function getUser($params)
{
    $result = file_get_contents("http://oauth-server:8081/token?"
        . "client_id=" . CLIENT_ID
        . "&client_secret=" . CLIENT_SECRET
        . "&" . http_build_query($params));
    $token = json_decode($result, true)["access_token"];
    // GET USER by TOKEN
    $context = stream_context_create([
        'http' => [
            'method' => "GET",
            'header' => "Authorization: Bearer " . $token
        ]
    ]);
    $result = file_get_contents("http://oauth-server:8081/me", false, $context);
    $user = json_decode($result, true);
    var_dump($user);
}

// début get google




function handleLogin($login_button)
{
    if (count($_SESSION) > 0) {
        echo '<a href="/disconnect">Disconnect</a>';
        echo "<br>";

        if (isset($_SESSION['first_name'])) {
            echo $_SESSION['first_name'];
            echo "<br>";
        }

        if (isset($_SESSION['last_name'])) {
            echo $_SESSION['last_name'];
            echo "<br>";
        }

        if (isset($_SESSION['email'])) {
            echo $_SESSION['email'];
            echo "<br>";
        }

    } else {

        echo '<h1>Login with Auth-Code</h1>';
        echo $login_button;
        echo "<br>";
        echo "<a href='http://localhost:8081/auth?"
            . "response_type=code"
            . "&client_id=" . CLIENT_ID
            . "&scope=basic&state=dsdsfsfds'>Login with oauth-server</a>";
        echo "<br>";

        echo "<a href='https://www.facebook.com/v2.10/dialog/oauth?"
            . "response_type=code"
            . "&client_id=" . CLIENT_FBID
            . "&scope=email&state=dsdsfsfds&redirect_uri=http://localhost:8082/fbauth-success'>Login with Facebook</a>";

        echo "<br>";
        /* echo '<a href="https://discord.com/api/oauth2/authorize?scope=email'
             . '&access_type=online'
             . '&redirect_uri=' . urlencode("http://localhost:8082/discauth-success")
             . '&response_type=code'
             . '&client_id=' . DISC_ID . '">Login with discord</a>';

        */
        if (!empty($_SESSION['name'])) {
            echo "vous êtes connectez " . $_SESSION['name'] . " avec l'email " . $_SESSION['email'];
        } else {
            echo '<a href="https://discord.com/api/oauth2/authorize?client_id=865885940572094465&redirect_uri=http%3A%2F%2Flocalhost%2Fdiscauth-success&response_type=code&scope=email">Discord</a>';
            echo "<br>";
        }
    }


}

function logout()
{
    session_unset();
    session_destroy();
    //var_dump($_SESSION);
    header('Location: /login');
}


function handleSuccess()
{
    ["code" => $code, "state" => $state] = $_GET;
    // ECHANGE CODE => TOKEN
    getUser([
        "grant_type" => "authorization_code",
        "code" => $code
    ]);
}


function handleFBSuccess()
{
    ["code" => $code, "state" => $state] = $_GET;
    // ECHANGE CODE => TOKEN
    $result = file_get_contents("https://graph.facebook.com/oauth/access_token?"
        . "client_id=" . CLIENT_FBID
        . "&client_secret=" . CLIENT_FBSECRET
        . "&redirect_uri=http://localhost:8082/fbauth-success"
        . "&grant_type=authorization_code&code={$code}");
    $token = json_decode($result, true)["access_token"];
    // GET USER by TOKEN
    $context = stream_context_create([
        'http' => [
            'method' => "GET",
            'header' => "Authorization: Bearer " . $token
        ]
    ]);
    $result = file_get_contents("https://graph.facebook.com/me?fields=id,name,email", false, $context);
    $user = json_decode($result, true);
    var_dump($user);
}

function handleError()
{
    echo "refusé";
}

/**
 * AUTH_CODE WORKFLOW
 * => GET Code <- Générer le lien /auth (login)
 * => EXCHANGE Code <> Token (auth-success)
 * => GET USER by Token (auth-success)
 */
$route = strtok($_SERVER["REQUEST_URI"], '?');
switch ($route) {

    case '/discauth-success':
        discordConnect();
        var_dump($_SESSION);
        break;

    case '/goauth-success':
        header('Location: /login');
        break;

    case '/disconnect':
        logout();
        break;

    case '/login':
        handleLogin($login_button);

        break;
    case '/auth-success':
        handleSuccess();
        break;
    case '/goauth-success':
        //header('Location /login');
        break;
    case '/fbauth-success':
        handleFBSuccess();
        break;
    case '/auth-error':
        handleError();
        break;
    case '/password':
        if ($_SERVER['REQUEST_METHOD'] === "GET") {
            echo "<form method='POST'>";
            echo "<input name='username'>";
            echo "<input name='password'>";
            echo "<input type='submit' value='Log with oauth'>";
            echo "</form>";
        } else {
            ['username' => $username, 'password' => $password] = $_POST;
            getUser([
                'grant_type' => "password",
                'username' => $username,
                'password' => $password
            ]);
        }
        break;
    default:
        http_response_code(404);
}
