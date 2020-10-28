<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
// use App\Http\Controllers\Attach;
use Illuminate\Validation\ValidationException;

class ClientController extends Controller
{
    public function index()
    {
        $broker = Attach::Attach();

        if ($broker['redirect'] ?? false) return redirect($broker['redirect']);

        if ($broker['brokerId'] ?? false) return view('error', $broker);

        try {
            $userInfo = $broker['broker']->request('GET', '/info');
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

    public function login()
    {
        $broker = Attach::Attach();

        if ($broker['redirect'] ?? false) return redirect($broker['redirect']);

        if ($broker['brokerId'] ?? false) return view('error', $broker);

        return view('login', ['broker' => $broker['broker']]);
    }

    public function loginPost(Request $request)
    {
        $broker = Attach::Attach();

        if ($broker['redirect'] ?? false) return redirect($broker['redirect']);

        if ($broker['brokerId'] ?? false) return view('error', $broker);

        try {
            $data = $request->validate([
                'username' => 'required',
                'password' => 'required',
                '_token' => 'required'
            ]);
        } catch (ValidationException $th) {
            return redirect('/home');
        }

        try {

            $credentials = [
                // 'username' => $_POST['username'],
                // 'password' => $_POST['password']
                'username' => $data['username'],
                'password' => $data['password'],
            ];

            $broker['broker']->request('POST', '/login', $credentials);

            return redirect('/home');
        } catch (\RuntimeException $exception) {
            $error = $exception->getMessage();
        }

        return view('login', ['broker' => $broker['broker'], 'error' => $error]);
    }

    public function logout()
    {
        $broker = Attach::Attach();

        if ($broker['redirect'] ?? false) return redirect($broker['redirect']);

        if ($broker['brokerId'] ?? false) return view('error', $broker);

        try {
            $userInfo = $broker['broker']->request('POST', '/logout');
        } catch (\RuntimeException $exception) {

            $brokerId = getenv('SSO_BROKER_ID');

            $error = isset($exception) ? $exception->getMessage() : ($_GET['sso_error'] ?? "Unknown error");
            $errorDetails = isset($exception) && $exception->getPrevious() !== null
                ? $exception->getPrevious()->getMessage()
                : null;

            return view('error', ['brokerId' => $brokerId, 'error' => $error, 'errorDetails' => $errorDetails]);
        }

        return redirect("home");
    }
}
