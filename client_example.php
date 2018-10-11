<?php
session_start();
if (!isset($_GET['code']) && !isset($_SESSION['token'])) {
    $param = [
        'client_id' => '3',
        'redirect_uri' => 'redirect_uri=https://yourapp.com/api/callback',
        'response_type' => 'code',
        'scope' => 'user profile',
    ];
    http_redirect('https://ldap.tp.edu.tw/oauth/authorize', $param);
    exit;
} elseif (!isset($_SESSION['token'])) {
    $param = [
        'grant_type' => 'authorization_code',
        'client_id' => '3',
        'client_secret' => '5uyghc0DpeRJHsv43Di567fjasuy083kf6hiDAT',
        'redirect_uri' => 'redirect_uri=https://yourapp.com/api/callback',
        'code' => _GET[‘code’],
    ];
    $response = http_post_fields('https://ldap.tp.edu.tw/oauth/token', $param);
    $token = json_decode($response);
    ini_set("session.gc_maxlifetime", $token->expires_in);
    $_SESSION['token'] = $token->access_token;
    $_SESSION['refresh'] = $token->refresh_token;
    $_SESSION['expire'] = time() + $token->expires_in;
} elseif (time() > $_SESSION['expire']) {
    $param = [
        'grant_type' => 'refresh_token',
        'refresh_token' => $_SESSION['refresh'],
        'client_id' => '3',
        'client_secret' => '5uyghc0DpeRJHsv43Di567fjasuy083kf6hiDAT',
        'scope' => 'user profile',
    ];
    $response = http_post_fields('https://ldap.tp.edu.tw/oauth/token', $param);
    $token = json_decode($response);
    $_SESSION['token'] = $token->access_token;
    $_SESSION['refresh'] = $token->refresh_token;
    $_SESSION['expire'] = time() + $token->expires_in;
}
$header = [
    'Authorization' => 'Bearer '.$_SESSION['token'],
];
$response = http_get('https://ldap.tp.edu.tw/api/user', $header);
$user = json_decode($response);
$response = http_get('https://ldap.tp.edu.tw/api/profile', $header);
$profile = json_decode($response);

//logout
http_redirect('https://ldap.tp.edu.tw/api/logout');
?>
