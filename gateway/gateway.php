<?php
/*-------------------------------------------------------+
| PHP-Fusion Content Management System
| Copyright (C) PHP-Fusion Inc
| https://www.php-fusion.co.uk/
+--------------------------------------------------------+
| Filename: gateway.php
| Author: PHP-Fusion Development Team
+--------------------------------------------------------+
| This program is released as free software under the
| Affero GPL license. You can redistribute it and/or
| modify it under the terms of this license which you
| can read by viewing the included agpl.txt or online
| at www.gnu.org/licenses/agpl.html. Removal of this
| copyright header is strictly prohibited without
| written permission from the original author(s).
+--------------------------------------------------------*/
if (!defined("IN_FUSION")) {
    die("Access Denied");
}

/*
Experimental Anti Bot Gateway that combine multiple methods to prevent auto bots.
*/
require_once "constants_include.php";
require_once "functions_include.php";

// Terminate and ban all excessive access atempts.
antiflood_countaccess();

// Flag for pass, just increment on amount of checks we add.
$multiplier = "0";
$reply_method = "";

if (empty($_SESSION["validated"])) {
    $_SESSION['validated'] = 'False';
}

// Don´t run twice
if (!isset($_POST['gateway_submit']) && !isset($_POST['Register']) && isset($_SESSION["validated"]) && $_SESSION['validated'] !== 'True') {

    $_SESSION['validated'] = 'False';

    // Get some numbers up
    $a = rand(10, 20);
    $b = rand(1, 10);

    if ($a > 15) {
        $antibot = intval($a + $b);
        $multiplier = "+";
        $reply_method = $locale['gateway_062'];
        $a = convertNumberToWord($a);
        $antibot = convertNumberToWord($antibot);
        $antibot = preg_replace('/\s+/', '', $antibot);
        $_SESSION["antibot"] = strtolower($antibot);
    } else {
        $antibot = intval($a - $b);
        $multiplier = "-";
        $reply_method = $locale['gateway_063'];
        $_SESSION["antibot"] = intval($antibot);
        $b = convertNumberToWord($b);
    }

    $a = str_rot47($a);
    $b = str_rot47($b);

    echo "<noscript>".$locale['gateway_052']."</noscript>";

    // Just add fields to random
    $honeypot_array = [$locale['gateway_053'], $locale['gateway_054'], $locale['gateway_055'], $locale['gateway_056'], $locale['gateway_057'], $locale['gateway_058'], $locale['gateway_059']];
    shuffle($honeypot_array);
    $_SESSION["honeypot"] = $honeypot_array[3];

    opentable('<span id="formtitle"></span>');

    echo openform('Fusion_Gateway', 'post', 'register.php', ['class' => 'm-t-20']);

    // Try this and we see, Rot47 Encryption etc..
    echo '<script type="text/javascript">
        function decode(x) {
            let s = "";

            for (let i = 0; i < x.length; i++) {
                let j = x.charCodeAt(i);
                if ((j >= 33) && (j <= 126)) {
                    s += String.fromCharCode(33 + ((j + 14) % 94));
                } else {
                    s += String.fromCharCode(j);
                }
            }

            return s;
        }

        $("#formtitle").append("'.$locale['gateway_060'].' " + decode("'.$a.'") + " '.$multiplier.' " + decode("'.$b.'") + " '.$locale['gateway_061'].' '.$reply_method.'");
    </script>';

    echo form_text('gateway_answer', "", "", [
        'error_text' => $locale['gateway_064'],
        'required'   => 1
    ]);
    echo form_hidden($honeypot_array[3], "", "");
    echo form_button('gateway_submit', $locale['gateway_065'], $locale['gateway_065'], ['class' => 'btn-primary m-t-10']);
    echo closeform();
    closetable();
} else if (!isset($_SESSION["validated"])) {
    echo '<div class="well text-center"><h3 class="m-0">'.$locale['gateway_068'].'</h3></div>';
}

if (isset($_POST['gateway_answer'])) {
    $honeypot = '';

    if (isset($_SESSION["honeypot"])) {
        $honeypot = $_SESSION["honeypot"];
    }

    // if the honeypot is empty, run rest of the verify script
    if (isset($_POST["$honeypot"]) && $_POST["$honeypot"] == "") {

        $antibot = stripinput(strtolower($_POST["gateway_answer"]));

        if (isset($_SESSION["antibot"])) {
            if ($_SESSION["antibot"] == $antibot) {
                $_SESSION["validated"] = "True";
            } else {
                echo "<div class='well text-center'><h3 class='m-0'>".$locale['gateway_066']."</h3></div>";
                echo "<input type='button' value='".$locale['gateway_067']."' class='text-center btn btn-info spacer-xs' onclick=\"location='".BASEDIR."register.php'\">";
                $_SESSION["validated"] = "False";
            }
        } else {
            $_SESSION["validated"] = "False";
        }
    }
}