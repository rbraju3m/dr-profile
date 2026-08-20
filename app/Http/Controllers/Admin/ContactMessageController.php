<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\ContactMessage;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

class ContactMessageController extends Controller
{
    public function index(Request $request): View
    {
        return view('admin.messages.index', [
            'messages' => ContactMessage::query()
                ->when($request->boolean('unread'), fn ($q) => $q->unread())
                ->latest()
                ->paginate(20)
                ->withQueryString(),
            'onlyUnread' => $request->boolean('unread'),
        ]);
    }

    public function show(ContactMessage $message): View
    {
        if (! $message->is_read) {
            $message->forceFill(['is_read' => true])->save();
        }

        return view('admin.messages.show', compact('message'));
    }

    public function destroy(ContactMessage $message): RedirectResponse
    {
        $message->delete();

        return redirect()
            ->route('admin.messages.index')
            ->with('success', __('admin.flash.deleted', ['item' => __('admin.nav.messages')]));
    }
}
