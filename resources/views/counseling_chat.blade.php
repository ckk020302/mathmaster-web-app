@extends('layouts.app')

@section('content')
{{-- STUDENT ONLY ACCESS - Redirect teachers immediately --}}
@if(session('auth.user.role') !== 'student')
    <script>
        window.location.href = "{{ route('dashboard') }}";
    </script>
    {{-- Fallback if JavaScript is disabled --}}
    <meta http-equiv="refresh" content="0;url={{ route('dashboard') }}">
@else
    <link rel="stylesheet" href="{{ asset('css/AIchat.css') }}">

    <div class="container">
        <div id="chat-wrapper">
            <div id="chat-header-line"></div>
            <div id="chat-box">
                <!-- Messages rendered here -->
            </div>
            <div id="input-area">
                <input type="text" id="user-input" placeholder="Type your message...">
                <button onclick="sendMessage()" title="Send">
                    <img src="{{ asset('send.png') }}" alt="Send" style="width:18px;height:18px;filter:invert(1)">
                </button>
            </div>
        </div>
    </div>

    @push('scripts')
    <script src="{{ asset('js/AIchat.js') }}"></script>
    @endpush
@endif
@endsection