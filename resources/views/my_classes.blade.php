@extends('layouts.app')

@section('content')
<div class="container">
    @if(session('success'))
        <div class="alert alert-success">{{ session('success') }}</div>
    @endif
    @if(session('error'))
        <div class="alert alert-danger">{{ session('error') }}</div>
    @endif

    <div class="row">
        <div class="col-md-6">
            <div class="card mb-4">
                <div class="card-header">
                    <h5 class="mb-0">Create New Class</h5>
                </div>
                <div class="card-body">
                    <form method="POST" action="{{ route('classrooms.create') }}">
                        @csrf
                        <div class="mb-3">
                            <label for="name" class="form-label">Class Name</label>
                            <input type="text" class="form-control" id="name" name="name" required maxlength="100">
                            @error('name')<div class="text-danger small">{{ $message }}</div>@enderror
                        </div>
                        <button type="submit" class="btn btn-primary">Create</button>
                    </form>
                </div>
            </div>
        </div>

        <div class="col-md-6">
            <div class="card mb-4">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <h5 class="mb-0">My Classes</h5>
                </div>
                <div class="card-body">
                    @forelse($classes as $c)
                        <div class="border rounded p-3 mb-3">
                            <div class="d-flex justify-content-between align-items-center">
                                <div>
                                    <div class="fw-bold">{{ $c->name }}</div>
                                    <div class="text-muted small">Code: <code>{{ $c->code }}</code> • Enrolled: {{ $c->enrollments_count }}</div>
                                </div>
                                <div class="d-flex gap-2">
                                    <a href="{{ route('classroom.show', $c->id) }}" class="btn btn-sm btn-outline-secondary">Open</a>
                                    <a href="{{ route('classroom.manage', $c->id) }}" class="btn btn-sm btn-outline-primary">Manage</a>
                                    <button type="button" class="btn btn-sm btn-danger" data-bs-toggle="modal" data-bs-target="#deleteClassModal-{{ $c->id }}">Delete</button>
                                </div>
                            </div>
                            <hr>
                            <form method="POST" action="{{ route('classroom.invite.send', $c->id) }}" class="d-flex gap-2">
                                @csrf
                                <input type="email" name="email" class="form-control" placeholder="Invite student by email" required>
                                <button type="submit" class="btn btn-sm btn-info text-white">Send Invite</button>
                            </form>
                            <!-- Delete Modal per class -->
                            <div class="modal fade" id="deleteClassModal-{{ $c->id }}" tabindex="-1" aria-labelledby="deleteClassModalLabel-{{ $c->id }}" aria-hidden="true">
                                <div class="modal-dialog">
                                    <div class="modal-content">
                                        <div class="modal-header">
                                            <h5 class="modal-title" id="deleteClassModalLabel-{{ $c->id }}">Confirm Delete Classroom</h5>
                                            <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                                        </div>
                                        <div class="modal-body">
                                            This action will permanently delete "{{ $c->name }}" including all posts, invitations, and enrollments. This cannot be undone.
                                        </div>
                                        <div class="modal-footer">
                                            <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                                            <form method="POST" action="{{ route('classroom.delete', $c->id) }}" class="d-inline">
                                                @csrf
                                                <button type="submit" class="btn btn-danger">Delete</button>
                                            </form>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    @empty
                        <div class="text-muted">You have not created any classes yet.</div>
                    @endforelse
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
