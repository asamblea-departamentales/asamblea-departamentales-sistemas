<?php

namespace App\Services;

use LdapRecord\Container;
use Throwable;

class LdapAuthenticator
{
    public function authenticate(string $username, string $password): bool
    {
        if (empty($username) || empty($password)) {
            return false;
        }

        try {
            $connection = Container::get('default');

            $success = $connection->auth()->attempt(
                "ASAMBLEA\\{$username}",
                $password
            );

            return $success;
        } catch (Throwable $e) {
            return false;
        }
    }
}
