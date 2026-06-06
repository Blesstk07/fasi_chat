const API_BASE = '/FasiChatClassRoom/api';

function setNav(el) {
  document.querySelectorAll('.nav-item').forEach(n => n.classList.remove('active'));
  el.classList.add('active');
}

function openModal() { document.getElementById('convocModal').classList.add('open'); }
function closeModal() { document.getElementById('convocModal').classList.remove('open'); }
function closeOut(e) { if (e.target.id === 'convocModal') closeModal(); }

async function sendModal() {
  const obj = document.getElementById('mObj').value.trim();
  if (!obj) { alert('Veuillez saisir l\'objet.'); return; }
  const formData = new FormData();
  formData.append('objet', obj);
  formData.append('date_reunion', document.getElementById('mDate').value);
  formData.append('heure_reunion', document.getElementById('mHeure').value);
  formData.append('lieu', document.getElementById('mLieu').value);
  formData.append('message', document.getElementById('mMsg').value);
  try {
    const res = await fetch(API_BASE + '/convocations.php', { method: 'POST', body: formData });
    const data = await res.json();
    if (data.success) { closeModal(); alert(data.message); }
    else alert(data.error);
  } catch (err) { alert('Erreur réseau.'); }
}

async function sendConvoc() {
  const obj = document.querySelector('.convoc-form .form-input').value.trim();
  if (!obj) { alert('Veuillez saisir l\'objet.'); return; }
  const date = document.querySelectorAll('.form-row-2 .form-input')[0]?.value;
  const time = document.querySelectorAll('.form-row-2 .form-input')[1]?.value;
  const lieu = document.getElementById('convocLieu')?.value || '';
  const formData = new FormData();
  formData.append('objet', obj);
  formData.append('date_reunion', date);
  formData.append('heure_reunion', time);
  formData.append('lieu', lieu);
  try {
    const res = await fetch(API_BASE + '/convocations.php', { method: 'POST', body: formData });
    const data = await res.json();
    if (data.success) alert(data.message);
    else alert(data.error);
  } catch (err) { alert('Erreur réseau.'); }
}

function handlePrivKey(e) {
  if (e.key === 'Enter' && !e.shiftKey) { e.preventDefault(); sendPrivMsg(); }
}

async function sendPrivMsg() {
  const ta = document.getElementById('privInput');
  const text = ta.value.trim();
  if (!text) return;
  const formData = new FormData();
  formData.append('type', 'prive');
  formData.append('contenu', text);
  formData.append('destinataire_id', document.getElementById('doyenId')?.value || '1');
  try {
    const res = await fetch(API_BASE + '/messages.php', { method: 'POST', body: formData });
    const data = await res.json();
    if (data.success) {
      const box = document.getElementById('privMsgs');
      const row = document.createElement('div');
      row.className = 'msg-row mine';
      row.innerHTML = `<div class="msg-av" style="background:linear-gradient(135deg,#7c3aed,#5b21b6);">VD</div><div class="msg-group"><div class="bubble mine">${escapeHtml(text)}</div><div class="msg-time">Maintenant ✓</div></div>`;
      box.appendChild(row);
      ta.value = '';
      box.scrollTop = box.scrollHeight;
    }
  } catch (err) { console.error('Erreur envoi:', err); }
}

function escapeHtml(text) {
  const d = document.createElement('div');
  d.textContent = text;
  return d.innerHTML;
}

window.addEventListener('load', () => {
  const box = document.getElementById('privMsgs');
  if (box) box.scrollTop = box.scrollHeight;
});
