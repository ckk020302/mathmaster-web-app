<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <title>Welcome • MathMaster Lab</title>
  <link rel="stylesheet" href="{{ asset('css/auth.css') }}">
</head>
<body>
  <div class="auth-split">
    <div class="auth-left">
      <div class="brand"><img src="{{ asset('plus.png') }}" alt="Plus"> MathMaster Lab</div>
      <div class="auth-panel welcome-wrap">
        <h1 class="welcome-title">Welcome to MathMaster Lab</h1>
        <div class="hero-actions">
          <a class="btn-primary btn-lg" href="{{ route('login.form') }}" style="text-decoration:none; text-align:center;">Get Started</a>
        </div>
      </div>
    </div>
    <div class="auth-right" style="background-image:url('{{ asset('maths formulae.jpg') }}')"></div>
  </div>
</body>
</html>
