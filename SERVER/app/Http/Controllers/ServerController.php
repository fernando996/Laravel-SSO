<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Jasny\SSO\Server\Server;
use Jasny\SSO\Server\ExceptionInterface as SSOException;
use Desarrolla2\Cache\File as FileCache;
use Illuminate\Support\Facades\Log;
use Loggy;
use Session;

class ServerController extends Controller
{
    public function index()
    {
        $config = Config::get();

        // Instantiate the SSO server.
        $ssoServer = (new Server(
            function (string $id) use ($config) {
                return $config['brokers'][$id] ?? null;  // Callback to get the broker secret. You might fetch this from DB.
            },
            new FileCache(sys_get_temp_dir())
        ))->withLogger(new Loggy('SSO'));

        //->withLogger(new Loggy('SSO'));

        try {
            // Attach the broker token to the user session. Uses query parameters from $_GET.
            $verificationCode = $ssoServer->attach();
            // 
            $error = null;
        } catch (SSOException $exception) {
            $verificationCode = null;
            $error = ['code' => $exception->getCode(), 'message' => $exception->getMessage()];
        }

        // The token is attached; output 'success'.

        // In this demo we support multiple types of attaching the session. If you choose to support only one method,
        // you don't need to detect the return type.

        $returnType =
            (isset($_GET['return_url']) ? 'redirect' : null) ??
            (isset($_GET['callback']) ? 'jsonp' : null) ??
            (strpos($_SERVER['HTTP_ACCEPT'], 'text/html') !== false ? 'html' : null) ??
            (strpos($_SERVER['HTTP_ACCEPT'], 'application/json') !== false ? 'json' : null);

        switch ($returnType) {
            case 'json':
                header('Content-type: application/json');
                http_response_code($error['code'] ?? 200);
                echo json_encode($error ?? ['verify' => $verificationCode]);
                break;

            case 'jsonp':
                header('Content-type: application/javascript');
                $data = json_encode($error ?? ['verify' => $verificationCode]);
                $responseCode = $error['code'] ?? 200;
                echo $_REQUEST['callback'] . "($data, $responseCode);";
                break;

            case 'redirect':
                $query = isset($error) ? 'sso_error=' . $error['message'] : 'sso_verify=' . $verificationCode;
                $url = $_GET['return_url'] . (strpos($_GET['return_url'], '?') === false ? '?' : '&') . $query;
                header('Location: ' . $url, true, 303);
                echo "You're being redirected to <a href='{$url}'>$url</a>";
                break;

            default:
                http_response_code(400);
                header('Content-Type: text/plain');
                echo "Missing 'return_url' query parameter";
                break;
        }
    }

    public function login(Request $request)
    {
        $config = Config::get();

        StartSession::start();

        // Take the username and password from the POST params.
        $username = $_POST['username'];
        $password = $_POST['password'];

        // Authenticate the user.
        if (!isset($config['users'][$username]) || !password_verify($password, $config['users'][$username]['password'])) {
            http_response_code(400);
            header('Content-Type: application/json');
            echo json_encode(['error' => "Invalid credentials"]);
            exit();
        }

        // Store the current user in the session.
        $_SESSION['user'] = $username;

        // Output user info as JSON.
        $info = ['username' => $username] + $config['users'][$username];
        unset($info['password']);

        // header('Content-Type: application/json');
        return  response()->json($info);
    }

    public function logout(Request $request)
    {
        StartSession::start();

        // Clear the session user.
        unset($_SESSION['user']);



        // Done (no output)
        http_response_code(204);
    }

    public function info(Request $request)
    {

        StartSession::start();
        // if (!isset($_SESSION['user'])) {
        //     http_response_code(204);
        //     exit();
        // }


        Log::info($_SESSION['user'] ?? '');


        // No user is logged in; respond with a 204 No content
        if (!($_SESSION['user'] ?? false)) {
            http_response_code(204);
            exit();
        }

        // Get the username from the session
        $username = $_SESSION['user'];

        $config = Config::get();


        Log::info($username);

        // Output user info as JSON.
        $info = ['username' => $username] + $config['users'][$username];
        unset($info['password']);

        return  response()->json($info);
    }
}
