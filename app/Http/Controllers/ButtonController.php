<?php

namespace App\Http\Controllers;

use App\Klaviyo\KlaviyoHelper;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Klaviyo\Exception\KlaviyoException;

class ButtonController extends Controller
{
    /**
     * @return RedirectResponse
     */
    public function click()
    {
        $klaviyoHelper = new KlaviyoHelper();
        try {
            $klaviyoHelper->trackEvent('Click to simple button', \Auth::user()->email);
            return redirect()->route('contacts.list')->with('message', 'You have successfully clicked on the button');
        } catch (KlaviyoException $e) {
            return redirect()->route('contacts.list')->with('error_message', 'Oops, failed to register the action');
        }
    }
}
