<?php

namespace App\Http\Controllers\Dashboard;

use App\Http\Controllers\Controller;
use App\Http\Requests\Dashboard\StoreContactRequest;
use App\Http\Requests\Dashboard\UpdateContactRequest;
use App\Models\Contact_us;
use App\Models\ContactRequest;
use Illuminate\Http\Request;

class ContactRequestController extends Controller
{
    public function index(Request $request)
    {
        $this->authorize('view_contact_us');

        if($request->ajax())
            return response(getModelData( model: new Contact_us()));
        else
            return view('dashboard.contact-requests.index');
    }
public function show($contact_id)
{
    // Authorize the action
    $this->authorize('view_contact_us');

    // Fetch contact with related models
    $contact = Contact_us::with(['student'])
        ->findOrFail($contact_id);

    return view('dashboard.contact-requests.show', compact('contact'));
}


    public function destroy(Contact_us $contactRequest)
    {
        $this->authorize('view_contact_us');

        $contactRequest->delete();

        return response(["Contact request deleted successfully"]);
    }

    public function deleteSelected(Request $request)
    {
        $this->authorize('view_contact_us');

        Contact_us::whereIn('id', $request->selected_items_ids)->delete();

        return response(["selected contact requests deleted successfully"]);
    }


public function reply(Request $request, $id)
{
    $contact = Contact_us::findOrFail($id);
    // if audio uploaded
    if ($request->hasFile('reply')) {
        $request->validate([
            'reply' => 'mimes:mp3,wav,ogg|max:10240', // audio only
        ]);

        $audioName = uploadAudioToDirectory($request->file('reply'), 'Contact');

        $contact->update([
            'reply'      => $audioName,
        ]);
    } else {
        // else treat as text
        $request->validate([
            'reply' => 'required|string',
        ]);

        $contact->update([
            'reply'      => $request->reply,
        ]);
    }

    return redirect()
        ->route('dashboard.contact-requests.show', $contact->id)
        ->with('success', 'Reply sent successfully.');
}


}
