<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
// use App\Http\Controllers\Attach;
use Illuminate\Validation\ValidationException;
use Jasny\SSO\Broker\Broker;

class AjaxController extends Controller
{
    public function attach()
    {
        // Configure the broker.
        $broker = new Broker(
            getenv('SSO_SERVER'),
            getenv('SSO_BROKER_ID'),
            getenv('SSO_BROKER_SECRET')
        );

        $jsCallback = $_GET['callback'];


        // Already attached
        if ($broker->isAttached()) {

            Log::info($jsCallback);
            echo $jsCallback . '(null, 200)';
        }

        // Attach through redirect if the client isn't attached yet.
        $url = $broker->getAttachUrl(['callback' => $jsCallback]);
        // header("Location: $url", true, 303);
        return redirect($url);
    }

    public function verify()
    {
        // Configure the broker.
        $broker = new Broker(
            getenv('SSO_SERVER'),
            getenv('SSO_BROKER_ID'),
            getenv('SSO_BROKER_SECRET')
        );

        // Set the verification cookie.
        // Don't do this in JS using document.cookie, because an XSS vulnerability would grand access to the session.
        $broker->verify($_POST['verify']);

        http_response_code(204);
    }

    public function index()
    {
        // Configure the broker.
        $broker = new Broker(
            getenv('SSO_SERVER'),
            getenv('SSO_BROKER_ID'),
            getenv('SSO_BROKER_SECRET')
        );

        try {
            $path = '' . $_GET['command'];
            $result = $broker->request($_SERVER['REQUEST_METHOD'], $path, $_POST);
        } catch (\Exception $e) {
            $status = $e->getCode() ?: 500;
            $result = ['error' => $e->getMessage()];
        }

        // REST
        if (!$result) {
            return response()->json([], 204);
        } else {
            return response()->json($result, isset($status) ? $status : 200);
        }
    }
}
