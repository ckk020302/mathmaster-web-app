<?php

namespace App\Http\Controllers;

use Illuminate\Routing\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use App\Models\Question;
use App\Models\User;
use Illuminate\Support\Arr;



class QuestionController extends Controller
{
    // Helper method to get or generate user ID
    private function getOrGenerateUserId()
    {
        // Try to get authenticated user first
        $user = Auth::user();
        if ($user) {
            return $user->id;
        }

        // Fallback to session
        $auth = (array) session('auth.user', []);
        $sessionUserId = $auth['id'] ?? null;
        
        if ($sessionUserId) {
            return $sessionUserId;
        }

        // Generate sequential user ID if none exists
        return $this->generateSequentialUserId();
    }

    // Generate sequential user ID
    private function generateSequentialUserId()
    {
        // Get the highest user_id from questions table and increment
        try {
            $maxUserId = Question::max('user_id') ?? 0;
            $nextUserId = $maxUserId + 1;
            
            Log::info('Generated sequential user ID', [
                'max_existing' => $maxUserId,
                'generated' => $nextUserId
            ]);
            
            return $nextUserId;
        } catch (\Throwable $e) {
            Log::error('Failed to generate sequential user ID', ['error' => $e->getMessage()]);
            // Fallback to timestamp-based ID
            return (int) substr(time(), -6); // Last 6 digits of timestamp
        }
    }

    // Helper method to get user name
    private function getUserName()
    {
        $user = Auth::user();
        if ($user) {
            return $user->name;
        }

        $auth = (array) session('auth.user', []);
        return $auth['name'] ?? 'Anonymous User';
    }

    // Original manage method (unchanged)
    public function manage()
    {
        $auth = (array) session('auth.user', []);
        if (($auth['role'] ?? 'student') !== 'teacher') {
            return redirect()->route('dashboard')->with('error', 'Only teachers can manage Question Bank.');
        }

        $recent = [];
        try {
            $recent = Question::with('user')->orderByDesc('created_at')->limit(20)->get();
        } catch (\Throwable $e) {
            Log::error('Failed to load recent questions: ' . $e->getMessage());
            $recent = collect();
        }
        return view('teacher_question_bank', compact('recent'));
    }

    // Enhanced index method
    public function index(Request $request)
    {
        try {
            $query = Question::with('user');

            if ($request->filled('academic_level')) {
                $query->where('academic_level', $request->academic_level);
            }

            if ($request->filled('chapter')) {
                $query->where('chapter', 'like', '%' . $request->chapter . '%');
            }

            if ($request->filled('difficulty')) {
                $query->where('difficulty', $request->difficulty);
            }

            $questions = $query->orderByDesc('created_at')->paginate(10);
        } catch (\Throwable $e) {
            Log::error('Failed to load questions in index: ' . $e->getMessage());
            $questions = collect();
        }

        if ($request->expectsJson()) {
            return response()->json($questions);
        }

        return view('question_bank', compact('questions'));
    }

    // Enhanced search method
    public function search(Request $request)
    {
        $filters = $request->validate([
            'academic_level' => 'nullable|string',
            'chapter' => 'nullable|string',
            'difficulty' => 'nullable|string',
            'search_term' => 'nullable|string',
        ]);

        $query = Question::with('user');

        // Apply filters
        foreach ($filters as $key => $value) {
            if (!empty($value)) {
                switch ($key) {
                    case 'academic_level':
                        $query->where('academic_level', $value);
                        break;
                    case 'difficulty':
                        $query->where('difficulty', $value);
                        break;
                    case 'search_term':
                        $query->where(function($q) use ($value) {
                            $q->where('chapter', 'like', '%' . $value . '%');
                        });
                        break;
                    default:
                        $query->where($key, 'like', '%' . $value . '%');
                        break;
                }
            }
        }

        try {
            $questions = $query->orderBy('created_at', 'desc')->paginate(10);
            $questions->withQueryString();
        } catch (\Throwable $e) {
            Log::error('Search failed: ' . $e->getMessage());
            $questions = collect();
        }

        if ($request->expectsJson()) {
            return response()->json([
                'questions' => method_exists($questions, 'items') ? $questions->items() : [],
                'pagination' => method_exists($questions, 'currentPage') ? [
                    'current_page' => $questions->currentPage(),
                    'total_pages' => $questions->lastPage(),
                    'total_count' => $questions->total()
                ] : null
            ]);
        }

        return view('question_bank', compact('questions', 'filters'));
    }

    // Show single question
    public function show($id)
    {
        try {
            $question = Question::with('user')->findOrFail($id);
        } catch (\Throwable $e) {
            Log::error('Question not found: ' . $e->getMessage());
            if (request()->expectsJson()) {
                return response()->json(['error' => 'Question not found'], 404);
            }
            return redirect()->back()->with('error', 'Question not found');
        }
        
        if (request()->expectsJson()) {
            return response()->json($question);
        }
        
        return view('question_bank', compact('question'));
    }

    // Get question info for modal
// Get question info for modal
public function getQuestionInfo($id)
{
    try {
        $question = Question::with('user')->findOrFail($id);
        
        $questionData = [
            'id' => $question->id,
            'question_image' => $question->question_image ? Storage::url($question->question_image) : null,
            'academic_level' => $question->academic_level,
            'chapter' => $question->chapter,
            'difficulty' => $question->difficulty,
            // Fix: Extract answer from corrupted data
            'answer_image' => $this->extractAnswerFromPath($question->answer_image),
            // Fix: Only return tip images if they're actual file paths
            'tip_easy' => $this->isValidImagePath($question->tip_easy) ? $question->tip_easy : null,
            'tip_intermediate' => $this->isValidImagePath($question->tip_intermediate) ? $question->tip_intermediate : null,
            'tip_advanced' => $this->isValidImagePath($question->tip_advanced) ? $question->tip_advanced : null,
            'user_name' => $question->user->name ?? $question->uploaded_by ?? 'Unknown',
            'created_at' => $question->created_at ? $question->created_at->format('M d, Y H:i') : null,
            'upload_date' => $question->upload_date ? $question->upload_date->format('M d, Y H:i') : null,
        ];

        return response()->json([
            'success' => true,
            'question' => $questionData
        ]);
    } catch (\Throwable $e) {
        Log::error('Failed to get question info', ['question_id' => $id, 'error' => $e->getMessage()]);
        return response()->json([
            'success' => false,
            'message' => 'Question not found'
        ], 404);
    }
}

// Add these missing helper methods to your controller
private function extractAnswerFromPath($answerField)
{
    if (!$answerField) return null;
    
    // If it's already A/B/C/D, return as is
    if (in_array(strtoupper($answerField), ['A', 'B', 'C', 'D'])) {
        return strtoupper($answerField);
    }
    
    // If it contains a path, try to extract answer from filename
    if (strpos($answerField, '/') !== false) {
        $filename = basename($answerField);
        if (preg_match('/^([A-D])/i', $filename, $matches)) {
            return strtoupper($matches[1]);
        }
    }
    
    return null;
}

private function isValidImagePath($value)
{
    if (!$value || strlen($value) < 5) return false;
    // Check if it looks like a file path and not just text
    return (strpos($value, '/') !== false || strpos($value, '.') !== false) && 
           !in_array(strtolower($value), ['thth', 'thrth', 'test', 'text']);
}

    // Show submit options page
    public function showSubmitOptions()
    {
        return view('question_bank');
    }

    // Show user's submitted questions
    public function showUserSubmittedQuestions(Request $request)
    {
        $currentUserId = $this->getOrGenerateUserId();
        $currentUser = $this->getUserName();
        
        try {
            $query = Question::with('user');
            
            // Filter by user (support both user_id and uploaded_by)
            $query->where(function($q) use ($currentUserId, $currentUser) {
                $q->where('user_id', $currentUserId);
                if ($currentUser) {
                    $q->orWhere('uploaded_by', $currentUser);
                }
            });

            // Apply additional filters
            if ($request->filled('academic_level')) {
                $query->where('academic_level', $request->academic_level);
            }
            if ($request->filled('chapter')) {
                $query->where('chapter', 'like', '%' . $request->chapter . '%');
            }
            if ($request->filled('difficulty')) {
                $query->where('difficulty', $request->difficulty);
            }

            $questions = $query->orderBy('created_at', 'desc')->paginate(10);
        } catch (\Throwable $e) {
            Log::error('Failed to load user questions: ' . $e->getMessage());
            $questions = collect();
        }

        return view('question_bank', compact('questions'));
    }

    // Show create form
    public function create()
    {
        return view('question_bank');
    }

    // Store method (create question) - UPDATED
   // Replace your existing store method with this enhanced version

public function store(Request $request)
{
    // Get user info with improved ID generation
    $userId = $this->getOrGenerateUserId();
    $userName = $this->getUserName();

    // Ensure we have a valid user_id (never null)
    if (!$userId || $userId <= 0) {
        // Get the first available user as fallback
        $fallbackUserId = DB::table('users')->orderBy('id')->value('id');
        if ($fallbackUserId) {
            $userId = $fallbackUserId;
        } else {
            // Create a system user if no users exist
            $userId = DB::table('users')->insertGetId([
                'name' => 'System User',
                'email' => 'system@questionbank.local',
                'password' => bcrypt('system123'),
                'created_at' => now(),
                'updated_at' => now(),
            ]);
        }
    }

    // Verify the user exists in the database
    $userExists = DB::table('users')->where('id', $userId)->exists();
    if (!$userExists) {
        // Use first available user
        $userId = DB::table('users')->orderBy('id')->value('id');
        if (!$userId) {
            return back()->with('error', 'No valid users found in system. Please contact administrator.');
        }
    }

    // Check if user has teacher role (if using session-based auth)
    $auth = (array) session('auth.user', []);
    if (!Auth::user() && ($auth['role'] ?? 'student') !== 'teacher') {
        return redirect()->route('dashboard')->with('error', 'Only teachers can upload questions.');
    }

    // ENHANCED VALIDATION - ALL FIELDS REQUIRED FOR CREATE
    $data = $request->validate([
        'academic_level' => [
            'required',
            'string',
            'in:Form 4,Form 5'
        ],
        'chapter' => [
            'required',
            'string',
            'max:200',
            'min:1'  // Ensure it's not just whitespace
        ],
        'difficulty' => [
            'required',
            'string',
            'in:Easy,Intermediate,Advanced'
        ],
        'answer_image' => [
            'required',  // Made REQUIRED for create
            'string',
            'in:A,B,C,D'
        ],
        'question_image' => [
            'required',  // Already required
            'file',
            'image',
            'mimes:jpg,jpeg,png,gif,webp',
            'max:4096',  // 4MB max
            'min:1'      // Must have content
        ],
        // Tip images remain optional but if provided, must be valid
        'tip_easy' => [
            'nullable',
            'file',
            'image',
            'mimes:jpg,jpeg,png,gif,webp',
            'max:4096'
        ],
        'tip_intermediate' => [
            'nullable',
            'file', 
            'image',
            'mimes:jpg,jpeg,png,gif,webp',
            'max:4096'
        ],
        'tip_advanced' => [
            'nullable',
            'file',
            'image', 
            'mimes:jpg,jpeg,png,gif,webp',
            'max:4096'
        ],
    ], [
        // Custom error messages for better UX
        'academic_level.required' => 'Please select an academic level.',
        'academic_level.in' => 'Please select a valid academic level (Form 4 or Form 5).',
        
        'chapter.required' => 'Please select or enter a chapter.',
        'chapter.min' => 'Chapter cannot be empty.',
        'chapter.max' => 'Chapter name is too long (maximum 200 characters).',
        
        'difficulty.required' => 'Please select a difficulty level.',
        'difficulty.in' => 'Please select a valid difficulty (Easy, Intermediate, or Advanced).',
        
        'answer_image.required' => 'Please select the correct answer (A, B, C, or D).',
        'answer_image.in' => 'Please select a valid answer option (A, B, C, or D).',
        
        'question_image.required' => 'Question image is required.',
        'question_image.file' => 'Question image must be a valid file.',
        'question_image.image' => 'Question image must be an image file.',
        'question_image.mimes' => 'Question image must be in JPG, JPEG, PNG, GIF, or WebP format.',
        'question_image.max' => 'Question image size must not exceed 4MB.',
        'question_image.min' => 'Question image file appears to be empty.',
        
        'tip_easy.file' => 'Easy tip must be a valid file.',
        'tip_easy.image' => 'Easy tip must be an image file.',
        'tip_easy.mimes' => 'Easy tip must be in JPG, JPEG, PNG, GIF, or WebP format.',
        'tip_easy.max' => 'Easy tip image size must not exceed 4MB.',
        
        'tip_intermediate.file' => 'Intermediate tip must be a valid file.',
        'tip_intermediate.image' => 'Intermediate tip must be an image file.',
        'tip_intermediate.mimes' => 'Intermediate tip must be in JPG, JPEG, PNG, GIF, or WebP format.',
        'tip_intermediate.max' => 'Intermediate tip image size must not exceed 4MB.',
        
        'tip_advanced.file' => 'Advanced tip must be a valid file.',
        'tip_advanced.image' => 'Advanced tip must be an image file.',
        'tip_advanced.mimes' => 'Advanced tip must be in JPG, JPEG, PNG, GIF, or WebP format.',
        'tip_advanced.max' => 'Advanced tip image size must not exceed 4MB.',
    ]);

    // ADDITIONAL CUSTOM VALIDATION
    
    // Validate chapter is not just whitespace
    if (empty(trim($data['chapter']))) {
        return back()
            ->withErrors(['chapter' => 'Chapter cannot be empty or contain only spaces.'])
            ->withInput();
    }

    // Validate question image file integrity
    if ($request->hasFile('question_image')) {
        $questionFile = $request->file('question_image');
        
        // Check if file is readable
        if (!$questionFile->isValid()) {
            return back()
                ->withErrors(['question_image' => 'Question image file is corrupted or invalid.'])
                ->withInput();
        }
        
        // Check file dimensions (optional - ensure it's a real image)
        try {
            $imageInfo = getimagesize($questionFile->getPathname());
            if (!$imageInfo) {
                return back()
                    ->withErrors(['question_image' => 'Question image file is not a valid image.'])
                    ->withInput();
            }
            

        } catch (\Exception $e) {
            return back()
                ->withErrors(['question_image' => 'Unable to process question image file.'])
                ->withInput();
        }
    }

    // Use database transaction for atomicity
    DB::beginTransaction();
    try {
        $imagePaths = [
            'question_image' => null,
            'tip_easy' => null,
            'tip_intermediate' => null,
            'tip_advanced' => null,
        ];

        // Handle question image upload (REQUIRED)
        if ($request->hasFile('question_image')) {
            $questionFile = $request->file('question_image');
            $imagePaths['question_image'] = $questionFile->store('questions', 'public');
            
            // Verify the file was actually stored
            if (!Storage::disk('public')->exists($imagePaths['question_image'])) {
                throw new \Exception('Failed to store question image file.');
            }
        } else {
            // This should never happen due to validation, but safety check
            throw new \Exception('Question image is required but was not provided.');
        }

        // Handle tip image uploads (optional)
        foreach (['tip_easy', 'tip_intermediate', 'tip_advanced'] as $tipField) {
            if ($request->hasFile($tipField)) {
                $tipFile = $request->file($tipField);
                if ($tipFile->isValid()) {
                    $imagePaths[$tipField] = $tipFile->store('questions', 'public');
                    
                    // Verify the file was stored
                    if (!Storage::disk('public')->exists($imagePaths[$tipField])) {
                        Log::warning("Failed to store {$tipField} image, continuing without it.");
                        $imagePaths[$tipField] = null;
                    }
                }
            }
        }

        // Create question with validated data
        $question = Question::create([
            'academic_level' => $data['academic_level'],
            'chapter' => trim($data['chapter']),  // Remove any whitespace
            'difficulty' => $data['difficulty'],
            'question_image' => $imagePaths['question_image'],
            'answer_image' => $data['answer_image'],  // Now required
            'tip_easy' => $imagePaths['tip_easy'],
            'tip_intermediate' => $imagePaths['tip_intermediate'],
            'tip_advanced' => $imagePaths['tip_advanced'],
            'uploaded_by' => $userName,
            'user_id' => (int) $userId,
            'upload_date' => now(),
        ]);

        // Verify the question was created with all required data
        if (!$question->academic_level || !$question->chapter || !$question->difficulty || 
            !$question->question_image || !$question->answer_image) {
            throw new \Exception('Question was not created with all required fields.');
        }

        DB::commit();

        Log::info('Enhanced question created successfully', [
            'question_id' => $question->id,
            'user' => $userName,
            'user_id' => $userId,
            'academic_level' => $question->academic_level,
            'chapter' => $question->chapter,
            'difficulty' => $question->difficulty,
            'answer' => $question->answer_image,
        ]);

        if ($request->expectsJson()) {
            return response()->json([
                'success' => true,
                'message' => 'Question created successfully with all required information!',
                'question_id' => $question->id
            ]);
        }

        return redirect()->route('questionbank.index')
            ->with('success', 'Question submitted successfully with all required information!');
                
    } catch (\Throwable $e) {
        DB::rollback();
        
        // Clean up uploaded files on failure
        foreach ($imagePaths as $path) {
            if ($path && Storage::disk('public')->exists($path)) {
                Storage::disk('public')->delete($path);
            }
        }
        
        Log::error('Question creation failed', [
            'error' => $e->getMessage(),
            'user' => $userName,
            'user_id' => $userId,
            'request_data' => $request->except(['question_image', 'tip_easy', 'tip_intermediate', 'tip_advanced']) // Don't log file data
        ]);

        if ($request->expectsJson()) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to create question: ' . $e->getMessage()
            ], 500);
        }
        
        return back()
            ->with('error', 'Failed to create question. Please ensure all required fields are filled and try again.')
            ->withInput();
    }
} 
    // IMPROVED EDIT METHOD with proper authorization
    public function edit($id)
    {
        $currentUserId = $this->getOrGenerateUserId();
        $currentUser = $this->getUserName();
        
        try {
            $question = Question::with('user')->findOrFail($id);
            
            // Check authorization - user can edit their own questions
            $canEdit = $this->userCanEditQuestion($question, $currentUserId, $currentUser);
            
            if (!$canEdit) {
                Log::warning('Unauthorized edit attempt', [
                    'question_id' => $id, 
                    'user' => $currentUser,
                    'user_id' => $currentUserId
                ]);
                return redirect()->back()->with('error', 'You are not authorized to edit this question.');
            }
            
        } catch (\Throwable $e) {
            Log::error('Failed to load question for editing', [
                'question_id' => $id, 
                'error' => $e->getMessage()
            ]);
            return redirect()->back()->with('error', 'Question not found.');
        }

        return view('question_bank', compact('question'));
    }

    // IMPROVED UPDATE METHOD with proper authorization and transactions
    public function update(Request $request, $id)
    {
        $currentUserId = $this->getOrGenerateUserId();
        $currentUser = $this->getUserName();
        
        try {
            $question = Question::with('user')->findOrFail($id);
            
            // Check authorization
            $canEdit = $this->userCanEditQuestion($question, $currentUserId, $currentUser);
            
            if (!$canEdit) {
                Log::warning('Unauthorized update attempt', [
                    'question_id' => $id, 
                    'user' => $currentUser
                ]);
                return redirect()->back()->with('error', 'You are not authorized to update this question.');
            }
            
        } catch (\Throwable $e) {
            Log::error('Question not found for update', [
                'question_id' => $id, 
                'error' => $e->getMessage()
            ]);
            return redirect()->back()->with('error', 'Question not found.');
        }

        // Validation (same as create)
        $data = $request->validate([
        'academic_level' => 'required|in:Form 4,Form 5',
        'chapter' => 'required|string|max:200',
        'difficulty' => 'required|in:Easy,Intermediate,Advanced',
        'answer_image' => 'nullable|in:A,B,C,D',
        'question_image' => 'nullable|image|mimes:jpg,jpeg,png,gif,webp|max:4096',
        'tip_easy' => 'nullable|image|mimes:jpg,jpeg,png,gif,webp|max:4096',
        'tip_intermediate' => 'nullable|image|mimes:jpg,jpeg,png,gif,webp|max:4096',
        'tip_advanced' => 'nullable|image|mimes:jpg,jpeg,png,gif,webp|max:4096',
    
        ]);

        // Use database transaction
        DB::beginTransaction();
        try {
        $oldImages = [
            'question_image' => $question->question_image,
            'tip_easy' => $question->tip_easy,
            'tip_intermediate' => $question->tip_intermediate,
            'tip_advanced' => $question->tip_advanced,
        ];

        // Prepare update data
        $updateData = [
            'academic_level' => $data['academic_level'],
            'chapter' => trim($data['chapter']),
            'difficulty' => $data['difficulty'],
            'answer_image' => $data['answer_image'] ?? null,
        ];

            // Handle question image upload
        if ($request->hasFile('question_image')) {
            $updateData['question_image'] = $request->file('question_image')->store('questions', 'public');
        }

        // Handle tip image uploads - only update if new files are uploaded
        if ($request->hasFile('tip_easy')) {
            $updateData['tip_easy'] = $request->file('tip_easy')->store('questions', 'public');
        }
        if ($request->hasFile('tip_intermediate')) {
            $updateData['tip_intermediate'] = $request->file('tip_intermediate')->store('questions', 'public');
        }
        if ($request->hasFile('tip_advanced')) {
            $updateData['tip_advanced'] = $request->file('tip_advanced')->store('questions', 'public');
        }

        // Update the question
        $question->update($updateData);

       
// Delete old images only after successful update and only if new ones were uploaded
if ($request->hasFile('question_image') && $oldImages['question_image']) {
    Storage::disk('public')->delete($oldImages['question_image']);
}

// FIX: Use the correct array keys that match the $oldImages array
if ($request->hasFile('tip_easy') && $oldImages['tip_easy']) {
    Storage::disk('public')->delete($oldImages['tip_easy']);
}
if ($request->hasFile('tip_intermediate') && $oldImages['tip_intermediate']) {
    Storage::disk('public')->delete($oldImages['tip_intermediate']);
}
if ($request->hasFile('tip_advanced') && $oldImages['tip_advanced']) {
    Storage::disk('public')->delete($oldImages['tip_advanced']);
}

            DB::commit();

            Log::info('Question updated successfully', ['question_id' => $id]);

            if ($request->expectsJson()) {
                return response()->json([
                    'success' => true,
                    'message' => 'Question updated successfully!'
                ]);
            }

            return redirect()->route('questionbank.user-questions')
                ->with('success', 'Question updated successfully!');
                
        } catch (\Throwable $e) {
            DB::rollback();
            
            Log::error('Question update failed', [
                'question_id' => $id,
                'error' => $e->getMessage()
            ]);
            
            if ($request->expectsJson()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Failed to update question.'
                ], 500);
            }
            
            return back()
                ->with('error', 'Failed to update question: ' . $e->getMessage())
                ->withInput();
        }
    }

    // IMPROVED DELETE METHOD with proper authorization
    public function destroy($id)
    {
        $currentUserId = $this->getOrGenerateUserId();
        $currentUser = $this->getUserName();
        
        try {
            $question = Question::with('user')->findOrFail($id);
            
            // Check authorization
            $canDelete = $this->userCanEditQuestion($question, $currentUserId, $currentUser);
            
            if (!$canDelete) {
                Log::warning('Unauthorized delete attempt', [
                    'question_id' => $id, 
                    'user' => $currentUser
                ]);
                
                if (request()->expectsJson()) {
                    return response()->json([
                        'success' => false,
                        'message' => 'You are not authorized to delete this question.'
                    ], 403);
                }
                return redirect()->back()->with('error', 'You are not authorized to delete this question.');
            }

            // Use database transaction
            DB::beginTransaction();
            
            $questionImage = $question->question_image;

            // Delete the question from database first
            $question->delete();

            // Delete associated images only after successful database deletion
            if ($questionImage) {
                Storage::disk('public')->delete($questionImage);
            }

            DB::commit();

            Log::info('Question deleted successfully', [
                'question_id' => $id,
                'user' => $currentUser
            ]);

            if (request()->expectsJson()) {
                return response()->json([
                    'success' => true,
                    'message' => 'Question deleted successfully!'
                ]);
            }

            return redirect()->route('questionbank.user-questions')
                ->with('success', 'Question deleted successfully!');
                
        } catch (\Throwable $e) {
            DB::rollback();
            
            Log::error('Question deletion failed', [
                'question_id' => $id, 
                'error' => $e->getMessage()
            ]);
            
            if (request()->expectsJson()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Failed to delete question.'
                ], 500);
            }
            return redirect()->back()->with('error', 'Question not found or could not be deleted.');
        }
    }

    // HELPER METHOD: Check if user can edit/delete question
    private function userCanEditQuestion($question, $currentUserId, $currentUser)
    {
        // Check by user_id first (more reliable)
        if ($currentUserId && $question->user_id == $currentUserId) {
            return true;
        }
        
        // Fallback to uploaded_by for legacy data
        if ($currentUser && $question->uploaded_by == $currentUser) {
            return true;
        }
        
        return false;
    }

    // Additional utility methods remain the same...
    public function getByChapter(Request $request)
    {
        $request->validate([
            'chapter' => 'required|string',
            'academic_level' => 'nullable|string',
            'difficulty' => 'nullable|string',
        ]);

        try {
    $query = Question::where('chapter', 'like', '%' . $request->chapter . '%');  

    if ($request->filled('academic_level')) {
        $query->where('academic_level', $request->academic_level);
    }

            if ($request->filled('difficulty')) {
                $query->where('difficulty', $request->difficulty);
            }

            $questions = $query->get();
        } catch (\Throwable $e) {
            Log::error('Failed to get questions by chapter', ['error' => $e->getMessage()]);
            $questions = collect();
        }

        return response()->json($questions);
    }

    public function getRandomQuestions(Request $request)
    {
        $request->validate([
            'count' => 'nullable|integer|min:1|max:50',
            'academic_level' => 'nullable|string',
            'difficulty' => 'nullable|string',
        ]);

        try {
            $query = Question::query();

            if ($request->filled('academic_level')) {
                $query->where('academic_level', $request->academic_level);
            }

            if ($request->filled('difficulty')) {
                $query->where('difficulty', $request->difficulty);
            }

            $questions = $query->inRandomOrder()->limit($request->count ?? 10)->get();
        } catch (\Throwable $e) {
            Log::error('Failed to get random questions', ['error' => $e->getMessage()]);
            $questions = collect();
        }

        return response()->json($questions);
    }

    // DEBUG METHOD - Remove in production
    public function debugQuestionCreation(Request $request)
    {
        if (app()->environment('production')) {
            abort(404);
        }

        $testData = [
            'academic_level' => 'Form 4',
            'difficulty' => 'Easy',
            'uploaded_by' => 'Debug User',
            'user_id' => $this->getOrGenerateUserId(),
            'upload_date' => now(),
        ];

        try {
            $question = Question::create($testData);
            return response()->json([
                'success' => true,
                'message' => 'Debug question created successfully',
                'question_id' => $question->id,
                'created_data' => $question->toArray()
            ]);
        } catch (\Throwable $e) {
            return response()->json([
                'success' => false,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
        }
    }
}
