@extends('layouts.app')

@section('content')
<div class="container">
    <div class="d-flex justify-content-between align-items-center mb-3">
        <h3 class="mb-0">Manage: {{ $class->name }}</h3>
        <a href="{{ route('teacher.classes') }}" class="btn btn-sm btn-outline-secondary">Back to My Classes</a>
    </div>

    @if(session('success'))
        <div class="alert alert-success">{{ session('success') }}</div>
    @endif
    @if(session('error'))
        <div class="alert alert-danger">{{ session('error') }}</div>
    @endif

    <div class="card mb-4">
        <div class="card-header d-flex justify-content-between align-items-center">
            <div>
                <strong>Class Code:</strong> <code>{{ $class->code }}</code>
            </div>
            <div class="d-flex gap-2">
                <a href="{{ route('classroom.show', $class->id) }}" class="btn btn-sm btn-outline-primary">Open Class</a>
                <button type="button" class="btn btn-sm btn-danger" data-bs-toggle="modal" data-bs-target="#deleteClassModal-{{ $class->id }}">Delete Class</button>
            </div>
        </div>
        <div class="card-body">
            <h5>Enrolled Students</h5>
            <div class="list-group">
                @forelse($class->enrollments as $en)
                    <div class="list-group-item d-flex justify-content-between align-items-center">
                        <div>
                            <strong>{{ $en->user?->name ?? 'Student' }}</strong>
                            <div class="text-muted small">{{ $en->user?->email }}</div>
                        </div>
                        <form method="POST" action="{{ route('classroom.enrollment.remove', [$class->id, $en->user_id]) }}" onsubmit="return confirm('Remove this student from class?');">
                            @csrf
                            <button type="submit" class="btn btn-sm btn-danger">Remove</button>
                        </form>
                    </div>
                @empty
                    <div class="list-group-item">No students enrolled yet.</div>
                @endforelse
            </div>
        </div>
    </div>

    <!-- Delete Classroom Modal -->
    <div class="modal fade" id="deleteClassModal-{{ $class->id }}" tabindex="-1" aria-labelledby="deleteClassModalLabel-{{ $class->id }}" aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="deleteClassModalLabel-{{ $class->id }}">Confirm Delete Classroom</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    This action will permanently delete "{{ $class->name }}" including all posts, invitations, and enrollments. This cannot be undone.
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <form method="POST" action="{{ route('classroom.delete', $class->id) }}" class="d-inline">
                        @csrf
                        <button type="submit" class="btn btn-danger">Delete</button>
                    </form>
                </div>
            </div>
        </div>
    </div>

    <div class="card">
        <div class="card-header">
            <h5 class="mb-0">Invite Student</h5>
        </div>
        <div class="card-body">
            <form method="POST" action="{{ route('classroom.invite.send', $class->id) }}" class="row g-2">
                @csrf
                <div class="col-md-8">
                    <input type="email" name="email" class="form-control" placeholder="Student email" required>
                </div>
                <div class="col-md-4">
                    <button class="btn btn-info text-white" type="submit">Send Invite</button>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection
