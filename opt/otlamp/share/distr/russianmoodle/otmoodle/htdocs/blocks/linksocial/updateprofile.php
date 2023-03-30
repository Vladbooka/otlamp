<?php
// This file is part of Moodle - http://moodle.org/
//
// Moodle is free software: you can redistribute it and/or modify
// it under the terms of the GNU General Public License as published by
// the Free Software Foundation, either version 3 of the License, or
// (at your option) any later version.
//
// Moodle is distributed in the hope that it will be useful,
// but WITHOUT ANY WARRANTY; without even the implied warranty of
// MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
// GNU General Public License for more details.
//
// You should have received a copy of the GNU General Public License
// along with Moodle.  If not, see <http://www.gnu.org/licenses/>.

/**
 * Блок привязки аккаунтов соцсетей. Обновление профиля из соцсетей
 * 
 * @package    block
 * @subpackage linksocial
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

require_once('../../config.php');

global $OUTPUT, $PAGE, $CFG, $COURSE;

// Подключение библиотек
require_once($CFG->dirroot . '/blocks/linksocial/lib.php');

// Требуется авторизация
require_login();

// Получение GET параметров

// Установка параметров страницы
$PAGE->set_context(context::instance_by_id($COURSE->id));
$PAGE->set_url('/blocks/linksocial/updateprofile.php');
$PAGE->set_pagelayout('standard');
$PAGE->set_heading(get_string('page_updateprofile', 'block_linksocial'));
$PAGE->set_title(get_string('page_updateprofile', 'block_linksocial'));

// Установка хлебных крошек
$PAGE->navbar->ignore_active();
$PAGE->navbar->add(get_string('page_updateprofile', 'block_linksocial'), new moodle_url('/blocks/linksocial/updateprofile.php'));
 
// Шапка страницы
echo $OUTPUT->header();

/*
print('
     <script>
  // This is called with the results from from FB.getLoginStatus().
  function statusChangeCallback(response) {
    console.log("statusChangeCallback");
    console.log(response);
    // The response object is returned with a status field that lets the
    // app know the current login status of the person.
    // Full docs on the response object can be found in the documentation
    // for FB.getLoginStatus().
    if (response.status === "connected") {
      // Logged into your app and Facebook.
      testAPI();
    } else if (response.status === "not_authorized") {
      // The person is logged into Facebook, but not your app.
      document.getElementById("status").innerHTML = "Please log " +
        "into this app.";
    } else {
      // The person is not logged into Facebook, so were not sure if
      // they are logged into this app or not.
      document.getElementById("status").innerHTML = "Please log " +
        "into Facebook.";
    }
  }

  // This function is called when someone finishes with the Login
  // Button.  See the onlogin handler attached to it in the sample
  // code below.
  function checkLoginState() {
    FB.getLoginStatus(function(response) {
      statusChangeCallback(response);
    });
  }

  window.fbAsyncInit = function() {
  FB.init({
    appId      : "428502140666458",
    cookie     : true,  // enable cookies to allow the server to access 
                        // the session
    xfbml      : true,  // parse social plugins on this page
    version    : "v2.2" // use version 2.2
  });

  // Now that weve initialized the JavaScript SDK, we call 
  // FB.getLoginStatus().  This function gets the state of the
  // person visiting this page and can return one of three states to
  // the callback you provide.  They can be:
  //
  // 1. Logged into your app ("connected")
  // 2. Logged into Facebook, but not your app ("not_authorized")
  // 3. Not logged into Facebook and cant tell if they are logged into
  //    your app or not.
  //
  // These three cases are handled in the callback function.

  FB.getLoginStatus(function(response) {
    statusChangeCallback(response);
  });

  };

  // Load the SDK asynchronously
  (function(d, s, id) {
    var js, fjs = d.getElementsByTagName(s)[0];
    if (d.getElementById(id)) return;
    js = d.createElement(s); js.id = id;
    js.src = "//connect.facebook.net/en_US/sdk.js";
    fjs.parentNode.insertBefore(js, fjs);
  }(document, "script", "facebook-jssdk"));

  // Here we run a very simple test of the Graph API after login is
  // successful.  See statusChangeCallback() for when this call is made.
  function testAPI() {
    console.log("Welcome!  Fetching your information.... ");
    FB.api("/me", function(response) {
      console.log("Successful login for: " + response.name);
      document.getElementById("status").innerHTML =
        "Thanks for logging in, " + response.name + "!";
    });
  }
</script>

<!--
  Below we include the Login Button social plugin. This button uses
  the JavaScript SDK to present a graphical Login button that triggers
  the FB.login() function when clicked.
-->

<fb:login-button scope="public_profile,email" onlogin="checkLoginState();">
</fb:login-button>

<div id="status">
</div>
');
*/
// Подвал страницы
echo $OUTPUT->footer();