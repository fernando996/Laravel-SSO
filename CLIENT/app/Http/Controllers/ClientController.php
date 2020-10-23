<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Jasny\SSO\Broker\Broker;
// use App\Http\Controllers\Attach;

class ClientController extends Controller
{
    public function index()
    {
        $broker = Attach::Attach();

        if ($broker['redirect'] ?? false) return redirect($broker['redirect']);

        if ($broker['brokerId'] ?? false) return view('error', $broker);

        try {
            $userInfo = $broker['broker']->request('GET', '/api/info');
        } catch (\RuntimeException $exception) {

            $brokerId = getenv('SSO_BROKER_ID');

            $error = isset($exception) ? $exception->getMessage() : ($_GET['sso_error'] ?? "Unknown error");
            $errorDetails = isset($exception) && $exception->getPrevious() !== null
                ? $exception->getPrevious()->getMessage()
                : null;

            return view('error', ['brokerId' => $brokerId, 'error' => $error, 'errorDetails' => $errorDetails]);
        }

        return view('home', ['broker' => $broker['broker'], 'userInfo' => $userInfo]);
    }
}
