<?php

use RA\ArticleType;

require_once __DIR__ . '/../../../vendor/autoload.php';
require_once __DIR__ . '/../../../lib/bootstrap.php';

if (!ValidatePOSTChars("uct")) {
    header("Location: " . getenv('APP_URL') . "?e=invalidparams");
    exit;
}

$user = requestInputPost('u');
$cookie = requestInputPost('c');
$prefType = requestInputPost('t');
$value = requestInputPost('v', 0, 'integer');

global $db;
$changeErrorCode = null;
if (validateUser_cookie($user, $cookie, 1)) {
    if ($prefType == 'wall') {
        $query = "UPDATE UserAccounts
                SET UserWallActive=$value, Updated=NOW()
                WHERE User='$user'";

        $dbResult = mysqli_query($db, $query);
        if ($dbResult !== false) {
            $changeErrorCode = "changeok";
        } else {
            log_sql_fail();
            $changeErrorCode = "changeerror";
        }
    } else {
        if ($prefType == 'cleanwall') {
            $query = "DELETE FROM Comment
                      WHERE ArticleType = " . ArticleType::User . " && ArticleID = ( SELECT ua.ID FROM UserAccounts AS ua WHERE ua.User = '$user' )";

            $dbResult = mysqli_query($db, $query);
            if ($dbResult !== false) {
                $changeErrorCode = "changeok";
            } else {
                log_sql_fail();
                $changeErrorCode = "changeerror";
            }
        }
    }
} else {
    $changeErrorCode = "changeerror";
}

header("Location: " . getenv('APP_URL') . "/controlpanel.php?e=$changeErrorCode");
