{{-- resources/views/classroom.blade.php --}}
@extends('layouts.app')

@section('content')

@php
// --- Optional: dedupe just in case the controller sent duplicates ---
if (isset($classrooms)) {
$classrooms = collect($classrooms)->unique('id')->values()->all();
}
if (isset($classroom)) {
if (!empty($classroom['people'])) {
$classroom['people'] = collect($classroom['people'])
->unique(function ($p) {
return mb_strtolower(($p['name'] ?? '').'|'.($p['role'] ?? ''));
})
->values()
->all();
}
if (!empty($classroom['posts'])) {
// Keep original keys because $key is used as postIndex in routes
$classroom['posts'] = collect($classroom['posts'])
->unique(function ($p) {
if (isset($p['id'])) return $p['id'];
return mb_strtolower(($p['content'] ?? '')).'|'.($p['timestamp'] ?? '');
})
->all();
}
}
@endphp

<div class="container">
    {{-- If $classrooms is passed, show the list of classrooms or empty state --}}
    @if(isset($classrooms))
    @php $authUser = (array) session('auth.user', []); $isTeacher = (($authUser['role'] ?? 'student') === 'teacher'); @endphp
    @if(empty($classrooms))
    <div class="d-flex align-items-center justify-content-center" style="min-height:60vh;">
        <div class="text-center text-muted">
            <p class="h5 mb-3">You haven't join any classroom.</p>
            <div class="d-inline-block text-start small mb-3">
                <div class="mb-1">✅ Join Classroom through class code.</div>
                <div>✅ Contact teacher to invite you.</div>
            </div>
        </div>
    </div>
    @php $authUser = (array) session('auth.user', []); $isTeacher = (($authUser['role'] ?? 'student') === 'teacher'); @endphp
    @if(!$isTeacher && !empty($pendingInvites))
    <div class="mt-4" id="pending-invites">
        @php $inviteCount = is_countable($pendingInvites) ? count($pendingInvites) : 0; @endphp
        <div class="d-flex align-items-center gap-2 mb-3">
            <h5 class="mb-0">Pending Invitations</h5>
            <span class="badge bg-primary">{{ $inviteCount }}</span>
        </div>
        <div class="row g-3">
            @foreach($pendingInvites as $inv)
            <div class="col-12 col-sm-6 col-md-4 col-lg-3">
                <div class="pending-card shadow-sm p-3">
                    <div class="small text-muted mb-1">Pending:</div>
                    <div class="rounded-4 overflow-hidden mb-2">
                        <div style="height:140px;background-image: linear-gradient(rgba(0,0,0,.0), rgba(0,0,0,.0)), url('{{ asset($inv['image'] ?? 'EXQq82JWkAAYtes.jpg') }}');background-size:cover;background-position:center;"></div>
                    </div>
                    <div class="text-center text-muted mb-1" style="font-weight:600;">{{ $inv['name'] }}</div>
                    <div class="text-center small text-muted mb-2">Teacher: {{ $inv['teacher'] }}</div>
                    <div class="d-flex justify-content-center gap-2">
                        <form method="POST" action="{{ route('classroom.invite.accept', $inv['id']) }}">
                            @csrf
                            <button type="submit" class="btn btn-accept-soft px-3">Accept</button>
                        </form>
                        <form method="POST" action="{{ route('classroom.invite.decline', $inv['id']) }}">
                            @csrf
                            <button type="submit" class="btn btn-decline-soft px-3">Decline</button>
                        </form>
                    </div>
                </div>
            </div>
            @endforeach
        </div>
    </div>
    @endif
    @else
    <div class="row" id="classroom-grid">
        @foreach($classrooms as $classroom)
        <div class="col-md-4 mb-4 classroom-col" data-classroom-id="{{ $classroom['id'] }}">
            <div class="card classroom-card h-100">
                <div class="card-header classroom-banner" style="background-image: linear-gradient(rgba(0,0,0,0.3), rgba(0,0,0,0.3)), url('{{ asset($classroom['image'] ?? '/EXQq82JWkAAYtes.jpg') }}'); background-size: cover; background-position: center;">
                    <h5 class="card-title">
                        <a href="{{ route('classroom.show', $classroom['id']) }}" class="text-white">
                            {{ $classroom['name'] }}
                        </a>
                    </h5>
                    <p class="card-teacher mb-0">{{ $classroom['teacher'] }}</p>
                </div>
                <div class="card-body">
                    {{-- Optional content --}}
                </div>
            </div>
        </div>
        @endforeach
    </div>
    @php $authUser = (array) session('auth.user', []); $isTeacher = (($authUser['role'] ?? 'student') === 'teacher'); @endphp
    @if(!$isTeacher && !empty($pendingInvites))
    <div class="mt-4">
        <h5 class="mb-3">Pending Invitations</h5>
        <div class="row g-3">
            @foreach($pendingInvites as $inv)
            <div class="col-12 col-sm-6 col-md-4 col-lg-3">
                <div class="pending-card shadow-sm p-3">
                    <div class="small text-muted mb-1">Pending:</div>
                    <div class="rounded-4 overflow-hidden mb-2">
                        <div style="height:140px;background-image: linear-gradient(rgba(0,0,0,.0), rgba(0,0,0,.0)), url('{{ asset($inv['image'] ?? 'EXQq82JWkAAYtes.jpg') }}');background-size:cover;background-position:center;"></div>
                    </div>
                    <div class="text-center text-muted mb-1" style="font-weight:600;">{{ $inv['name'] }}</div>
                    <div class="text-center small text-muted mb-2">Teacher: {{ $inv['teacher'] }}</div>
                    <div class="d-flex justify-content-center gap-2">
                        <form method="POST" action="{{ route('classroom.invite.accept', $inv['id']) }}">
                            @csrf
                            <button type="submit" class="btn btn-accept-soft px-3">Accept</button>
                        </form>
                        <form method="POST" action="{{ route('classroom.invite.decline', $inv['id']) }}">
                            @csrf
                            <button type="submit" class="btn btn-decline-soft px-3">Decline</button>
                        </form>
                    </div>
                </div>
            </div>
            @endforeach
        </div>
    </div>
    @endif
    @endif

{{-- Else, if a single $classroom is passed, show the detail view --}}
    @elseif(isset($classroom))
    <div class="classroom-header"
        style="background-image: linear-gradient(rgba(0,0,0,0.3), rgba(0,0,0,0.3)), url('{{ asset($classroom['image']) }}'); background-size: cover; background-position: center; color: white;">
        <h1>{{ $classroom['name'] }}</h1>
        <p class="lead">Teacher: {{ $classroom['teacher'] }}</p>
        @if(!empty($classroom['details']))
            <p class="lead">Description: {{ $classroom['details'] }}</p>
        @endif
    </div>


    <div class="row mt-4">
        <div class="col-md-8">
            @if(!empty($classroom['is_owner']))
            <div class="card mb-4">
                <div class="card-header">
                    <h4>Create Post</h4>
                </div>
                <div class="card-body">
                    <form action="{{ route('classroom.post', $classroom['id']) }}" method="POST" enctype="multipart/form-data">
                        @csrf
                        @if($errors->any())
                        <div class="alert alert-danger">
                            <ul class="mb-0">
                                @foreach($errors->all() as $error)
                                <li>{{ $error }}</li>
                                @endforeach
                            </ul>
                        </div>
                        @endif
                        <div class="mb-2 input-container d-flex align-items-center gap-2">
                            <img src="{{ asset('profile.png') }}" alt="Me" class="rounded-circle" style="width:32px;height:32px;object-fit:cover;">
                            <input type="text" name="content" id="post-content" class="form-control border-0 shadow-0" placeholder="Type something..">
                            <button type="button" class="btn btn-link p-0" onclick="document.getElementById('attachment').click()" aria-label="Attach">
                                <svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" fill="currentColor" class="bi bi-paperclip" viewBox="0 0 16 16">
                                    <path d="M4.5 3a2.5 2.5 0 0 1 5 0v9a1.5 1.5 0 0 1-3 0V5a.5.5 0 0 1 1 0v7a.5.5 0 0 0 1 0V3a1.5 1.5 0 1 0-3 0v9a2.5 2.5 0 0 0 5 0V5a.5.5 0 0 1 1 0v7a3.5 3.5 0 1 1-7 0V3z" />
                                </svg>
                            </button>
                            <input type="file" name="attachment" id="attachment" class="d-none" accept="image/*,.pdf">
                            <button type="submit" id="post-button" class="btn btn-primary ms-1" disabled aria-label="Send">
                                <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="currentColor" class="bi bi-send" viewBox="0 0 16 16">
                                    <path d="M15.854.146a.5.5 0 0 1 .11.54l-5.819 14.547a.75.75 0 0 1-1.329.124l-3.178-4.995L.643 7.184a.75.75 0 0 1 .124-1.33L15.314.037a.5.5 0 0 1 .54.11ZM6.636 10.07l2.761 4.338L14.13 2.576 6.636 10.07Zm6.787-8.201L1.591 6.602l4.339 2.76 7.494-7.493Z" />
                                </svg>
                            </button>
                        </div>
                        {{-- Display for selected attachment with remove button --}}
                        <div class="text-muted small mb-2 d-flex align-items-center gap-2" id="attachment-preview-container">
                            <span id="attachment-name"></span>
                            <button type="button" class="btn btn-sm btn-outline-secondary d-none" id="remove-attachment-btn">
                                <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="currentColor" class="bi bi-x" viewBox="0 0 16 16">
                                    <path d="M4.646 4.646a.5.5 0 0 1 .708 0L8 7.293l2.646-2.647a.5.5 0 0 1 .708.708L8.707 8l2.647 2.646a.5.5 0 0 1-.708.708L8 8.707l-2.646 2.647a.5.5 0 0 1-.708-.708L7.293 8 4.646 5.354a.5.5 0 0 1 0-.708z"/>
                                </svg>
                            </button>
                        </div>
                    </form>
                </div>
            </div>
            @endif

            <div class="card">
                <div class="card-header">
                    <h2>Stream</h2>
                </div>
                <div class="card-body">
                    <div class="list-group">
                        @forelse($classroom['posts'] as $key => $post)
                        <div class="list-group-item mb-3 shadow-sm">
                            <div class="d-flex w-100 justify-content-between">
                                <div class="d-flex align-items-center">
                                    @if(is_array($post['user']))
                                    <img src="{{ asset($post['user']['avatar']) }}" alt="{{ $post['user']['name'] }}" class="rounded-circle me-2" width="40" height="40">
                                    <div>
                                        <h5 class="mb-0">{{ $post['user']['name'] }}</h5>
                                        <small class="text-muted">{{ $post['timestamp'] }} @if(isset($post['edited_at'])) (edited) @endif</small>
                                    </div>
                                    @else
                                    <img src="{{ asset('/profile.png') }}" alt="{{ $post['user'] }}" class="rounded-circle me-2" width="40" height="40">
                                    <div>
                                        <h5 class="mb-0">{{ $post['user'] }}</h5>
                                        <small class="text-muted">{{ $post['timestamp'] }} @if(isset($post['edited_at'])) (edited) @endif</small>
                                    </div>
                                    @endif
                                </div>
                                @if(!empty($classroom['is_owner']))
                                <div class="dropdown">
                                    <button class="btn btn-link" type="button" id="dropdownMenuButton-{{ $key }}" data-bs-toggle="dropdown" aria-expanded="false">
                                        <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="currentColor" class="bi bi-three-dots-vertical" viewBox="0 0 16 16">
                                            <path d="M9.5 13a1.5 1.5 0 1 1-3 0 1.5 1.5 0 0 1 3 0zm0-5a1.5 1.5 0 1 1-3 0 1.5 1.5 0 0 1 3 0zm0-5a1.5 1.5 0 1 1-3 0 1.5 1.5 0 0 1 3 0z" />
                                        </svg>
                                    </button>
                                    <ul class="dropdown-menu" aria-labelledby="dropdownMenuButton-{{ $key }}">
                                        <li><a class="dropdown-item" href="#" onclick="event.preventDefault(); window.toggleEditForm('{{ $key }}')">Edit</a></li>
                                        <li>
                                            <form action="{{ route('classroom.post.delete', ['id' => $classroom['id'], 'postIndex' => $key]) }}" method="POST">
                                                @csrf
                                                <button type="submit" class="dropdown-item">Delete</button>
                                            </form>
                                        </li>
                                    </ul>
                                </div>
                                @endif
                            </div>
<p class="mb-1 mt-2">{{ $post['content'] }}</p>

@php
    $attachmentUrl = null;
    $extSource = null;
    if (!empty($post['attachment_url'])) {
        $attachmentUrl = $post['attachment_url'];
        $extSource = $post['attachment_url'];
    } elseif (!empty($post['attachment_path'])) {
        $attachmentUrl = asset('storage/' . $post['attachment_path']);
        $extSource = $post['attachment_path'];
    }
@endphp
@if(!empty($attachmentUrl))
    @php
        $extension = pathinfo($extSource, PATHINFO_EXTENSION);
    @endphp
    @if(in_array(strtolower($extension), ['jpg', 'jpeg', 'png', 'gif', 'webp']))
        <img src="{{ $attachmentUrl }}" alt="Attachment" style="max-width: 100%; height: auto;">
    @else
        <p class="mb-1">
            <a href="{{ $attachmentUrl }}" target="_blank">View Attachment</a>
        </p>
    @endif
@endif

                            @if(!empty($classroom['is_owner']))
                            <div id="edit-form-{{ $key }}" class="d-none mt-3">
                                <form action="{{ route('classroom.post.update', ['id' => $classroom['id'], 'postIndex' => $key]) }}" method="POST" enctype="multipart/form-data">
                                    @csrf
                                    {{-- Display validation errors if any --}}
                                    @if($errors->has('content') && old('postIndex') == $key)
                                    <div class="alert alert-danger">
                                        {{ $errors->first('content') }}
                                    </div>
                                    @endif
                                    <div class="mb-3">
                                        <textarea name="content" class="form-control mb-2" rows="3">{{ old('content', $post['content']) }}</textarea>

                                        {{-- Current Attachment Display (for editing) --}}
                                        <div class="d-flex align-items-center gap-2 mb-2" id="edit-attachment-{{ $key }}-current">
                                            @if(!empty($attachmentUrl))
                                                <span class="text-muted small" id="current-attachment-name-{{ $key }}">Current: {{ basename($extSource) }}</span>
                                                <button type="button" class="btn btn-sm btn-outline-secondary" onclick="window.removeExistingAttachment('{{ $key }}')">
                                                    <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="currentColor" class="bi bi-x" viewBox="0 0 16 16">
                                                        <path d="M4.646 4.646a.5.5 0 0 1 .708 0L8 7.293l2.646-2.647a.5.5 0 0 1 .708.708L8.707 8l2.647 2.646a.5.5 0 0 1-.708.708L8 8.707l-2.646 2.647a.5.5 0 0 1-.708-.708L7.293 8 4.646 5.354a.5.5 0 0 1 0-.708z"/>
                                                    </svg>
                                                </button>
                                            @endif
                                        </div>

                                        {{-- New Attachment Input for Editing --}}
                                        <div class="d-flex align-items-center gap-2 mb-2">
                                            <button type="button" class="btn btn-sm btn-link p-0" onclick="document.getElementById('edit-attachment-{{ $key }}').click()" aria-label="Change attachment">
                                                <svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" fill="currentColor" class="bi bi-paperclip" viewBox="0 0 16 16">
                                                    <path d="M4.5 3a2.5 2.5 0 0 1 5 0v9a1.5 1.5 0 0 1-3 0V5a.5.5 0 0 1 1 0v7a.5.5 0 0 0 1 0V3a1.5 1.5 0 1 0-3 0v9a2.5 2.5 0 0 0 5 0V5a.5.5 0 0 1 1 0v7a3.5 3.5 0 1 1-7 0V3z" />
                                                </svg>
                                                <span class="ms-1 small" id="edit-attachment-display-{{ $key }}">Upload new attachment</span>
                                            </button>
                                            <input type="file" name="attachment" id="edit-attachment-{{ $key }}" class="d-none" accept="image/*,.pdf">
                                            {{-- Hidden input to signal removal of existing attachment --}}
                                            <input type="hidden" name="remove_attachment" id="remove-attachment-hidden-{{ $key }}" value="0">
                                            {{-- Hidden input to re-pass the postIndex for error handling --}}
                                            <input type="hidden" name="postIndex" value="{{ $key }}">
                                        </div>
                                        <div class="text-muted small" id="edit-attachment-name-{{ $key }}"></div>

                                    </div>
                                    <button type="submit" class="btn btn-primary">Save Changes</button>
                                    <button type="button" class="btn btn-secondary ms-2" onclick="window.toggleEditForm('{{ $key }}')">Discard</button>
                                </form>
                            </div>
                            @endif

                            {{-- Comments --}}
                            <div class="mt-3">
                                @if(!empty($post['comments']))
                                <div class="mb-2 fw-semibold small text-muted">Replies</div>
                                <ul class="list-unstyled mb-3">
                                    @foreach($post['comments'] as $comment)
                                    <li class="d-flex align-items-start mb-2">
                                        <img src="{{ asset($comment['user']['avatar']) }}" class="rounded-circle me-2" width="28" height="28" alt="{{ $comment['user']['name'] }}">
                                        <div class="bg-light rounded px-2 py-1" style="max-width: 100%;">
                                            <div class="small"><strong>{{ $comment['user']['name'] }}</strong> <span class="text-muted">· {{ $comment['timestamp'] }}</span></div>
                                            <div class="small">{{ $comment['content'] }}</div>
                                        </div>
                                    </li>
                                    @endforeach
                                </ul>
                                @endif

                                {{-- Add comment (server validates membership) --}}
                                <form method="POST" action="{{ route('classroom.post.comment', ['id' => $classroom['id'], 'postIndex' => $post['id']]) }}" class="d-flex align-items-center gap-2">
                                    @csrf
                                    <input type="text" name="content" class="form-control" placeholder="Write a message..." maxlength="500" required>
                                    <button type="submit" class="btn btn-primary btn-sm" aria-label="Send">
                                        <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="currentColor" class="bi bi-send" viewBox="0 0 16 16">
                                            <path d="M15.854.146a.5.5 0 0 1 .11.54l-5.819 14.547a.75.75 0 0 1-1.329.124l-3.178-4.995L.643 7.184a.75.75 0 0 1 .124-1.33L15.314.037a.5.5 0 0 1 .54.11ZM6.636 10.07l2.761 4.338L14.13 2.576 6.636 10.07Zm6.787-8.201L1.591 6.602l4.339 2.76 7.494-7.493Z" />
                                        </svg>
                                    </button>
                                </form>
                            </div>
                        </div>
                        @empty
                        <p>No posts yet.</p>
                        @endforelse
                    </div>
                </div>
            </div>
        </div>

        <div class="col-md-4">
            <div class="card">
                <div class="card-header">
                    <h4>People</h4>
                </div>
                <div class="card-body">
                    <div class="input-group mb-3">
                        <input type="text" id="people-search" class="form-control" placeholder="Search people..." aria-label="Search people">
                        <span class="input-group-text" id="search-icon" role="button" title="Search">
                            <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="currentColor" class="bi bi-search" viewBox="0 0 16 16">
                                <path d="M11.742 10.344a6.5 6.5 0 1 0-1.397 1.398h-.001c.03.04.062.078.098.115l3.85 3.85a1 1 0 0 0 1.415-1.414l-3.85-3.85a1.007 1.007 0 0 0-.115-.1zM12 6.5a5.5 5.5 0 1 1-11 0 5.5 5.5 0 0 1 11 0z" />
                            </svg>
                        </span>
                    </div>
                    @php $isOwner = (bool)($classroom['is_owner'] ?? false); @endphp
                    @if($isOwner) {{-- Only show this text for classroom owners (teachers) --}}
                        <p class="text-muted text-center mt-2 mb-3">Click Student Profile Image to View Progress</p>
                    @endif
                    <ul class="list-group" id="people-list">
                        @foreach($classroom['people'] as $person)
                        <li class="list-group-item d-flex align-items-center justify-content-between">
                            <div class="d-flex align-items-center">
                                @php $canViewProgress = $isOwner && (($person['role'] ?? '') === 'Student') && !empty($person['user_id']); @endphp
                                @if($canViewProgress)
                                <a href="{{ route('classroom.student.progress', [$classroom['id'], $person['user_id']]) }}" title="View progress">
                                    <img src="{{ asset($person['avatar']) }}" alt="{{ $person['name'] }}" class="rounded-circle me-2" width="30" height="30">
                                </a>
                                @else
                                <img src="{{ asset($person['avatar']) }}" alt="{{ $person['name'] }}" class="rounded-circle me-2" width="30" height="30">
                                @endif
                                <div>
                                    <strong data-original-name="{{ $person['name'] }}">{{ $person['name'] }}</strong>
                                    <small class="text-muted d-block person-meta">{{ $person['role'] }}</small>
                                </div>
                            </div>
                            @if($isOwner && (($person['role'] ?? '') === 'Student') && !empty($person['user_id']))
                            <form method="POST" action="{{ route('classroom.enrollment.remove', [$classroom['id'], $person['user_id']]) }}" onsubmit="return confirm('Remove {{ $person['name'] }} from this class?');">
                                @csrf
                                <button type="submit" class="btn btn-sm btn-outline-danger">Remove</button>
                            </form>
                            @endif
                        </li>
                        @endforeach
                    </ul>
                    <p id="no-results" class="text-muted text-center d-none mt-3">No results found.</p>
                </div>
            </div>
            
            @php
                $authUser = (array) session('auth.user', []);
                $isOwner = (bool)($classroom['is_owner'] ?? false);
                $isStudentViewer = (($authUser['role'] ?? 'student') === 'student');
            @endphp
            @if($isStudentViewer && !$isOwner)
            <div class="card mt-3">
                <div class="card-body">
                    <form method="POST" action="{{ route('classroom.leave', $classroom['id']) }}" onsubmit="return confirm('Leave this classroom?');">
                        @csrf
                        <button type="submit" class="btn btn-outline-danger w-100">Leave classroom</button>
                    </form>
                </div>
            </div>
            @endif

            @if($isOwner)
            <div class="card mt-4">
                @php $auth = (array) session('auth.user', []); $isOwner = (bool)($classroom['is_owner'] ?? false); @endphp
                <div class="card-body">
                    @if($isOwner)

                    @if(session('info'))
                    <div class="alert alert-info alert-dismissible fade show" role="alert">
                        {{ session('info') }}
                        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                    </div>
                    @endif

                    @if(session('success'))
                    <div class="alert alert-success alert-dismissible fade show" role="alert">
                        {{ session('success') }}
                        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                    </div>
                    @endif

                    @if(session('error'))
                    <div class="alert alert-danger alert-dismissible fade show" role="alert">
                        {{ session('error') }}
                        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                    </div>
                    @endif

@php
    $currentCount = count($classroom['people'] ?? []);
    $maxSize = $classroom['max_size'] ?? null;
    $isFull = $maxSize !== null && $currentCount >= $maxSize;
@endphp


<h5 class="mb-3">Invite Student</h5>

@if($isFull)
    <div class="alert alert-warning">
        Classroom is full. You cannot invite more students.
    </div>
@else
    <form method="POST" action="{{ route('classroom.invite.send', $classroom['id']) }}" class="row g-2">
        @csrf
        <div class="col-8">
            <input type="email" name="email" class="form-control" placeholder="student@example.com" required>
        </div>
        <div class="col-4">
            <button type="submit" class="btn btn-info text-white w-100">Send Invite</button>
        </div>
    </form>
@endif

<div class="text-muted small mt-2">or share code: <code>{{ $classroom['code'] }}</code></div>

<hr class="my-3">
<button type="button"
        class="btn btn-outline-danger w-100"
        data-bs-toggle="modal"
        data-bs-target="#deleteClassModal">
    Delete classroom
  </button>

                </div>
            </div>
            @endif
        </div>
    </div>

    <!-- Leave Class Modal -->
    <div class="modal fade" id="leaveClassModal" tabindex="-1" aria-labelledby="leaveClassModalLabel" aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="leaveClassModalLabel">Confirm Leave Classroom</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    Are you sure you want to leave this classroom? This action cannot be undone.
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <form action="{{ route('classroom.leave', $classroom['id']) }}" method="POST" class="d-inline">
                        @csrf
                        <button type="submit" class="btn btn-danger">Leave</button>
                    </form>
                </div>
            </div>
        </div>
    </div>
    @endif
</div>

@if(!empty($classroom) && !empty($classroom['is_owner']))
<!-- Delete Class Modal (owner only) -->
<div class="modal fade" id="deleteClassModal" tabindex="-1" aria-labelledby="deleteClassModalLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="deleteClassModalLabel">Confirm Delete Classroom</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="card-body">
                This action will permanently delete this classroom including all posts, invitations, and enrollments. This cannot be undone.
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                <form action="{{ route('classroom.delete', $classroom['id']) }}" method="POST" class="d-inline">
                    @csrf
                    <button type="submit" class="btn btn-danger">Delete</button>
                </form>
            </div>
        </div>
    </div>
</div>
@endif

@push('scripts')
<script>
    if (!window.__classroomScriptsBound) {
        window.__classroomScriptsBound = true;

        // Toggle inline edit form for posts
        window.toggleEditForm = function(index) {
            const form = document.getElementById('edit-form-' + index);
            if (form) {
                form.classList.toggle('d-none');
                // Re-initialize attachment event listeners if form is shown
                if (!form.classList.contains('d-none')) {
                    setupEditAttachmentListeners(index);
                }
            }
        };
        // --- PEOPLE SEARCH (show only matches) ---
        (function() {
            const input = document.getElementById('people-search');
            const list = document.getElementById('people-list');
            const items = list ? Array.from(list.querySelectorAll('li.list-group-item')) : [];
            const noRes = document.getElementById('no-results');
            const iconBtn = document.getElementById('search-icon');

            if (!input || !list) return;

            function applyFilter() {
                const q = input.value.trim().toLowerCase();
                let visible = 0;

                items.forEach(li => {
                    const name = (li.querySelector('strong')?.textContent || '').toLowerCase();
                    if (q === '' || name.includes(q)) {
                        li.classList.remove('d-none'); // show match (or show all if empty)
                        visible++;
                    } else {
                        li.classList.add('d-none'); // hide non-match
                    }
                });

                if (noRes) noRes.classList.toggle('d-none', visible > 0);
            }

            // filter while typing; prevent Enter from submitting
            input.addEventListener('input', applyFilter);
            input.addEventListener('keydown', function(e) {
                if (e.key === 'Enter') e.preventDefault();
            });
            iconBtn?.addEventListener('click', applyFilter);

            applyFilter(); // initial state shows all
        })();

        // --- POST composer button enable/disable and attachment handling ---
        const attachmentInput = document.getElementById('attachment');
        const postContent = document.getElementById('post-content');
        const postButton = document.getElementById('post-button');
        const attachmentNameSpan = document.getElementById('attachment-name');
        const removeAttachmentBtn = document.getElementById('remove-attachment-btn');
        const attachmentPreviewContainer = document.getElementById('attachment-preview-container');

        function checkPostButtonStatus() {
            const contentVal = (postContent?.value || '').trim();
            const hasAttachment = (attachmentInput?.files.length || 0) > 0;
            if (postButton) postButton.disabled = !(contentVal.length > 0 || hasAttachment);
        }

        function updateAttachmentPreview() {
            if (attachmentInput && attachmentNameSpan && removeAttachmentBtn && attachmentPreviewContainer) {
                if (attachmentInput.files.length > 0) {
                    attachmentNameSpan.textContent = attachmentInput.files[0].name;
                    removeAttachmentBtn.classList.remove('d-none');
                    attachmentPreviewContainer.classList.remove('d-none');
                } else {
                    attachmentNameSpan.textContent = '';
                    removeAttachmentBtn.classList.add('d-none');
                    attachmentPreviewContainer.classList.add('d-none');
                }
            }
            checkPostButtonStatus();
        }

        attachmentInput?.addEventListener('change', updateAttachmentPreview);
        postContent?.addEventListener('input', checkPostButtonStatus);

        removeAttachmentBtn?.addEventListener('click', function() {
            if (attachmentInput) {
                attachmentInput.value = ''; // Clear the file input
            }
            updateAttachmentPreview();
        });

        // Initial check for create post form
        updateAttachmentPreview();


        // --- EDIT POST ATTACHMENT HANDLING ---
        window.removeExistingAttachment = function(key) {
            const currentAttachmentDiv = document.getElementById('edit-attachment-' + key + '-current');
            const removeHiddenInput = document.getElementById('remove-attachment-hidden-' + key);
            const currentAttachmentNameSpan = document.getElementById('current-attachment-name-' + key);
            const editAttachmentInput = document.getElementById('edit-attachment-' + key);
            const editAttachmentDisplayName = document.getElementById('edit-attachment-display-' + key);


            if (currentAttachmentDiv) currentAttachmentDiv.innerHTML = ''; // Hide current attachment info
            if (removeHiddenInput) removeHiddenInput.value = '1'; // Signal to backend to remove
            if (currentAttachmentNameSpan) currentAttachmentNameSpan.textContent = ''; // Clear the name
            if (editAttachmentInput) editAttachmentInput.value = ''; // Clear any newly selected file
            if (editAttachmentDisplayName) editAttachmentDisplayName.textContent = 'Upload new attachment'; // Reset display text
        };

        function setupEditAttachmentListeners(key) {
            const editAttachmentInput = document.getElementById('edit-attachment-' + key);
            const editAttachmentName = document.getElementById('edit-attachment-name-' + key); // This is for new file selected in edit mode
            const editAttachmentDisplayName = document.getElementById('edit-attachment-display-' + key); // Button text
            const removeHiddenInput = document.getElementById('remove-attachment-hidden-' + key);
            const currentAttachmentDiv = document.getElementById('edit-attachment-' + key + '-current');
            const originalCurrentAttachmentHtml = currentAttachmentDiv ? currentAttachmentDiv.innerHTML : ''; // Store original content


            // Reset the hidden input whenever the edit form is opened or attachment selection changes
            if (removeHiddenInput) removeHiddenInput.value = '0';
            // Restore current attachment display if it was hidden by new selection
            if (currentAttachmentDiv && currentAttachmentDiv.innerHTML === '') {
                 // You might need to fetch the original content for this specific post if it was cleared
                 // For now, let's assume it was cleared by JS and we re-render or reload the page
            }


            editAttachmentInput?.addEventListener('change', function() {
                if (this.files.length > 0) {
                    const fileName = this.files[0].name;
                    if (editAttachmentDisplayName) editAttachmentDisplayName.textContent = 'New: ' + fileName;

                    // When a new attachment is selected, automatically "remove" the old one conceptually
                    // and signal to backend.
                    if (removeHiddenInput) removeHiddenInput.value = '0'; // A new file is being uploaded, so don't remove old one
                    if (currentAttachmentDiv) currentAttachmentDiv.innerHTML = ''; // Hide current attachment info because a new one is selected
                } else {
                    if (editAttachmentDisplayName) editAttachmentDisplayName.textContent = 'Upload new attachment';
                    // If file input cleared, but there was an existing one,
                    // we need to put back the existing attachment display for removal option
                    // This is handled by a full page reload or initial render
                }
            });
        }
    }
</script>
@endpush
@endif
@endsection