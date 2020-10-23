<?php

namespace App\Http\Controllers;

use Jasny\SSO\Server\Server;
use Jasny\SSO\Server\ExceptionInterface as SSOException;
use Desarrolla2\Cache\File as FileCache;

class StartSession extends Controller
{
    public static function start()
    {
        $config = [
            'brokers' => [
                'Alice' => [
                    'secret' => '8iwzik1bwd',
                    'domains' => ['localhost'],
                ],
                'Greg' => [
                    'secret' => '7pypoox2pc',
                    'domains' => ['localhost'],
                ],
                'Julius' => [
                    'secret' => 'ceda63kmhp',
                    'domains' => ['localhost'],
                ],
            ],
            'users' => [
                'jackie' => [
                    'fullname' => 'Jackie Black',
                    'email' => 'jackie.black@example.com',
                    'password' => '$2y$10$lVUeiphXLAm4pz6l7lF9i.6IelAqRxV4gCBu8GBGhCpaRb6o0qzUO' // jackie123
                ],
                'john' => [
                    'fullname' => 'John Doe',
                    'email' => 'john.doe@example.com',
                    'password' => '$2y$10$RU85KDMhbh8pDhpvzL6C5.kD3qWpzXARZBzJ5oJ2mFoW7Ren.apC2' // john123
                ],
            ],
        ];

        // Instantiate the SSO server.
        $ssoServer = (new Server(
            function (string $id) use ($config) {
                return $config['brokers'][$id] ?? null;  // Callback to get the broker secret. You might fetch this from DB.
            },
            new FileCache(sys_get_temp_dir())            // Any PSR-16 compatible cache
        ));
        //->withLogger(new Loggy('SSO'));

        // Start the session using the broker bearer token (rather than a session cookie).
        try {
            $ssoServer->startBrokerSession();
        } catch (SsoException $exception) {
            $code = $exception->getCode();
            $message = $code === 403
                ? "Invalid or expired bearer token"
                : $exception->getMessage();

            http_response_code($code);
            if ($code === 401) {
                header('WWW-Authenticate: Bearer');
            }

            header('Content-Type: application/json');
            echo json_encode(['error' => $message]);

            exit();
        }

        return $ssoServer;
    }
}
