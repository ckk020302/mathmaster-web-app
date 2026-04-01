<!DOCTYPE html>
<html>
<body>
    <p>Hello,</p>
    <p>
        You have been invited by {{ $teacher->name }} to join the classroom
        <strong>{{ $classroom->name }}</strong>.
    </p>
    <p>
        Class code: <strong>{{ $classroom->code }}</strong>
    </p>
    <p>
        Login to your account and accept the invite on your dashboard, or join using the code above.
    </p>
    <p>Thanks!</p>
</body>
<script>
// Expose props for log mailer preview
window.teacher = @json($teacher);
window.classroom = @json($classroom);
</script>
</html>

