@extends('layouts.app')

@section('content')
<div class="container">
    <div class="mb-3">
        <a href="{{ url()->previous() }}" class="btn btn-outline-primary">
            &larr; Back
        </a>
    </div>

    <div class="row justify-content-center">
        <div class="col-md-8">
            <div class="card">
                <div class="card-header text-center">
                    <h2 class="d-inline-flex align-items-center">{{ $user['name'] }}
                        <img src="{{ asset($user['wreath_url']) }}" alt="Wreath" style="height: 1.2em; margin-left: 0.5rem;">
                    </h2>
                </div>
                <div class="card-body">
                    <div class="text-center mb-4">
                        <img src="{{ asset($user['avatar']) }}" alt="User Avatar" class="rounded-circle img-thumbnail" width="150">
                    </div>

                    <div class="text-center mb-4">
                        <h5>Personal Information</h5>
                        {{-- Use the $user array passed from the controller --}}
                        <p class="mb-1"><strong>Email:</strong> {{ $user['email'] }}</p>
                        <p class="mb-1"><strong>Role:</strong> {{ ucfirst($user['role']) }}</p>
                    </div>

                    @php
                    $currentRole = session('auth.user.role', 'student');
                    @endphp

                    @if($currentRole === 'student')
                    <hr>

                    <div class="mt-4">
                        <h4 class="text-center mb-4">Achievements</h4>
                        <div class="row text-center">
                            @foreach($user['achievements'] as $achievement)
                            @php $unlocked = (bool)($achievement['unlocked'] ?? false); @endphp
                            <div class="col-md-4 mb-4">
                                <img src="{{ asset($achievement['image']) }}" alt="{{ $achievement['title'] }} badge" class="achievement-badge mb-2" style="{{ $unlocked ? '' : 'filter:grayscale(100%) opacity(0.6);' }}">
                                <h6 class="fw-bold">{{ $achievement['title'] }}</h6>
                                <p class="small text-muted mb-0">{{ $achievement['description'] }}</p>
                            </div>
                            @endforeach
                        </div>
                    </div>
                    @endif
                </div>
            </div>
        </div>
    </div>
</div>
@endsection