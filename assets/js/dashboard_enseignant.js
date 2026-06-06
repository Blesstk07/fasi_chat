const API_BASE = '/FasiChatClassRoom/api';
let currentCoursId = null;
let currentUser = {};
let mesCours = [];
let collegues = [];
let allStudents = [];

function showView(view, btn) {
  document.getElementById('view-students').classList.remove('visible');
  document.getElementById('view-mur').classList.remove('visible');
  document.getElementById('view-msgs').classList.remove('visible');
  document.getElementById('input-area').style.display = 'none';
  if (view === 'students') document.getElementById('view-students').classList.add('visible');
  else if (view === 'mur') { document.getElementById('view-mur').classList.add('visible'); loadMurPosts(); }
  else if (view === 'msgs') {
    document.getElementById('view-msgs').classList.add('visible');
    document.getElementById('input-area').style.display = 'block';
    if (currentCoursId) loadMessages(currentCoursId);
  }
  if (btn) { document.querySelectorAll('.nav-tab').forEach(b => b.classList.remove('active')); btn.classList.add('active'); }
}

function selectConv(item, coursId, title, sub) {
  document.querySelectorAll('.conv-item').forEach(i => i.classList.remove('active'));
  item.classList.add('active');
  currentCoursId = coursId;
  document.getElementById('topbarTitle').textContent = title;
  document.getElementById('topbarSub').textContent = sub;
  loadStudentsForCours(coursId);
  if (document.getElementById('view-msgs').classList.contains('visible')) loadMessages(coursId);
}

function filterStudentList() {
  const q = document.getElementById('studentSearch')?.value.toLowerCase().trim() || '';
  const statusFilter = document.querySelector('.filter-btn.active')?.textContent?.trim() || 'Tous';
  document.querySelectorAll('.student-card').forEach(card => {
    const name = card.querySelector('.student-name')?.textContent?.toLowerCase() || '';
    const online = card.dataset.online === 'true';
    const matchName = !q || name.includes(q);
    const matchStatus = statusFilter === 'Tous' || (statusFilter === 'En ligne' && online) || (statusFilter === 'Hors ligne' && !online);
    card.style.display = matchName && matchStatus ? '' : 'none';
  });
  updateStudentCounts();
}

function updateStudentCounts() {
  const total = document.querySelectorAll('.student-card').length;
  const online = document.querySelectorAll('.student-card[data-online="true"]').length;
  const visible = document.querySelectorAll('.student-card:not([style*="display: none"])').length;
  const header = document.querySelector('.panel-header p');
  if (header) header.textContent = visible + ' étudiants affichés · ' + online + ' en ligne';
}

function handleKey(e) { if (e.key === 'Enter' && !e.shiftKey) { e.preventDefault(); sendMsg(); } }

async function sendMsg() {
  const ta = document.getElementById('msgInput');
  const text = ta.value.trim();
  if (!text) return;
  const formData = new FormData();
  formData.append('type', 'public');
  formData.append('contenu', text);
  if (currentCoursId) formData.append('cours_id', currentCoursId);
  try {
    const res = await fetch(API_BASE + '/messages.php', { method: 'POST', body: formData });
    const data = await res.json();
    if (data.success && currentCoursId) loadMessages(currentCoursId);
    ta.value = '';
    ta.style.height = 'auto';
  } catch (err) { console.error('Erreur envoi:', err); }
}

async function loadMessages(coursId) {
  try {
    const res = await fetch(API_BASE + '/messages.php?action=cours&cours_id=' + coursId + '&type=public');
    const data = await res.json();
    if (data.success) renderMessages(data.messages);
  } catch (err) { console.error('Erreur chargement:', err); }
}

function renderMessages(messages) {
  const container = document.getElementById('view-msgs');
  container.innerHTML = '<div class="date-sep">Aujourd\'hui</div>';
  for (const msg of messages) {
    const isMine = msg.expediteur_id === currentUser.id;
    const row = document.createElement('div');
    row.className = 'msg-row' + (isMine ? ' mine' : '');
    const initials = (msg.prenom?.charAt(0)||'') + (msg.nom?.charAt(0)||'');
    row.innerHTML = '<div class="msg-avatar" style="background:linear-gradient(135deg,#f59e0b,#d97706);">' + initials + '</div>' +
      '<div class="msg-group"><div class="msg-sender">' + escapeHtml(msg.prenom + ' ' + msg.nom) + '</div>' +
      '<div class="bubble ' + (isMine ? 'mine' : 'theirs') + '">' + escapeHtml(msg.contenu||'') + '</div>' +
      '<div class="msg-meta">' + (msg.created_at||'').slice(11,16) + '</div></div>';
    container.appendChild(row);
  }
  container.scrollTop = container.scrollHeight;
}

async function publishPost() {
  const ta = document.querySelector('.mur-textarea');
  const text = ta.value.trim();
  if (!text || !currentCoursId) return;
  const formData = new FormData();
  formData.append('type', 'mur');
  formData.append('contenu', text);
  formData.append('cours_id', currentCoursId);
  try {
    const res = await fetch(API_BASE + '/messages.php', { method: 'POST', body: formData });
    const data = await res.json();
    if (data.success) { ta.value = ''; loadMurPosts(); }
  } catch (err) { console.error('Erreur publication:', err); }
}

async function loadMurPosts() {
  if (!currentCoursId) return;
  try {
    const res = await fetch(API_BASE + '/messages.php?action=cours&cours_id=' + currentCoursId + '&type=mur');
    const data = await res.json();
    if (data.success) renderMurPosts(data.messages);
  } catch (err) { console.error('Erreur chargement mur:', err); }
}

function renderMurPosts(posts) {
  const container = document.getElementById('mur-posts');
  container.innerHTML = '';
  for (const p of posts) {
    const div = document.createElement('div');
    div.className = 'mur-post';
    div.innerHTML = '<div class="post-header"><div class="post-avatar" style="background:linear-gradient(135deg,#f59e0b,#d97706);">PM</div>' +
      '<div><div class="post-author">' + escapeHtml(p.prenom + ' ' + p.nom) + '</div>' +
      '<div class="post-meta">' + (p.created_at||'').slice(0,16) + '</div></div></div>' +
      '<div class="post-content">' + escapeHtml(p.contenu||'') + '</div>';
    container.appendChild(div);
  }
}

async function loadStudentsForCours(coursId) {
  try {
    const res = await fetch(API_BASE + '/cours.php?action=details&cours_id=' + coursId);
    const data = await res.json();
    if (data.success) {
      renderStudents(data.etudiants || []);
    }
  } catch (err) { console.error('Erreur chargement étudiants:', err); }
}

function renderStudents(students) {
  const grid = document.querySelector('.students-grid');
  if (!grid) return;
  grid.innerHTML = '';
  const header = document.querySelector('.panel-header p');
  if (header) header.textContent = students.length + ' étudiants inscrits';
  for (const s of students) {
    const initials = (s.prenom?.charAt(0)||'') + (s.nom?.charAt(0)||'');
    const isOnline = s.statut === 'en_ligne';
    const card = document.createElement('div');
    card.className = 'student-card';
    card.dataset.online = isOnline ? 'true' : 'false';
    card.innerHTML =
      '<div class="st-avatar" style="background:linear-gradient(135deg,#3b82f6,#1d4ed8);">' + initials + '</div>' +
      '<div class="st-info">' +
        '<div class="student-name">' + escapeHtml(s.prenom + ' ' + s.nom) + '</div>' +
        '<div class="st-matricule">' + escapeHtml(s.matricule || s.email) + '</div>' +
      '</div>' +
      '<div class="st-status"><div class="status-dot-sm ' + (isOnline ? 'dot-online' : 'dot-offline') + '"></div><span style="font-size:11px;color:' + (isOnline ? '#16a34a' : '#94a3b8') + ';font-weight:600;">' + (isOnline ? 'En ligne' : 'Hors ligne') + '</span></div>';
    grid.appendChild(card);
  }
  updateStudentCounts();
}

function escapeHtml(text) {
  const d = document.createElement('div');
  d.textContent = text;
  return d.innerHTML;
}

window.addEventListener('load', async () => {
  try {
    const res = await fetch(API_BASE + '/me.php');
    currentUser = await res.json();
    if (currentUser.error || currentUser.role !== 'enseignant') { window.location.href = '/FasiChatClassRoom/login.html'; return; }
    document.querySelector('.profile-info h4').textContent = currentUser.prenom + ' ' + currentUser.nom;
    const dashRes = await fetch(API_BASE + '/dashboard.php?type=enseignant');
    const dash = await dashRes.json();
    if (dash.success) {
      mesCours = dash.cours || [];
      collegues = dash.collegues || [];
      renderCours(mesCours);
      renderCollegues(collegues);
      if (mesCours.length > 0) {
        currentCoursId = mesCours[0].id;
        selectConv(document.querySelector('.conv-item'), currentCoursId, mesCours[0].nom, mesCours[0].promotion_nom || '');
        loadMessages(currentCoursId);
        loadMurPosts();
      }
    }
  } catch (err) { console.error('Erreur init:', err); }
});

function renderCours(cours) {
  const list = document.querySelector('.conv-list');
  const section = list?.querySelector('.section-label');
  if (!list || !section) return;
  let next = section.nextElementSibling;
  const toRemove = [];
  while (next && next.classList.contains('conv-item') && !next.querySelector('.section-label')) {
    toRemove.push(next);
    next = next.nextElementSibling;
  }
  toRemove.forEach(el => el.remove());
  for (const c of cours) {
    const div = document.createElement('div');
    div.className = 'conv-item' + (c.id === currentCoursId ? ' active' : '');
    div.onclick = function() { selectConv(this, c.id, c.nom, c.promotion_nom || ''); };
    div.innerHTML =
      '<div class="avatar" style="background:linear-gradient(135deg,#3b82f6,#1d4ed8);">🖥</div>' +
      '<div class="conv-info"><div class="conv-name">' + escapeHtml(c.nom) + '</div><div class="conv-preview">' + escapeHtml(c.code || '') + '</div></div>' +
      '<div class="conv-meta"><div class="conv-time">Actif</div></div>';
    section.after(div);
  }
}

function renderCollegues(collegues) {
  const list = document.querySelector('.conv-list');
  const sections = list?.querySelectorAll('.section-label');
  if (!sections || sections.length < 2) return;
  const privSection = sections[1];
  if (!privSection || privSection.textContent !== 'Collègues (Privé)') return;
  let next = privSection.nextElementSibling;
  const toRemove = [];
  while (next) {
    toRemove.push(next);
    next = next.nextElementSibling;
  }
  toRemove.forEach(el => el.remove());
  const colors = ['#6366f1','#f59e0b','#14b8a6','#0ea5e9','#8b5cf6'];
  for (const c of collegues) {
    const initials = (c.prenom?.charAt(0)||'') + (c.nom?.charAt(0)||'');
    const color = colors[c.id % colors.length];
    const div = document.createElement('div');
    div.className = 'conv-item';
    const label = c.role === 'assistant' ? 'Ass. ' : 'Prof. ';
    div.onclick = function() { location.href = 'dashboard_enseignant.html'; };
    div.innerHTML =
      '<div class="avatar" style="background:linear-gradient(135deg,' + color + ',' + color + 'dd);font-size:12px;font-weight:700;color:white;">' + initials + '</div>' +
      '<div class="conv-info"><div class="conv-name">' + label + escapeHtml(c.prenom + ' ' + c.nom) + '</div><div class="conv-preview">' + (c.role === 'assistant' ? 'Assistant' : 'Enseignant') + '</div></div>' +
      '<div class="conv-meta"></div>';
    privSection.after(div);
  }
}

document.getElementById('msgInput')?.addEventListener('input', function() {
  this.style.height = 'auto';
  this.style.height = Math.min(this.scrollHeight, 120) + 'px';
});
