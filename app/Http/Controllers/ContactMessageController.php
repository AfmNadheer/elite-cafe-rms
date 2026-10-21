<?php

namespace App\Http\Controllers;

use App\Models\ContactMessage;
use Illuminate\Http\RedirectResponse;
use Illuminate\View\View;

/**
 * Messages sent from the website's Contact form (Admin).
 */
class ContactMessageController extends Controller
{
    public function index(): View
    {
        return view('messages.index', ['messages' => ContactMessage::latest()->paginate(20)]);
    }

    public function toggle(ContactMessage $contactMessage): RedirectResponse
    {
        $contactMessage->update(['is_read' => ! $contactMessage->is_read]);

        return back();
    }

    public function destroy(ContactMessage $contactMessage): RedirectResponse
    {
        $contactMessage->delete();

        return back()->with('success', 'Message deleted.');
    }
}
