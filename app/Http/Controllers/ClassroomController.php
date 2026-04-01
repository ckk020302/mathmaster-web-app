<?php

namespace App\Http\Controllers;

use Illuminate\Routing\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Schema;
use App\Models\{User, Classroom, Enrollment, Invitation, Post};
use Illuminate\Support\Facades\Mail;
use App\Mail\ClassroomInvite as ClassroomInviteMail;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule; // Don't forget to import this

class ClassroomController extends Controller
{
    private function classroomImage(int $id): string
    {
        $images = [
            1 => '/EXQq82JWkAAYtes.jpg',
            2 => '/1669652490608.jpeg',
            0 => '/original-83bd0a68be7542cc11c8b6a994c791d6.webp',
        ];
        $key = $id % 3; // 0,1,2
        return $images[$key] ?? '/EXQq82JWkAAYtes.jpg';
    }
    private function enrolledKey(): string
    {
        $email = strtolower((string) session('auth.user.email', 'guest'));
        return 'classrooms.enrolled.' . md5($email);
    }
    private function enrollmentsFile(): string
    {
        return 'private/enrollments.json';
    }
    private function readEnrollments(): array
    {
        $path = $this->enrollmentsFile();
        if (!Storage::exists($path)) {
            return [];
        }
        $json = Storage::get($path);
        $data = json_decode($json, true);
        return is_array($data) ? $data : [];
    }
    private function writeEnrollments(array $enrollments): void
    {
        Storage::makeDirectory('private');
        Storage::put($this->enrollmentsFile(), json_encode($enrollments, JSON_PRETTY_PRINT));
    }

    private function currentEmail(): string
    {
        return strtolower((string) session('auth.user.email', 'guest'));
    }
    private function getHardcodedUser()
    {
        return [
            'name' => 'John',
            'avatar' => '/profile.png',
        ];
    }

    public function index()
    {
        $email = $this->currentEmail();
        $user = User::whereRaw('LOWER(email) = ?', [$email])->first();

        $classrooms = [];
        $pendingInvites = [];
        if ($user) {
            // Enrolled classrooms
            $enrolled = Enrollment::where('user_id', $user->id)
                ->with(['classroom.teacher'])
                ->get()
                ->map(function ($en) {
                    $c = $en->classroom;
                    return [
                        'id' => $c->id,
                        'name' => $c->name,
                        'teacher' => $c->teacher?->name ?? 'Teacher',
                        'code' => $c->code,
                        'image' => $this->classroomImage((int)$c->id),
                    ];
                })->values()->all();

            // If teacher, also include classes they teach
            $teaches = [];
            if (($user->role ?? 'student') === 'teacher') {
                $teaches = Classroom::where('teacher_id', $user->id)
                    ->with('teacher')
                    ->get()
                    ->map(function ($c) {
                        return [
                            'id' => $c->id,
                            'name' => $c->name,
                            'teacher' => $c->teacher?->name ?? 'Teacher',
                            'code' => $c->code,
                            'image' => $this->classroomImage((int)$c->id),
                        ];
                    })->values()->all();
            }

            // Merge and dedupe by id
            $classrooms = collect(array_merge($enrolled, $teaches))
                ->unique('id')->values()->all();

            $pendingInvites = Invitation::where('user_id', $user->id)
                ->where('status', Invitation::STATUS_PENDING)
                ->with(['classroom.teacher'])
                ->get()
                ->map(function ($inv) {
                    $c = $inv->classroom;
                    return [
                        'id' => $c->id,
                        'name' => $c->name,
                        'teacher' => $c->teacher?->name ?? 'Teacher',
                        'code' => $c->code,
                        'image' => $this->classroomImage((int)($c->id)),
                    ];
                })->values()->all();
        }

        return view('classroom', compact('classrooms', 'pendingInvites'));
    }

    public function show($id)
    {
        $c = Classroom::with('teacher')->find($id);
        if (!$c) abort(404);

        $email = $this->currentEmail();
        $viewer = User::whereRaw('LOWER(email) = ?', [$email])->first();

        $classroom = [
            'id' => $c->id,
            'name' => $c->name,
            'details' => $c->details, // <--- ADD THIS LINE
            'teacher' => $c->teacher?->name ?? 'Teacher',
            'code' => $c->code,
            'is_owner' => $viewer ? ($c->teacher_id === $viewer->id) : false,
        ];

        // Load posts from DB and map to existing view shape
        $with = ['user'];
        try {
            if (Schema::hasTable('comments')) {
                $with[] = 'comments.user';
            }
        } catch (\Throwable $e) {
        }
        $posts = Post::where('classroom_id', $c->id)
            ->with($with)
            ->orderByDesc('created_at')
            ->get();
        $mapped = [];
        foreach ($posts as $p) {
            $mapped[$p->id] = [
                'id' => $p->id,
                'title' => 'New Post',
                'content' => $p->content,
                'user' => [
                    'name' => $p->user?->name ?? 'User',
                    'avatar' => $p->user?->avatar ?? '/profile.png',
                ],
                'timestamp' => optional($p->created_at)->format('M d, Y h:i A'),
                'edited_at' => ($p->updated_at && $p->updated_at->ne($p->created_at)) ? $p->updated_at->format('M d, Y h:i A') : null,
            ];
            if ($p->attachment_path) {
                // Build a host-relative URL so it works regardless of APP_URL host/port
                $rel = ltrim((string)$p->attachment_path, '/');
                $mapped[$p->id]['attachment_url'] = '/storage/' . $rel;
            }

            // Map comments
            $comments = [];
            foreach ($p->comments as $cm) {
                $comments[] = [
                    'id' => $cm->id,
                    'content' => $cm->content,
                    'user' => [
                        'name' => $cm->user?->name ?? 'User',
                        'avatar' => $cm->user?->avatar ?? '/profile.png',
                    ],
                    'timestamp' => optional($cm->created_at)->diffForHumans(),
                ];
            }
            $mapped[$p->id]['comments'] = $comments;
        }
        $classroom['posts'] = $mapped;

        $classroom['image'] = $this->classroomImage((int)$c->id);

        $people = [];
        $people[] = ['name' => $classroom['teacher'], 'role' => 'Teacher', 'avatar' => '/profile.png'];
        $students = Enrollment::where('classroom_id', $c->id)->with('user')->get();
        foreach ($students as $en) {
            // Skip teacher if also in enrollments by ID
            if ($en->user_id === $c->teacher_id) continue;
            $people[] = [
                'name' => $en->user?->name ?? 'Student',
                'role' => 'Student',
                'avatar' => $en->user?->avatar ?? '/profile.png',
                'user_id' => $en->user_id,
            ];
        }
        $classroom['people'] = $people;

        return view('classroom', compact('classroom'));
    }

public function notificationsIndex()
{
    $auth = (array) session('auth.user', []);
    $email = strtolower((string) ($auth['email'] ?? ''));
    $user = User::whereRaw('LOWER(email) = ?', [$email])->first();

    $isTeacher = (($auth['role'] ?? 'student') === 'teacher');

    $invites = collect();
    $studentPosts = collect();
    $acceptedInvites = collect();
    $declinedInvites = collect();
    $joinedViaCode = collect();
    $teacherPosts = collect();

    if ($user) {
        if ($isTeacher) {
            
    $classroomIds = Classroom::where('teacher_id', $user->id)->pluck('id');

    // Accepted invites
    $acceptedInvites = Invitation::where('status', Invitation::STATUS_ACCEPTED)
        ->whereIn('classroom_id', $classroomIds)
        ->with(['classroom', 'user'])
        ->orderByDesc('updated_at')
        ->get();

    // Declined invites
    $declinedInvites = Invitation::where('status', Invitation::STATUS_DECLINED)
        ->whereIn('classroom_id', $classroomIds)
        ->with(['classroom', 'user'])
        ->orderByDesc('updated_at')
        ->get();

    // Posts by teacher's classrooms
    $teacherPosts = Post::whereIn('classroom_id', $classroomIds)
        ->with(['classroom', 'user'])
        ->orderByDesc('created_at')
        ->get();

    // Enrollments in these classrooms
    $enrollments = Enrollment::whereIn('classroom_id', $classroomIds)
        ->with(['user', 'classroom'])
        ->get();

    // Filter enrollments that do NOT have an accepted invitation
    $joinedViaCode = $enrollments->filter(function ($enrollment) {
        return !Invitation::where('user_id', $enrollment->user_id)
            ->where('classroom_id', $enrollment->classroom_id)
            ->where('status', Invitation::STATUS_ACCEPTED)
            ->exists();
    })->sortByDesc('created_at');
}
 else {
            // Student notifications (unchanged)
            $invites = Invitation::where('user_id', $user->id)
                ->with('classroom.teacher')
                ->orderByDesc('created_at')
                ->get();

            $classIds = Enrollment::where('user_id', $user->id)->pluck('classroom_id');
            $studentPosts = Post::whereIn('classroom_id', $classIds)
                ->where('user_id', '<>', $user->id)
                ->with(['classroom', 'user'])
                ->orderByDesc('created_at')
                ->get();
        }
    }

    session(['notifications.last_seen_at' => now()]);

    return view('notifications', compact(
        'isTeacher', 'invites', 'studentPosts',
        'acceptedInvites', 'declinedInvites', 'joinedViaCode', 'teacherPosts'
    ));
}



    public function notificationsSeen()
    {
        session(['notifications.last_seen_at' => now()]);
        return response()->noContent();
    }

    public function assignmentsIndex()
    {
        // Placeholder assignments page
        return view('assignments');
    }

    public function studyIndex()
    {
        // Placeholder study room page
        return view('study_room');
    }

    public function questionBankIndex()
    {
        return view('question_bank');
    }

// In ClassroomController.php

public function profileIndex()
{
    // Pull name/avatar from current session user; compute achievements from DB
    $auth = (array) session('auth.user', []);
    $name = (string) ($auth['name'] ?? 'Guest');
    $avatar = (string) ($auth['avatar'] ?? '/profile.png');
    // Extract email and role from session, or provide defaults
    $email = strtolower((string) ($auth['email'] ?? '')); 
    $role = (string) ($auth['role'] ?? 'student'); // Default role if not in session

    $dbUser = null;
    if ($email !== 'guest' && $email !== '') { // Only try to find in DB if email is valid
        $dbUser = User::whereRaw('LOWER(email) = ?', [$email])->first();
    }

    // If a DB user is found, prefer its name, email, role, and avatar
    if ($dbUser) {
        $name = $dbUser->name;
        $avatar = $dbUser->avatar;
        $email = $dbUser->email; // Get email from DB user
        $role = $dbUser->role;   // Get role from DB user
    }


    $totalCorrect = 0;
    $chaptersCompleted = 0;
    try {
        // Sum correct answers across all finished rounds
        // Only proceed if dbUser is available
        if ($dbUser) { 
            $rounds = \App\Models\AnswerQuestionModels::where('user_id', $dbUser->id)->get();
            foreach ($rounds as $r) {
                $answers = (array) ($r->answers ?? []);
                foreach ($answers as $a) {
                    if (!empty($a['is_correct'])) {
                        $totalCorrect++;
                    }
                }
            }
            // Compute chapters with all 3 difficulties finished
            $byChapter = [];
            foreach ($rounds as $r) {
                if (($r->status ?? '') !== 'finished') continue;
                $chap = (string) $r->chapter;
                $diff = strtolower((string) $r->difficulty);
                if ($chap === '') continue;
                $byChapter[$chap] = $byChapter[$chap] ?? [];
                $byChapter[$chap][$diff] = true;
            }
            foreach ($byChapter as $chap => $set) {
                if ((int) (isset($set['easy'])) + (int) (isset($set['intermediate'])) + (int) (isset($set['advanced'])) >= 3) {
                    $chaptersCompleted++;
                }
            }
        }
    } catch (\Throwable $e) {
        $totalCorrect = 0;
        $chaptersCompleted = 0;
    }

    $achievements = [
        ['title' => 'First Solver', 'description' => 'solve the first question', 'image' => '/badge.png', 'unlocked' => $totalCorrect >= 1],
        ['title' => 'Knowledge Builder', 'description' => 'solved 100 questions in total', 'image' => '/medal.png', 'unlocked' => $totalCorrect >= 100],
        ['title' => 'Question Crusher', 'description' => 'solved 200 questions in total', 'image' => '/medal (1).png', 'unlocked' => $totalCorrect >= 200],
        ['title' => 'Quick Learner', 'description' => 'finished 1 chapter', 'image' => '/well-done.png', 'unlocked' => $chaptersCompleted >= 1],
        ['title' => 'Dedicated Learner', 'description' => 'finished 10 chapter', 'image' => '/good-job.png', 'unlocked' => $chaptersCompleted >= 10],
        ['title' => 'Chapter Master', 'description' => 'finished 20 chapter', 'image' => '/great-job.png', 'unlocked' => $chaptersCompleted >= 20],
    ];

    $user = [
        'name' => $name,
        'avatar' => $avatar,
        'email' => $email, // <--- ADD EMAIL HERE
        'role' => $role,   // <--- ADD ROLE HERE
        'wreath_url' => '/wreath.png', // Assuming this is static or derived elsewhere
        'achievements' => $achievements,
        'stats' => ['total_correct' => $totalCorrect, 'chapters_completed' => $chaptersCompleted],
    ];

    return view('profile', compact('user'));
}

public function join(Request $request)
{
    $code = strtoupper(trim((string)$request->input('code')));
    if ($code === '') {
        return response()->json(['success' => false, 'message' => 'Class code is required.'], 422);
    }

    $email = $this->currentEmail();
    $user = User::whereRaw('LOWER(email) = ?', [$email])->first();
    if (!$user) {
        return response()->json(['success' => false, 'message' => 'Please login first.'], 401);
    }

    $c = Classroom::whereRaw('UPPER(code) = ?', [$code])->with('teacher', 'people')->first();
    if (!$c) {
        return response()->json(['success' => false, 'message' => 'Invalid class code. Please try again.'], 422);
    }

    // Check if class is full based on max_size
    $currentCount = $c->people->count(); // assuming 'people' relation includes enrolled students
    if ($c->max_size !== null && $currentCount >= $c->max_size) {
        return response()->json(['success' => false, 'message' => 'This classroom is full and cannot accept more students.'], 422);
    }

    Enrollment::firstOrCreate(['user_id' => $user->id, 'classroom_id' => $c->id]);

    $payload = [
        'id' => $c->id,
        'name' => $c->name,
        'teacher' => $c->teacher?->name ?? 'Teacher',
        'code' => $c->code,
    ];

    $html = view('partials.classroom_card', ['classroom' => $payload])->render();

    return response()->json([
        'success' => true,
        'message' => 'Successfully enrolled in ' . $c->name . '!',
        'id'      => $c->id,
        'html'    => $html,
    ]);
}

    public function storePost(Request $request, $id)
    {
        $request->validate([
            'content' => 'nullable|string|max:255|required_without:attachment',
            'attachment' => 'nullable|file|mimes:jpg,jpeg,png,gif,webp,pdf|max:4096|required_without:content',
        ]);

        $email = $this->currentEmail();
        $user = User::whereRaw('LOWER(email) = ?', [$email])->first();
        if (!$user) {
            return redirect()->route('login.form');
        }

        // Only the class teacher can create posts
        $class = Classroom::find((int)$id);
        if (!$class) {
            return redirect()->route('dashboard')->with('error', 'Classroom not found.');
        }
        if ($class->teacher_id !== $user->id) {
            return redirect()->route('classroom.show', $id)->with('error', 'Only the class teacher can create posts.');
        }

$attachmentPath = null;
if ($request->hasFile('attachment')) {
    $path = $request->file('attachment')->store('attachments', 'public');
    $attachmentPath = $path; // store relative path like 'attachments/filename.png'
}


        Post::create([
            'classroom_id' => (int)$id,
            'user_id' => $user->id,
            'content' => $request->input('content'),
            'attachment_path' => $attachmentPath,
        ]);

        return redirect()->route('classroom.show', $id);
    }


public function updatePost(Request $request, $id, $postIndex)
{
    // 1. Authorization & Fetching (existing code)
    $email = $this->currentEmail();
    $user = User::whereRaw('LOWER(email) = ?', [$email])->first();
    $class = Classroom::find((int)$id);

    if (!$class) {
        return redirect()->route('dashboard')->with('error', 'Classroom not found.');
    }
    if (!$user || $class->teacher_id !== $user->id) {
        return redirect()->route('classroom.show', $id)->with('error', 'Only the class teacher can edit posts.');
    }

    $post = Post::where('classroom_id', $id)->where('id', $postIndex)->first();
    if (!$post) {
        return redirect()->route('classroom.show', $id)->with('error', 'Post not found.');
    }

    // 2. Validation (existing code)
    $request->validate([
        'content' => ['nullable', 'string', 'max:5000'],
        'attachment' => ['nullable', 'file', 'mimes:jpg,jpeg,png,gif,webp,pdf', 'max:10240'],
        'remove_attachment' => ['boolean'],
    ]);

    // 3. Conditional check to ensure at least content OR an attachment exists (after potential changes)
    $hasNewContent = !empty($request->input('content'));
    $hasNewAttachment = $request->hasFile('attachment');
    $removingOldAttachment = $request->boolean('remove_attachment');
    $hasExistingAttachment = !empty($post->attachment_path);

    // If no new content, no new attachment, and the old attachment is being removed, and there was no existing attachment, it's an error.
    // If no new content, no new attachment, and the old attachment existed but is now being removed, it's an error.
    // If no new content, no new attachment, and there was NO existing attachment, it's an error.
    $willHaveContent = $hasNewContent;
    $willHaveAttachment = $hasNewAttachment || ($hasExistingAttachment && !$removingOldAttachment);

    if (!$willHaveContent && !$willHaveAttachment) {
        return back()->withErrors(['content' => 'Either post content or an attachment is required.'])->withInput();
    }

    // 4. Handle Attachment Removal (existing code)
    if ($removingOldAttachment) {
        if (!empty($post->attachment_path)) {
            Storage::disk('public')->delete($post->attachment_path);
        }
        $post->attachment_path = null;
    }

    // 5. Handle New Attachment Upload (existing code)
    if ($hasNewAttachment) {
        // Delete old attachment if a new one is being uploaded, unless it was already explicitly removed
        if (!empty($post->attachment_path) && !$removingOldAttachment) {
            Storage::disk('public')->delete($post->attachment_path);
        }
        $path = $request->file('attachment')->store('attachments', 'public');
        $post->attachment_path = $path;
    }

    // 6. Update Post Content (existing code)
    $post->content = $request->input('content');

    // 7. Save Changes & Mark as Edited (existing code)
    if ($post->isDirty('content') || $post->isDirty('attachment_path')) {
        $post->edited_at = now();
    }
    $post->save();

    return redirect()->route('classroom.show', $id)->with('success', 'Post updated successfully!');
}
    public function deletePost($id, $postIndex)
    {
        $email = $this->currentEmail();
        $user = User::whereRaw('LOWER(email) = ?', [$email])->first();
        $class = Classroom::find((int)$id);
        if (!$class) {
            return redirect()->route('dashboard')->with('error', 'Classroom not found.');
        }
        if (!$user || $class->teacher_id !== $user->id) {
            return redirect()->route('classroom.show', $id)->with('error', 'Only the class teacher can delete posts.');
        }

        $post = Post::where('classroom_id', $id)->where('id', $postIndex)->first();
        if ($post) {
            try {
                if (!empty($post->attachment_path)) {
                    // Remove the stored attachment file from the public disk
                    Storage::disk('public')->delete($post->attachment_path);
                }
            } catch (\Throwable $e) {
                // Ignore storage errors on delete; proceed with DB deletion
            }
            $post->delete();
        }
        return redirect()->route('classroom.show', $id);
    }

    public function leaveClassroom($id)
    {
        session()->forget('classroom.posts.' . $id);

        $email = $this->currentEmail();
        $user = User::whereRaw('LOWER(email) = ?', [$email])->first();
        if ($user) {
            Enrollment::where('user_id', $user->id)->where('classroom_id', $id)->delete();
        }

        if (request()->expectsJson()) {
            return response()->json(['success' => true, 'message' => 'Left classroom successfully.']);
        }
        return redirect()->route('dashboard')->with('success', 'You have successfully left the classroom.');
    }

    // Accept a pending invitation: move to enrollments and remove invite
    public function acceptInvite($id)
    {
        $email = $this->currentEmail();
        $user = User::whereRaw('LOWER(email) = ?', [$email])->first();
        if ($user) {
            $inv = Invitation::where('user_id', $user->id)->where('classroom_id', $id)->where('status', Invitation::STATUS_PENDING)->first();
            if ($inv) {
                $inv->status = Invitation::STATUS_ACCEPTED;
                $inv->save();
                Enrollment::firstOrCreate(['user_id' => $user->id, 'classroom_id' => $id]);
            }
        }
        return redirect()->route('dashboard');
    }

    // Decline a pending invitation: remove invite only
    public function declineInvite($id)
    {
        $email = $this->currentEmail();
        $user = User::whereRaw('LOWER(email) = ?', [$email])->first();
        if ($user) {
            $inv = Invitation::where('user_id', $user->id)->where('classroom_id', $id)->where('status', Invitation::STATUS_PENDING)->first();
            if ($inv) {
                $inv->status = Invitation::STATUS_DECLINED;
                $inv->save();
            }
        }
        return redirect()->route('dashboard');
    }

    // Dev helper: add a sample invite for current user
    public function devAddInvite(Request $request)
    {
        $email = $this->currentEmail();
        $user = User::whereRaw('LOWER(email) = ?', [$email])->first();
        if (!$user) {
            return redirect()->route('login.form')->with('error', 'Please login first.');
        }

        $classId = (int) ($request->query('id', 1));
        $class = Classroom::find($classId);
        if (!$class) {
            return redirect()->route('dashboard')->with('error', 'Classroom not found.');
        }

        Invitation::firstOrCreate(['user_id' => $user->id, 'classroom_id' => $class->id], [
            'status' => Invitation::STATUS_PENDING,
        ]);

        return redirect()->route('dashboard')->with('success', 'Test invite added.');
    }

    // Teacher-only: list classes, create new ones, and invite students
    public function myClassesIndex()
    {
        $auth = (array) session('auth.user', []);
        if (($auth['role'] ?? 'student') !== 'teacher') {
            return redirect()->route('dashboard')->with('error', 'Only teachers can access My Classes.');
        }

        $email = strtolower((string)($auth['email'] ?? ''));
        $teacher = User::whereRaw('LOWER(email) = ?', [$email])->first();
        if (!$teacher) return redirect()->route('dashboard')->with('error', 'Please login again.');

        $classes = Classroom::where('teacher_id', $teacher->id)
            ->withCount('enrollments')
            ->orderBy('created_at', 'desc')
            ->get();

        return view('my_classes', compact('classes'));
    }

    public function createClassroom(Request $request)
    {
        $auth = (array) session('auth.user', []);
        if (($auth['role'] ?? 'student') !== 'teacher') {
            return redirect()->route('dashboard')->with('error', 'Only teachers can create classes.');
        }

        $data = $request->validate([
            'name' => 'required|string|max:100',
            'details' => 'nullable|string|max:500',
            'max_size' => 'nullable|integer|min:1|max:500',
        ]);

        $email = strtolower((string)($auth['email'] ?? ''));
        $teacher = User::whereRaw('LOWER(email) = ?', [$email])->first();
        if (!$teacher) return redirect()->route('dashboard')->with('error', 'Please login again.');

        // generate unique 6-char uppercase code
        do {
            $code = strtoupper(Str::random(6));
        } while (Classroom::where('code', $code)->exists());

        Classroom::create([
            'name' => $data['name'],
            'details' => $data['details'] ?? null,
            'max_size' => $data['max_size'] ?? null,
            'code' => $code,
            'teacher_id' => $teacher->id,
        ]);

        return redirect()->route('dashboard')->with('success', 'Classroom created. Share the code: ' . $code);
    }

public function sendInvite(Request $request, $id)
{
    $auth = (array) session('auth.user', []);

    if (($auth['role'] ?? 'student') !== 'teacher') {
        return back()->with('error', 'Only teachers can send invites.');
    }

    $request->validate([
        'email' => 'required|email',
    ]);

    $class = Classroom::with('people')->find($id);
    if (!$class) {
        return back()->with('error', 'Classroom not found.');
    }

    // Check if class is full before sending invite
    $currentCount = $class->people->count();
    if ($class->max_size !== null && $currentCount >= $class->max_size) {
        return back()->with('error', 'Classroom is full. You cannot invite more students.');
    }

$email = strtolower($request->input('email'));
$student = User::whereRaw('LOWER(email) = ?', [$email])->first();

if (!$student) {
    return back()->with('error', 'Student email is not registered.');
}

if (($student->role ?? '') !== 'student') {
    return back()->with('error', 'You can only invite registered students.');
}

if ($student->id === $class->teacher_id) {
    return back()->with('error', 'Cannot invite the class teacher.');
}

    $already = Enrollment::where('user_id', $student->id)
        ->where('classroom_id', $class->id)
        ->exists();

    if ($already) {
        return back()->with('info', 'This student is already enrolled in the class.');
    }

    // Update or create invitation
    $invite = Invitation::updateOrCreate(
        ['user_id' => $student->id, 'classroom_id' => $class->id],
        ['status' => Invitation::STATUS_PENDING]
    );

    // Send email (optional)
    try {
        $teacher = User::find($auth['id']) ?? $class->teacher;
        Mail::to($student->email)->send(new ClassroomInviteMail($class, $teacher));
    } catch (\Throwable $e) {
        // Optional: Log this if needed
        // Log::error('Invite email failed: ' . $e->getMessage());
    }

    return back()->with('success', 'Invitation sent to ' . $student->email);
}


    public function manageClassroom($id)
    {
        $auth = (array) session('auth.user', []);
        if (($auth['role'] ?? 'student') !== 'teacher') {
            return redirect()->route('dashboard')->with('error', 'Only teachers can manage classes.');
        }
        $teacher = User::whereRaw('LOWER(email) = ?', [strtolower((string)($auth['email'] ?? ''))])->first();
        $class = Classroom::with(['enrollments.user'])->find($id);
        if (!$class || ($class->teacher_id !== $teacher?->id)) {
            return redirect()->route('dashboard')->with('error', 'Classroom not found or not owned by you.');
        }
        return view('manage_class', compact('class'));
    }

    public function removeEnrollment($id, $user)
    {
        $auth = (array) session('auth.user', []);
        if (($auth['role'] ?? 'student') !== 'teacher') {
            return back()->with('error', 'Only teachers can remove enrollments.');
        }
        $teacher = User::whereRaw('LOWER(email) = ?', [strtolower((string)($auth['email'] ?? ''))])->first();
        $class = Classroom::find($id);
        if (!$class || ($class->teacher_id !== $teacher?->id)) {
            return back()->with('error', 'Classroom not found or not owned by you.');
        }
        Enrollment::where('classroom_id', $class->id)->where('user_id', $user)->delete();
        return back()->with('success', 'Removed from class.');
    }

    // Teacher-only: delete an owned classroom
    public function deleteClassroom($id)
    {
        $auth = (array) session('auth.user', []);
        if (($auth['role'] ?? 'student') !== 'teacher') {
            return redirect()->route('dashboard')->with('error', 'Only teachers can delete classes.');
        }

        $teacher = User::whereRaw('LOWER(email) = ?', [strtolower((string)($auth['email'] ?? ''))])->first();
        $class = Classroom::find($id);
        if (!$class || ($class->teacher_id !== $teacher?->id)) {
            return redirect()->route('dashboard')->with('error', 'Classroom not found or not owned by you.');
        }

        // Clean up any stored attachments for posts before deleting classroom
        try {
            $attachments = Post::where('classroom_id', $class->id)
                ->whereNotNull('attachment_path')
                ->pluck('attachment_path');
            foreach ($attachments as $path) {
                try {
                    Storage::disk('public')->delete($path);
                } catch (\Throwable $e) { /* ignore */
                }
            }
        } catch (\Throwable $e) { /* ignore */
        }

        // Deleting the classroom will cascade delete enrollments, invitations, posts (per FKs)
        $class->delete();

        return redirect()->route('dashboard')->with('success', 'Classroom deleted successfully.');
    }

    // Teacher-only: see a student's progress (assignments)
    public function studentProgress($id, $user)
    {
        $auth = (array) session('auth.user', []);
        if (($auth['role'] ?? 'student') !== 'teacher') {
            return redirect()->route('dashboard')->with('error', 'Only teachers can view student progress.');
        }

        $teacher = User::whereRaw('LOWER(email) = ?', [strtolower((string)($auth['email'] ?? ''))])->first();
        $class = Classroom::find($id);
        if (!$class || ($class->teacher_id !== $teacher?->id)) {
            return redirect()->route('dashboard')->with('error', 'Classroom not found or not owned by you.');
        }

        // Ensure the user is/was enrolled in this class
        $isMember = Enrollment::where('classroom_id', $class->id)->where('user_id', $user)->exists();
        if (!$isMember) {
            return redirect()->route('classroom.show', $id)->with('error', 'Student is not enrolled in this class.');
        }

        $student = User::find($user);
        if (!$student) {
            return redirect()->route('classroom.show', $id)->with('error', 'Student not found.');
        }

        return view('student_progress', [
            'class' => $class,
            'student' => $student,
        ]);
    }

    public function storeComment(Request $request, $id, $postIndex)
    {
        $request->validate([
            'content' => 'required|string|max:500',
        ]);

        $email = $this->currentEmail();
        $user = User::whereRaw('LOWER(email) = ?', [$email])->first();
        if (!$user) return redirect()->route('login.form');

        $class = Classroom::find((int)$id);
        if (!$class) return redirect()->route('dashboard')->with('error', 'Classroom not found.');

        // Allow teacher or any enrolled student to comment
        $isTeacher = ($class->teacher_id === $user->id);
        $isEnrolled = Enrollment::where('classroom_id', $class->id)->where('user_id', $user->id)->exists();
        if (!$isTeacher && !$isEnrolled) {
            return redirect()->route('classroom.show', $id)->with('error', 'Only class members can comment.');
        }

        $post = Post::where('classroom_id', $class->id)->where('id', $postIndex)->first();
        if (!$post) return redirect()->route('classroom.show', $id)->with('error', 'Post not found.');

        \App\Models\Comment::create([
            'post_id' => $post->id,
            'user_id' => $user->id,
            'content' => $request->input('content'),
        ]);

        return redirect()->route('classroom.show', $id);
    }
}
