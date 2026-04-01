<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Kreait\Firebase\Factory;
use Cloudinary\Cloudinary;
use Cloudinary\Configuration\Configuration;
use Illuminate\Support\Facades\Log;

class StudyRoomController extends Controller
{
    protected $database;
    protected $cloudinary;

    public function __construct()
    {
        Log::debug('StudyRoomController __construct called.');

        $credentialsPath = config('services.firebase.credentials_file_path');
        $databaseUri = config('services.firebase.database_url');

        if (!$credentialsPath || !file_exists($credentialsPath)) {
            Log::critical("Firebase credentials file not found at {$credentialsPath}");
            abort(500, "Firebase credentials file not found at {$credentialsPath}");
        }

        try {
            $factory = (new Factory)
                ->withServiceAccount($credentialsPath)
                ->withDatabaseUri($databaseUri);

            $this->database = $factory->createDatabase();
            Log::info('Firebase database initialized successfully.');
        } catch (\Exception $e) {
            Log::critical('Failed to initialize Firebase database: ' . $e->getMessage());
            abort(500, 'Failed to initialize Firebase database.');
        }

        $config = config('services.cloudinary');

        if (empty($config['cloud_name']) || empty($config['api_key']) || empty($config['api_secret'])) {
            Log::warning('Cloudinary configuration missing. Cloudinary will not be used.');
            $this->cloudinary = null;
        } else {
            try {
                Configuration::instance([
                    'cloud' => [
                        'cloud_name' => $config['cloud_name'],
                        'api_key' => $config['api_key'],
                        'api_secret' => $config['api_secret'],
                    ],
                    'url' => ['secure' => true],
                ]);
                $this->cloudinary = new Cloudinary();
                Log::info('Cloudinary initialized successfully.');
            } catch (\Exception $e) {
                Log::error('Failed to initialize Cloudinary: ' . $e->getMessage());
                $this->cloudinary = null;
            }
        }
        Log::debug('StudyRoomController __construct finished.');
    }

    public function index(Request $request)
    {
        Log::info('----- StudyRoomController@index called -----');
        Log::debug('Request URL: ' . $request->fullUrl());
        Log::debug('Request parameters:', $request->all());

        $searchCode = $request->get('search_room_code');
        $userEmail = session('auth.user.email');

        Log::debug('Index - Session Data: ', [
            'userEmail' => $userEmail,
            'searchCode' => $searchCode,
        ]);

        $roomRef = $this->database->getReference('studyRooms');
        $allRoomsData = null;
        try {
            $allRoomsData = $roomRef->getValue() ?? [];
            Log::debug('Index - All rooms fetched from Firebase.');
        } catch (\Exception $e) {
            Log::error('Index - Firebase error fetching all rooms: ' . $e->getMessage());
            // Clear session data if Firebase error occurs and redirect
            session()->forget(['joined_room_code', 'joined_room_name', 'joined_room_key', 'joined_room_user_timestamp']);
            return redirect()->route('study.room.index')->with('error', 'Failed to load study rooms due to a database error.');
        }

        // --- Determine the user's currently joined room, if any ---
        $userJoinedRoomCode = null;
        $userJoinedRoomDetails = null;
        $userJoinedRoomKey = null;
        $joinedTimestamp = null;

        if ($userEmail) {
            foreach ($allRoomsData as $key => $room) {
                // IMPORTANT: Ensure 'members' and 'room_code' keys exist
                if (isset($room['members']) && is_array($room['members']) && in_array($userEmail, $room['members']) && isset($room['room_code'])) {
                    $userJoinedRoomCode = $room['room_code'];
                    $userJoinedRoomDetails = $room;
                    $userJoinedRoomKey = $key;

                    $firebaseUserEmailKey = str_replace('.', '_', str_replace('@', '__at__', $userEmail));
                    $joinedTimestamp = $room['members_timestamps'][$firebaseUserEmailKey] ?? time();

                    Log::debug('Index - User found in room from Firebase.', ['userEmail' => $userEmail, 'roomCode' => $userJoinedRoomCode, 'key' => $key]);
                    break;
                }
            }
        }

        // Update session with the *actual* joined room details from Firebase
        // This ensures the session is consistent with the database state
        if ($userJoinedRoomCode && $userJoinedRoomDetails) {
            session([
                'joined_room_code' => $userJoinedRoomCode,
                'joined_room_name' => $userJoinedRoomDetails['name'] ?? 'Unknown Room',
                'joined_room_key' => $userJoinedRoomKey,
                'joined_room_user_timestamp' => $joinedTimestamp,
            ]);
            Log::info('Index - Session updated with user\'s current room.', ['joinedRoomCode' => $userJoinedRoomCode]);
        } else {
            // If user is not found in any room, ensure session is clear
            session()->forget(['joined_room_code', 'joined_room_name', 'joined_room_key', 'joined_room_user_timestamp']);
            Log::info('Index - User not in any room, session cleared of room data.');
        }

        // --- Prepare the list of rooms for display (public or search results) ---
        $displayRooms = [];
        foreach ($allRoomsData as $room) {
            if ($searchCode) {
                // Case-insensitive search by room code
                if (str_contains(strtolower($room['room_code'] ?? ''), strtolower($searchCode))) {
                    $displayRooms[] = $room;
                }
            } else {
                // Only show public rooms if no search code
                if (empty($room['is_private'])) {
                    $displayRooms[] = $room;
                }
            }
        }

        usort($displayRooms, fn($a, $b) => strcmp($a['name'] ?? '', $b['name'] ?? ''));
        Log::debug('Index - Rooms filtered and sorted for display.', ['count' => count($displayRooms)]);

        // Always return the view, passing all necessary data
        return view('study_room', [
            'rooms' => $displayRooms,
            'joinedRoomCode' => $userJoinedRoomCode, // Pass the joined room code
            'joinedRoomName' => $userJoinedRoomDetails['name'] ?? null, // Pass the joined room name
            'joinedRoomKey' => $userJoinedRoomKey, // Pass the Firebase key
            'joinedRoomUserTimestamp' => $joinedTimestamp, // Pass the join timestamp
            'chatting' => (bool) $userJoinedRoomCode, // True if user is in a room
        ]);
    }

    public function addRoom(Request $request)
    {
        Log::info('----- StudyRoomController@addRoom called -----');
        Log::debug('Request data:', $request->all());

        $request->validate([
            'name' => 'required|string',
            'member_limit' => 'required|integer|min:1|max:50',
            'description' => 'nullable|string',
            'is_private' => 'nullable|boolean',
        ]);

        $user = session('auth.user');
        $userEmail = $user['email'] ?? null; // Use $userEmail for clarity
        Log::debug('addRoom - Current userEmail: ' . ($userEmail ?? 'N/A'));

        if (!$userEmail) { // Check against $userEmail
            Log::warning('addRoom - User not logged in.');
            return redirect()->route('study.room.index')->with('error', 'You must be logged in to create a room.');
        }

        if (session('joined_room_code')) {
            $existingRoomCode = session('joined_room_code');
            Log::warning('addRoom - User already in a room, redirecting to existing room.', ['existingRoomCode' => $existingRoomCode]);
            return redirect()->route('study.room.index')->with('error', 'You are already in a study room. Please exit your current room before creating a new one.');
        }

        $roomsRef = $this->database->getReference('studyRooms');
        $code = null;

        try {
            do {
                $code = strtoupper(substr(bin2hex(random_bytes(3)), 0, 6));
                $exists = false;
                $rooms = $roomsRef->getValue() ?? [];
                foreach ($rooms as $room) {
                    if (($room['room_code'] ?? '') === $code) {
                        $exists = true;
                        Log::debug('addRoom - Generated room code exists, trying again.', ['code' => $code]);
                        break;
                    }
                }
            } while ($exists);
        } catch (\Exception $e) {
            Log::error('addRoom - Firebase error during room code generation: ' . $e->getMessage());
            return redirect()->back()->with('error', 'Failed to generate room code due to a database error.');
        }

        $currentTimestamp = time();
        Log::debug('addRoom - Generated unique room code: ' . $code);

        // Create a Firebase-safe key for the user's email
        $firebaseUserEmailKey = str_replace('.', '_', str_replace('@', '__at__', $userEmail));

        $newRoom = [
            'name' => $request->name,
            'member_limit' => $request->member_limit,
            'description' => $request->description ?? '',
            'is_private' => $request->has('is_private'),
            'room_code' => $code,
            'members' => [$userEmail], // Store the actual email in the 'members' array
            'members_timestamps' => [$firebaseUserEmailKey => $currentTimestamp], // Use the Firebase-safe key here
        ];

        try {
            $newRoomRef = $roomsRef->push($newRoom);
            $newRoomKey = $newRoomRef->getKey();
            Log::info('addRoom - New room pushed to Firebase successfully.', ['roomCode' => $code, 'firebaseKey' => $newRoomKey]);
        } catch (\Exception $e) {
            Log::error('addRoom - Firebase error pushing new room: ' . $e->getMessage());
            return redirect()->back()->with('error', 'Failed to create room due to a database error.');
        }

        // We update the session here, but the index method will re-verify from Firebase
        session([
            'joined_room_code' => $code,
            'joined_room_name' => $request->name,
            'joined_room_key' => $newRoomKey,
            'joined_room_user_timestamp' => $currentTimestamp,
        ]);
        Log::info('addRoom - Session updated after room creation.', [
            'joined_room_code' => $code,
            'joined_room_name' => $request->name,
            'joined_room_key' => $newRoomKey,
            'joined_room_user_timestamp' => $currentTimestamp,
        ]);

        // Redirect back to index, which will now show both list and chat
        return redirect()->route('study.room.index')->with('success', 'Room created and joined successfully!');
    }


    public function join(Request $request)
    {
        Log::info('----- StudyRoomController@join called -----');
        Log::debug('Request data:', $request->all());

        $request->validate(['room_code' => 'required|string']);

        $userSession = session('auth.user');
        $userId = $userSession['email'] ?? null;
        Log::debug('join - Current userId: ' . ($userId ?? 'N/A'));

        if (!$userSession || !$userId) {
            Log::warning('join - User not logged in or email missing.');
            return redirect()->route('login.form')->with('error', 'You must be logged in to join a room.');
        }

        $roomCode = strtoupper(trim($request->room_code));
        $joinedRoomCodeInSession = session('joined_room_code');
        Log::debug('join - Attempting to join roomCode: ' . $roomCode);
        Log::debug('join - joinedRoomCodeInSession: ' . ($joinedRoomCodeInSession ?? 'N/A'));

        if ($joinedRoomCodeInSession && $joinedRoomCodeInSession !== $roomCode) {
            $existingRoomName = session('joined_room_name');
            Log::warning('join - User already in a different room, redirecting to existing room.', [
                'existingRoomCode' => $joinedRoomCodeInSession,
                'targetRoomCode' => $roomCode,
            ]);
            // If already in a room, we don't allow joining another, but refresh the current view
            return redirect()->route('study.room.index')->with('error', 'You are already in another study room (' . $existingRoomName . '). Please exit your current room before joining a new one.');
        }
        if ($joinedRoomCodeInSession === $roomCode) {
            Log::info('join - User already in this room, refreshing to ensure chat loads.', ['room_code' => $roomCode]);
            return redirect()->route('study.room.index')->with('success', 'You are already in this room!');
        }


        $roomsRef = $this->database->getReference('studyRooms');
        $foundRoomKey = null;
        $foundRoom = null;

        try {
            $rooms = $roomsRef->getValue() ?? [];
            foreach ($rooms as $key => $room) {
                if (($room['room_code'] ?? '') === $roomCode) {
                    $foundRoomKey = $key;
                    $foundRoom = $room;
                    break;
                }
            }
        } catch (\Exception $e) {
            Log::error('join - Firebase error fetching studyRooms: ' . $e->getMessage());
            return redirect()->back()->with('error', 'Failed to fetch rooms. Try again later.');
        }

        if (!$foundRoom) {
            Log::warning('join - Room not found.', ['room_code' => $roomCode]);
            return redirect()->back()->with('error', 'Room not found!');
        }
        Log::debug('join - Room found:', ['room_code' => $roomCode, 'firebaseKey' => $foundRoomKey, 'details' => $foundRoom]);

        $members = $foundRoom['members'] ?? [];
        $currentTimestamp = time();

        $firebaseUserIdKey = str_replace('.', '_', str_replace('@', '__at__', $userId));

        if (!in_array($userId, $members)) {
            Log::debug('join - User not in members list, attempting to add.');
            if (count($members) >= ($foundRoom['member_limit'] ?? PHP_INT_MAX)) {
                Log::warning('join - Room is full!', ['room_code' => $roomCode, 'member_count' => count($members), 'limit' => $foundRoom['member_limit']]);
                return redirect()->back()->with('error', 'Room is full! Cannot join.');
            }

            // Add user to the members list
            $members[] = $userId;

            try {
                // Update the members array (ensure it's re-indexed)
                $roomsRef->getChild($foundRoomKey)->getChild('members')->set(array_values($members));

                // Set the individual user's timestamp directly
                $roomsRef->getChild($foundRoomKey)->getChild('members_timestamps')->getChild($firebaseUserIdKey)->set($currentTimestamp);

                Log::info('join - User added to room members and join timestamp stored.', ['user' => $userId, 'room_key' => $foundRoomKey, 'timestamp' => $currentTimestamp]);
            } catch (\Exception $e) {
                Log::error('join - Firebase error adding member to room: ' . $e->getMessage(), ['user' => $userId, 'room_key' => $foundRoomKey]);
                return redirect()->back()->with('error', 'Failed to join the room. Please try again.');
            }
        } else {
            // User is already a member, retrieve their existing join timestamp
            $currentTimestamp = $roomsRef->getChild($foundRoomKey)->getChild('members_timestamps')->getChild($firebaseUserIdKey)->getValue() ?? $currentTimestamp;
            Log::info('join - User already in room members, retrieving existing join timestamp.', ['user' => $userId, 'room_key' => $foundRoomKey, 'timestamp' => $currentTimestamp]);
        }

        // We update the session here, but the index method will re-verify from Firebase
        session([
            'joined_room_code' => $roomCode,
            'joined_room_name' => $foundRoom['name'] ?? 'Unknown',
            'joined_room_key' => $foundRoomKey,
            'joined_room_user_timestamp' => $currentTimestamp,
        ]);
        Log::info('join - Session updated with joined room info, preparing redirect.', [
            'user' => $userId,
            'room_code' => $roomCode,
            'room_name' => $foundRoom['name'] ?? 'Unknown',
            'join_timestamp' => $currentTimestamp,
        ]);

        return redirect()->route('study.room.index')->with('success', 'Successfully joined the room!');
    }


    public function exitRoom(Request $request)
    {
        Log::info('----- StudyRoomController@exitRoom called -----');
        Log::debug('Request data:', $request->all());

        $roomCode = $request->room_code;
        $userSession = session('auth.user');
        $userId = $userSession['email'] ?? null;
        Log::debug('exitRoom - Room code: ' . $roomCode . ', userId: ' . ($userId ?? 'N/A'));

        if (!$userId) {
            Log::warning('exitRoom - User not logged in.');
            return redirect()->route('study.room.index')->with('error', 'You must be logged in to leave a room.');
        }

        $roomsRef = $this->database->getReference('studyRooms');
        $foundRoomKey = null;
        $foundRoom = null;

        try {
            $rooms = $roomsRef->getValue() ?? [];
            foreach ($rooms as $key => $room) {
                if (($room['room_code'] ?? '') === $roomCode) {
                    $foundRoomKey = $key;
                    $foundRoom = $room;
                    break;
                }
            }
        } catch (\Exception $e) {
            Log::error('exitRoom - Firebase error fetching studyRooms: ' . $e->getMessage());
            return redirect()->back()->with('error', 'Failed to exit room due to a database error.');
        }


        if ($foundRoom && $foundRoomKey) {
            Log::debug('exitRoom - Room found, processing exit.', ['room_code' => $roomCode, 'firebaseKey' => $foundRoomKey]);
            $members = $foundRoom['members'] ?? [];
            $membersTimestamps = $foundRoom['members_timestamps'] ?? [];

            $firebaseUserIdKey = str_replace('.', '_', str_replace('@', '__at__', $userId));


            $newMembers = array_values(array_filter($members, fn($m) => $m !== $userId));
            if (isset($membersTimestamps[$firebaseUserIdKey])) { // Use Firebase-safe key here
                unset($membersTimestamps[$firebaseUserIdKey]);
                Log::debug('exitRoom - User timestamp removed from members_timestamps.');
            } else {
                Log::debug('exitRoom - User timestamp not found in members_timestamps (might have been removed already).');
            }

            try {
                if (count($newMembers) === 0) {
                    Log::info('exitRoom - Room became empty, deleting room and chat data.', ['room_code' => $roomCode]);
                    $roomsRef->getChild($foundRoomKey)->remove();
                    $this->database->getReference('chat/' . $roomCode)->remove();
                } else {
                    Log::info('exitRoom - Updating room members and timestamps.', ['room_code' => $roomCode, 'new_member_count' => count($newMembers)]);
                    $roomsRef->getChild($foundRoomKey)->update([
                        'members' => $newMembers,
                        'members_timestamps' => $membersTimestamps,
                    ]);
                }
            } catch (\Exception $e) {
                Log::error('exitRoom - Firebase error updating/deleting room data: ' . $e->getMessage());
                return redirect()->back()->with('error', 'Failed to exit room due to a database error.');
            }

        } else {
            Log::warning('exitRoom - Attempted to exit non-existent or already deleted room.', ['room_code' => $roomCode]);
        }

        session()->forget(['joined_room_code', 'joined_room_name', 'joined_room_key', 'joined_room_user_timestamp']);
        Log::info('exitRoom - Session cleared for joined room.');

        return redirect()->route('study.room.index')->with('success', 'You left the room.');
    }

    public function sendMessage(Request $request)
    {
        Log::info('----- StudyRoomController@sendMessage called -----');
        Log::debug('Request data:', $request->all());

        $request->validate([
            'room_code' => 'required|string',
            'message' => 'nullable|string',
            'file_url' => 'nullable|string',
            'original_filename' => 'nullable|string',
        ]);

        $user = session('auth.user');
        $userEmail = $user['email'] ?? null;
        Log::debug('sendMessage - User email: ' . ($userEmail ?? 'N/A'));

        if (!$user || !$userEmail) {
            Log::warning('sendMessage - Unauthorized user or email missing.');
            return response()->json(['error' => 'Unauthorized'], 401);
        }

        $messageData = [
            'user_email' => $userEmail,
            'user_name' => $user['name'] ?? 'Guest',
            'message' => $request->message,
            'file_url' => $request->file_url,
            'timestamp' => time(),
            'original_filename' => $request->original_filename,
        ];

        Log::debug('sendMessage - Prepared messageData:', $messageData);

        try {
            $msgRef = $this->database->getReference('chat/' . $request->room_code);
            $msgRef->push($messageData);
            Log::info('sendMessage - Message pushed successfully to Firebase.', ['room_code' => $request->room_code, 'user_email' => $userEmail]);
        } catch (\Exception $e) {
            Log::error('sendMessage - Failed to send message to Firebase: ' . $e->getMessage(), [
                'user_email' => $userEmail,
                'room_code' => $request->room_code,
                'message_data' => $messageData,
            ]);
            return response()->json(['error' => 'Failed to send message.'], 500);
        }

        return response()->json(['status' => 'ok']);
    }

    public function getMessages($roomCode)
    {
        Log::info('----- StudyRoomController@getMessages called -----');
        Log::debug('getMessages - Room code: ' . $roomCode);

        try {
            // Get user's joined timestamp from the session (populated by index or join/add methods)
            $userJoinTimestamp = session('joined_room_user_timestamp');
            Log::debug('getMessages - User join timestamp from session: ' . ($userJoinTimestamp ?? 'N/A'));

            $messagesQuery = $this->database->getReference('chat/' . $roomCode)
                                            ->orderByChild('timestamp');

            if ($userJoinTimestamp) {
                 // Fetch messages starting from the moment the user joined
                 $messagesQuery = $messagesQuery->startAt($userJoinTimestamp);
                 Log::debug('getMessages - Querying messages starting at timestamp: ' . $userJoinTimestamp);
            }

            $messages = $messagesQuery->getValue() ?? [];
            Log::debug('getMessages - Fetched ' . count($messages) . ' messages from Firebase.');
            return response()->json(array_values($messages));
        } catch (\Exception $e) {
            Log::error('getMessages - Firebase error fetching messages for room ' . $roomCode . ': ' . $e->getMessage());
            return response()->json(['error' => 'Failed to retrieve messages.'], 500);
        }
    }

    public function getRoomMembersJson($roomCode)
    {
        Log::info('----- StudyRoomController@getRoomMembersJson called -----');
        Log::debug('getRoomMembersJson - Room code: ' . $roomCode);

        try {
            $rooms = $this->database->getReference('studyRooms')->getValue() ?? [];
            $room = null;
            $roomKey = null; // Also get the key
            foreach ($rooms as $key => $r) {
                if (($r['room_code'] ?? '') === $roomCode) {
                    $room = $r;
                    $roomKey = $key;
                    break;
                }
            }

            if (!$room) {
                Log::warning('getRoomMembersJson - Room not found.', ['room_code' => $roomCode]);
                return response()->json(['error' => 'Room not found'], 404);
            }

            // Fetch current members' emails and names
            $membersData = [];
            foreach ($room['members'] ?? [] as $memberEmail) {
                // You might need to fetch user names from another system if they're not in Firebase
                // For now, let's just use the email or a part of it.
                $membersData[] = [
                    'email' => $memberEmail,
                    'name' => explode('@', $memberEmail)[0], // Simple extraction
                ];
            }


            Log::debug('getRoomMembersJson - Room details found.', ['room_code' => $roomCode, 'room_name' => $room['name'] ?? 'N/A']);
            return response()->json([
                'name' => $room['name'] ?? '',
                'description' => $room['description'] ?? '',
                'member_limit' => $room['member_limit'] ?? 0,
                'room_code' => $room['room_code'] ?? '',
                'is_private' => $room['is_private'] ?? false,
                'members' => $membersData, // Return structured member data
                'members_timestamps' => $room['members_timestamps'] ?? [],
            ]);
        } catch (\Exception $e) {
            Log::error('getRoomMembersJson - Firebase error fetching room info for ' . $roomCode . ': ' . $e->getMessage());
            return response()->json(['error' => 'Failed to retrieve room information.'], 500);
        }
    }
}