<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <title>Login - Select Role</title>
  <link rel="stylesheet" href="{{ asset('css/auth.css') }}">
</head>
<body>
  <div class="auth-split">
    <div class="auth-left">
      <div class="brand">MathMaster Lab</div>
      <div class="auth-panel">
        <h1>Welcome Back</h1>
        <p class="muted">What is your role?</p>
        <div class="btn-stack">
          <a class="btn-outline" href="{{ route('login.form', 'teacher') }}">Teacher</a>
          <a class="btn-outline" href="{{ route('login.form', 'student') }}">Student</a>
        </div>
      </div>
    </div>
    <div class="auth-right" style="background-image:url('{{ asset('maths formulae.jpg') }}')"></div>
  </div>
</body>
</html>
