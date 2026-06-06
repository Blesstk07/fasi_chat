const API_BASE = '/FasiChatClassRoom/api';

let currentUser = {};
let currentCoursId = null;
let currentConvType = 'cours';
let currentPriveAvec = null;
let mesCours = [];
let allCours = [];
let camarades = [];
let enseignants = [];
let reactions = {};

function switchTab(btn, tab) {
  document.querySelectorAll('.nav-tab').forEach(b => b.classList.remove('active'));
  btn.classList.add('active');
  document.getElementById('msgs-panel').style.display = tab === 'msgs' ? 'block' : 'none';
  document.getElementById('cours-panel').style.display = tab === 'cours' ? 'block' : 'none';
  document.getElementById('input-area').style.display = 'none';
  document.getElementById('messages').innerHTML = '<div class="empty-state-msg"><div class="icon">📚</div><h3>' + (tab === 'cours' ? 'Mes cours' : 'Messages') + '</h3><p>' + (tab === 'cours' ? 'Rejoignez des cours ci-dessous' : 'Sélectionnez une conversation') + '</p></div>';
  document.getElementById('right-panel').style.display = 'none';
}

async function loadMesCours() {
  try {
    const res = await fetch(API_BASE + '/dashboard.php?type=etudiant');
    const data = await res.json();
    if (data.success) {
      mesCours = data.cours || [];
      camarades = data.camarades || [];
      enseignants = data.enseignants || [];
      renderMesCours();
      renderPrivateConversations();
    }
  } catch (err) {
    console.error('Erreur chargement cours:', err);
  }
}

async function loadAllCours() {
  try {
    const res = await fetch(API_BASE + '/cours.php');
    const data = await res.json();
    if (data.success) {
      allCours = data.cours || [];
      renderAllCours();
    }
  } catch (err) {
    console.error('Erreur chargement tous les cours:', err);
  }
}

function renderMesCours() {
  const panel = document.getElementById('msgs-panel');
  const section = document.getElementById('section-cours-publics');
  let next = section.nextElementSibling;
  const toRemove = [];
  while (next && next.id !== 'section-messages-prives') {
    toRemove.push(next);
    next = next.nextElementSibling;
  }
  toRemove.forEach(el => el.remove());

  for (const c of mesCours) {
    const div = document.createElement('div');
    div.className = 'conv-item' + (currentCoursId === c.id && currentConvType === 'cours' ? ' active' : '');
    div.dataset.coursId = c.id;
    div.onclick = function() { selectCours(c.id); };
    div.innerHTML =
      '<div class="avatar avatar-group">👥</div>' +
      '<div class="conv-info">' +
        '<div class="conv-name">' + escapeHtml(c.nom) + '</div>' +
        '<div class="conv-preview">' + escapeHtml(c.code || '') + ' · ' + escapeHtml(c.promotion_nom || '') + '</div>' +
      '</div>' +
      '<div class="conv-meta"><div class="conv-time">Actif</div></div>';
    section.after(div);
  }
}

function renderPrivateConversations() {
  const section = document.getElementById('section-messages-prives');
  let next = section.nextElementSibling;
  const toRemove = [];
  while (next) {
    toRemove.push(next);
    next = next.nextElementSibling;
  }
  toRemove.forEach(el => el.remove());

  const allPrive = {};
  for (const c of camarades) {
    allPrive[c.id] = c;
  }
  for (const e of enseignants) {
    allPrive[e.id] = e;
  }

  for (const id in allPrive) {
    const p = allPrive[id];
    const div = document.createElement('div');
    div.className = 'conv-item' + (currentPriveAvec === p.id && currentConvType === 'prive' ? ' active' : '');
    div.dataset.privUserId = p.id;
    const initials = (p.prenom?.charAt(0) || '') + (p.nom?.charAt(0) || '');
    const colors = ['#3b82f6','#6366f1','#0ea5e9','#14b8a6','#f59e0b','#ef4444','#8b5cf6'];
    const color = colors[p.id % colors.length];
    const roleLabel = p.role === 'enseignant' ? 'Prof' : (p.role === 'assistant' ? 'Ass' : '');
    div.onclick = function() { selectPrive(p.id, p.prenom + ' ' + p.nom, initials, color, roleLabel); };
    div.innerHTML =
      '<div class="avatar" style="background:linear-gradient(135deg,' + color + ',' + color + 'dd);font-size:13px;font-weight:700;color:#fff;">' + initials + '</div>' +
      '<div class="conv-info">' +
        '<div class="conv-name">' + escapeHtml(p.prenom + ' ' + p.nom) + (roleLabel ? ' <span class="tag-public" style="margin-left:4px;font-size:9px;">' + roleLabel + '</span>' : '') + '</div>' +
        '<div class="conv-preview">' + (p.role === 'enseignant' ? 'Enseignant' : 'Camarade') + '</div>' +
      '</div>' +
      '<div class="conv-meta"></div>';
    section.after(div);
  }
}

function renderAllCours() {
  const container = document.getElementById('all-cours-list');
  if (!container) return;
  container.innerHTML = '';
  const inscritsIds = new Set(mesCours.map(c => c.id));
  for (const c of allCours) {
    const isInscrit = inscritsIds.has(c.id);
    const div = document.createElement('div');
    div.className = 'cours-card';
    div.innerHTML =
      '<div class="cours-card-header">' +
        '<h4>' + escapeHtml(c.nom) + '</h4>' +
        '<span class="cours-code">' + escapeHtml(c.code || '') + '</span>' +
      '</div>' +
      '<p class="cours-card-desc">' + escapeHtml(c.description || 'Aucune description') + '</p>' +
      '<div class="cours-card-footer">' +
        '<span class="cours-promo">' + escapeHtml(c.promotion_nom || '') + '</span>' +
        (isInscrit
          ? '<button class="btn-inscrit" disabled>✓ Inscrit</button>'
          : '<button class="btn-rejoindre" onclick="rejoindreCours(' + c.id + ')">+ Rejoindre</button>') +
      '</div>';
    container.appendChild(div);
  }
}

async function rejoindreCours(coursId) {
  const formData = new FormData();
  formData.append('cours_id', coursId);
  try {
    const res = await fetch(API_BASE + '/inscriptions.php', { method: 'POST', body: formData });
    const data = await res.json();
    if (data.success) {
      await loadMesCours();
      renderAllCours();
      const msgTab = document.querySelectorAll('.nav-tab')[0];
      switchTab(msgTab, 'msgs');
      selectCours(coursId);
    } else {
      alert(data.error || 'Erreur lors de l\'inscription.');
    }
  } catch (err) {
    alert('Erreur réseau.');
  }
}

function selectCours(coursId) {
  currentConvType = 'cours';
  currentCoursId = coursId;
  currentPriveAvec = null;
  document.querySelectorAll('.conv-item').forEach(i => i.classList.remove('active'));
  const items = document.querySelectorAll('.conv-item[data-cours-id]');
  for (const item of items) {
    if (parseInt(item.dataset.coursId) === coursId) {
      item.classList.add('active');
      break;
    }
  }
  document.getElementById('input-area').style.display = 'flex';
  loadMessages(coursId);
  loadCoursDetails(coursId);
  loadReactions(coursId);
}

function selectPrive(userId, name, initials, color, roleLabel) {
  currentConvType = 'prive';
  currentPriveAvec = userId;
  currentCoursId = null;
  document.querySelectorAll('.conv-item').forEach(i => i.classList.remove('active'));
  const items = document.querySelectorAll('.conv-item[data-priv-user-id]');
  for (const item of items) {
    if (parseInt(item.dataset.privUserId) === userId) {
      item.classList.add('active');
      break;
    }
  }
  document.getElementById('topbar-avatar').textContent = initials;
  document.getElementById('topbar-avatar').style.background = 'linear-gradient(135deg,' + color + ',' + color + 'dd)';
  document.getElementById('topbar-title').textContent = name;
  document.getElementById('topbar-sub').textContent = roleLabel || 'Message privé';
  const badge = document.getElementById('topbar-badge');
  badge.style.display = 'none';
  document.getElementById('input-area').style.display = 'flex';
  document.getElementById('right-panel').style.display = 'none';
  loadPrivateMessages(userId);
}

async function loadMessages(coursId) {
  try {
    const res = await fetch(API_BASE + '/messages.php?action=cours&cours_id=' + coursId + '&type=public');
    const data = await res.json();
    if (data.success) {
      renderMessages(data.messages);
    }
  } catch (err) {
    console.error('Erreur chargement messages:', err);
  }
}

async function loadPrivateMessages(userId) {
  try {
    const res = await fetch(API_BASE + '/messages.php?action=prive&avec=' + userId);
    const data = await res.json();
    if (data.success) {
      renderPrivateMessages(data.messages);
    }
  } catch (err) {
    console.error('Erreur chargement messages privés:', err);
  }
}

function renderMessages(messages) {
  const container = document.getElementById('messages');
  container.innerHTML = '<div class="date-sep">Aujourd\'hui</div>';
  for (const msg of messages) {
    const isMine = msg.expediteur_id === currentUser.id;
    const row = document.createElement('div');
    row.className = 'msg-row' + (isMine ? ' mine' : '');
    const initials = (msg.prenom?.charAt(0) || '') + (msg.nom?.charAt(0) || '');
    row.innerHTML =
      '<div class="msg-avatar" style="background:linear-gradient(135deg,#3b82f6,#1d4ed8);">' + initials + '</div>' +
      '<div class="msg-group">' +
        '<div class="msg-sender">' + escapeHtml(msg.prenom + ' ' + msg.nom) + ' · ' + (msg.created_at?.slice(11,16) || '') + '</div>' +
        '<div class="bubble ' + (isMine ? 'mine' : 'theirs') + '">' + escapeHtml(msg.contenu || '') + '</div>' +
        '<div class="msg-reactions" id="reactions-' + msg.id + '">' + renderReactions(msg.id) + '</div>' +
        '<div class="msg-meta">' +
          '<button class="reaction-btn" onclick="toggleReaction(' + msg.id + ', \'👍\')">👍</button>' +
          '<button class="reaction-btn" onclick="toggleReaction(' + msg.id + ', \'❤️\')">❤️</button>' +
          '<button class="reaction-btn" onclick="toggleReaction(' + msg.id + ', \'😂\')">😂</button>' +
        '</div>' +
      '</div>';
    container.appendChild(row);
  }
  container.scrollTop = container.scrollHeight;
}

function renderPrivateMessages(messages) {
  const container = document.getElementById('messages');
  container.innerHTML = '<div class="date-sep">Aujourd\'hui</div>';
  for (const msg of messages) {
    const isMine = msg.expediteur_id === currentUser.id;
    const row = document.createElement('div');
    row.className = 'msg-row' + (isMine ? ' mine' : '');
    const initials = (msg.prenom?.charAt(0) || '') + (msg.nom?.charAt(0) || '');
    row.innerHTML =
      '<div class="msg-avatar" style="background:linear-gradient(135deg,#6366f1,#4f46e5);">' + initials + '</div>' +
      '<div class="msg-group">' +
        '<div class="bubble ' + (isMine ? 'mine' : 'theirs') + '">' + escapeHtml(msg.contenu || '') + '</div>' +
        '<div class="msg-meta">' + (msg.created_at?.slice(11,16) || '') + '</div>' +
      '</div>';
    container.appendChild(row);
  }
  container.scrollTop = container.scrollHeight;
}

function renderReactions(messageId) {
  const msgReactions = reactions[messageId];
  if (!msgReactions || msgReactions.length === 0) return '';
  const counts = {};
  for (const r of msgReactions) {
    counts[r.emoji] = (counts[r.emoji] || 0) + 1;
  }
  return Object.entries(counts).map(([emoji, count]) =>
    '<span class="reaction-display">' + emoji + ' ' + count + '</span>'
  ).join('');
}

async function loadReactions(coursId) {
  try {
    const res = await fetch(API_BASE + '/reactions.php?cours_id=' + coursId);
    const data = await res.json();
    if (data.success) {
      reactions = {};
      for (const r of data.reactions) {
        if (!reactions[r.message_id]) reactions[r.message_id] = [];
        reactions[r.message_id].push(r);
      }
    }
  } catch (err) {
    console.error('Erreur chargement réactions:', err);
  }
}

async function toggleReaction(messageId, emoji) {
  const formData = new FormData();
  formData.append('message_id', messageId);
  formData.append('emoji', emoji);
  try {
    const res = await fetch(API_BASE + '/reactions.php', { method: 'POST', body: formData });
    const data = await res.json();
    if (data.success && currentCoursId) {
      await loadReactions(currentCoursId);
      if (currentCoursId) loadMessages(currentCoursId);
    }
  } catch (err) {
    console.error('Erreur réaction:', err);
  }
}

async function loadCoursDetails(coursId) {
  try {
    const res = await fetch(API_BASE + '/cours.php?action=details&cours_id=' + coursId);
    const data = await res.json();
    if (data.success) {
      renderCoursDetails(data.cours, data.enseignants, data.etudiants);
    }
  } catch (err) {
    console.error('Erreur chargement détails cours:', err);
  }
}

function renderCoursDetails(cours, enseignantsList, etudiantsList) {
  const panel = document.getElementById('right-panel');
  panel.style.display = 'block';

  document.getElementById('cours-nom').textContent = cours?.nom || 'Cours';
  document.getElementById('cours-description').textContent = cours?.description || '';
  const tags = document.getElementById('cours-tags');
  tags.innerHTML = '';
  if (cours?.code) {
    const t = document.createElement('span');
    t.className = 'tag tag-blue';
    t.textContent = cours.code;
    tags.appendChild(t);
  }
  if (cours?.promotion_nom) {
    const t = document.createElement('span');
    t.className = 'tag tag-navy';
    t.textContent = cours.promotion_nom;
    tags.appendChild(t);
  }

  document.getElementById('topbar-avatar').textContent = '👥';
  document.getElementById('topbar-title').textContent = cours?.nom || 'Cours';
  document.getElementById('topbar-sub').textContent = (etudiantsList?.length || 0) + ' étudiants · ' + (enseignantsList?.length || 0) + ' enseignant(s)';
  const badge = document.getElementById('topbar-badge');
  badge.style.display = 'flex';
  document.getElementById('topbar-badge-text').textContent = 'Salle de cours';

  const ensList = document.getElementById('enseignants-list');
  ensList.innerHTML = '';
  const assList = document.getElementById('assistants-list');
  assList.innerHTML = '';

  let hasAssistants = false;
  for (const e of enseignantsList || []) {
    const item = document.createElement('div');
    item.className = 'member-item';
    const initials = (e.prenom?.charAt(0) || '') + (e.nom?.charAt(0) || '');
    const isAssistant = e.role === 'assistant';
    if (isAssistant) hasAssistants = true;
    const color = isAssistant ? '#6366f1' : '#3b82f6';
    const label = isAssistant ? 'Assistant' : 'Professeur';
    item.innerHTML =
      '<div class="member-ava" style="background:linear-gradient(135deg,' + color + ',' + color + 'dd);">' + initials + '</div>' +
      '<div class="member-info">' +
        '<h5>' + escapeHtml(e.prenom + ' ' + e.nom) + '</h5>' +
        '<p>' + label + '</p>' +
      '</div>' +
      '<div class="online-dot" style="width:8px;height:8px;background:' + (e.statut === 'en_ligne' ? 'var(--online)' : '#94a3b8') + ';border-radius:50%;"></div>';
    if (isAssistant) {
      assList.appendChild(item);
    } else {
      ensList.appendChild(item);
    }
  }
  document.getElementById('panel-assistants').style.display = hasAssistants ? 'block' : 'none';

  const etuList = document.getElementById('etudiants-list');
  etuList.innerHTML = '';
  const maxVisible = 5;
  const allEtudiants = etudiantsList || [];
  const showToggle = allEtudiants.length > maxVisible;
  const displayed = showToggle ? allEtudiants.slice(0, maxVisible) : allEtudiants;
  for (const e of displayed) {
    const item = document.createElement('div');
    item.className = 'member-item';
    const initials = (e.prenom?.charAt(0) || '') + (e.nom?.charAt(0) || '');
    const isMe = e.id === currentUser.id;
    item.innerHTML =
      '<div class="member-ava" style="background:linear-gradient(135deg,#0ea5e9,#0284c7);font-size:11px;">' + initials + '</div>' +
      '<div class="member-info">' +
        '<h5>' + escapeHtml(e.prenom + ' ' + e.nom) + (isMe ? ' <span style="font-size:9px;color:var(--sky);">(vous)</span>' : '') + '</h5>' +
        '<p>Étudiant</p>' +
      '</div>' +
      '<div class="online-dot" style="width:8px;height:8px;background:' + (e.statut === 'en_ligne' ? 'var(--online)' : '#94a3b8') + ';border-radius:50%;"></div>';
    etuList.appendChild(item);
  }

  const toggleBtn = document.getElementById('toggle-members-btn');
  if (showToggle) {
    toggleBtn.style.display = 'block';
    toggleBtn.textContent = 'Voir tous les ' + allEtudiants.length + ' →';
    toggleBtn.dataset.showing = 'partial';
    toggleBtn.onclick = function() { toggleMembers(allEtudiants, etuList, maxVisible); };
  } else {
    toggleBtn.style.display = 'none';
  }
}

function toggleMembers(allEtudiants, etuList, maxVisible) {
  const btn = document.getElementById('toggle-members-btn');
  if (btn.dataset.showing === 'partial') {
    etuList.innerHTML = '';
    for (const e of allEtudiants) {
      const item = document.createElement('div');
      item.className = 'member-item';
      const initials = (e.prenom?.charAt(0) || '') + (e.nom?.charAt(0) || '');
      const isMe = e.id === currentUser.id;
      item.innerHTML =
        '<div class="member-ava" style="background:linear-gradient(135deg,#0ea5e9,#0284c7);font-size:11px;">' + initials + '</div>' +
        '<div class="member-info">' +
          '<h5>' + escapeHtml(e.prenom + ' ' + e.nom) + (isMe ? ' <span style="font-size:9px;color:var(--sky);">(vous)</span>' : '') + '</h5>' +
          '<p>Étudiant</p>' +
        '</div>' +
        '<div class="online-dot" style="width:8px;height:8px;background:' + (e.statut === 'en_ligne' ? 'var(--online)' : '#94a3b8') + ';border-radius:50%;"></div>';
      etuList.appendChild(item);
    }
    btn.textContent = 'Réduire ↑';
    btn.dataset.showing = 'all';
  } else {
    etuList.innerHTML = '';
    for (const e of allEtudiants.slice(0, maxVisible)) {
      const item = document.createElement('div');
      item.className = 'member-item';
      const initials = (e.prenom?.charAt(0) || '') + (e.nom?.charAt(0) || '');
      item.innerHTML =
        '<div class="member-ava" style="background:linear-gradient(135deg,#0ea5e9,#0284c7);font-size:11px;">' + initials + '</div>' +
        '<div class="member-info">' +
          '<h5>' + escapeHtml(e.prenom + ' ' + e.nom) + '</h5>' +
          '<p>Étudiant</p>' +
        '</div>' +
        '<div class="online-dot" style="width:8px;height:8px;background:' + (e.statut === 'en_ligne' ? 'var(--online)' : '#94a3b8') + ';border-radius:50%;"></div>';
      etuList.appendChild(item);
    }
    btn.textContent = 'Voir tous les ' + allEtudiants.length + ' →';
    btn.dataset.showing = 'partial';
  }
}

function handleKey(e) {
  if (e.key === 'Enter' && !e.shiftKey) { e.preventDefault(); sendMsg(); }
}

async function sendMsg() {
  const ta = document.getElementById('msgInput');
  const text = ta.value.trim();
  if (!text) return;

  const formData = new FormData();
  if (currentConvType === 'prive' && currentPriveAvec) {
    formData.append('type', 'prive');
    formData.append('contenu', text);
    formData.append('destinataire_id', currentPriveAvec);
  } else if (currentConvType === 'cours' && currentCoursId) {
    formData.append('type', 'public');
    formData.append('contenu', text);
    formData.append('cours_id', currentCoursId);
  } else {
    return;
  }

  try {
    const res = await fetch(API_BASE + '/messages.php', { method: 'POST', body: formData });
    const data = await res.json();
    if (data.success) {
      if (currentConvType === 'cours' && currentCoursId) loadMessages(currentCoursId);
      else if (currentConvType === 'prive' && currentPriveAvec) loadPrivateMessages(currentPriveAvec);
      ta.value = '';
      ta.style.height = 'auto';
    }
  } catch (err) {
    console.error('Erreur envoi:', err);
  }
}

function toggleEmojiPicker() {
  const picker = document.getElementById('emoji-picker');
  picker.style.display = picker.style.display === 'none' ? 'flex' : 'none';
}

function insertEmoji(emoji) {
  const ta = document.getElementById('msgInput');
  ta.value += emoji;
  ta.focus();
  document.getElementById('emoji-picker').style.display = 'none';
}

function escapeHtml(text) {
  if (!text) return '';
  const d = document.createElement('div');
  d.textContent = text;
  return d.innerHTML;
}

function showMembers() {
  const panel = document.getElementById('right-panel');
  panel.style.display = panel.style.display === 'none' ? 'block' : 'none';
}

window.addEventListener('load', async () => {
  try {
    const res = await fetch(API_BASE + '/me.php');
    currentUser = await res.json();
    if (currentUser.error || currentUser.role !== 'etudiant') {
      window.location.href = '/FasiChatClassRoom/login.html';
      return;
    }
    document.getElementById('profile-name').textContent = currentUser.prenom + ' ' + currentUser.nom;
  } catch (err) {
    console.error('Erreur init:', err);
  }
  await loadMesCours();
  await loadAllCours();
});

document.getElementById('msgInput')?.addEventListener('input', function() {
  this.style.height = 'auto';
  this.style.height = Math.min(this.scrollHeight, 120) + 'px';
});
