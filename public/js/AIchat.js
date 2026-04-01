function getTimestamp() {
  const now = new Date();
  let hours = now.getHours();
  const minutes = now.getMinutes().toString().padStart(2, '0');
  const ampm = hours >= 12 ? 'PM' : 'AM';
  hours = hours % 12 || 12;
  return `${hours}:${minutes} ${ampm}`;
}

function esc(text) {
  return String(text)
    .replace(/&/g, '&amp;')
    .replace(/</g, '&lt;')
    .replace(/>/g, '&gt;')
    .replace(/"/g, '&quot;')
    .replace(/'/g, '&#39;');
}

function appendUser(message) {
  const box = document.getElementById('chat-box');
  box.insertAdjacentHTML('beforeend', `
    <div class="message user">
      <div class="bubble-container">
        <div class="bubble">${esc(message)}</div>
        <div class="timestamp">${getTimestamp()}</div>
      </div>
    </div>
  `);
  box.scrollTop = box.scrollHeight;
}

function appendTyping(typingId) {
  const box = document.getElementById('chat-box');
  box.insertAdjacentHTML('beforeend', `
    <div class="message bot" id="${typingId}">
      <div class="icon"><img src="/bot.png" alt="Bot" class="bot-icon"/></div>
      <div class="bubble-container">
        <div class="bubble typing-bubble">
          <span class="typing vertical">
            <span class="dot"></span>
            <span class="dot"></span>
            <span class="dot"></span>
          </span>
        </div>
      </div>
    </div>
  `);
  box.scrollTop = box.scrollHeight;
}

function appendBot(message) {
  const box = document.getElementById('chat-box');
  box.insertAdjacentHTML('beforeend', `
    <div class="message bot">
      <div class="icon"><img src="/bot.png" alt="Bot" class="bot-icon"/></div>
      <div class="bubble-container">
<div class="bubble">${formatBold(esc(message))}</div>
        <div class="timestamp">${getTimestamp()}</div>
      </div>
    </div>
  `);
  box.scrollTop = box.scrollHeight;
}

function formatBold(text) {
  // Replace **text** with <strong>text</strong>
  return text.replace(/\*\*(.*?)\*\*/g, '<strong>$1</strong>');
}


async function sendMessage() {
  const input = document.getElementById('user-input');
  const message = input.value.trim();
  if (!message) return;
  input.value = ''; 
  appendUser(message);

  // Add typing indicator
  const typingId = 'typing-' + Date.now();
  appendTyping(typingId);

  try {
    const response = await fetch('/chat', {
      method: 'POST',
      headers: {
        'Content-Type': 'application/json',
        'Accept': 'application/json',
        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content'),
      },
      body: JSON.stringify({ message })
    });
    const data = await response.json();

    // Remove typing indicator
    const typingElement = document.getElementById(typingId);
    if (typingElement) typingElement.remove();

    appendBot(data.reply || '...');
  } catch (e) {
    const typingElement = document.getElementById(typingId);
    if (typingElement) typingElement.remove();
    appendBot('Network error.');
  }

  input.value = '';
}

// Enter to send
document.addEventListener('DOMContentLoaded', () => {
  const input = document.getElementById('user-input');
  if (input) {
    input.addEventListener('keydown', (e) => {
      if (e.key === 'Enter') {
        e.preventDefault();
        sendMessage();
      }
    });
  }
});
