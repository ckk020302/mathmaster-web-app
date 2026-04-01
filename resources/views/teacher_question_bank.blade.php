@extends('layouts.app')

@section('content')
<div class="container">
  <div class="row">
    <div class="col-lg-6">
      @if(session('success'))<div class="alert alert-success">{{ session('success') }}</div>@endif
      @if(session('error'))<div class="alert alert-danger">{{ session('error') }}</div>@endif

      <div class="card mb-4">
        <div class="card-header"><strong>Upload Question</strong></div>
        <div class="card-body">
          <form method="POST" action="{{ route('teacher.questionbank.store') }}" enctype="multipart/form-data">
            @csrf
            <div class="mb-2">
              <label class="form-label">Academic Level</label>
              <select class="form-select" name="academic_level" required>
                <option>Form 4</option>
                <option>Form 5</option>
              </select>
            </div>
            <div class="mb-2">
              <label class="form-label">Chapter</label>
              <input type="text" class="form-control" name="chapter" placeholder="e.g. Functions" required>
            </div>
            <div class="mb-2">
              <label class="form-label">Subject</label>
              <input type="text" class="form-control" name="subject" value="Mathematics" required>
            </div>
            <div class="mb-2">
              <label class="form-label">Difficulty</label>
              <select class="form-select" name="difficulty" required>
                <option>Easy</option>
                <option>Intermediate</option>
                <option>Advanced</option>
              </select>
            </div>
            <div class="mb-2">
              <label class="form-label">Question Text</label>
              <input type="text" class="form-control" name="question_name" required>
            </div>
            <div class="mb-2">
              <label class="form-label">Question Image (optional)</label>
              <input type="file" class="form-control" name="question_image" accept="image/*">
              <div class="form-text">Stored publicly; recommended width up to 900px.</div>
            </div>
            <div class="row g-2 mb-2">
              <div class="col-6"><input class="form-control" name="option_a" placeholder="Option A" required></div>
              <div class="col-6"><input class="form-control" name="option_b" placeholder="Option B" required></div>
              <div class="col-6"><input class="form-control" name="option_c" placeholder="Option C" required></div>
              <div class="col-6"><input class="form-control" name="option_d" placeholder="Option D" required></div>
            </div>
            <div class="mb-2">
              <label class="form-label">Correct Answer</label>
              <select class="form-select" name="answer" required>
                <option>A</option><option>B</option><option>C</option><option>D</option>
              </select>
            </div>
            <div class="mb-2">
              <label class="form-label">Tip (Easy)</label>
              <textarea class="form-control" name="tip_easy" rows="2" placeholder="Short hint for Easy"></textarea>
            </div>
            <div class="mb-2">
              <label class="form-label">Tip (Intermediate)</label>
              <textarea class="form-control" name="tip_intermediate" rows="2" placeholder="Hint for Intermediate"></textarea>
            </div>
            <div class="mb-3">
              <label class="form-label">Tip (Advanced)</label>
              <textarea class="form-control" name="tip_advanced" rows="2" placeholder="Hint for Advanced"></textarea>
            </div>
            <button class="btn btn-primary">Save Question</button>
          </form>
        </div>
      </div>
    </div>

    <div class="col-lg-6">
      <div class="card">
        <div class="card-header"><strong>Recent Questions</strong></div>
        <div class="card-body">
          <div class="list-group">
            @forelse($recent as $q)
              <div class="list-group-item">
                <div class="d-flex justify-content-between align-items-center">
                  <div>
                    <div class="fw-bold">{{ $q->question_name }}</div>
                    <div class="small text-muted">{{ $q->academic_level }} — {{ $q->chapter }} — {{ $q->difficulty }}</div>
                  </div>
                  @if($q->question_image)
                    <img src="{{ $q->question_image_url ?? asset('storage/' . $q->question_image) }}" alt="img" style="height:40px;width:auto;">
                  @endif
                </div>
              </div>
            @empty
              <div class="text-muted">No questions yet.</div>
            @endforelse
          </div>
        </div>
      </div>
    </div>
  </div>
</div>
@endsection
