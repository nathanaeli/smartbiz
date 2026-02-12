<?php

namespace App\Http\Controllers;

use App\Models\Message;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class MessageController extends Controller
{
    /**
     * Display a listing of messages for the current user/tenant
     */
    public function index()
    {
        $messages = Message::forTenant(auth()->user()->tenant?->id ?? null)
            ->with(['sender', 'tenant'])
            ->latest()
            ->paginate(10);

        return view('messages.index', compact('messages'));
    }

    /**
     * Display the specified message
     */
    public function show(Message $message)
    {
        // Check if user can view this message
        if (!$this->canViewMessage($message)) {
            abort(403, 'You are not authorized to view this message.');
        }

        // Mark as read if not already read
        $message->markAsRead();

        // Load replies
        $message->load(['replies' => function($query) {
            $query->with('sender')->orderBy('created_at', 'asc');
        }]);

        return view('messages.show', compact('message'));
    }

    /**
     * Store a reply to the message
     */
    public function reply(Request $request, Message $message)
    {
        // Check if user can view this message
        if (!$this->canViewMessage($message)) {
            abort(403, 'You are not authorized to reply to this message.');
        }

        $request->validate([
            'body' => 'required|string|max:5000',
            'video_url' => 'nullable|url|max:500',
            'attachment' => 'nullable|file|max:10240', // 10MB max
        ]);

        $replyData = [
            'sender_id' => auth()->id(),
            'parent_id' => $message->id,
            'subject' => 'Re: ' . $message->subject,
            'body' => $request->body,
            'video_url' => $request->video_url,
            'is_broadcast' => false,
            'sent_at' => now(),
        ];

        // Handle file attachment if provided
        if ($request->hasFile('attachment')) {
            $file = $request->file('attachment');
            $filename = time() . '_' . $file->getClientOriginalName();
            $path = $file->storeAs('messages', $filename, 'public');

            $replyData['attachment_path'] = $path;
            $replyData['attachment_name'] = $file->getClientOriginalName();
            $replyData['attachment_type'] = $file->getMimeType();
            $replyData['attachment_size'] = $file->getSize();
        }

        // Determine recipient based on original message
        if ($message->is_broadcast) {
            // If original was broadcast, reply goes back to admin
            $admin = \App\Models\User::where('role', 'admin')->first();
            $replyData['tenant_id'] = null; // Admin reply
        } else {
            // If original was to specific tenant, reply goes to that tenant
            $replyData['tenant_id'] = $message->tenant_id;
        }

        Message::create($replyData);

        return redirect()->back()->with('success', 'Reply sent successfully!');
    }

    /**
     * Download message attachment
     */
    public function downloadAttachment(Message $message)
    {
        // Check if user can view this message
        if (!$this->canViewMessage($message)) {
            abort(403, 'You are not authorized to access this attachment.');
        }

        if (!$message->hasAttachment()) {
            abort(404, 'Attachment not found.');
        }

        return Storage::download($message->attachment_path, $message->attachment_name);
    }

    /**
     * Check if the current user can view the message
     */
    private function canViewMessage(Message $message)
    {
        $user = auth()->user();

        // Admin can view all messages
        if ($user->hasRole('admin') || $user->role === 'admin') {
            return true;
        }

        // Users can view messages sent to their tenant or broadcast messages
        return $message->tenant_id === $user->tenant?->id || $message->is_broadcast;
    }
}
