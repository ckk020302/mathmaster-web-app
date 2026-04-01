<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\ChatController;
use App\Http\Controllers\ClassroomController;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\QuestionController;
use App\Http\Controllers\AnswerQuestionController;
use App\Http\Controllers\StudyRoomController;

/*
|--------------------------------------------------------------------------
| Public / Landing
|--------------------------------------------------------------------------
*/
// Welcome landing where users choose Login or Signup
Route::get('/', [AuthController::class, 'showWelcome'])->name('welcome');

/*
|--------------------------------------------------------------------------
| Auth (simple, session-based)
|--------------------------------------------------------------------------
*/
// Login does not require choosing role
Route::get('/login', [AuthController::class, 'showLoginForm'])->name('login.form');
Route::post('/login', [AuthController::class, 'login'])->name('login.submit');
Route::post('/logout', [AuthController::class, 'logout'])->name('logout');
// Signup lets the user choose role on the form (optional route param to pre-select)
Route::get('/signup/{role?}', [AuthController::class, 'showSignupForm'])->name('signup.form');
Route::post('/signup', [AuthController::class, 'signup'])->name('signup.submit');

/*
|--------------------------------------------------------------------------
| Classroom
|--------------------------------------------------------------------------
*/

// Index (list of classes) — this is the one your Blade expects
Route::get('/classroom', [ClassroomController::class, 'index'])->name('classroom.index');

// Keep your existing dashboard route pointing to the same index action
Route::get('/dashboard', [ClassroomController::class, 'index'])->name('dashboard');

// Optional: normalize plural path to the index
Route::redirect('/classrooms', '/classroom');

// Detail
Route::get('/classroom/{id}', [ClassroomController::class, 'show'])->name('classroom.show');

// Posts
Route::post('/classroom/{id}/post', [ClassroomController::class, 'storePost'])->name('classroom.post');
Route::post('/classroom/{id}/post/{postIndex}/update', [ClassroomController::class, 'updatePost'])->name('classroom.post.update');
Route::post('/classroom/{id}/post/{postIndex}/delete', [ClassroomController::class, 'deletePost'])->name('classroom.post.delete');
Route::post('/classroom/{id}/post/{postIndex}/comment', [ClassroomController::class, 'storeComment'])->name('classroom.post.comment');

// Join / Leave
Route::post('/classrooms/join', [ClassroomController::class, 'join'])->name('classrooms.join');
Route::post('/classroom/{id}/leave', [ClassroomController::class, 'leaveClassroom'])->name('classroom.leave');

// Invitations
Route::post('/classroom/{id}/invite/accept', [ClassroomController::class, 'acceptInvite'])->name('classroom.invite.accept');
Route::post('/classroom/{id}/invite/decline', [ClassroomController::class, 'declineInvite'])->name('classroom.invite.decline');

// Dev helper to seed an invite for current user
Route::get('/dev/invite', [ClassroomController::class, 'devAddInvite'])->name('dev.invite.add');

// Misc pages
Route::get('/notifications', [ClassroomController::class, 'notificationsIndex'])->name('notifications.index');
Route::post('/notifications/seen', [ClassroomController::class, 'notificationsSeen'])->name('notifications.seen');
Route::get('/profile', [ClassroomController::class, 'profileIndex'])->name('profile.index');

/*
|--------------------------------------------------------------------------
| Teacher: My Classes (create + invite)
|--------------------------------------------------------------------------
*/
Route::get('/my-classes', [ClassroomController::class, 'myClassesIndex'])->name('teacher.classes');
Route::post('/classrooms', [ClassroomController::class, 'createClassroom'])->name('classrooms.create');
Route::post('/classroom/{id}/invite/send', [ClassroomController::class, 'sendInvite'])->name('classroom.invite.send');
Route::get('/classroom/{id}/manage', [ClassroomController::class, 'manageClassroom'])->name('classroom.manage');
Route::post('/classroom/{id}/enrollment/{user}/remove', [ClassroomController::class, 'removeEnrollment'])->name('classroom.enrollment.remove');
Route::post('/classroom/{id}/delete', [ClassroomController::class, 'deleteClassroom'])->name('classroom.delete');
// Teacher: view a student's assignment progress
Route::get('/classroom/{id}/student/{user}/progress', [ClassroomController::class, 'studentProgress'])->name('classroom.student.progress');

/*
|--------------------------------------------------------------------------
| Counseling Chatbot
|--------------------------------------------------------------------------
*/
Route::post('/chat', [ChatController::class, 'sendMessage']);
Route::get('/counseling-chat', [ChatController::class, 'showCounselingChat'])->name('counseling.chat');

/*
|--------------------------------------------------------------------------
| Assignments + Study Room
|--------------------------------------------------------------------------
*/
Route::get('/assignments', [ClassroomController::class, 'assignmentsIndex'])->name('assignments.index');
Route::get('/study_rooms', [StudyRoomController::class, 'index'])->name('study.room.index');
Route::post('/study_rooms/add', [StudyRoomController::class, 'addRoom'])->name('study.room.add');
Route::get('/chat/messages/{roomCode}', [StudyRoomController::class, 'getMessages'])->name('chat.messages');
Route::get('/study-rooms/members-json/{roomCode}', [StudyRoomController::class, 'getRoomMembersJson'])
    ->name('study-rooms.members-json');
    Route::post('/study_room/join', [StudyRoomController::class, 'join'])->name('study.room.join');
    Route::post('/study_room/exit', [StudyRoomController::class, 'exitRoom'])->name('study.room.exit');
Route::post('/chat/send', [StudyRoomController::class, 'sendMessage'])->name('chat.send');
Route::get('/chat/send', function () {
    return 'You hit the GET route!';
});

/*
|--------------------------------------------------------------------------
| Student Assignment System (Question Bank for Students)
|--------------------------------------------------------------------------
*/
// Student Question Bank (for answering questions/assignments)
Route::get('/assignments/question-bank', [AnswerQuestionController::class, 'getQuestionBank'])->name('assignments.questionbank');

// Student Assignment APIs
Route::post('/initialize-user-answers', [AnswerQuestionController::class, 'initializeUserAnswers']);
Route::get('/get-next-unfinished-round', [AnswerQuestionController::class, 'getNextUnfinishedRound']);
Route::post('/submit-answer', [AnswerQuestionController::class, 'submitAnswer']);
Route::get('/get-round-result', [AnswerQuestionController::class, 'getRoundResult']);
Route::get('/chapter-progress', [AnswerQuestionController::class, 'getChapterProgress']);
Route::get('/achievements-status', [AnswerQuestionController::class, 'achievementsStatus'])->name('achievements.status');

/*
|--------------------------------------------------------------------------
| Teacher Question Management System
|--------------------------------------------------------------------------
*/
// Teacher Question Bank Routes - Main Interface
// Replace the existing questionbank routes section with this:
Route::prefix('questionbank')->name('questionbank.')->group(function () {
    Route::get('/', [QuestionController::class, 'index'])->name('index');
    Route::get('/search', [QuestionController::class, 'search'])->name('search');
    Route::post('/search', [QuestionController::class, 'search'])->name('search.post');
    
    // Submit/Edit workflow
    Route::get('/submit-options', [QuestionController::class, 'showSubmitOptions'])->name('submit-options');
    Route::post('/submit-options', [QuestionController::class, 'processSubmitOptions'])->name('process-options');
    
    // CRUD operations
    Route::get('/create', [QuestionController::class, 'create'])->name('create');
    Route::post('/store', [QuestionController::class, 'store'])->name('store');
    Route::get('/my-questions', [QuestionController::class, 'showUserSubmittedQuestions'])->name('user-questions');
    
    // FIXED: Move specific routes before general {id} route
    Route::get('/{id}/edit', [QuestionController::class, 'edit'])->name('edit');
    Route::get('/{id}/info', [QuestionController::class, 'getQuestionInfo'])->name('info');
    Route::get('/{id}', [QuestionController::class, 'show'])->name('show');
    Route::put('/{id}', [QuestionController::class, 'update'])->name('update');
    Route::delete('/{id}', [QuestionController::class, 'destroy'])->name('destroy');
});

// Teacher Question Bank Management (Original System)
Route::get('/teacher/question-bank', [QuestionController::class, 'manage'])->name('teacher.questionbank');
Route::post('/teacher/question-bank', [QuestionController::class, 'store'])->name('teacher.questionbank.store');

/*
|--------------------------------------------------------------------------
| Question API Endpoints
|--------------------------------------------------------------------------
*/
// General Question APIs (for data access)
Route::get('/questions', [QuestionController::class, 'index']);
Route::get('/questions/by-chapter', [QuestionController::class, 'getByChapter']);

// API endpoints for questions
Route::prefix('api/questions')->name('api.questions.')->group(function () {
    Route::get('/', [QuestionController::class, 'index'])->name('index');
    Route::get('/by-chapter', [QuestionController::class, 'getByChapter'])->name('by-chapter');
    Route::get('/random', [QuestionController::class, 'getRandomQuestions'])->name('random');
    Route::get('/chapters', [QuestionController::class, 'getChapters'])->name('chapters');
});

// Legacy API endpoint
Route::get('/api/subtopics', [QuestionController::class, 'getSubtopics'])->name('api.subtopics');

/*
|--------------------------------------------------------------------------
| Legacy/Redirect Routes
|--------------------------------------------------------------------------
*/
// Redirect old question-bank URL to the appropriate system based on user role
Route::get('/question-bank', function() {
    $auth = (array) session('auth.user', []);
    $isTeacher = (($auth['role'] ?? 'student') === 'teacher');
    
    if ($isTeacher) {
        return redirect()->route('questionbank.index');
    } else {
        return redirect()->route('assignments.questionbank');
    }
})->name('question-bank.redirect');