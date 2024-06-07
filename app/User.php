<?php
require_once 'vendor/autoload.php';

class User
{
    private string $username;
    private array $allUsers;

    public function __construct()
    {
        $this->allUsers = json_decode(file_get_contents("users.json"), true);
    }

    public function logIn(): void
    {
        $attempts = 3;

        while ($attempts > 0) {
            $username = readline("Username: ");
            $password = readline("Password: ");

            foreach ($this->allUsers["users"] as $user) {
                if ($user["username"] === $username && $user["password"] === $password) {
                    echo "Login was successful!" . PHP_EOL;
                    $this->username = $username;
                    return;
                }
            }
            echo "Login was not successful! Try again!" . PHP_EOL;
            $attempts--;
        }
        exit("Maximum login attempts reached! Bye!");
    }

    public function getUsername(): string
    {
        return $this->username;
    }
}
