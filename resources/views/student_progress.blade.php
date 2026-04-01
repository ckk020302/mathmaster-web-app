@extends('layouts.app')

@section('content')
<div class="container">
    <div class="d-flex justify-content-between align-items-center mb-3">
        <h3 class="mb-0">Progress: {{ $student->name }}</h3>
        <a href="{{ route('classroom.show', $class->id) }}" class="btn btn-sm btn-outline-secondary">Back to Class</a>
    </div>

    <div class="card mb-3">
        <div class="card-body d-flex align-items-center gap-3">
            <img src="{{ asset($student->avatar ?? 'profile.png') }}" class="rounded-circle" width="48" height="48" alt="{{ $student->name }}">
            <div>
                <div class="fw-bold">{{ $student->name }}</div>
                <div class="text-muted small">Email: {{ $student->email }}</div>
            </div>
        </div>
    </div>

    <div class="card">
        <div class="card-header d-flex justify-content-between align-items-center">
            <div class="fw-bold">Chapter Progress</div>
            <div>
                <select id="levelSelect" class="form-select form-select-sm">
                    <option value="Form 4">Form 4</option>
                    <option value="Form 5">Form 5</option>
                </select>
            </div>
        </div>
        <div class="card-body">
            <div id="progressList" class="d-flex flex-column gap-3"></div>
            <div id="emptyState" class="text-muted text-center d-none">No progress yet for this level.</div>
        </div>
    </div>
</div>

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function(){
  const userId = {{ (int) $student->id }};
  const levelSelect = document.getElementById('levelSelect');
  const list = document.getElementById('progressList');
  const empty = document.getElementById('emptyState');

  async function loadProgress(){
    list.innerHTML = '';
    empty.classList.add('d-none');
    const level = levelSelect.value;
    try {
      const url = new URL(`{{ url('/chapter-progress') }}` , window.location.origin);
      url.searchParams.set('user_id', userId);
      url.searchParams.set('academic_level', level);
      const res = await fetch(url.toString());
      const data = await res.json();
      if (!Array.isArray(data) || data.length === 0) {
        empty.classList.remove('d-none');
        return;
      }
      for (const row of data){
        const percent = Math.max(0, Math.min(100, parseInt(row.percent || 0)));
        const wrap = document.createElement('div');
        wrap.innerHTML = `
          <div>
            <div class="d-flex justify-content-between small mb-1">
              <strong>${escapeHtml(row.chapter || 'Untitled')}</strong>
              <span>${percent}%</span>
            </div>
            <div class="progress" role="progressbar" aria-valuemin="0" aria-valuemax="100" aria-valuenow="${percent}" style="height: 10px;">
              <div class="progress-bar bg-success" style="width:${percent}%;"></div>
            </div>
          </div>`;
        list.appendChild(wrap.firstElementChild);
      }
    } catch (e) {
      empty.textContent = 'Failed to load progress.';
      empty.classList.remove('d-none');
    }
  }

  function escapeHtml(s){
    return String(s||'').replaceAll('&','&amp;').replaceAll('<','&lt;').replaceAll('>','&gt;').replaceAll('"','&quot;').replaceAll("'",'&#39;');
  }

  levelSelect.addEventListener('change', loadProgress);
  loadProgress();
});
</script>
@endpush
@endsection

