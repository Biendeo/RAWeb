<?php

use RA\Permissions;

require_once __DIR__ . '/../../../vendor/autoload.php';
require_once __DIR__ . '/../../../lib/bootstrap.php';

if (!ValidatePOSTChars("u")) {
    header("Location: " . getenv('APP_URL') . "/controlpanel.php?e=invalidparams");
    exit;
}

$userIn = requestInputPost('u');

$permOk = RA_ReadCookieCredentials($user, $points, $truePoints, $unreadMessageCount, $permissions, Permissions::Registered)
          && ($user == $userIn
              || $permissions >= Permissions::Admin);
if (!$permOk) {
    header("Location: " . getenv('APP_URL') . "?e=badcredentials");
    exit;
}

if (recalcScore($userIn)) {
    header("Location: " . getenv('APP_URL') . "/controlpanel.php?e=recalc_ok");
    exit;
} else {
    header("Location: " . getenv('APP_URL') . "/controlpanel.php?e=recalc_error");
    exit;
}
