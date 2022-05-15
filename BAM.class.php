<?php

namespace BAM;

use Database;
use PDO;

class BAM
{

    public function __construct()
    {
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }
    }

    public static function getNameFromBAM($nin) {
        $user = Database::Sql(BAM_DATABASE)->doSelect("SELECT * FROM " . BAM_DATABASE_EMPLOYEEVIEW . " WHERE `noredupersonnin` = ? LIMIT 1", [$nin]);

        if (count($user) === 1) {
            $user = $user[0];
            $name = $user['legal_surname'] . "_" . $user['legal_firstname'];
            return str_replace(" ", "_", $name);
        }
    }

}