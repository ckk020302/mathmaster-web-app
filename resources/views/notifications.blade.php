@extends('layouts.app')

@section('content')
<div class="container">
                        <div class="fixed-back-button">
        <a href="javascript:history.back()" class="btn btn-outline-primary">
            &larr; Back
        </a>
    </div>
    <div class="row justify-content-center">
        <div class="col-md-10">
            <div class="d-flex justify-content-between align-items-center mb-4">
                <h2 class="mb-0">All Notifications</h2>

            </div>

            @if(!$isTeacher)
                {{-- Student view (unchanged) --}}
                <div class="mb-4">
                    <h5>Invitations</h5>
                    <div class="list-group">
                        @forelse($invites as $inv)
                            <div class="list-group-item flex-column align-items-start mb-2">
                                <div class="d-flex w-100 justify-content-between">
                                    <h6 class="mb-1">{{ $inv->classroom?->name }}</h6>
                                    <span class="badge {{ $inv->status === 'pending' ? 'bg-warning text-dark' : ($inv->status === 'accepted' ? 'bg-success' : 'bg-secondary') }}">{{ ucfirst($inv->status) }}</span>
                                </div>
                                <p class="mb-1 text-muted">Teacher: {{ $inv->classroom?->teacher?->name }}</p>
                                @if($inv->status === 'pending')
                                    <div class="d-flex gap-2">
                                        <form method="POST" action="{{ route('classroom.invite.accept', $inv->classroom_id) }}">
                                            @csrf
                                            <button type="submit" class="btn btn-sm btn-success">Accept</button>
                                        </form>
                                        <form method="POST" action="{{ route('classroom.invite.decline', $inv->classroom_id) }}">
                                            @csrf
                                            <button type="submit" class="btn btn-sm btn-outline-secondary">Decline</button>
                                        </form>
                                    </div>
                                @endif
                            </div>
                        @empty
                            <div class="list-group-item">No invitations</div>
                        @endforelse
                    </div>
                </div>

                <div>
                    <h5>Recent Posts</h5>
                    <div class="list-group">
                        @forelse($studentPosts as $p)
                            <div class="list-group-item">
                                <div class="d-flex justify-content-between">
                                    <div><strong>{{ $p->classroom?->name }}</strong></div>
                                    <small class="text-muted">{{ optional($p->created_at)->diffForHumans() }}</small>
                                </div>
                                <div class="small text-muted">{{ $p->user?->name }} posted</div>
                                <a class="small" href="{{ route('classroom.show', $p->classroom_id) }}">Open classroom</a>
                            </div>
                        @empty
                            <div class="list-group-item">No recent posts</div>
                        @endforelse
                    </div>
                </div>
            @else
                {{-- Teacher view --}}

                {{-- Accepted Invites --}}
                <div class="mb-4">
                    <h5>Accepted Invites</h5>
                    <div class="list-group">
                        @forelse($acceptedInvites as $ai)
                            <div class="list-group-item">
                                <div class="d-flex justify-content-between">
                                    <div><strong>{{ $ai->user?->name }}</strong> joined <strong>{{ $ai->classroom?->name }}</strong></div>
                                    <small class="text-muted">{{ optional($ai->updated_at)->diffForHumans() }}</small>
                                </div>
                            </div>
                        @empty
                            <div class="list-group-item">No accepted invites</div>
                        @endforelse
                    </div>
                </div>

                {{-- Declined Invites --}}
                <div class="mb-4">
                    <h5>Declined Invites</h5>
                    <div class="list-group">
                        @forelse($declinedInvites as $di)
                            <div class="list-group-item">
                                <div class="d-flex justify-content-between">
                                    <div><strong>{{ $di->user?->name }}</strong> declined invite to <strong>{{ $di->classroom?->name }}</strong></div>
                                    <small class="text-muted">{{ optional($di->updated_at)->diffForHumans() }}</small>
                                </div>
                            </div>
                        @empty
                            <div class="list-group-item">No declined invites</div>
                        @endforelse
                    </div>
                </div>

                {{-- Joined via Class Code --}}
                <div class="mb-4">
                    <h5>Students Joined via Class Code</h5>
                    <div class="list-group">
                        @forelse($joinedViaCode as $jvc)
                            <div class="list-group-item">
                                <div class="d-flex justify-content-between">
                                    <div><strong>{{ $jvc->user?->name }}</strong> joined <strong>{{ $jvc->classroom?->name }}</strong> via class code</div>
                                    <small class="text-muted">{{ optional($jvc->created_at)->diffForHumans() }}</small>
                                </div>
                            </div>
                        @empty
                            <div class="list-group-item">No students joined via class code</div>
                        @endforelse
                    </div>
                </div>

                {{-- Recent Posts --}}
                <div>
                    <h5>Recent Posts</h5>
                    <div class="list-group">
                        @forelse($teacherPosts as $tp)
                            <div class="list-group-item">
                                <div class="d-flex justify-content-between">
                                    <div><strong>{{ $tp->classroom?->name }}</strong></div>
                                    <small class="text-muted">{{ optional($tp->created_at)->diffForHumans() }}</small>
                                </div>
                                <div class="small text-muted">{{ $tp->user?->name }} posted</div>
                                <a class="small" href="{{ route('classroom.show', $tp->classroom_id) }}">Open classroom</a>
                            </div>
                        @empty
                            <div class="list-group-item">No recent posts</div>
                        @endforelse
                    </div>
                </div>
            @endif
        </div>
    </div>
</div>
@endsection
