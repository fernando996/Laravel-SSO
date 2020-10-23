<?php

namespace App\Http\Controllers;

use Jasny\SSO\Broker\Broker;

class Attach extends Controller
{
    public static function Attach()
    {
        // Configure the broker.
        $broker = new Broker(
            getenv('SSO_SERVER'),
            getenv('SSO_BROKER_ID'),
            getenv('SSO_BROKER_SECRET')
        );

        // Handle error from SSO server
        if (isset($_GET['sso_error'])) {
            $brokerId = getenv('SSO_BROKER_ID');

            $error = isset($exception) ? $exception->getMessage() : ($_GET['sso_error'] ?? "Unknown error");
            $errorDetails = isset($exception) && $exception->getPrevious() !== null
                ? $exception->getPrevious()->getMessage()
                : null;

            return ['brokerId' => $brokerId, 'error' => $error, 'errorDetails' => $errorDetails];
        }

        // Handle verification from SSO server
        if (isset($_GET['sso_verify'])) {

            $broker->verify($_GET['sso_verify']);

            $url = (!empty($_SERVER['HTTPS']) ? 'https://' : 'http://') . $_SERVER['HTTP_HOST'] . $_SERVER['REQUEST_URI'];
            $redirectUrl = preg_replace('/sso_verify=\w+&|[?&]sso_verify=\w+$/', '', $url);

            return self::setReturn($redirectUrl);
        }
        // return "a";

        // Attach through redirect if the client isn't attached yet.
        if (!$broker->isAttached()) {

            $returnUrl = (!empty($_SERVER['HTTPS']) ? 'https://' : 'http://') . $_SERVER['HTTP_HOST'] . $_SERVER['REQUEST_URI'];

            $attachUrl = $broker->getAttachUrl(['return_url' => $returnUrl]);

            return self::setReturn($attachUrl);
        }

        return self::setReturn('', $broker);
    }

    private static function setReturn(string $redirectUrl = '', Broker $broker = null)
    {
        return $redirectUrl === '' ? ['broker' => $broker] : ['redirect' => $redirectUrl];
    }
}
