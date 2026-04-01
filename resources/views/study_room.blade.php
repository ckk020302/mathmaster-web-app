@extends('layouts.app')

@section('title', 'Study Room')

@section('content')
<style>
    /* General body and container styles for a clean chat background */
    body {
        background-color: #f0f2f5;
    }

    .container {
        background-color: #ffffff;
        padding: 20px;
        border-radius: 8px;
        box-shadow: 0 0 15px rgba(0, 0, 0, 0.05);
    }

    /* Layout for side-by-side view */
    .dual-view-container {
        display: flex;
        gap: 20px;
        min-height: 600px;
    }

    .room-list-column {
        flex: 0 0 40%;
        max-width: 40%;
    }

    .chat-column {
        flex: 1;
        min-width: 0;
    }

    /* Room List Section */
    .room-list-container {
        background-color: #ffffff;
        border-radius: 8px;
        box-shadow: 0 2px 10px rgba(0, 0, 0, 0.05);
        padding: 15px;
        max-height: 700px;
        overflow-y: auto;
    }

    /* Chat Section Layout */
    #chatSection {
        display: flex;
        flex-direction: column;
        height: 600px;
        max-height: 80vh;
        background-color: #ffffff;
        border-radius: 8px;
        box-shadow: 0 2px 10px rgba(0, 0, 0, 0.05);
        overflow: hidden;
    }

    .chat-header {
        display: flex;
        justify-content: space-between;
        align-items: center;
        padding: 10px 15px;
        border-bottom: 1px solid #eee;
        background-color: #f8f8f8;
        border-top-left-radius: 8px;
        border-top-right-radius: 8px;
    }

    .chat-header h3 {
        margin-bottom: 0;
        font-size: 1.25rem;
        color: #333;
    }

    .info-icon {
        cursor: pointer;
        font-size: 1.2rem;
        color: #6c757d;
    }

    /* Messages container */
    #messages {
        flex-grow: 1;
        overflow-y: auto;
        padding: 15px;
        background-color: #f0f2f5;
        scroll-behavior: smooth;
    }

    /* Message content bubble */
    .message-content {
        max-width: 70%;
        padding: 10px 14px;
        border-radius: 18px;
        word-wrap: break-word;
        white-space: pre-wrap;
        position: relative;
        box-shadow: 0 1px 0.5px rgba(0, 0, 0, 0.13);
        font-size: 0.95rem;
    }

    .message-content p {
        margin-bottom: 0;
    }

    /* Sent messages (by current user) */
    .message-sent {
        background-color: #007bff;
        color: white;
    }

    /* Received messages (from other users) */
    .message-received {
        background-color: #e9e9eb;
        color: #333;
    }

    /* Sender's name for received messages */
    .message-sender-name {
        font-size: 0.8rem;
        color: #555;
        margin-bottom: 2px;
        display: block;
    }

    .message-sent .message-sender-name {
        display: none;
    }

    /* Timestamp styling */
    .message-timestamp {
        font-size: 0.7rem;
        color: #888;
        flex-shrink: 0;
        line-height: 1.2;
        margin-inline: 8px;
    }

    /* Images within messages */
    .chat-image-preview {
        max-width: 180px;
        max-height: 180px;
        display: block;
        margin-top: 8px;
        border-radius: 8px;
        cursor: pointer;
        border: 1px solid rgba(0, 0, 0, 0.1);
    }

    /* File download button style within message bubbles */
    .btn-file-download {
        font-size: 0.8rem;
        padding: 4px 8px;
        margin-top: 5px;
        border-radius: 12px;
    }

    .message-sent .btn-file-download {
        border-color: rgba(255, 255, 255, 0.5);
        color: white;
    }

    .message-received .btn-file-download {
        border-color: #6c757d;
        color: #333;
    }

    /* Chat input field */
    #chatInput,
    #chatFile {
        background-color: #f8f9fa;
        border: 1px solid #ced4da;
        border-radius: 0.25rem;
        padding: 0.5rem 0.75rem;
    }

    /* Responsive adjustments */
    @media (max-width: 992px) {
        .dual-view-container {
            flex-direction: column;
        }

        .room-list-column,
        .chat-column {
            flex: 1;
            max-width: 100%;
        }

        .room-list-container {
            max-height: 400px;
            margin-bottom: 20px;
        }
    }

    /* Room list styling */
    .room-card {
        transition: background-color 0.2s;
    }

    .room-card:hover {
        background-color: #f8f9fa;
    }

    .current-room-indicator {
        background-color: #e7f3ff;
        border-left: 3px solid #007bff;
    }
</style>

<div class="container">
    {{-- Alerts --}}
    @if(session('success'))
    <div class="alert alert-success">{{ session('success') }}</div>
    @endif
    @if(session('error'))
    <div class="alert alert-danger">{{ session('error') }}</div>
    @endif

    <!-- Hidden input for room code -->
    <input type="hidden" id="chatRoomCode" value="{{ $joinedRoomCode }}" />

    {{-- Main Content Area --}}
    @if ($chatting)
    {{-- User is in a room: Show both room list and chat side by side --}}
    <div class="dual-view-container">
        {{-- Left Column: Room List --}}
        <div class="room-list-column">
            <div class="room-list-container">
                {{-- Search Bar --}}
                <div class="mb-3">
                    <form method="GET" action="{{ route('study.room.index') }}">
                        <div class="input-group">
                            <input type="text" name="search_room_code" class="form-control form-control-sm"
                                placeholder="Search room code" value="{{ request('search_room_code') }}">
                            <button class="btn btn-primary btn-sm" type="submit">Search</button>
                            @if(request('search_room_code'))
                            <a href="{{ route('study.room.index') }}" class="btn btn-secondary btn-sm">Clear</a>
                            @endif
                        </div>
                    </form>
                </div>

                {{-- Add Room Button --}}
                <div class="mb-3">
                    <button class="btn btn-primary btn-sm w-100" data-bs-toggle="modal"
                        data-bs-target="#addRoomModal" disabled
                        title="Exit current room to create a new one">
                        + Add Room (Exit current room first)
                    </button>
                </div>

                {{-- Current Room Indicator --}}
                <div class="alert alert-info mb-3">
                    <small>Currently in: <strong>{{ $joinedRoomName }}</strong> ({{ $joinedRoomCode }})</small>
                </div>

                {{-- Room List --}}
                <h5 class="mb-3">Available Rooms</h5>
                @if (!request('search_room_code') && count($rooms) > 0)
                <div class="text-center text-muted mb-3">
                    <small>💡 Only Public Study Room(s) are displayed.</small><br>
                    <small>💡 You can join only one study room at a time.</small>
                </div>
                @endif

                @if(count($rooms) > 0)
                <div class="list-group">
                    @foreach($rooms as $room)
                    <div class="list-group-item room-card {{ $room['room_code'] == $joinedRoomCode ? 'current-room-indicator' : '' }}">
                        <div class="d-flex justify-content-between align-items-start">
                            <div class="flex-grow-1">
                                <h6 class="mb-1">{{ $room['name'] }}</h6>
                                <small class="text-muted">
                                    Code: {{ $room['room_code'] }} |
                                    Members: {{ count($room['members'] ?? []) }}/{{ $room['member_limit'] }}
                                </small>
                            </div>
                            <div>
                                @if($room['room_code'] == $joinedRoomCode)
                                <span class="badge bg-primary">Current</span>
                                @else
                                <button class="btn btn-success btn-sm" disabled
                                    title="Exit current room first">
                                    Join
                                </button>
                                @endif
                                <button class="btn btn-info btn-sm"
                                    onclick='showRoomInfoFromList(@json($room))'>
                                    Info
                                </button>
                            </div>
                        </div>
                    </div>
                    @endforeach
                </div>
                @else
                @if(request('search_room_code'))
                <div class="alert alert-warning text-center">
                    No rooms found with that code.
                </div>
                @else
                <div class="text-center p-3 border rounded bg-light">
                    <p class="mb-0">No public study rooms available.</p>
                </div>
                @endif
                @endif
            </div>
        </div>

        {{-- Right Column: Chat --}}
        <div class="chat-column">
            <div id="chatSection">
                <div class="chat-header">
                    <h3 id="chatRoomName">{{ $joinedRoomName }}</h3>
                    <span class="info-icon" onclick="showRoomInfoFromChat()">ℹ️</span>
                </div>
                <div id="messages"></div>
                <form id="chatForm" class="d-flex p-3 border-top" method="POST" action="{{ route('chat.send') }}">
                    @csrf
                    <input type="text" id="chatInput" class="form-control me-2" placeholder="Type a message..." />
                    <input type="file" id="chatFile" class="form-control me-2" style="max-width: 200px;" />
                    <button type="submit" class="btn btn-primary">Send</button>
                </form>
            </div>
        </div>
    </div>
    @else
    {{-- User is NOT in a room: Show only room list --}}
    <div class="room-list-container" style="max-width: 800px; margin: 0 auto;">
        {{-- Search Bar --}}
        <div class="d-flex align-items-center mb-4">
            <form method="GET" action="{{ route('study.room.index') }}" class="flex-grow-1">
                <div class="input-group">
                    <input type="text" name="search_room_code" class="form-control"
                        placeholder="Enter Room Code" value="{{ request('search_room_code') }}">
                    <button class="btn btn-primary" type="submit">Search</button>
                    @if(request('search_room_code'))
                    <a href="{{ route('study.room.index') }}" class="btn btn-secondary">Clear</a>
                    @endif
                </div>
            </form>
            <button class="btn btn-primary ms-3" data-bs-toggle="modal"
                data-bs-target="#addRoomModal">+ Add Room</button>
        </div>

        @if (!request('search_room_code') && count($rooms) > 0)
        <div class="text-center text-muted mb-4">
            <small>💡 Only Public Study Room(s) are displayed.</small>
        </div>
        @endif

        {{-- Room List --}}
        @if(count($rooms) > 0)
        <ul class="list-group">
            @foreach($rooms as $room)
            <li class="list-group-item d-flex justify-content-between align-items-center">
                <div>
                    <strong>{{ $room['name'] }}</strong><br />
                    Code: {{ $room['room_code'] }} |
                    <small>{{ count($room['members'] ?? []) }}/{{ $room['member_limit'] }}</small>
                </div>
                <div class="d-flex gap-2">
                    <form method="POST" action="{{ route('study.room.join') }}" class="d-inline join-room-form">
                        @csrf
                        <input type="hidden" name="room_code" value="{{ $room['room_code'] }}" />
                        <button class="btn btn-success btn-sm btn-join-room"
                            data-room-name="{{ $room['name'] }}"
                            data-member-count="{{ count($room['members'] ?? []) }}"
                            data-member-limit="{{ $room['member_limit'] }}">Join</button>
                    </form>
                    <button class="btn btn-info btn-sm" onclick='showRoomInfoFromList(@json($room))'>Info</button>
                </div>
            </li>
            @endforeach
        </ul>
        @else
        @if(request('search_room_code'))
        <div class="alert alert-warning text-center">
            No rooms found with that code.
        </div>
        @else
        <div class="text-center p-5 my-5 border rounded bg-light">
            <h4 class="mb-3">No public study rooms found.</h4>
            <p class="lead mb-2">✅ Search with a private room code</p>
            <p class="lead">✅ Create a new study room to get started!</p>
        </div>
        @endif
        @endif
    </div>
    @endif
</div>

{{-- Room Info Modal --}}
<div class="modal fade" id="roomInfoModal" tabindex="-1" aria-labelledby="roomInfoModalLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="roomInfoModalLabel">Room Info</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <p><strong>Name:</strong> <span id="infoName"></span></p>
                <p><strong>Description:</strong> <span id="infoDesc"></span></p>
                <p><strong>Limit:</strong> <span id="infoLimit"></span></p>
                <p><strong>Visibility:</strong> <span id="infoVisibility">—</span></p>
                <p><strong>Code:</strong> <span id="infoCode"></span></p>
                <hr />
                <div style="display: none;">
                    <p><strong>Members:</strong></p>
                    <ul id="infoMemberList" class="list-group"></ul>
                </div>
            </div>
            <div class="modal-footer">
                <form method="POST" action="{{ route('study.room.exit') }}" class="d-inline d-none" id="exitRoomForm">
                    @csrf
                    <input type="hidden" name="room_code" id="exitRoomCode" value="{{ $joinedRoomCode }}" />
                    <button type="submit" class="btn btn-danger">Exit Room</button>
                </form>
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
            </div>
        </div>
    </div>
</div>

{{-- Add Room Modal --}}
<div class="modal fade" id="addRoomModal" tabindex="-1" aria-labelledby="addRoomModalLabel" aria-hidden="true">
    <div class="modal-dialog">
        <form method="POST" action="{{ route('study.room.add') }}" class="modal-content">
            @csrf
            <div class="modal-header">
                <h5 class="modal-title" id="addRoomModalLabel">Add New Room</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <div class="mb-3">
                    <label for="roomName" class="form-label">Room Name</label>
                    <input type="text" class="form-control" id="roomName" name="name" required />
                </div>
                <div class="mb-3">
                    <label for="memberLimit" class="form-label">Member Limit</label>
                    <input type="number" class="form-control" id="memberLimit" name="member_limit" min="1" max="50" required />
                </div>
                <div class="mb-3">
                    <label for="description" class="form-label">Description (optional)</label>
                    <textarea class="form-control" id="description" name="description"></textarea>
                </div>
                <div class="form-check">
                    <input class="form-check-input" type="checkbox" id="isPrivate" name="is_private" value="1" />
                    <label class="form-check-label" for="isPrivate">Private Room</label>
                </div>
            </div>
            <div class="modal-footer">
                <button type="submit" class="btn btn-primary">Add Room</button>
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
            </div>
        </form>
    </div>
</div>

{{-- Image Viewer Modal (Lightbox) --}}
<div class="modal fade" id="imageViewerModal" tabindex="-1" aria-labelledby="imageViewerModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="imageViewerModalLabel">Image Preview</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body text-center">
                <img src="" id="modalImage" class="img-fluid" alt="Full size preview">
            </div>
        </div>
    </div>
</div>

{{-- Yes/No Confirmation Modal --}}
<div class="modal fade" id="confirmationModal" tabindex="-1" aria-labelledby="confirmationModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="confirmationModalLabel">Confirmation</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body" id="confirmationModalBody">
                <!-- Message will be injected here by JavaScript -->
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">No</button>
                <button type="button" class="btn btn-primary" id="confirmationModalYes">Yes</button>
            </div>
        </div>
    </div>
</div>

{{-- Simple Alert Modal --}}
<div class="modal fade" id="alertModal" tabindex="-1" aria-labelledby="alertModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered modal-sm">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="alertModalLabel">Alert</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body" id="alertModalBody">
                <!-- Message will be injected here by JavaScript -->
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-primary" data-bs-dismiss="modal">OK</button>
            </div>
        </div>
    </div>
</div>

{{-- Already in Room Alert Modal --}}
<div class="modal fade" id="alreadyInRoomModal" tabindex="-1" aria-labelledby="alreadyInRoomModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="alreadyInRoomModalLabel">Already in a Room</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                You are currently in "<span id="currentRoomName"></span>".
                You must exit your current room before joining another one.
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-primary" data-bs-dismiss="modal">OK</button>
            </div>
        </div>
    </div>
</div>

@push('scripts')
<script>
    document.addEventListener('DOMContentLoaded', () => {
        const chatForm = document.getElementById('chatForm');
        if (!chatForm) return;

        const roomCode = document.getElementById('chatRoomCode')?.value;
        if (!roomCode) return;

        chatForm.addEventListener('submit', async (chatEvent) => {
            chatEvent.preventDefault();

            const cloudName = "{{ config('services.cloudinary.cloud_name') }}";
            const uploadPreset = "{{ config('services.cloudinary.upload_preset') }}";
            const cloudinaryURL = `https://api.cloudinary.com/v1_1/${cloudName}/auto/upload`;
            const msgInput = document.getElementById('chatInput');
            const fileInput = document.getElementById('chatFile');
            const submitButton = chatForm.querySelector('button[type="submit"]');
            const originalButtonHTML = submitButton.innerHTML;
            const message = msgInput.value.trim();
            const file = fileInput.files[0];
            if (!message && !file) return;

            msgInput.disabled = true;
            fileInput.disabled = true;
            submitButton.disabled = true;
            submitButton.innerHTML = '<span class="spinner-border spinner-border-sm" role="status" aria-hidden="true"></span> Sending...';

            try {
                let fileUrl = null;
                const originalFileName = file ? file.name : null;
                if (file) {
                    const formData = new FormData();
                    formData.append('file', file);
                    formData.append('upload_preset', uploadPreset);
                    formData.append('public_id', file.name);

                    const response = await fetch(cloudinaryURL, {
                        method: 'POST',
                        body: formData,
                    });

                    if (!response.ok) {
                        throw new Error('Cloudinary upload failed.');
                    }
                    const data = await response.json();
                    fileUrl = data.secure_url;
                }

                const response = await fetch("{{ route('chat.send') }}", {
                    method: 'POST',
                    headers: {
                        "X-CSRF-TOKEN": '{{ csrf_token() }}',
                        "Content-Type": "application/json",
                        "Accept": "application/json",
                    },
                    body: JSON.stringify({
                        room_code: roomCode,
                        message: message || null,
                        file_url: fileUrl || null,
                        original_filename: originalFileName
                    }),
                });

                if (response.ok) {
                    msgInput.value = '';
                    fileInput.value = '';
                } else {
                    alert('Failed to send message. Please try again.');
                }
            } catch (error) {
                console.error('An error occurred:', error);
                alert('An error occurred. Please check your connection and try again.');
            } finally {
                msgInput.disabled = false;
                fileInput.disabled = false;
                submitButton.disabled = false;
                submitButton.innerHTML = originalButtonHTML;
                msgInput.focus();
            }
        });
    });
</script>
@endpush

@endsection

@push('scripts')
<script src="https://www.gstatic.com/firebasejs/9.17.1/firebase-app-compat.js"></script>
<script src="https://www.gstatic.com/firebasejs/9.17.1/firebase-database-compat.js"></script>
<script src="https://www.gstatic.com/firebasejs/9.17.1/firebase-storage-compat.js"></script>

<script>
    let db, storage;
    try {
        const firebaseConfig = {
            apiKey: "{{ config('services.firebase.api_key') }}",
            authDomain: "{{ config('services.firebase.auth_domain') }}",
            databaseURL: "{{ config('services.firebase.database_url') }}",
            projectId: "{{ config('services.firebase.project_id') }}",
            storageBucket: "{{ config('services.firebase.storage_bucket') }}",
            messagingSenderId: "{{ config('services.firebase.messaging_sender_id') }}",
            appId: "{{ config('services.firebase.app_id') }}"
        };
        if (!firebaseConfig.apiKey || !firebaseConfig.databaseURL) {
            throw new Error("Firebase configuration is missing or incomplete.");
        }
        firebase.initializeApp(firebaseConfig);
        db = firebase.database();
        storage = firebase.storage();
        const currentUserEmail = @json(session('auth.user.email') ?? '');
        console.log("Current User Email:", currentUserEmail);

    } catch (error) {
        console.error("Firebase initialization failed:", error);
        alert("Error: The chat service could not be initialized. Please contact support.");
    }

    let roomInfoModal;
    let imageViewerModal;
    let modalImage;
    let confirmationModal;
    let alreadyInRoomModal;

    function showImageModal(imageUrl) {
        modalImage.src = imageUrl;
        imageViewerModal.show();
    }

    function showRoomInfoFromChat() {
        const roomCodeLocal = document.getElementById('chatRoomCode')?.value;
        if (!roomCodeLocal) {
            console.error('Room code not found in #chatRoomCode input.');
            return;
        }

        console.log('Room Code:', roomCodeLocal);

        fetch(`/study-rooms/members-json/${roomCodeLocal}`)
            .then(response => {
                if (!response.ok) {
                    throw new Error(`HTTP error! Status: ${response.status}`);
                }
                return response.json();
            })
            .then(data => {
                document.getElementById('infoName').textContent = data.name || '—';
                document.getElementById('infoDesc').textContent = data.description || '—';
                document.getElementById('infoLimit').textContent = data.member_limit || '—';
                document.getElementById('infoCode').textContent = roomCodeLocal;
                document.getElementById('infoVisibility').textContent = data.is_private ? 'Private' : 'Public';
                document.getElementById('exitRoomCode').value = roomCodeLocal;

                const memberList = document.getElementById('infoMemberList');
                memberList.innerHTML = '';
                if (data.members && data.members.length > 0) {
                    data.members.forEach(member => {
                        const li = document.createElement('li');
                        li.className = 'list-group-item';
                        li.textContent = `${member.name || member.email}`;
                        memberList.appendChild(li);
                    });
                } else {
                    memberList.innerHTML = '<li class="list-group-item">No members yet</li>';
                }

                document.getElementById('infoMemberList').parentElement.style.display = '';
                document.getElementById('exitRoomForm').classList.remove('d-none');

                roomInfoModal.show();
            })
            .catch((error) => {
                console.error('Error loading room info:', error);
                alert('Failed to load room info. Check console for more details.');
            });
    }

    function showRoomInfoFromList(room) {
        document.getElementById('infoName').textContent = room.name || '—';
        document.getElementById('infoDesc').textContent = room.description || '—';
        document.getElementById('infoLimit').textContent = room.member_limit || '—';
        document.getElementById('infoCode').textContent = room.room_code || '—';
        document.getElementById('infoVisibility').textContent = room.is_private ? 'Private' : 'Public';

        document.getElementById('infoMemberList').parentElement.style.display = 'none';
        document.getElementById('exitRoomForm').classList.add('d-none');

        roomInfoModal.show();
    }

    const currentUserEmail = @json(session('auth.user.email') ?? null);
    console.log("[DEBUG] Current User Email (global):", currentUserEmail);

    const roomCodeInput = document.getElementById('chatRoomCode');
    const roomCode = roomCodeInput ? roomCodeInput.value : null;

    if (roomCode && currentUserEmail) {
        listenMessages(roomCode, currentUserEmail);
    } else {
        console.warn("Room code or current user email is missing for chat listener. This is expected if not currently chatting.");
    }

    function listenMessages(roomCode, currentUserEmail) {
        const messagesDiv = document.getElementById('messages');
        if (!messagesDiv) {
            console.warn("Messages div not found, cannot listen to messages.");
            return;
        }
        messagesDiv.innerHTML = '';

        const msgRef = db.ref('chat/' + roomCode);
        msgRef.off();

        const userJoinTimestamp = @json(session('joined_room_user_timestamp') ?? null);
        console.log(`[DEBUG] Listening to messages for room: ${roomCode}, user: ${currentUserEmail}, joined at: ${userJoinTimestamp}`);

        let query = msgRef.orderByChild('timestamp');
        if (userJoinTimestamp) {
            // Start listening from the user's join timestamp
            query = query.startAt(userJoinTimestamp);
        }

        query.on('child_added', snap => {
            const message = snap.val();
            // Only append messages if they occurred after the user's join timestamp
            // (Firebase startAt handles this server-side, but a client-side check doesn't hurt for robustness)
            if (!userJoinTimestamp || message.timestamp >= userJoinTimestamp) {
                appendMessage(message, currentUserEmail);
            }
        }, error => {
            console.error("Firebase message listener error:", error);
        });
    }




    function appendMessage(message) {
        const messagesDiv = document.getElementById('messages');
        const isSentByMe = message.user_email === currentUserEmail;

        const timestamp = new Date(message.timestamp * 1000);
        const formattedTime = timestamp.toLocaleTimeString([], {
            hour: '2-digit',
            minute: '2-digit'
        });

        const messageRow = document.createElement('div');
        messageRow.className = `message-row d-flex mb-2 ${isSentByMe ? 'justify-content-end' : 'justify-content-start'}`;

        const timestampSpan = document.createElement('span');
        timestampSpan.className = 'message-timestamp align-self-end text-muted';
        timestampSpan.textContent = formattedTime;

        const messageContentDiv = document.createElement('div');
        messageContentDiv.className = `message-content ${isSentByMe ? 'message-sent' : 'message-received'} p-2 rounded-3`;

        let contentHTML = '';

        // Display sender's name if it's not the current user
        if (!isSentByMe && message.user_name) {
            const displayName = message.user_name || (message.user_email ? message.user_email.substring(0, message.user_email.indexOf('@')) : 'Anon');
            contentHTML += `<small class="message-sender-name d-block mb-1 text-secondary">${displayName}</small>`;
        }

        // Add the actual message text
        if (message.message) {
            contentHTML += `<p class="mb-0">${message.message}</p>`;
        }

        if (message.file_url) {
            const urlString = message.file_url;
            const imageExtensions = ['.jpg', '.jpeg', '.png', '.gif', '.webp', '.svg'];
            const isImage = imageExtensions.some(ext => urlString.toLowerCase().endsWith(ext.toLowerCase()));

            if (isImage) {
                contentHTML += `<img src="${urlString}" alt="User uploaded image" class="img-fluid chat-image-preview mt-2 rounded" onclick="showImageModal('${urlString}')">`;
            } else {
                const originalFileName = message.original_filename || urlString.substring(urlString.lastIndexOf('/') + 1);
                contentHTML += `<div class="mt-2"><a href="${urlString}" class="btn btn-sm btn-file-download ${isSentByMe ? 'btn-outline-light' : 'btn-outline-secondary'} d-inline-flex align-items-center" download="${decodeURIComponent(originalFileName)}" target="_blank"><i class="bi bi-file-earmark-arrow-down me-1"></i> Download ${decodeURIComponent(originalFileName)}</a></div>`;
            }
        }

        messageContentDiv.innerHTML = contentHTML;

        // Arrange elements based on who sent the message
        // If sent by me, timestamp is on the left of the bubble. Otherwise, right.
        if (isSentByMe) {
            messageRow.appendChild(timestampSpan);
            messageRow.appendChild(messageContentDiv);
        } else {
            messageRow.appendChild(messageContentDiv);
            messageRow.appendChild(timestampSpan);
        }

        messagesDiv.appendChild(messageRow);
        messagesDiv.scrollTop = messagesDiv.scrollHeight; // Scroll to bottom
    }
    window.addEventListener('DOMContentLoaded', () => {
            // ... your modal initializations ...
            roomInfoModal = new bootstrap.Modal(document.getElementById('roomInfoModal'));
            imageViewerModal = new bootstrap.Modal(document.getElementById('imageViewerModal'));
            confirmationModal = new bootstrap.Modal(document.getElementById('confirmationModal'));
            const alertModal = new bootstrap.Modal(document.getElementById('alertModal'));
            modalImage = document.getElementById('modalImage');

            const roomCodeInput = document.getElementById('chatRoomCode');
            const currentChatRoomCode = roomCodeInput ? roomCodeInput.value : null;

            if (currentChatRoomCode && currentUserEmail) {
                listenMessages(currentChatRoomCode, currentUserEmail);
            }
            // 1. Handle "Join Room" and "Exit Room" confirmations
            document.body.addEventListener('submit', function(e) {
                const form = e.target;

                // --- START OF THE NEW LOGIC FOR JOINING ---
                if (form.matches('.join-room-form')) {
                    e.preventDefault(); // ALWAYS stop the submission first

                    // Get all the info we need from the button and the form
                    const joinButton = form.querySelector('.btn-join-room');
                    const roomName = joinButton.dataset.roomName;
                    const memberCount = parseInt(joinButton.dataset.memberCount);
                    const memberLimit = parseInt(joinButton.dataset.memberLimit);

                    // CHECK IF THE ROOM IS FULL FIRST
                    if (memberCount >= memberLimit) {
                        // If it's full, show the alert modal and STOP
                        document.getElementById('alertModalLabel').textContent = 'Room Full';
                        document.getElementById('alertModalBody').textContent = `The room "${roomName}" is full.`;
                        alertModal.show();
                        return; // Stop further execution
                    }

                    // If the room is NOT full, then show the confirmation modal
                    document.getElementById('confirmationModalLabel').textContent = 'Join Room';
                    document.getElementById('confirmationModalBody').textContent = `Would you like to join "${roomName}"?`;
                    document.getElementById('confirmationModalYes').textContent = 'Yes, Join';
                    confirmationModal.show();

                    // Only if "Yes" is clicked, submit the form
                    document.getElementById('confirmationModalYes').onclick = () => form.submit();
                }
                // --- END OF THE NEW LOGIC FOR JOINING ---


                // Check if the form is for exiting (this part remains the same)
                if (form.matches('#exitRoomForm')) {
                    e.preventDefault(); // Stop submission

                    document.getElementById('confirmationModalLabel').textContent = 'Exit Room';
                    document.getElementById('confirmationModalBody').textContent = 'Are you sure you want to exit?';
                    document.getElementById('confirmationModalYes').textContent = 'Yes, Exit';
                    confirmationModal.show();

                    document.getElementById('confirmationModalYes').onclick = () => form.submit();
                }

                const roomCode = document.getElementById('chatRoomCode')?.value;
                if (roomCode) {
                    listenMessages(roomCode, currentUserEmail);
                }


            });

        }

    );
</script>
@endpush