<?php
/*-------------------------------------------------------+
| PHP-Fusion Content Management System
| Copyright (C) PHP-Fusion Inc
| https://www.php-fusion.co.uk/
+--------------------------------------------------------+
| Filename: currency.geo.php
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
require_once __DIR__.'/../../../../../maincore.php';

/**
 * $q - iso2 country code
 */
if (!empty($_REQUEST['q'])) {
    $_REQUEST['q'] = stripinput($_REQUEST['q']);
    $result = \PHPFusion\Geomap::get_Currency($_REQUEST['q']);
    if (!empty($result)) {
        header('Content-Type: application/json');
        echo json_encode($result);
    }
}
