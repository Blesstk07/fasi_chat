function showView(view, btn) {
  document.getElementById('view-students').classList.remove('visible');
  document.getElementById('view-mur').classList.remove('visible');
  document.getElementById('view-msgs').classList.remove('visible');
  document.getElementById('input-area').style.display = 'none';
  if (view === 'students') document.getElementById('view-students').classList.add('visible');
  else if (view === 'mur') document.getElementById('view-mur').classList.add('visible');
  else if (view === 'msgs') {
    document.getElementById('view-msgs').classList.add('visible');
    document.getElementById('input-area').style.display = 'block';
    setTimeout(() => { const m = document.getElementById('view-msgs'); m.scrollTop = m.scrollHeight; }, 50);
  }
  if (btn) { document.querySelectorAll('.nav-tab').forEach(b => b.classList.remove('active')); btn.classList.add('active'); }
}
function selectConv(item, title, icon, bg, sub, type) {
  document.querySelectorAll('.conv-item').forEach(i => i.classList.remove('active'));
  item.classList.add('active');
  document.getElementById('topbarTitle').textContent = title;
  document.getElementById('topbarSub').textContent = sub;
}
function handleKey(e) { if (e.key === 'Enter' && !e.shiftKey) { e.preventDefault(); sendMsg(); } }
function sendMsg() {
  const ta = document.getElementById('msgInput');
  const text = ta.value.trim();
  if (!text) return;
  const msgs = document.getElementById('view-msgs');
  const now = new Date();
  const time = now.getHours().toString().padStart(2,'0') + ':' + now.getMinutes().toString().padStart(2,'0');
  const row = document.createElement('div');
  row.className = 'msg-row mine';
  row.innerHTML = `<div class="msg-avatar" style="background:linear-gradient(135deg,#f59e0b,#d97706);">PM</div><div class="msg-group"><div class="bubble mine">${text.replace(/</g,'&lt;')}</div><div class="msg-meta">${time} ✓</div></div>`;
  msgs.appendChild(row);
  ta.value = '';
  ta.style.height = 'auto';
  msgs.scrollTop = msgs.scrollHeight;
}
function publishPost() {
  const ta = document.querySelector('.mur-textarea');
  const text = ta.value.trim();
  if (!text) return;
  const posts = document.getElementById('mur-posts');
  const post = document.createElement('div');
  post.className = 'mur-post';
  post.innerHTML = `<div class="post-header"><div class="post-avatar" style="background:linear-gradient(135deg,#f59e0b,#d97706);">PM</div><div><div class="post-author">Prof. Mbaye</div><div class="post-meta">À l'instant · PHP POO L3</div></div><div class="post-actions"><button class="post-action-btn">✏️</button><button class="post-action-btn">🗑</button></div></div><div class="post-content">${text.replace(/</g,'&lt;')}</div>`;
  posts.insertBefore(post, posts.firstChild);
  ta.value = '';
}
document.getElementById('msgInput').addEventListener('input', function() {
  this.style.height = 'auto';
  this.style.height = Math.min(this.scrollHeight, 120) + 'px';
});