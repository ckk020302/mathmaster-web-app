<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">

    <!-- CSRF Token -->
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <title>Classroom</title>

    <!-- Fonts -->
    <link rel="preconnect" href="https://fonts.bunny.net">
    <link href="https://fonts.bunny.net/css?family=figtree:400,600&display=swap" rel="stylesheet" />

    <!-- Styles / Scripts -->
    <!-- Use CDN Bootstrap to avoid Vite manifest dependency in local setups -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet" integrity="sha384-QWTKZyjpPEjISv5WaRU9OFeRpok6YctnYmDr5pNlyT2bRjXh0JMhjY6hW+ALEwIH" crossorigin="anonymous">
    <link rel="stylesheet" href="{{ asset('css/classroom.css') }}">
</head>
<body class="antialiased">
<div id="app">

    {{-- Top navbar (single copy) --}}
<nav class="navbar navbar-expand-md navbar-light bg-white shadow-sm fixed-top">
    <div class="container-fluid">
        <button class="btn btn-light me-2" id="sidebar-toggle" type="button" aria-label="Toggle sidebar">
            <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="currentColor" class="bi bi-list" viewBox="0 0 16 16">
                <path fill-rule="evenodd" d="M2.5 12a.5.5 0 0 1 .5-.5h10a.5.5 0 0 1 0 1H3a.5.5 0 0 1-.5-.5zm0-4a.5.5 0 0 1 .5-.5h10a.5.5 0 0 1 0 1H3a.5.5 0 0 1-.5-.5zm0-4a.5.5 0 0 1 .5-.5h10a.5.5 0 0 1 0 1H3a.5.5 0 0 1-.5-.5z"/>
            </svg>
        </button>

        @php
            $brandTitle = 'Classroom';
            $brandIcon = 'education.png';
            $brandHref = route('dashboard'); // Default to Classroom dashboard
            
            if (request()->routeIs('counseling.chat')) {
                $brandTitle = 'Counseling Chat';
                $brandIcon = 'bot.png';
                $brandHref = route('counseling.chat');
            } elseif (request()->routeIs('assignments.*')) {
                $brandTitle = 'Assessment';
                $brandIcon = 'contract.png';
                $brandHref = route('assignments.index');
            } elseif (request()->routeIs('study.*')) {
                $brandTitle = 'Study Room';
                $brandIcon = 'chat.png';
                $brandHref = route('study.room.index');
            } elseif (request()->routeIs('questionbank.*')) {
                $brandTitle = 'Question Bank';
                $brandIcon = 'icons/question-bank.svg';
                $brandHref = route('questionbank.index');
            }
        @endphp
        {{-- Use $brandHref here --}}
        <a class="navbar-brand" href="{{ $brandHref }}">
            <img src="{{ asset($brandIcon) }}" alt="App Icon" class="me-2" style="height: 1.2em; width: auto;">
            {{ $brandTitle }}
        </a>
        <div class="collapse navbar-collapse" id="navbarSupportedContent">
                @php
                    $__auth = (array) session('auth.user', []);
                    $__user = null; $__invites = collect(); $__notifCount = 0;
                    try {
                        $__user = \App\Models\User::whereRaw('LOWER(email) = ?', [strtolower($__auth['email'] ?? '')])->first();
                        if ($__user && (($__auth['role'] ?? 'student') !== 'teacher')) {
                            $__invites = \App\Models\Invitation::where('user_id', $__user->id)
                                ->where('status', 'pending')
                                ->with(['classroom.teacher'])
                                ->latest()->limit(5)->get();
                            $__notifCount = $__invites->count();
                        }
                    } catch (\Throwable $e) { $__invites = collect(); $__notifCount = 0; }
                @endphp
                @php
                    // Determine if we should show the red dot based on last seen timestamp
                    $lastSeen = session('notifications.last_seen_at');
                    $hasNew = false;
                    try {
                        if (($__auth['role'] ?? 'student') !== 'teacher') {
                            // Student: consider pending invites + recent posts
                            if ($__notifCount > 0 || $__postCount > 0) {
                                if (!$lastSeen) { $hasNew = true; }
                                else {
                                    $invLatest = optional($__invites->max('created_at'));
                                    $postLatest = optional($__recentPosts->max('created_at'));
                                    $latest = $invLatest && $postLatest ? max($invLatest, $postLatest) : ($invLatest ?: $postLatest);
                                    if ($latest) { $hasNew = \Illuminate\Support\Carbon::parse($latest)->gt(\Illuminate\Support\Carbon::parse($lastSeen)); }
                                }
                            }
                        } else {
                            // Teacher: accepted/declined invites + recent posts + joins via code
                            $accepted = \App\Models\Invitation::where('status','accepted')
                                ->whereHas('classroom', function($q) use ($__user){ $q->where('teacher_id', $__user->id); })
                                ->orderByDesc('updated_at')->limit(5)->pluck('updated_at');
                            $declined = \App\Models\Invitation::where('status','declined')
                                ->whereHas('classroom', function($q) use ($__user){ $q->where('teacher_id', $__user->id); })
                                ->orderByDesc('updated_at')->limit(5)->pluck('updated_at');
                            $tPosts = \App\Models\Post::whereHas('classroom', function($q) use ($__user){ $q->where('teacher_id', $__user->id); })
                                ->orderByDesc('created_at')->limit(5)->pluck('created_at');
                            // check latest join via code
                            $classIds = \App\Models\Classroom::where('teacher_id', optional($__user)->id)->pluck('id');
                            $recentEnrollments = \App\Models\Enrollment::whereIn('classroom_id', $classIds)
                                ->orderByDesc('created_at')->limit(10)->get();
                            $joinCodesLatest = collect();
                            foreach ($recentEnrollments as $en) {
                                $hasAccepted = \App\Models\Invitation::where('user_id', $en->user_id)
                                    ->where('classroom_id', $en->classroom_id)
                                    ->where('status', 'accepted')->exists();
                                if (!$hasAccepted && $en->created_at) {
                                    $joinCodesLatest->push($en->created_at);
                                }
                            }
                            if (($accepted->count()+$declined->count()+$tPosts->count()+$joinCodesLatest->count())>0) {
                                if (!$lastSeen) { $hasNew = true; }
                                else {
                                    $accLatest = $accepted->max();
                                    $decLatest = $declined->max();
                                    $tpLatest = $tPosts->max();
                                    $jvLatest = $joinCodesLatest->max();
                                    $latest = collect([$accLatest,$decLatest,$tpLatest,$jvLatest])->filter()->max();
                                    if ($latest) { $hasNew = \Illuminate\Support\Carbon::parse($latest)->gt(\Illuminate\Support\Carbon::parse($lastSeen)); }
                                }
                            }
                        }
                    } catch (\Throwable $e) { $hasNew = false; }
                @endphp
                @php
                    // Teacher notifications: accepted/declined/joins + recent posts in owned classes
                    $__acceptedInvites = collect(); $__acceptedCount = 0; $__declinedInvites = collect(); $__declinedCount = 0; $__joinedViaCode = collect(); $__joinedCount = 0; $__tPosts = collect(); $__tPostCount = 0;
                    try {
                        if ($__user && (($__auth['role'] ?? 'student') === 'teacher')) {
                            $__acceptedInvites = \App\Models\Invitation::where('status', 'accepted')
                                ->whereHas('classroom', function($q) use ($__user){ $q->where('teacher_id', $__user->id); })
                                ->with(['classroom','user'])
                                ->orderByDesc('updated_at')->limit(5)->get();
                            $__acceptedCount = $__acceptedInvites->count();

                            $__declinedInvites = \App\Models\Invitation::where('status', 'declined')
                                ->whereHas('classroom', function($q) use ($__user){ $q->where('teacher_id', $__user->id); })
                                ->with(['classroom','user'])
                                ->orderByDesc('updated_at')->limit(5)->get();
                            $__declinedCount = $__declinedInvites->count();

                            $classIds = \App\Models\Classroom::where('teacher_id', $__user->id)->pluck('id');
                            $enrs = \App\Models\Enrollment::whereIn('classroom_id', $classIds)
                                ->with(['user','classroom'])
                                ->orderByDesc('created_at')->limit(10)->get();
                            $__joinedViaCode = $enrs->filter(function($en){
                                return !\App\Models\Invitation::where('user_id',$en->user_id)
                                    ->where('classroom_id',$en->classroom_id)
                                    ->where('status','accepted')->exists();
                            })->values();
                            $__joinedCount = $__joinedViaCode->count();

                            $__tPosts = \App\Models\Post::whereHas('classroom', function($q) use ($__user){ $q->where('teacher_id', $__user->id); })
                                ->with(['classroom','user'])
                                ->orderByDesc('created_at')->limit(5)->get();
                            $__tPostCount = $__tPosts->count();
                        }
                    } catch (\Throwable $e) { $__acceptedInvites = collect(); $__declinedInvites = collect(); $__joinedViaCode = collect(); $__tPosts = collect(); }
                @endphp
                @php
                    $__recentPosts = collect(); $__postCount = 0;
                    try {
                        if ($__user && (($__auth['role'] ?? 'student') !== 'teacher')) {
                            $classIds = \App\Models\Enrollment::where('user_id', $__user->id)->pluck('classroom_id');
                            $__recentPosts = \App\Models\Post::whereIn('classroom_id', $classIds)
                                ->where('user_id', '<>', $__user->id)
                                ->with(['classroom','user'])
                                ->latest()->limit(5)->get();
                            $__postCount = $__recentPosts->count();
                        }
                    } catch (\Throwable $e) { $__recentPosts = collect(); $__postCount = 0; }
                @endphp
                <ul class="navbar-nav ms-auto align-items-center">

                    @php $__role = (string) (session('auth.user.role') ?? 'student'); @endphp
                    @if($__role !== 'teacher')
                        <li class="nav-item me-2">
                            <button class="btn btn-info text-white" data-bs-toggle="modal" data-bs-target="#joinClassModal">
                                Join Class
                            </button>
                        </li>
                    @endif

                    {{-- Notifications (role-aware) --}}
                    <li class="nav-item dropdown me-2" id="notifDropdown">
                        <a class="nav-link position-relative" href="#" role="button" data-bs-toggle="dropdown" aria-expanded="false" aria-label="Notifications">
                            <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" fill="currentColor" class="bi bi-bell" viewBox="0 0 16 16">
                                <path d="M8 16a2 2 0 0 0 2-2H6a2 2 0 0 0 2 2zM8 1.918l-.797.161A4.002 4.002 0 0 0 4 6c0 .628-.134 2.197-.459 3.742-.16.767-.376 1.566-.663 2.258h10.244c-.287-.692-.502-1.49-.663-2.258C12.134 8.197 12 6.628 12 6a4.002 4.002 0 0 0-3.203-3.92L8 1.918zM14.22 12c .223 .447 .481 .801 .78 1H1c .299 -.199 .557 -.553 .78 -1C2.68 10.2 3 6.88 3 6c0-2.42 1.72-4.44 4.005-4.901a1 1 0 1 1 1.99 0A5.002 5.002 0 0 1 13 6c0 .88 .32 4.2 1.22 6z"/>
                            </svg>
                             @php $showDot = isset($hasNew) ? $hasNew : ((($__auth['role'] ?? 'student') === 'teacher') ? ($__acceptedCount > 0 || $__declinedCount > 0 || $__joinedCount > 0 || $__tPostCount > 0) : ($__notifCount > 0 || $__postCount > 0)); @endphp
                            @if($showDot)
                                <span class="position-absolute top-0 start-100 translate-middle p-1 bg-danger border border-light rounded-circle"></span>
                            @endif
                        </a>
                         <ul class="dropdown-menu dropdown-menu-end p-2" style="width:340px; max-height: 420px; overflow-y: auto;">
                            <li class="dropdown-header d-flex justify-content-between align-items-center">
                                <span>Notifications</span>
                                 @if((($__auth['role'] ?? 'student') === 'teacher'))
                                     @if($__acceptedCount>0 || $__declinedCount>0 || $__joinedCount>0 || $__tPostCount>0)
                                         <span class="badge bg-danger">{{ $__acceptedCount + $__declinedCount + $__joinedCount + $__tPostCount }}</span>
                                     @endif
                                @else
                                    @if($__notifCount>0 || $__postCount>0)
                                        <span class="badge bg-danger">{{ $__notifCount + $__postCount }}</span>
                                    @endif
                                @endif
                            </li>
                            <li><hr class="dropdown-divider"></li>
                            <script>
                              // Mark notifications as seen when opening the dropdown
                              (function(){
                                const dd = document.getElementById('notifDropdown');
                                if (!dd) return;
                                dd.addEventListener('show.bs.dropdown', function(){
                                  fetch('{{ route('notifications.seen') }}', {
                                    method: 'POST',
                                    headers: { 'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content }
                                  }).catch(()=>{});
                                });
                              })();
                            </script>
                            @if((($__auth['role'] ?? 'student') === 'teacher'))
                                @if($__acceptedCount > 0)
                                    <li class="dropdown-header">Accepted Invites</li>
                                    <li><hr class="dropdown-divider"></li>
                                    @foreach($__acceptedInvites as $ai)
                                        <li class="px-2 py-1">
                                            <div class="small"><strong>{{ $ai->user?->name }}</strong> joined <strong>{{ $ai->classroom?->name }}</strong></div>
                                            <div class="small text-muted">{{ optional($ai->updated_at)->diffForHumans() }}</div>
                                        </li>
                                        <li><hr class="dropdown-divider"></li>
                                    @endforeach
                                @endif
                                @if($__joinedCount > 0)
                                    <li class="dropdown-header">Joined via Code</li>
                                    <li><hr class="dropdown-divider"></li>
                                    @foreach($__joinedViaCode as $jvc)
                                        <li class="px-2 py-1">
                                            <div class="small"><strong>{{ $jvc->user?->name }}</strong> joined <strong>{{ $jvc->classroom?->name }}</strong> via class code</div>
                                            <div class="small text-muted">{{ optional($jvc->created_at)->diffForHumans() }}</div>
                                        </li>
                                        <li><hr class="dropdown-divider"></li>
                                    @endforeach
                                @endif
                                @if($__declinedCount > 0)
                                    <li class="dropdown-header">Declined Invites</li>
                                    <li><hr class="dropdown-divider"></li>
                                    @foreach($__declinedInvites as $di)
                                        <li class="px-2 py-1">
                                            <div class="small"><strong>{{ $di->user?->name }}</strong> declined invite to <strong>{{ $di->classroom?->name }}</strong></div>
                                            <div class="small text-muted">{{ optional($di->updated_at)->diffForHumans() }}</div>
                                        </li>
                                        <li><hr class="dropdown-divider"></li>
                                    @endforeach
                                @endif
                                @if($__tPostCount > 0)
                                    <li class="dropdown-header">Recent Posts</li>
                                    <li><hr class="dropdown-divider"></li>
                                    @foreach($__tPosts as $tp)
                                        <li class="px-2 py-1">
                                            <div class="small mb-1"><strong>{{ $tp->classroom?->name }}</strong></div>
                                            <div class="small text-muted">{{ $tp->user?->name }} posted {{ optional($tp->created_at)->diffForHumans() }}</div>
                                            <a class="small" href="{{ route('classroom.show', $tp->classroom_id) }}">Open classroom</a>
                                        </li>
                                        <li><hr class="dropdown-divider"></li>
                                    @endforeach
                                @endif
                                @if($__acceptedCount === 0 && $__declinedCount === 0 && $__joinedCount === 0 && $__tPostCount === 0)
                                    <li class="px-2 py-2 text-muted small">No new notifications.</li>
                                    <li><hr class="dropdown-divider"></li>
                                @endif
                            @else
                            @forelse($__invites as $inv)
                                <li class="px-2 py-1">
                                    <div class="small mb-1"><strong>{{ $inv->classroom->name }}</strong></div>
                                    <div class="small text-muted mb-2">Teacher: {{ $inv->classroom->teacher?->name }}</div>
                                    <div class="d-flex gap-2">
                                        <form method="POST" action="{{ route('classroom.invite.accept', $inv->classroom_id) }}">
                                            @csrf
                                            <button class="btn btn-sm btn-success">Accept</button>
                                        </form>
                                        <form method="POST" action="{{ route('classroom.invite.decline', $inv->classroom_id) }}">
                                            @csrf
                                            <button class="btn btn-sm btn-outline-secondary">Decline</button>
                                        </form>
                                    </div>
                                </li>
                                <li><hr class="dropdown-divider"></li>
                            @empty
                                <li class="px-2 py-2 text-muted small">No new notifications.</li>
                                <li><hr class="dropdown-divider"></li>
                            @endforelse
                            @if($__postCount > 0)
                                <li class="dropdown-header">Recent Posts</li>
                                <li><hr class="dropdown-divider"></li>
                                @foreach($__recentPosts as $p)
                                    <li class="px-2 py-1">
                                        <div class="small mb-1"><strong>{{ $p->classroom?->name }}</strong></div>
                                        <div class="small text-muted">{{ $p->user?->name }} posted {{ optional($p->created_at)->diffForHumans() }}</div>
                                        <a class="small" href="{{ route('classroom.show', $p->classroom_id) }}">Open classroom</a>
                                    </li>
                                    <li><hr class="dropdown-divider"></li>
                                @endforeach
                            @endif
                            @endif
                            <li><a class="dropdown-item text-center small" href="{{ route('notifications.index') }}">View all notifications</a></li>
                        </ul>
                    </li>

                    {{-- User menu (with your small avatar) --}}
                    @php
                        $authUser = (array) session('auth.user', []);
                        $displayName = (string) ($authUser['name'] ?? 'John');
                        $avatarPath = ltrim((string) ($authUser['avatar'] ?? 'profile.png'), '/');
                    @endphp
                    <li class="nav-item dropdown">
                        <a class="nav-link dropdown-toggle d-flex align-items-center" href="#" role="button" data-bs-toggle="dropdown" aria-expanded="false">
                            <img src="{{ asset($avatarPath) }}"
                                 alt="{{ $displayName }}"
                                 class="rounded-circle me-2"
                                 style="width:18px;height:18px;object-fit:cover;">
                            <span>{{ $displayName }}</span>
                        </a>
                        <ul class="dropdown-menu dropdown-menu-end">
                            <li><a class="dropdown-item" href="{{ route('profile.index') }}">Profile</a></li>
                        </ul>
                    </li>

                </ul>
            </div>
        </div>
    </nav>

    {{-- App layout wrapper --}}
    <div class="layout-wrapper">
        <aside class="sidebar" id="sidebar">
            @php
                $authUser = (array) session('auth.user', []);
                $isTeacher = (($authUser['role'] ?? 'student') === 'teacher');
                $isClassroom = request()->routeIs('dashboard') || request()->routeIs('classroom.*');
                $isStudy = request()->routeIs('study.*');
                $isQBank = request()->routeIs('questionbank.*');
                $isAssignments = request()->routeIs('assignments.*');
                $isCounseling = request()->routeIs('counseling.chat');
            @endphp
            <nav class="nav flex-column">
                <a class="nav-link {{ $isClassroom ? 'active' : '' }}" href="{{ route('dashboard') }}" title="Classroom" {{ $isClassroom ? 'aria-current=page' : '' }}>
                    <img src="{{ asset('education.png') }}" alt="Classroom Icon" class="sidebar-icon">
                    <span>Classroom</span>
                </a>
                @if($isTeacher)
                    <a class="nav-link {{ $isStudy ? 'active' : '' }}" href="{{ route('study.room.index') }}" title="Study Room" {{ $isStudy ? 'aria-current=page' : '' }}>
                        <img src="{{ asset('chat.png') }}" alt="Study Room Icon" class="sidebar-icon">
                        <span>Study room</span>
                    </a>
                    <a class="nav-link {{ $isQBank ? 'active' : '' }}" href="{{ route('questionbank.index') }}" title="Question Bank" {{ $isQBank ? 'aria-current=page' : '' }}>
                        <img src="{{ asset('icons/question-bank.svg') }}" alt="Question Bank Icon" class="sidebar-icon">
                        <span>Question Bank</span>
                    </a>
                @else
                    <a class="nav-link {{ $isAssignments ? 'active' : '' }}" href="{{ route('assignments.index') }}" title="Assignments" {{ $isAssignments ? 'aria-current=page' : '' }}>
                        <img src="{{ asset('contract.png') }}" alt="Assignments Icon" class="sidebar-icon">
                        <span>Assessment</span>
                    </a>
                    <a class="nav-link {{ $isStudy ? 'active' : '' }}" href="{{ route('study.room.index') }}" title="Study Room" {{ $isStudy ? 'aria-current=page' : '' }}>
                        <img src="{{ asset('chat.png') }}" alt="Study Room Icon" class="sidebar-icon">
                        <span>Study room</span>
                    </a>
                    <a class="nav-link {{ $isCounseling ? 'active' : '' }}" href="{{ route('counseling.chat') }}" title="Counseling Chat" {{ $isCounseling ? 'aria-current=page' : '' }}>
                        <img src="{{ asset('bot.png') }}" alt="Counseling Chat Icon" class="sidebar-icon">
                        <span>Counseling Chat</span>
                    </a>
                @endif
            </nav>
            <div class="logout-wrapper">
                <form method="POST" action="{{ route('logout') }}">
                    @csrf
                    <button type="submit" class="nav-link w-100 text-start" title="Logout" style="background:none;border:none;padding:0;">
                        <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="#d9534f" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="feather feather-log-out"><path d="M9 21H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h4"></path><polyline points="16 17 21 12 16 7"></polyline><line x1="21" y1="12" x2="9" y2="12"></line></svg>
                        <span style="color:#d9534f;">Logout</span>
                    </button>
                </form>
            </div>
        </aside>

        <main class="main-content position-relative">
            @yield('content')
            @php $authUser = (array) session('auth.user', []); $onClassroomIndex = request()->routeIs('dashboard') || request()->routeIs('classroom.index'); @endphp
            @if(($authUser['role'] ?? 'student') === 'teacher' && $onClassroomIndex)
                <!-- Floating Create Class button (bottom-right) -->
                <button type="button" class="btn btn-success rounded-circle shadow fab" title="Create Class" aria-label="Create Class"
                        data-bs-toggle="modal" data-bs-target="#createClassModal">
                    <!-- Changed to inline SVG icon -->
                    <svg xmlns="http://www.w3.org/2000/svg" width="26" height="26" viewBox="0 0 16 16" fill="currentColor" aria-hidden="true">
                        <path d="M8 4a.75.75 0 0 1 .75.75V7.25h2.5a.75.75 0 0 1 0 1.5h-2.5v2.5a.75.75 0 0 1-1.5 0v-2.5h-2.5a.75.75 0 0 1 0-1.5h2.5V4.75A.75.75 0 0 1 8 4z"/>
                    </svg>
                </button>
            @endif
        </main>
    </div>

</div>

<!-- Join Class Modal (single copy) -->
<div class="modal fade" id="joinClassModal" tabindex="-1" aria-labelledby="joinClassModalLabel" aria-hidden="true">
  <div class="modal-dialog">
    <div class="modal-content">
      <div class="modal-header">
        <h5 class="modal-title" id="joinClassModalLabel">Join Class</h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
      </div>
      <div class="modal-body">
        <div id="join-class-message"></div>
        <form id="join-class-form">
          <div class="mb-3">
            <input type="text" class="form-control" id="class-code-input" placeholder="Class code: xxxxxx" required>
          </div>
        </form>
      </div>
      <div class="modal-footer">
        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
        <button type="button" class="btn btn-primary" id="join-class-submit-btn">Join</button>
      </div>
    </div>
  </div>
</div>

<!-- Create Class Modal (teacher only) -->
<div class="modal fade" id="createClassModal" tabindex="-1" aria-labelledby="createClassModalLabel" aria-hidden="true">
  <div class="modal-dialog">
    <div class="modal-content">
      <div class="modal-header">
        <h5 class="modal-title" id="createClassModalLabel">Create Class</h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
      </div>
      <form method="POST" action="{{ route('classrooms.create') }}">
        @csrf
        <div class="modal-body">
            <div class="border rounded p-3">
              <div class="mb-3 row align-items-center">
                <label for="new-class-name" class="col-sm-4 col-form-label">Enter the class name :</label>
                <div class="col-sm-8">
                  <input type="text" class="form-control" id="new-class-name" name="name" placeholder="e.g. Mathematics 1" required maxlength="100">
                </div>
              </div>
              <div class="mb-3 row align-items-center">
                <label for="new-class-details" class="col-sm-4 col-form-label">Enter the class details :</label>
                <div class="col-sm-8">
                  <input type="text" class="form-control" id="new-class-details" name="details" placeholder="Optional description" maxlength="500">
                </div>
              </div>
              <div class="mb-2 row align-items-center">
                <label for="new-class-max" class="col-sm-4 col-form-label">Max number per class :</label>
                <div class="col-sm-8">
                  <select id="new-class-max" name="max_size" class="form-select">
                    <option value="" selected>-- Select --</option>
                    <option>10</option>
                    <option>20</option>
                    <option>30</option>
                    <option>40</option>
                    <option>50</option>
                  </select>
                </div>
              </div>
            </div>
        </div>
        <div class="modal-footer">
          <button type="submit" class="btn btn-success rounded-pill px-4">Submit</button>
          <button type="button" class="btn btn-danger rounded-pill px-4" data-bs-dismiss="modal">Cancel</button>
        </div>
      </form>
    </div>
  </div>
 </div>

@stack('scripts')

<!-- Bootstrap JS (CDN bundle includes Popper) -->
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js" integrity="sha384-YvpcrYf0tY3lHB60NNkmXc5s9fDVZLESaAA55NDzOxhy9GkcIdslK1eN7N6jIeHz" crossorigin="anonymous"></script>

<!-- Minimal inline script to handle sidebar toggle (replaces Vite app.js) -->
<script>
  document.addEventListener('DOMContentLoaded', function(){
    const toggleButton = document.getElementById('sidebar-toggle');
    if (toggleButton) {
      toggleButton.addEventListener('click', function(){
        document.body.classList.toggle('sidebar-collapsed');
      });
    }
  });
  // Persist collapsed state across reloads (optional)
  // Uncomment to enable persistence via localStorage
  // document.addEventListener('DOMContentLoaded', function(){
  //   const key = 'sidebar-collapsed';
  //   if (localStorage.getItem(key) === '1') document.body.classList.add('sidebar-collapsed');
  //   document.getElementById('sidebar-toggle')?.addEventListener('click', function(){
  //     const isCollapsed = document.body.classList.toggle('sidebar-collapsed');
  //     localStorage.setItem(key, isCollapsed ? '1' : '0');
  //   });
  // });
  
</script>

<!-- Role-aware tweaks for empty states on Classroom page (teacher message) -->
<script>

    
  window.AUTH_ROLE = @json((string) (session('auth.user.role') ?? 'student'));
  document.addEventListener('DOMContentLoaded', function(){
    if (window.AUTH_ROLE === 'teacher') {
      // Find the empty-state message on classroom index and replace text
      const containers = document.querySelectorAll('.text-center.text-muted');
      containers.forEach(function(c){
        const header = c.querySelector('p.h5');
        if (!header) return;
        const txt = (header.textContent || '').toLowerCase();
        if (txt.includes("haven't join") || txt.includes("haven't joined")) {
          header.textContent = 'No classroom is available.';
          // Hide student-only suggestions (join code / invite)
          c.querySelectorAll('.d-inline-block.text-start.small').forEach(function(el){ el.remove(); });
          if (!c.querySelector('.teacher-empty-tip')) {
            const tip = document.createElement('div');
            tip.className = 'teacher-empty-tip small d-inline-flex align-items-start gap-2 justify-content-center';
tip.innerHTML = '<span class="text-success" aria-hidden="true">✅</span><span>Create a new classroom and add your students!</span>';            c.appendChild(tip);
          }
        }
      });
    }
  });
</script>

<script>
// Join class (AJAX; appends card if grid present, otherwise falls back)
document.getElementById('join-class-submit-btn')?.addEventListener('click', async () => {
    const input = document.getElementById('class-code-input');
    const message = document.getElementById('join-class-message');
    if (!input) return;

    message.innerHTML = '';
    try {
        const res = await fetch('{{ route('classrooms.join') }}', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                'Accept': 'application/json'
            },
            body: JSON.stringify({ code: input.value })
        });
        const json = await res.json();
        if (res.ok && json.success) {
            message.innerHTML = '<div class="alert alert-success">'+json.message+'</div>';
            input.value = '';

            // append to grid if we're on the index page
            const grid = document.getElementById('classroom-grid');
            if (grid && json.html) {
                const temp = document.createElement('div');
                temp.innerHTML = json.html.trim();
                const cardCol = temp.firstElementChild;
                if (!grid.querySelector('[data-classroom-id="'+ json.id +'"]')) {
                    grid.prepend(cardCol);
                }
            } else {
                // fallback: reload to reflect change
                location.reload();
            }
        } else {
            message.innerHTML = '<div class="alert alert-danger">'+(json.message || 'Invalid code')+'</div>';
        }
    } catch (e) {
        message.innerHTML = '<div class="alert alert-danger">Something went wrong.</div>';
    }
});

// Leave from index cards (delegated)
document.addEventListener('click', async (e) => {
    const btn = e.target.closest('.leave-card-btn');
    if (!btn) return;

    const id = btn.dataset.id;
    btn.disabled = true;

    try {
        const res = await fetch(`{{ url('/classroom') }}/${id}/leave`, {
            method: 'POST',
            headers: {
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                'Accept': 'application/json'
            }
        });
        const json = await res.json();
        if (res.ok && json.success) {
            document.querySelector(`.classroom-col[data-classroom-id="${id}"]`)?.remove();
        } else {
            alert(json.message || 'Failed to leave classroom.');
            btn.disabled = false;
        }
    } catch (err) {
        alert('Network error. Please try again.');
        btn.disabled = false;
    }
});
</script>
</body>
</html>
