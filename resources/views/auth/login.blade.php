<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <meta name="csrf-token" content="{{ csrf_token() }}">
  <title>Login</title>
  <link rel="stylesheet" href="{{ asset('css/auth.css') }}">
</head>
<body>
  <div class="auth-split">
    <div class="auth-left">
      <div class="brand"><img src="{{ asset('plus.png') }}" alt="Plus"> MathMaster Lab</div>
      <div class="auth-panel">
        <div class="panel-head">
          <h1>Welcome back!</h1>
        </div>

        @if ($errors->any())
          <div class="alert">
            @foreach ($errors->all() as $error)
              <div>{{ $error }}</div>
            @endforeach
          </div>
        @endif

        <form method="POST" action="{{ route('login.submit') }}" class="form">
          @csrf
          {{-- Role is inferred from the stored account --}}
          <div class="field">
            <input type="email" name="email" placeholder="Enter your email" required>
          </div>
          <div class="field password">
            <input type="password" id="password" name="password" placeholder="Enter your password" required>
            <button class="peek" type="button" aria-label="Show password" id="toggle-pass">
              <svg id="icon-eye" xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M1 12s4-7 11-7 11 7 11 7-4 7-11 7-11-7-11-7z"/><circle cx="12" cy="12" r="3"/></svg>
              <svg id="icon-eye-off" class="d-none" xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M17.94 17.94A10.94 10.94 0 0 1 12 20c-7 0-11-8-11-8a21.77 21.77 0 0 1 5.06-6.94"/><path d="M1 1l22 22"/><path d="M9.88 9.88A3 3 0 0 0 12 15a3 3 0 0 0 2.12-.88"/><path d="M7.11 7.11A10.94 10.94 0 0 1 12 4c7 0 11 8 11 8a21.8 21.8 0 0 1-3.12 4.56"/></svg>
            </button>
          </div>
          <button type="submit" class="btn-primary">Login</button>
        </form>

        <p class="muted small mt">Don't have an account? <a href="{{ route('signup.form') }}">Signup</a></p>
      </div>
    </div>
    <div class="auth-right" style="background-image:url('{{ asset('maths formulae.jpg') }}')"></div>
  </div>
</body>
</html>

<script>
  (function(){
    const input = document.getElementById('password');
    const btn = document.getElementById('toggle-pass');
    const eye = document.getElementById('icon-eye');
    const eyeOff = document.getElementById('icon-eye-off');
    if (!btn || !input) return;
    btn.addEventListener('click', function(){
      const showing = input.type === 'text';
      input.type = showing ? 'password' : 'text';
      if (showing) {
        eye.classList.remove('d-none');
        eyeOff.classList.add('d-none');
        btn.setAttribute('aria-label','Show password');
      } else {
        eye.classList.add('d-none');
        eyeOff.classList.remove('d-none');
        btn.setAttribute('aria-label','Hide password');
      }
    });
  })();
</script>
