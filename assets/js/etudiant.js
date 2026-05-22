function switchTab(btn, tab) {
  document.querySelectorAll('.nav-tab').forEach(b => b.classList.remove('active'));
  btn.classList.add('active');
}

function selectConv(item) {
  document.querySelectorAll('.conv-item').forEach(i => i.classList.remove('active'));
  item.classList.add('active');
}

function handleKey(e) {
  if (e.key === 'Enter' && !e.shiftKey) { e.preventDefault(); sendMsg(); }
}

function sendMsg() {
  const ta = document.getElementById('msgInput');
  const text = ta.value.trim();
  if (!text) return;
  const msgs = document.getElementById('messages');
  const now = new Date();
  const time = now.getHours().toString().padStart(2,'0') + ':' + now.getMinutes().toString().padStart(2,'0');
  const row = document.createElement('div');
  row.className = 'msg-row mine';
  row.innerHTML = `
    <div class="msg-avatar" style="background:linear-gradient(135deg,var(--sky),var(--accent));">AF</div>
    <div class="msg-group">
      <div class="bubble mine">${text.replace(/</g,'&lt;').replace(/>/g,'&gt;')}</div>
      <div class="msg-meta">${time} <span class="check-read">✓</span></div>
    </div>`;
  msgs.appendChild(row);
  ta.value = '';
  ta.style.height = 'auto';
  msgs.scrollTop = msgs.scrollHeight;
}

// Autoresize textarea
document.getElementById('msgInput').addEventListener('input', function() {
  this.style.height = 'auto';
  this.style.height = Math.min(this.scrollHeight, 120) + 'px';
});

// Scroll to bottom on load
window.addEventListener('load', () => {
  const msgs = document.getElementById('messages');
  msgs.scrollTop = msgs.scrollHeight;
});