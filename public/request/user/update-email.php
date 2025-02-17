<?php

use RA\ArticleType;

require_once __DIR__ . '/../../../vendor/autoload.php';
require_once __DIR__ . '/../../../lib/bootstrap.php';

if (!ValidatePOSTChars("efcu")) {
    header("Location: " . getenv('APP_URL') . "/controlpanel.php?e=e_baddata");
    exit;
}

$email = requestInputPost('e');
$email2 = requestInputPost('f');
$user = requestInputPost('u');
$cookie = requestInputPost('c');

if ($email !== $email2) {
    header("Location: " . getenv('APP_URL') . "/controlpanel.php?e=e_notmatch");
} else {
    if (filter_var($email, FILTER_VALIDATE_EMAIL) == false) {
        header("Location: " . getenv('APP_URL') . "/controlpanel.php?e=e_badnewemail");
    } else {
        if (validateUser_cookie($user, $cookie, 0) == true) {
            $query = "UPDATE UserAccounts SET EmailAddress='$email', Permissions=0, Updated=NOW() WHERE User='$user'";
            $dbResult = s_mysql_query($query);
            if ($dbResult) {
                sendValidationEmail($user, $email);

                if (getAccountDetails($user, $userData)) {
                    addArticleComment('Server', ArticleType::UserModeration, $userData['ID'],
                        $user . ' changed their email address');
                }

                header("Location: " . getenv('APP_URL') . "/controlpanel.php?e=e_changeok");
            } else {
                header("Location: " . getenv('APP_URL') . "/controlpanel.php?e=e_generalerror");
            }
        } else {
            header("Location: " . getenv('APP_URL') . "/controlpanel.php?e=e_badcredentials");
        }
    }
}
