<?php

namespace App;

use Jasny\SSO\Server;
use Jasny\ValidationResult;

class MySSOServer extends Server
{

    /**
     * Registered brokers
     * @var array
     */
    private static $brokers = [
        'Alice' => ['secret' => '8iwzik1bwd'],
        'Greg' => ['secret' => '7pypoox2pc'],
        'Julias' => ['secret' => 'ceda63kmhp']
    ];

    private static $users = [
        'jackie' => [
            'fullname' => 'Jackie Black',
            'email' => 'jackie.black@example.com',
            'password' => '$2y$10$lVUeiphXLAm4pz6l7lF9i.6IelAqRxV4gCBu8GBGhCpaRb6o0qzUO' // jackie123
        ],
        'john' => [
            'fullname' => 'John Doe',
            'email' => 'john.doe@example.com',
            'password' => '$2y$10$RU85KDMhbh8pDhpvzL6C5.kD3qWpzXARZBzJ5oJ2mFoW7Ren.apC2' // john123
        ]
    ];

    /**
     * Get the API secret of a broker and other info
     *
     * @param string $brokerId
     * @return array
     */
    protected function getBrokerInfo($brokerId)
    {
        return self::$brokers[$brokerId] ?? null;
    }

    /**
     * Authenticate using user credentials
     *
     * @param string $username
     * @param string $password
     * @return ValidationResult
     */
    protected function authenticate($username, $password)
    {
        if (!isset($username)) {
            return ValidationResult::error("username isn't set");
        }

        if (!isset($password)) {
            return ValidationResult::error("password isn't set");
        }

        // if (Auth::attempt(['email' => $username, 'password' => $password])) {
        //     return ValidationResult::success();
        // }
        return ValidationResult::error("can't find user");
    }


    /**
     * Get the user information
     *
     * @return array
     */
    protected function getUserInfo($username)
    {
        return $users[$username] ?? null;
    }
    public function getUserById($id)
    {
        return User::findOrFail($id);
    }
}
