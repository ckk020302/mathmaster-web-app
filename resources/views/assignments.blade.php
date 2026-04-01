@extends('layouts.app')

@section('content')
<style>
  body {
    background: #fff;
  }

  .main-container {
    display: flex;
    justify-content: center;
    gap: 60px;
    margin-top: 30px;
    flex-wrap: wrap;
    align-items: flex-start;
  }

  .chapter-card,
  .question-card {
    width: 600px;
    padding: 30px;
    border: 1px solid #ddd;
    border-radius: 12px;
    background: #fff;
    box-shadow: 0 4px 8px rgba(0, 0, 0, 0.05);
  }

  .progress-value {
    font-size: 36px;
    font-weight: bold;
    color: #28a745;
  }

  .chapter-card p,
  .question-card p {
    font-size: 20px;
  }

  .btn-start,
  .option-btn {
    border: none;
    padding: 10px 25px;
    border-radius: 25px;
    color: #000;
    font-weight: 600;
    margin-top: 15px;
    cursor: pointer;
  }

  .btn-start {
    background: #5be19b;
  }

  .option-btn {
    background: #f0f0f0;
    width: 48%;
    margin: 0;
  }

  .options-row {
    display: flex;
    justify-content: space-between;
    margin-top: 10px;
  }

  .timer-box,
  .progress-box {
    width: 280px;
    text-align: center;
    padding: 25px;
    border: 1px solid #ddd;
    border-radius: 12px;
    background: #fff;
    box-shadow: 0 4px 8px rgba(0, 0, 0, 0.05);
    cursor: pointer;
  }

  .timer-box h6,
  .progress-box h6 {
    font-size: 20px;
    font-weight: bold;
    display: flex;
    align-items: center;
    justify-content: center;
    gap: 10px;
  }

  .timer-display {
    font-weight: bold;
    font-size: 20px;
    cursor: pointer;
    margin-top: 20px;
  }

  #timerModal .modal-content {
    width: 200px;
    padding: 20px;
  }

  #countdownDisplay {
    font-size: 24px;
    font-weight: bold;
    text-align: center;
    margin-top: 20px;
  }

  .modal {
    display: none;
    position: fixed;
    top: 0;
    left: 0;
    width: 100%;
    height: 100%;
    background: rgba(0, 0, 0, 0.5);
    justify-content: center;
    align-items: center;
    z-index: 2000;
  }

  .modal-content {
    background: #fff;
    padding: 30px;
    border-radius: 12px;
    text-align: center;
    width: 450px;
  }

  #hoursInput,
  #minsInput {
    font-size: 24px;
    width: 60px;
    text-align: center;
  }

  .back-icon {
    position: absolute;
    top: 10px;
    right: 10px;
    width: 30px;
    height: 30px;
    cursor: pointer;
    background: #f0f0f0;
    border: none;
    border-radius: 50%;
    display: flex;
    align-items: center;
    justify-content: center;
  }

  #okBtn {
    padding: 5px 15px;
    font-size: 14px;
    border-radius: 8px;
  }

  /*---------------------------------------------------------------------------------*/
  #progressPage {
    display: none;
    flex-direction: column;
    padding: 20px;
    gap: 15px;
    width: 100%;
    align-items: center;
    gap: 15px;
    position: relative;
  }

  #progressTitle {
    display: flex;

    justify-content: center;
    align-items: center;
    gap: 10px;
    width: 100%;
    margin-top: 40px;
    margin-bottom: 50px;
  }

  .progress-item {
    display: flex;
    flex-direction: column;
    align-items: center;
    gap: 5px;
  }

  .progress-labels {
    display: flex;
    justify-content: space-between;
    font-weight: bold;
    width: 100%;
    max-width: 500px
  }

  #progressContent {
    display: flex;
    flex-direction: column;
    gap: 10px;
  }

  .progress-bar-container {
    width: 100%;
    max-width: 500px;
    height: 12px;
    margin: 0 auto;
    background: #e0e0e0;
    border-radius: 6px;
    overflow: hidden;
  }

  .progress-bar-fill {
    height: 100%;
    background: #28a745;
    width: 0%;
    border-radius: 6px;
    transition: width 0.3s ease;
  }

  #progressPage .back-icon {
    position: absolute;
    top: 10px;
    right: 10px;
    width: 30px;
    height: 30px;
    cursor: pointer;
    background: #f0f0f0;
    border: none;
    border-radius: 50%;
    display: flex;
    align-items: center;
    justify-content: center;
    z-index: 1000;
  }

  /*-----------------------------------------------------------*/
  #questionProgress,
  #tipContent {
    display: flex;
    flex-wrap: wrap;
    justify-content: center;
    gap: 8px;
    padding: 0;
    margin: 0 auto;
  }

  .progress-circle {
    width: 25px;
    height: 25px;
    border-radius: 50%;
    border: 2px solid #ccc;
    background-color: transparent;
  }

  .progress-circle.correct {
    background-color: #28a745;
    border-color: #28a745;
  }

  .progress-circle.wrong {
    background-color: #dc3545;
    border-color: #dc3545;
  }

  #tipContent {
    margin-top: 30px;
  }

  .question-card img {
    width: 1000px;
    height: 250px;
    object-fit: contain;
    border-radius: 12px;
    margin-bottom: 10px;
  }
</style>
</head>

<body>


  <div class="container main-container">

    @php
    $auth = (array) session('auth.user', []);
    try { $currentUserId = \App\Models\User::whereRaw('LOWER(email) = ?', [strtolower($auth['email'] ?? '')])->value('id'); }
    catch (\Throwable $e) { $currentUserId = null; }
    // Resolve avatar URL from session (fallback to public/profile.png)
    try {
    $avatarPath = ltrim((string)($auth['avatar'] ?? 'profile.png'), '/');
    $avatarUrl = asset($avatarPath);
    } catch (\Throwable $e) { $avatarUrl = asset('profile.png'); }
    @endphp

    <!-- Chapter Selection Page -->
    <div id="chapterSelection" style="display: flex; gap: 60px;">
      <div>
        <div class="mb-4">
          <select id="academicLevel" class="academicLevel-select w-auto">
            <option disabled selected>Loading academic level...</option>
          </select>
        </div>

        <div class="chapter-card">
          <div class="d-flex justify-content-between align-items-center mb-3">
            <select id="chapterSelect" class="academicLevel-select w-auto">
              <option disabled selected>Loading chapters...</option>
            </select>
            <span class="progress-value">0%</span>
          </div>
          <p id="chapterInfo"><strong>Level</strong></p>
          <button class="btn-start" id="startBtn">Start Answer</button>
        </div>
      </div>

      <div style="display: flex; flex-direction: column; gap: 20px; margin-top: 50px; align-items: flex-end;">
        <div class="timer-box">
          <h6>Set Timer
            <img src="/clock.png" alt="Progress Icon" style="width: 24px; height: 24px; margin-left: 8px;">
          </h6>
          <div id="timerDisplay" class="timer-display">0 hours 30 mins</div>
        </div>
        <div class="progress-box" id="progressBtn">
          <h6 style="display: flex; align-items: center; justify-content: space-between;">
            View My Progress
            <img src="/progress.png" alt="Progress Icon" style="width: 40px; height: 40px; margin-left: 8px;">
          </h6>
        </div>
      </div>
    </div>

    <!-- Timer Modal -->
    <div id="timerModal" class="modal">
      <div class="modal-content">
        <h6>Adjust Timer</h6>
        <div class="d-flex justify-content-center align-items-center my-3">
          <input id="hoursInput" type="number" min="0"> :
          <input id="minsInput" type="number" min="0" max="59">
        </div>
        <div class="d-flex justify-content-center gap-3 mt-3">
          <button class="btn btn-success" id="okBtn">OK</button>
        </div>
      </div>
    </div>

    <!-- Time Up Popup -->
    <div id="timeUpPopup" class="modal">
      <div class="modal-content">
        <p id="timeUpMessage" style="font-size: 18px; font-weight: bold;">Congratulations! You have study ----Good Job! Keep going on!</p>
        <img src="/clapHand.png" alt="Time Up" style="width: 100px; margin: 20px auto; display: block;">
        <div class="d-flex justify-content-center gap-3 mt-3">
          <button class="btn btn-primary" id="popupOkBtn">OK</button>
        </div>
      </div>
    </div>

    <!-- Question Page -->
    <div id="questionPage" style="display: none; flex-direction: row; justify-content: center; gap: 40px; width: 100%;">
      <!-- Left column: Question and Options -->
      <div style="flex: 1; max-width: 500px; display: flex; flex-direction: column; align-items: center; gap: 10px;">
        <button class="back-icon" id="backIcon" title="Exit">
          <img src="/out.png" alt="Exit" style="width: 20px; height: 20px;">
        </button>

        <div class="question-card">
          <h5 id="questionTitle">Chapter 1</h5>

          <p id="questionText">Question text will appear here</p>
        </div>

        <div class="options-container" style="width: 100%;">
          <div class="options-row">
            <button class="option-btn" id="optionA">A</button>
            <button class="option-btn" id="optionB">B</button>
          </div>
          <div class="options-row">
            <button class="option-btn" id="optionC">C</button>
            <button class="option-btn" id="optionD">D</button>
          </div>
        </div>

        <div id="countdownDisplay">Time left: --:--:--</div>
      </div>

      <!-- Right column: Tips -->
      <div id="tipBox" style="width: 200px; padding: 20px; border: 1px; border-radius: 12px; height: fit-content;">
        <div id="questionProgress"></div>
        <div id="tipContent"></div>
      </div>
    </div>

    <!-- Tip Modal -->
    <div id="tipModal" class="modal">
      <div class="modal-content" style="width: 700px;">
        <h6>Tip </h6>
        <div id="tipModalContent" style="margin-top: 10px;"></div>
        <div class="d-flex justify-content-center gap-3 mt-3">
          <button class="btn btn-primary" onclick="closeTipModal()">OK</button>
        </div>
      </div>
    </div>

    <!-- Progress Page -->
    <div id="progressPage">
      <button class="back-icon" id="backToQuestion">
        <img src="/out.png" alt="Exit" style="width: 20px; height: 20px;">
      </button>
      <div id="progressTitle">
        <h6 style="margin: 0; font-size:30px;">My Progress</h6>
        <img src="/progress.png" alt="Progress Icon" style="width: 40px; height: 40px;">
      </div>
      <!-- Academic Level Dropdown -->
      <div style="margin:10px 0 20px 0; padding-left: 325px;">
        <select id="progressAcademicLevel" style="padding: 5px 10px;"></select>
      </div>
      <div id="progressContent"></div>
    </div>

    <div id="finishedPopup" class="modal">
      <div class="modal-content">
        <p style="font-size: 18px; font-weight: bold;">Congratulations! You have finished all the questions</p>
        <img src="/clapHand.png" alt="Time Up" style="width: 100px; margin: 20px auto; display: block;">
        <div class="d-flex justify-content-center gap-3 mt-3">
          <button class="btn btn-secondary" id="finishedOkBtn">OK</button>
        </div>
      </div>
    </div>

    <!-- Advance to Next Level Popup -->
    <div id="advancePopup" class="modal">
      <div class="modal-content">
        <p style="font-size: 18px; font-weight: bold; color: black;">Congratulations! You have advanced to the next level!</p>
        <img src="/advanced.png" alt="Time Up" style="width: 100px; margin: 20px auto; display: block;">
        <div class="d-flex justify-content-center gap-3 mt-3">
          <button class="btn btn-secondary" id="advanceLeaveBtn">Leave</button>
          <button class="btn btn-success" id="advanceContinueBtn">Continue</button>
        </div>
      </div>
    </div>

    <!-- Not Advanced Popup -->
    <div id="notAdvancePopup" class="modal">
      <div class="modal-content">
        <p style="font-size: 18px; font-weight: bold; color: black;">Level not upgraded this time — but you're improving. Keep it up!</p>
        <img src="/noadvanced.png" alt="Time Up" style="width: 100px; margin: 20px auto; display: block;">
        <div class="d-flex justify-content-center gap-3 mt-3">
          <button class="btn btn-secondary mt-3" id="notAdvanceLeaveBtn">Leave</button>
          <button class="btn btn-success mt-3" id="notAdvanceContinueBtn">Continue</button>
        </div>
      </div>
    </div>

    <!-- Chapter Finished Popup -->
    <div id="chapterFinishedPopup" class="modal">
      <div class="modal-content">
        <p style="font-size: 18px; font-weight: bold; color: black;">
          🎉 Congratulations! You have finished this chapter!
        </p>
        <img src="/advanced.png" alt="Finished Chapter" style="width: 100px; margin: 20px auto; display: block;">
        <div class="d-flex justify-content-center gap-3 mt-3">
          <button class="btn btn-success mt-3" id="chapterFinishedOkBtn">OK</button>
        </div>
      </div>
    </div>

    <script>
      document.addEventListener('DOMContentLoaded', () => {
        const userId = @json($currentUserId);

        // ---------------- DOM ----------------
        const DOM = {
          academicLevel: document.getElementById('academicLevel'),
          chapterSelect: document.getElementById('chapterSelect'),
          chapterInfo: document.getElementById('chapterInfo'),
          progressValue: document.querySelector('.progress-value'),
          startBtn: document.getElementById('startBtn'),
          questionPage: document.getElementById('questionPage'),
          chapterSelection: document.getElementById('chapterSelection'),
          questionTitle: document.getElementById('questionTitle'),
          questionText: document.getElementById('questionText'),
          optionBtns: ['A', 'B', 'C', 'D'].map(l => document.getElementById(`option${l}`)),
          tipContent: document.getElementById('tipContent'),
          progressDiv: document.getElementById('questionProgress'),
          countdownDisplay: document.getElementById('countdownDisplay'),
          timerBox: document.querySelector('.timer-box'),
          timerDisplay: document.getElementById('timerDisplay'),
          timerModal: document.getElementById('timerModal'),
          hoursInput: document.getElementById('hoursInput'),
          minsInput: document.getElementById('minsInput'),
          okBtn: document.getElementById('okBtn'),
          tipModal: document.getElementById('tipModal'),
          tipModalContent: document.getElementById('tipModalContent')
        };

        // ---------------- Globals ----------------
        let allQuestions = [],
          questions = [];
        let currentLevel = null,
          currentChapter = null,
          currentDifficulty;
        let currentQuestionIndex = 0,
          currentAnswers = {};
        let currentRoundId = null;
        let remainingTime = 30 * 60,
          countdownInterval = null;
        const timerKey = 'remainingTime';
        let lastApiData = null;
        let tipsUnlocked = 0;

        const difficulties = ['Easy', 'Intermediate', 'Advanced'];
        // ---------------- Helpers ----------------
        function escapeHtml(s) {
          return String(s || '').replaceAll('&', '&amp;').replaceAll('<', '&lt;').replaceAll('>', '&gt;').replaceAll('"', '&quot;').replaceAll("'", '&#39;');
        }

        function shuffleArray(arr) {
          return [...arr].sort(() => Math.random() - 0.5);
        }

        function getNextDifficulty(current) {
          const idx = difficulties.indexOf(current);
          return idx < difficulties.length - 1 ? difficulties[idx + 1] : null;
        }

        function renderTips(q) {
          DOM.tipContent.innerHTML = '';
          if (!q.tips || !q.tips.length) return;

          for (let i = 0; i < tipsUnlocked && i < q.tips.length; i++) {
            const tipBtn = document.createElement('button');
            tipBtn.className = 'btn btn-outline-info btn-sm me-2 mb-2';
            tipBtn.textContent = `💡 Tip ${i+1} : Click Me`;
            tipBtn.onclick = () => showTipModal(`<img src="${q.tips[i]}" style="width:600px;height:auto; border-radius:8px;">`, i);
            DOM.tipContent.appendChild(tipBtn);
          }
        }

        // ---------------- Modal ----------------
        function showModal(modal) {
          modal.style.display = 'flex';
        }

        function hideModal(modal) {
          modal.style.display = 'none';
        }

        function showTipModal(content, index) {
          DOM.tipModal.querySelector("h6").textContent = `Tip ${index+1}`;
          DOM.tipModalContent.innerHTML = content;
          showModal(DOM.tipModal);
        }

        window.closeTipModal = () => hideModal(DOM.tipModal);

        // ---------------- Timer ----------------
        function formatTime(seconds) {
          const h = Math.floor(seconds / 3600).toString().padStart(2, '0');
          const m = Math.floor((seconds % 3600) / 60).toString().padStart(2, '0');
          const s = (seconds % 60).toString().padStart(2, '0');
          return `${h}:${m}:${s}`;
        }

        function updateCountdown() {
          if (remainingTime <= 0) {
            clearInterval(countdownInterval);
            DOM.countdownDisplay.textContent = 'Time left: 00:00:00';
            localStorage.removeItem(timerKey);

            const totalSeconds = (DOM.hoursInput.value * 3600 || 0) + (DOM.minsInput.value * 60 || 0);
            const studiedSeconds = totalSeconds;
            const h = Math.floor(studiedSeconds / 3600);
            const m = Math.floor((studiedSeconds % 3600) / 60);

            const parts = [];
            if (h > 0) parts.push(h + (h === 1 ? ' hour' : ' hours'));
            if (m > 0) parts.push(m + (m === 1 ? ' min' : ' mins'));
            const timeText = parts.join(' ');

            document.getElementById('timeUpMessage').innerHTML =
              `Congratulations! You have reached your study hour!Good Job! Keep going on!`;

            showModal(document.getElementById('timeUpPopup'));
            return;
          }
          DOM.countdownDisplay.textContent = 'Time left: ' + formatTime(remainingTime);
          remainingTime--;
          localStorage.setItem(timerKey, remainingTime);
        }

        document.getElementById('popupOkBtn').addEventListener('click', () => {
          hideModal(document.getElementById('timeUpPopup'));
        });


        DOM.timerBox.addEventListener('click', () => {
          const match = DOM.timerDisplay.textContent.match(/(\d+)\s*hours?\s*(\d+)\s*mins?/i);
          if (match) {
            DOM.hoursInput.value = parseInt(match[1], 10);
            DOM.minsInput.value = parseInt(match[2], 10);
          } else {
            DOM.hoursInput.value = 0;
            DOM.minsInput.value = 30;
          }
          showModal(DOM.timerModal);
        });

        DOM.okBtn.addEventListener('click', () => {
          let h = parseInt(DOM.hoursInput.value, 10) || 0;
          let m = parseInt(DOM.minsInput.value, 10) || 0;
          remainingTime = h * 3600 + m * 60;
          DOM.timerDisplay.textContent = `${h} hours ${m} mins`;
          localStorage.setItem(timerKey, remainingTime);
          hideModal(DOM.timerModal);
          if (countdownInterval) clearInterval(countdownInterval);
          updateCountdown();
          countdownInterval = setInterval(updateCountdown, 1000);
        });

        window.addEventListener('load', () => {
          const savedTime = parseInt(localStorage.getItem(timerKey));
          if (savedTime && savedTime > 0) {
            remainingTime = savedTime;
          }
          updateCountdown();
          countdownInterval = setInterval(updateCountdown, 1000);
        });

        document.getElementById('backIcon').addEventListener('click', async () => {
          if (countdownInterval) clearInterval(countdownInterval);

          DOM.questionPage.style.display = 'none';
          DOM.chapterSelection.style.display = 'flex';

          const prevChapter = currentChapter;
          await refreshChaptersForLevel(currentLevel);
          if (prevChapter && [...DOM.chapterSelect.options].some(o => o.value === prevChapter)) {
            DOM.chapterSelect.value = prevChapter;
            currentChapter = prevChapter;
          }
          await updateChapterCardProgress(currentLevel, currentChapter);
        });

        // ---------------- Fetch Questions ----------------
        async function fetchQuestionBank() {
          const res = await fetch('/assignments/question-bank');
          const json = await res.json();
          allQuestions = Array.isArray(json.data) ? json.data : [];

          allQuestions.forEach(q => {
            q.user_answered = q.user_answered || false;
          });

          // Academic Level
          const levels = [...new Set(allQuestions.map(q => q.academic_level))].filter(Boolean).sort();
          DOM.academicLevel.innerHTML = levels.map(l => `<option value="${escapeHtml(l)}">${escapeHtml(l)}</option>`).join('');
          currentLevel = levels[0];
          DOM.academicLevel.value = currentLevel;

          refreshChaptersForLevel(currentLevel);
        }
        async function refreshChaptersForLevel(level) {
          const chapters = [...new Set(allQuestions
            .filter(q => q.academic_level === level)
            .map(q => q.chapter)
          )].filter(Boolean).sort();

          const chapterOptions = chapters.map(c => `<option value="${escapeHtml(c)}">${escapeHtml(c)}</option>`);
          DOM.chapterSelect.innerHTML = chapterOptions.join('');

          currentChapter = chapters[0];

          await updateChapterCardProgress(level, currentChapter);
        }

        async function updateChapterCardProgress(level, chapter) {
          const params = new URLSearchParams({
            user_id: userId,
            academic_level: level,
            chapter: chapter
          });
          const res = await fetch(`/chapter-progress?${params.toString()}`); // 同 View My Progress API
          const data = await res.json();

          let diff = 'Easy',
            percent = 0; // 默认值

          // 查找当前章节的数据
          const chapData = data.find(d => d.chapter === chapter);

          if (chapData) {
            percent = chapData.percent || 0;

            if (chapData.status === 'finished') {
              diff = 'This chapter already finished';
              percent = 100;
              currentDifficulty = null;
            } else {
              diff = chapData.difficulty || 'Easy';
              currentDifficulty = diff;
            }
          }

          DOM.progressValue.textContent = `${percent}%`;
          DOM.chapterInfo.textContent = diff;
        }


        // ---------------- Load / Initialize Round ----------------
        async function loadNextUnfinishedRound() {
          const params = new URLSearchParams({
            user_id: userId,
            academic_level: currentLevel,
            chapter: currentChapter
          });
          const res = await fetch(`/get-next-unfinished-round?${params.toString()}`);
          const data = await res.json();

          if (data && data.status === 'in-progress') {
            currentRoundId = data.round_id;
            currentDifficulty = data.difficulty;
            currentAnswers = data.answers || {};

            const unansweredIds = data.question_pool.filter(qid => !(qid in currentAnswers));

            if (unansweredIds.length === 0) {
              finishRound();
              return;
            }

            currentQuestionIndex = data.question_pool.indexOf(unansweredIds[0]);

            questions = data.question_pool.map(qid => {
              const q = allQuestions.find(qq => qq.id === qid);
              return q ? {
                  ...q,
                  question_id: qid,
                  tips: [q.tip_easy, q.tip_intermediate, q.tip_advanced].filter(Boolean)
                } :
                {
                  question_id: qid,
                  question_text: 'Question not found',
                  tips: []
                };
            });
            renderProgressCircles();
          } else {
            await initializeUserRound();
          }
          loadQuestion();
        }

        async function initializeUserRound(newRound = true) {
          correctCount = 0;
          const csrfToken = document.querySelector('meta[name="csrf-token"]').content;

          if (!currentDifficulty || lastApiData) {
            currentDifficulty = 'Easy';
          }

          const pool = allQuestions.filter(
            q => q.academic_level === currentLevel && q.chapter === currentChapter && q.difficulty === currentDifficulty
          );
          const picked = shuffleArray(pool).slice(0, 5);
          questions = picked.map(r => ({
            ...r,
            question_id: r.id,
            tips: [r.tip_easy, r.tip_intermediate, r.tip_advanced].filter(Boolean)
          }));
          currentAnswers = {};
          currentQuestionIndex = 0;

          const res = await fetch('/initialize-user-answers', {
            method: 'POST',
            headers: {
              'Content-Type': 'application/json',
              'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
            },
            body: JSON.stringify({
              user_id: userId,
              academic_level: currentLevel,
              chapter: currentChapter,
              difficulty: currentDifficulty,
              question_pool: questions.map(q => q.question_id),
              answers: currentAnswers,
              current_index: 0,
              status: 'in-progress',
              new_round: newRound
            })
          });
          const json = await res.json();
          if (!json.round_id) {
            console.error('initializeUserRound failed: no round_id returned!', json);
            alert('Error: Failed to initialize new round. Please try again.');
          } else {
            currentRoundId = json.round_id;
            console.log('New round_id:', currentRoundId);
          }
          renderProgressCircles();
        }

        function renderProgressCircles() {
          DOM.progressDiv.innerHTML = '';
          // Added text here
          const scoreText = document.createElement('p');
          scoreText.textContent = 'Score 80% and above to Level Up!';
          scoreText.style.textAlign = 'center';
          scoreText.style.marginBottom = '15px';
          scoreText.style.fontWeight = 'bold';
          DOM.progressDiv.appendChild(scoreText);

          const totalCircles = 5;
          const rowCount = 1;
          const perRow = totalCircles / rowCount;

          for (let r = 0; r < rowCount; r++) {
            const rowDiv = document.createElement('div');
            rowDiv.style.display = 'flex';
            rowDiv.style.justifyContent = 'center';
            rowDiv.style.gap = '5px';
            rowDiv.style.marginBottom = '5px';

            for (let i = 0; i < perRow; i++) {
              const idx = r * perRow + i;
              const circle = document.createElement('div');
              circle.className = 'progress-circle';
              circle.style.width = '25px';
              circle.style.height = '25px';

              if (questions[idx]) {
                const ans = currentAnswers[questions[idx].question_id];
                if (ans) {
                  circle.classList.add(ans.is_correct ? 'correct' : 'wrong');
                }
              }

              if (idx === currentQuestionIndex) {
                circle.style.border = '3px solid #007bff';
              }

              rowDiv.appendChild(circle);
            }
            DOM.progressDiv.appendChild(rowDiv);
          }
        }
        // ---------------- Load Question ----------------
        function loadQuestion() {
          if (currentQuestionIndex >= questions.length) {
            finishRound();
            return;
          }

          const q = questions[currentQuestionIndex];
          DOM.questionTitle.textContent = `${escapeHtml(q.chapter)} - ${escapeHtml(q.difficulty)}`;
          let questionHtml = '';
          if (q.question_image) {
            questionHtml += `<img src="${q.question_image}" style="width:100%; border-radius:12px; margin-bottom:10px;">`;
          }

          const text = q.question_text || q.question_name || q.text || '';
          if (text) {
            questionHtml += `<div>${escapeHtml(text)}</div>`;
          }

          DOM.questionText.innerHTML = questionHtml || 'Question text not available';
          tipsUnlocked = 0;
          renderTips(q);

          ['A', 'B', 'C', 'D'].forEach((opt, i) => {
            const btn = DOM.optionBtns[i];
            btn.textContent = opt;
            btn.disabled = false;
            btn.style.backgroundColor = '#f0f0f0';
            btn.onclick = () => submitAnswer(opt);
          });
        }
        // ---------------- Submit Answer ----------------
        let correctCount = 0;
        async function submitAnswer(option) {
          const q = questions[currentQuestionIndex];
          if (!q) {
            console.warn('No question found for index', currentQuestionIndex);
            return;
          }
          const isCorrect = option === q.answer_image;

          if (!currentAnswers[q.question_id]) {
            currentAnswers[q.question_id] = {
              selected: option,
              is_correct: isCorrect
            };
            renderProgressCircles();
            if (isCorrect) correctCount++;
            await saveProgress('in-progress');
          }

          if (isCorrect) {
            const btn = DOM.optionBtns[['A', 'B', 'C', 'D'].indexOf(option)];
            btn.style.backgroundColor = '#28a745';
            btn.disabled = true;

            setTimeout(async () => {
              currentQuestionIndex++;
              renderProgressCircles();

              if (currentQuestionIndex >= questions.length) {
                window.latestScore = correctCount;
                await saveProgress('finished');
                showModal(document.getElementById('finishedPopup'));
              } else {
                loadQuestion();
              }
            }, 200);
          } else {
            const btn = DOM.optionBtns[['A', 'B', 'C', 'D'].indexOf(option)];
            btn.style.backgroundColor = '#dc3545';
            btn.disabled = true;

            tipsUnlocked++;
            renderTips(q);
          }
        }

        async function saveProgress(status) {
          await fetch('/submit-answer', {
            method: 'POST',
            headers: {
              'Content-Type': 'application/json',
              'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
            },
            body: JSON.stringify({
              user_id: userId,
              round_id: currentRoundId,
              academic_level: currentLevel,
              chapter: currentChapter,
              difficulty: currentDifficulty,
              question_pool: questions.map(q => q.question_id),
              answers: currentAnswers,
              current_index: currentQuestionIndex,
              status: status,
              score: correctCount
            })
          });
        }

        // ---------------- Finished → Next Round ----------------
        document.getElementById('finishedOkBtn').addEventListener('click', async () => {
          hideModal(document.getElementById('finishedPopup'));

          const scoreRate = window.latestScore / questions.length;
          const isAdvanced = currentDifficulty === 'Advanced';
          const enoughScore = scoreRate >= 0.8;

          if (!isAdvanced) {
            if (enoughScore) {
              showModal(document.getElementById('advancePopup'));
            } else {
              showModal(document.getElementById('notAdvancePopup'));
            }
          } else { // currentDifficulty === 'Advanced'
            if (enoughScore) {
              showModal(document.getElementById('chapterFinishedPopup'));
              await updateChapterCardProgress(currentLevel, currentChapter);
            } else {
              showModal(document.getElementById('notAdvancePopup'));
            }
          }
        });


        //-------------------Finished Chapter--------------------
        document.getElementById('chapterFinishedOkBtn').onclick = () => {
          hideModal(document.getElementById('chapterFinishedPopup'));
          DOM.chapterSelection.style.display = 'flex';
          DOM.questionPage.style.display = 'none';
        };

        // ---------------- Advance Popup ----------------

        document.getElementById('advanceLeaveBtn').addEventListener('click', async () => {
          hideModal(document.getElementById('advancePopup'));
          DOM.chapterSelection.style.display = 'flex';
          DOM.questionPage.style.display = 'none';
          await startNextRound();
          await updateChapterCardProgress(currentLevel, currentChapter);
          if (countdownInterval) clearInterval(countdownInterval);
        });

        document.getElementById("advanceContinueBtn").addEventListener("click", async () => {
          hideModal(document.getElementById("advancePopup"));
          await startNextRound();
        });

        async function startNextRound() {
          const nextDiff = getNextDifficulty(currentDifficulty);
          if (nextDiff) {
            currentDifficulty = nextDiff;
          } else {
            DOM.chapterSelection.style.display = 'flex';
            DOM.questionPage.style.display = 'none';
            return;
          }

          await initializeUserRound();
          loadQuestion();
        }


        document.getElementById("notAdvanceLeaveBtn").addEventListener("click", async () => {
          hideModal(document.getElementById("notAdvancePopup"));
          DOM.questionPage.style.display = 'none';
          DOM.chapterSelection.style.display = 'flex';
          await regenerateSameDifficultyRound();
          if (countdownInterval) clearInterval(countdownInterval);
        });


        document.getElementById("notAdvanceContinueBtn").addEventListener("click", async () => {
          hideModal(document.getElementById("notAdvancePopup"));
          await regenerateSameDifficultyRound();
          loadQuestion();
        });


        async function regenerateSameDifficultyRound() {
          await initializeUserRound();
          await updateChapterCardProgress(currentLevel, currentChapter);
        }

        // ---------------- Start Button ----------------
        DOM.startBtn.addEventListener('click', async () => {
          DOM.chapterSelection.style.display = 'none';
          DOM.questionPage.style.display = 'flex';
          await loadNextUnfinishedRound();
          loadQuestion();

          if (!remainingTime || remainingTime <= 0) {
            remainingTime = 30 * 60;
          }
          updateCountdown();
          if (countdownInterval) clearInterval(countdownInterval);
          countdownInterval = setInterval(updateCountdown, 1000);
        });
        DOM.academicLevel.addEventListener('change', (e) => {
          currentLevel = e.target.value;
          refreshChaptersForLevel(currentLevel);
        });

        DOM.chapterSelect.addEventListener('change', (e) => {
          currentChapter = e.target.value;
          updateChapterCardProgress(currentLevel, currentChapter);
        });
        //-----------------progress page-----------------
        const progressAcademicLevel = document.getElementById('progressAcademicLevel');

        progressBtn.addEventListener('click', async () => {
          // populate dropdown with levels (same as main academicLevel dropdown)
          const levels = [...new Set(allQuestions.map(q => q.academic_level))].filter(Boolean).sort();
          progressAcademicLevel.innerHTML = levels
            .map(l => `<option value="${escapeHtml(l)}">${escapeHtml(l)}</option>`)
            .join('');

          // default select = currentLevel
          progressAcademicLevel.value = currentLevel;

          // load progress for the selected level
          await loadProgress(progressAcademicLevel.value);

          document.getElementById('chapterSelection').style.display = 'none';
          document.getElementById('questionPage').style.display = 'none';
          progressPage.style.display = 'block';
        });

        progressAcademicLevel.addEventListener('change', async (e) => {
          currentLevel = e.target.value;
          await loadProgress(currentLevel);
        });

        // Reusable loader function
        async function loadProgress(level) {
          progressContent.innerHTML = '';

          const chapters = [...new Set(allQuestions
            .filter(q => q.academic_level === level)
            .map(q => q.chapter)
          )].sort();

          const chapterDivs = {};
          chapters.forEach(chap => {
            const div = document.createElement('div');
            div.style.marginBottom = '15px';
            div.innerHTML = `
          <div class="progress-item">
            <div class="progress-labels">
              <span class="chapter-name">${chap}</span>
              <span class="chapter-percent">0%</span>
            </div>
            <div class="progress-bar-container">
              <div class="progress-bar-fill" style="width: 0%;"></div>
            </div>
          </div>
      `;
            progressContent.appendChild(div);
            chapterDivs[chap] = div;
          });

          const params = new URLSearchParams({
            user_id: userId,
            academic_level: level
          });
          const res = await fetch(`chapter-progress?${params.toString()}`);
          const data = await res.json();

          data.forEach(chapData => {
            const div = chapterDivs[chapData.chapter];
            if (div) {
              div.querySelector('.chapter-percent').textContent = chapData.percent + '%';
              div.querySelector('.progress-bar-fill').style.width = chapData.percent + '%';
            }
          });
        }
        backToQuestion.addEventListener('click', () => {
          progressPage.style.display = 'none';
          document.getElementById('chapterSelection').style.display = 'flex';
        });

        fetchQuestionBank();
      });
    </script>
    @endsection