@extends('layouts.super-admin')

@section('title', 'Contact Message Details')

@section('content')
<div class="container-fluid">
    <div class="row">
        <div class="col-12">
            <div class="card">
                <div class="card-header">
                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            <h4 class="card-title">Contact Message Details</h4>
                            <p class="card-title-desc">View and manage contact form submission.</p>
                        </div>
                        <a href="{{ route('super-admin.contacts.index') }}" class="btn btn-secondary">
                            <i class="mdi mdi-arrow-left"></i> Back to Messages
                        </a>
                    </div>
                </div>
                <div class="card-body">
                    @if(session('success'))
                        <div class="alert alert-success alert-dismissible fade show" role="alert">
                            {{ session('success') }}
                            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                        </div>
                    @endif

                    @if(session('error'))
                        <div class="alert alert-danger alert-dismissible fade show" role="alert">
                            {{ session('error') }}
                            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                        </div>
                    @endif

                    <div class="row">
                        <div class="col-md-8">
                            <div class="card border">
                                <div class="card-header bg-light">
                                    <h5 class="mb-0">Message Content</h5>
                                </div>
                                <div class="card-body">
                                    <div class="mb-3">
                                        <strong>Subject:</strong> {{ $contact->subject }}
                                    </div>
                                    <div class="mb-3">
                                        <strong>Message:</strong>
                                        <div class="mt-2 p-3 bg-light rounded">
                                            {!! nl2br(e($contact->message)) !!}
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="card border">
                                <div class="card-header bg-light">
                                    <h5 class="mb-0">Sender Information</h5>
                                </div>
                                <div class="card-body">
                                    <div class="mb-3">
                                        <strong>Name:</strong> {{ $contact->name }}
                                    </div>
                                    <div class="mb-3">
                                        <strong>Email:</strong>
                                        <a href="mailto:{{ $contact->email }}">{{ $contact->email }}</a>
                                    </div>
                                    <div class="mb-3">
                                        <strong>Submitted:</strong> {{ $contact->created_at->format('M d, Y \a\t H:i') }}
                                    </div>
                                    <div class="mb-3">
                                        <strong>Status:</strong>
                                        @if($contact->is_read)
                                            <span class="badge bg-success">Read</span>
                                        @else
                                            <span class="badge bg-warning">Unread</span>
                                        @endif
                                    </div>
                                    @if($contact->read_at)
                                    <div class="mb-3">
                                        <strong>Read At:</strong> {{ $contact->read_at->format('M d, Y \a\t H:i') }}
                                    </div>
                                    @endif
                                </div>
                            </div>

                            <div class="card border mt-3">
                                <div class="card-header bg-light">
                                    <h5 class="mb-0">Quick Reply</h5>
                                </div>
                                <div class="card-body">
                                    <form method="POST" action="{{ route('super-admin.contacts.reply', $contact) }}">
                                        @csrf
                                        <div class="mb-3">
                                            <label for="reply_subject" class="form-label">Subject</label>
                                            <input type="text" class="form-control" id="reply_subject" name="subject"
                                                   value="Re: {{ $contact->subject }}" required>
                                        </div>
                                        <div class="mb-3">
                                            <label for="reply_message" class="form-label">Message</label>
                                            <textarea class="form-control" id="reply_message" name="message" rows="6" required>
Dear {{ $contact->name }},

Thank you for contacting us. We have received your message regarding "{{ $contact->subject }}".

[Your reply here]

Best regards,
stockflowkp Support Team
                                            </textarea>
                                        </div>
                                        <div class="d-flex gap-2">
                                            <button type="submit" class="btn btn-primary">
                                                <i class="mdi mdi-send"></i> Send Reply
                                            </button>
                                            <a href="mailto:{{ $contact->email }}?subject=Re: {{ $contact->subject }}&body={{ urlencode('Dear ' . $contact->name . ',' . PHP_EOL . PHP_EOL . 'Thank you for contacting us.' . PHP_EOL . PHP_EOL . 'Best regards,' . PHP_EOL . 'stockflowkp Support Team') }}"
                                               class="btn btn-outline-secondary" target="_blank">
                                                <i class="mdi mdi-email"></i> Open in Email Client
                                            </a>
                                        </div>
                                    </form>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
