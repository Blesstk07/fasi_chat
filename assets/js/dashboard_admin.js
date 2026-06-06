const API_BASE = '/FasiChatClassRoom/api';

let allUsers = [];

function setNav(el) {
  document.querySelectorAll('.nav-item').forEach(n => n.classList.remove('active'));
  el.classList.add('active');
}

function openConvocModal() { document.getElementById('convocModal').classList.add('open'); }
function closeConvocModal() { document.getElementById('convocModal').classList.remove('open'); }
function closeModalOutside(e) { if (e.target.id === 'convocModal') closeConvocModal(); }

async function sendConvocModal() {
  const obj = document.getElementById('convocObj').value.trim();
  if (!obj) { alert('Veuillez saisir l\'objet de la réunion.'); return; }
  const formData = new FormData();
  formData.append('objet', obj);
  formData.append('date_reunion', document.getElementById('convocDate').value);
  formData.append('heure_reunion', document.getElementById('convocHeure').value);
  formData.append('lieu', document.getElementById('convocLieu').value);
  formData.append('message', document.getElementById('convocMsg').value);
  try {
    const res = await fetch(API_BASE + '/convocations.php', { method: 'POST', body: formData });
    const data = await res.json();
    if (data.success) {
      closeConvocModal();
      alert(data.message);
      loadDashboard();
    } else {
      alert(data.error);
    }
  } catch (err) {
    alert('Erreur réseau.');
  }
}

async function sendConvoc() {
  const inputs = document.querySelectorAll('.convoc-form .form-input');
  const obj = inputs[0]?.value.trim();
  if (!obj) { alert('Veuillez saisir l\'objet de la réunion.'); return; }
  const date = document.querySelectorAll('.form-row-2 .form-input')[0]?.value;
  const time = document.querySelectorAll('.form-row-2 .form-input')[1]?.value;
  const lieu = document.querySelector('.convoc-form input[placeholder*="Salle"]')?.value || '';
  const formData = new FormData();
  formData.append('objet', obj);
  formData.append('date_reunion', date);
  formData.append('heure_reunion', time);
  formData.append('lieu', lieu);
  try {
    const res = await fetch(API_BASE + '/convocations.php', { method: 'POST', body: formData });
    const data = await res.json();
    if (data.success) { alert(data.message); loadDashboard(); }
    else alert(data.error);
  } catch (err) {
    alert('Erreur réseau.');
  }
}

function filterUsers() {
  const q = document.getElementById('searchUsers')?.value.toLowerCase().trim() || '';
  document.querySelectorAll('#usersTableBody tr').forEach(row => {
    const text = row.textContent.toLowerCase();
    row.style.display = !q || text.includes(q) ? '' : 'none';
  });
}

async function loadDashboard() {
  try {
    const meRes = await fetch(API_BASE + '/me.php');
    const me = await meRes.json();
    if (me.error || me.role !== 'doyen') { window.location.href = '/FasiChatClassRoom/login.html'; return; }
    const res = await fetch(API_BASE + '/dashboard.php?type=admin');
    const data = await res.json();
    if (data.success) {
      allUsers = data.users || [];
      updateStats(data.stats);
      renderUsers(allUsers);
      renderActivity(data.stats);
    }
  } catch (err) {
    console.error('Erreur chargement dashboard:', err);
  }
}

function updateStats(stats) {
  const nums = document.querySelectorAll('.stat-number');
  if (nums.length >= 4) {
    nums[0].textContent = stats.etudiants || 0;
    nums[1].textContent = stats.enseignants || 0;
    nums[2].textContent = stats.cours || 0;
    nums[3].textContent = stats.convocations || 0;
  }
}

function renderUsers(users) {
  const tbody = document.getElementById('usersTableBody');
  if (!tbody) return;
  tbody.innerHTML = '';
  const roleLabels = { etudiant:'🎓 Étudiant', enseignant:'👨‍🏫 Enseignant', assistant:'📋 Assistant', doyen:'🏛 Doyen', vice_doyen:'🏅 Vice-Doyen', apparitaire:'🗂 Apparitaire' };
  const roleColors = { etudiant:'#6366f1', enseignant:'#f59e0b', assistant:'#6366f1', doyen:'#dc2626', vice_doyen:'#7c3aed', apparitaire:'#7c3aed' };
  for (const u of users) {
    const tr = document.createElement('tr');
    tr.innerHTML =
      '<td><div class="user-cell"><div class="user-row-ava" style="background:linear-gradient(135deg,' + (roleColors[u.role] || '#6366f1') + ',' + (roleColors[u.role] || '#4f46e5') + ');">' + (u.prenom?.charAt(0)||'') + (u.nom?.charAt(0)||'') + '</div><div><div class="user-name">' + escapeHtml(u.prenom + ' ' + u.nom) + '</div><div class="user-email">' + escapeHtml(u.email) + '</div></div></div></td>' +
      '<td><span class="role-pill">' + (roleLabels[u.role] || u.role) + '</span></td>' +
      '<td><span class="status-dot-sm ' + (u.statut === 'en_ligne' ? 'dot-online' : 'dot-offline') + '"></span><span style="font-size:11px;color:' + (u.statut === 'en_ligne' ? '#16a34a' : '#94a3b8') + ';font-weight:600;">' + (u.statut === 'en_ligne' ? 'En ligne' : 'Hors ligne') + '</span></td>' +
      '<td><div class="action-btns"><button class="act-btn edit">✏</button></div></td>';
    tbody.appendChild(tr);
  }
}

function renderActivity() {
  const list = document.querySelector('.activity-list');
  if (!list) return;
  list.innerHTML = '';
  const now = new Date();
  const time = now.getHours().toString().padStart(2,'0') + ':' + now.getMinutes().toString().padStart(2,'0');
  const items = [
    { icon:'📊', bg:'rgba(79,163,224,0.1)', text:'<strong>Connexion administrateur</strong><p>Tableau de bord chargé</p>', time: time },
    { icon:'📅', bg:'rgba(245,158,11,0.1)', text:'<strong>' + (document.getElementById('convocObj')?.value || 'Convocations') + '</strong><p>Gestion des réunions académiques</p>', time: 'Aujourd\'hui' },
  ];
  for (const item of items) {
    const div = document.createElement('div');
    div.className = 'activity-item';
    div.innerHTML = '<div class="act-icon-wrap" style="background:' + item.bg + ';">' + item.icon + '</div><div class="act-text">' + item.text + '</div><div class="act-time">' + item.time + '</div>';
    list.appendChild(div);
  }
}

function escapeHtml(text) {
  if (!text) return '';
  const d = document.createElement('div');
  d.textContent = text;
  return d.innerHTML;
}

window.addEventListener('load', loadDashboard);
