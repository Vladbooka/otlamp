<?php
/**
 * Входная точка - /local/oauth/login.php?client_id=test&response_type=code 
 * Файл имитирует приложение, которые пытается авторизовать пользователя через OAUTH
 * 
 * На эту страницу пользователь редиректится автоматически
 * Этот url необходимо указать в настройках в redirect_url
 */
// require_once('../../../config.php');
// error_reporting(-1);
// $code = optional_param('code', 0, PARAM_ALPHANUM);
// $body = '{
// 	"code": "'.$code.'",
// 	"client_id": "test",
// 	"client_secret": "00ad31490caa1ee6e226bf3daf92327c9080b597f4c19bbf",
// 	"grant_type": "authorization_code",
// 	"scope": "user_info"
// }';

// echo "\n __GETTING_ACCESS_TOKEN__ \n";

// // получение access_token
// $ch = curl_init(new moodle_url('/local/oauth/token.php'));
// curl_setopt($ch, CURLOPT_CUSTOMREQUEST, "POST");
// curl_setopt($ch, CURLOPT_POSTFIELDS, $body);
// curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
// curl_setopt($ch, CURLOPT_HTTPHEADER, array(
//     'Content-Type: application/json',
//     'Content-Length: ' . strlen($body))
//         );       

// $data = curl_exec($ch);
// print_object($data);
// var_dump(curl_error($ch));
// if (!empty(curl_error($ch))) {
//     die;
// }
// curl_close($ch);

// // пришедшие данные с access_token
// $data = json_decode($data);
// print_object($data);

// // получение пользовательских данных
// $body2 = '{
// 	"access_token": "'.$data->access_token.'"
// }';

// print_object(json_decode($body2));

// echo "\n __GETTING_USER_INFO__ \n";

// // получение пользовательских данных по access_token
// $ch2 = curl_init(new moodle_url('/local/oauth/user_info.php'));
// curl_setopt($ch2, CURLOPT_CUSTOMREQUEST, "POST");
// curl_setopt($ch2, CURLOPT_RETURNTRANSFER, true);
// curl_setopt($ch2, CURLOPT_HTTPHEADER, array(
//     'Content-Length: ' . strlen($body2),
//     'Authorization: Bearer '.$data->access_token));

// $res = curl_exec($ch2);
// // print_object(curl_getinfo($ch2));
// $err = curl_error($ch2);
// if (!empty($err)) {
//     echo "\n __ERROR__ \n";
//     print_object($err);
//     die;
// }

// print_object(json_decode($res));
