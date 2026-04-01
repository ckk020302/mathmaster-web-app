<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <meta name="csrf-token" content="{{ csrf_token() }}">
  <title>Signup</title>
  <link rel="stylesheet" href="{{ asset('css/auth.css') }}">
</head>
<body>
  <div class="auth-split">
    <div class="auth-left">
      <div class="brand"><img src="{{ asset('plus.png') }}" alt="Plus"> MathMaster Lab</div>
      <div class="auth-panel">
        <div class="panel-head">
          <h1>Create your account</h1>
        </div>

        @if ($errors->any())
          <div class="alert">
            @foreach ($errors->all() as $error)
              <div>{{ $error }}</div>
            @endforeach
          </div>
        @endif

        <form method="POST" action="{{ route('signup.submit') }}" class="form">
          @csrf
          <div class="field">
            <label class="muted small" for="role-student">Role</label>
            <div class="role-toggle" role="tablist" aria-label="Select role">
              {{-- Determine the selected role, prioritizing old input --}}
              @php
                $selectedRole = old('role', $role ?? 'student');
              @endphp

              <input type="radio" id="role-student" name="role" value="student" {{ $selectedRole === 'student' ? 'checked' : '' }}>
              <label for="role-student" role="tab" aria-controls="role-student">Student</label>
              <input type="radio" id="role-teacher" name="role" value="teacher" {{ $selectedRole === 'teacher' ? 'checked' : '' }}>
              <label for="role-teacher" role="tab" aria-controls="role-teacher">Teacher</label>
            </div>
          </div>
          <div class="field">
            <input type="text" name="name" placeholder="Username" value="{{ old('name') }}" required>
          </div>
          <div class="field">
            <input type="email" name="email" placeholder="Enter your email" value="{{ old('email') }}" required>
          </div>
          <div class="field password">
            <input type="password" id="password" name="password" placeholder="Enter your password" required>
            <button class="peek" type="button" aria-label="Show password" id="toggle-pass">
              <svg id="icon-eye" xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M1 12s4-7 11-7 11 7 11 7-4 7-11 7-11-7-11-7z"/><circle cx="12" cy="12" r="3"/></svg>
              <svg id="icon-eye-off" class="d-none" xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M17.94 17.94A10.94 10.94 0 0 1 12 20c-7 0-11-8-11-8a21.77 21.77 0 0 1 5.06-6.94"/><path d="M1 1l22 22"/><path d="M9.88 9.88A3 3 0 0 0 12 15a3 3 0 0 0 2.12-.88"/><path d="M7.11 7.11A10.94 10.94 0 0 1 12 4c7 0 11 8 11 8a21.8 21.8 0 0 1-3.12 4.56"/></svg>
            </button>
          </div>
          <div class="field password">
            <input type="password" id="password_confirmation" name="password_confirmation" placeholder="Confirm your password" required>
            <button class="peek" type="button" aria-label="Show password" id="toggle-pass-confirm">
              <svg id="icon-eye-c" xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M1 12s4-7 11-7 11 7 11 7-4 7-11 7-11-7-11-7z"/><circle cx="12" cy="12" r="3"/></svg>
              <svg id="icon-eye-off-c" class="d-none" xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M17.94 17.94A10.94 10.94 0 0 1 12 20c-7 0-11-8-11-8a21.77 21.77 0 0 1 5.06-6.94"/><path d="M1 1l22 22"/><path d="M9.88 9.88A3 3 0 0 0 12 15a3 3 0 0 0 2.12-.88"/><path d="M7.11 7.11A10.94 10.94 0 0 1 12 4c7 0 11 8 11 8a21.8 21.8 0 0 1-3.12 4.56"/></svg>
            </button>
          </div>
          <button type="submit" class="btn-primary">Sign Up</button>
        </form>

        <p class="muted small mt">Already have an account? <a href="{{ route('login.form') }}">Login</a></p>
      </div>
    </div>
    <div class="auth-right" style="background-image:url('{{ asset('maths formulae.jpg') }}')"></div>
  </div>

  <script>
    (function(){
      function setupToggle(inputId, onId, offId, btnId){
        const input = document.getElementById(inputId);
        const eye = document.getElementById(onId);
        const eyeOff = document.getElementById(offId);
        const btn = document.getElementById(btnId);
        if (!input || !btn) return;
        btn.addEventListener('click', function(){
          const showing = input.type === 'text';
          input.type = showing ? 'password' : 'text';
          eye.classList.toggle('d-none', !showing);
          eyeOff.classList.toggle('d-none', showing);
        });
      }
      setupToggle('password','icon-eye','icon-eye-off','toggle-pass');
      setupToggle('password_confirmation','icon-eye-c','icon-eye-off-c','toggle-pass-confirm');
    })();
  </script>
</body>
</html>