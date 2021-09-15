<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreContactRequest;
use App\Http\Requests\UpdateContactRequest;
use App\Models\Contact;
use Illuminate\Http\RedirectResponse;
use Illuminate\View\View;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class ContactsController extends Controller
{
    /**
     * @return View
     */
    public function index()
    {
        $contacts = Auth::user()->contacts()->paginate();

        return view('contacts.list', [
            'contacts' => $contacts
        ]);
    }

    /**
     * @return View
     */
    public function create()
    {
        return view('contacts.create');
    }

    /**
     * @param StoreContactRequest $request
     * @return RedirectResponse
     */
    public function store(StoreContactRequest $request)
    {
        $contact = Contact::createForAuthUser($request->all(), false);
        if($contact) {
            return redirect()->route('contacts.list')->with('message', 'Contact successfully created')->withInput($request->all());
        } else {
            return redirect()->back()->withErrors($contact)->withInput($request->all());
        }
    }

    /**
     * @param int $contact_id
     * @return RedirectResponse
     */
    public function destroy(int $contact_id)
    {
        if(Contact::deleteById($contact_id) === true) {
            return redirect()->route('contacts.list')->with('message', 'Contact successfully deleted');
        } else {
            return redirect()->route('contacts.list')->with('error_message', 'Unable to delete contact');
        }
    }

    /**
     * @param int $contact_id
     * @return View|RedirectResponse
     */
    public function edit(int $contact_id)
    {
        $contact = Contact::getByIdForAuthUser($contact_id);

        if ($contact) {
            return view('contacts.edit', [
                'contact' => $contact
            ]);
        } else {
            return redirect()->route('contacts.list')->with('error_message', 'Unable to edit contact');
        }
    }

    /**
     * @param int $contact_id
     * @param UpdateContactRequest $request
     * @return RedirectResponse
     */
    public function update(int $contact_id, UpdateContactRequest $request)
    {
        $contact = Contact::getByIdForAuthUser($contact_id);
        if ($contact) {
            $contact->updateFromRequestData($request->all());
            return redirect()->route('contacts.list')->with('message', 'Contact edited successfully');
        } else {
            return redirect()->back()->with('error_message', 'Unable to edit contact');
        }
    }

    /**
     * @param Request $request
     * @return RedirectResponse
     */
    public function import(Request $request)
    {
        $path = $request->file('document')->getRealPath();
        $result = self::createArrayFromCsv($path);
        $import = Contact::createContactsFromArrayForAuthUser($result);
        return redirect()->route('contacts.list')->with('message', 'Uploaded ' . $import['added'] . ' out of ' . $import['total'] . ' users');
    }

    /**
     * @param string $path
     * @return array
     */
    public static function createArrayFromCsv(string $path)
    {
        $rows = array_map('str_getcsv', file($path));
        $header = array_shift($rows);
        $data = [];

        foreach($rows as $row) {
            $data[] = array_combine($header, $row);
        }

        return $data;
    }
}
