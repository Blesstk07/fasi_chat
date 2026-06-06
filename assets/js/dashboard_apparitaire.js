const API_BASE = '/FasiChatClassRoom/api';

function setNav(el) {
  document.querySelectorAll('.nav-item').forEach(n => n.classList.remove('active'));
  el.classList.add('active');
}

function openModal() { document.getElementById('modal').classList.add('open'); }
function closeModal() { document.getElementById('modal').classList.remove('open'); }
function closeOut(e) { if (e.target.id === 'modal') closeModal(); }

function openEditModal(title) {
  document.getElementById('eTitle').value = title;
  document.getElementById('eContent').value = '';
  document.getElementById('editModal').classList.add('open');
}

function closeEditModal() { document.getElementById('editModal').classList.remove('open'); }
function closeEditOut(e) { if (e.target.id === 'editModal') closeEditModal(); }

async function saveEdit() {
  const id = document.getElementById('editId')?.value || 0;
  const formData = new FormData();
  formData.append('action', 'update');
  formData.append('id', id);
  formData.append('titre', document.getElementById('eTitle').value.trim());
  formData.append('contenu', document.getElementById('eContent').value.trim());
  try {
    const res = await fetch(API_BASE + '/valve.php', { method: 'POST', body: formData });
    const data = await res.json();
    if (data.success) { closeEditModal(); loadAnnonces(); }
    else alert(data.error);
  } catch (err) { alert('Erreur réseau.'); }
}

async function publishFromModal() {
  const t = document.getElementById('mTitle').value.trim();
  const c = document.getElementById('mContent').value.trim();
  if (!t || !c) { alert('Veuillez remplir le titre et le contenu.'); return; }
  const cat = document.getElementById('mCat').value;
  const formData = new FormData();
  formData.append('action', 'create');
  formData.append('titre', t);
  formData.append('contenu', c);
  formData.append('categorie', cat);
  try {
    const res = await fetch(API_BASE + '/valve.php', { method: 'POST', body: formData });
    const data = await res.json();
    if (data.success) {
      closeModal();
      document.getElementById('mTitle').value = '';
      document.getElementById('mContent').value = '';
      if (document.getElementById('dashboardView').style.display !== 'none') loadAnnonces();
      else loadStatsData();
    } else alert(data.error);
  } catch (err) { alert('Erreur réseau.'); }
}

async function publishAnnonce() {
  const t = document.getElementById('compTitle').value.trim();
  const c = document.getElementById('compContent').value.trim();
  const cat = document.getElementById('compCat').value || 'information';
  if (!t || !c) { alert('Veuillez remplir le titre et le contenu.'); return; }
  const formData = new FormData();
  formData.append('action', 'create');
  formData.append('titre', t);
  formData.append('contenu', c);
  formData.append('categorie', cat);
  try {
    const res = await fetch(API_BASE + '/valve.php', { method: 'POST', body: formData });
    const data = await res.json();
    if (data.success) {
      document.getElementById('compTitle').value = '';
      document.getElementById('compContent').value = '';
      document.getElementById('compCat').value = '';
      loadAnnonces();
    } else alert(data.error);
  } catch (err) { alert('Erreur réseau.'); }
}

/* --- VIEW SWITCHING --- */

function showView(view) {
  document.querySelectorAll('.nav-item').forEach(n => n.classList.remove('active'));
  const target = document.querySelector(`.nav-item[data-view="${view}"]`);
  if (target) target.classList.add('active');
  const dv = document.getElementById('dashboardView');
  const sv = document.getElementById('statsView');
  if (dv) dv.style.display = view === 'dashboard' ? '' : 'none';
  if (sv) sv.style.display = view === 'stats' ? '' : 'none';
  if (view === 'stats') loadStatsData();
}

/* --- SEARCH --- */

function filterAnnonces() {
  const q = document.getElementById('searchInput').value.toLowerCase().trim();
  document.querySelectorAll('.annonce-item').forEach(item => {
    const title = item.querySelector('.ai-title')?.textContent.toLowerCase() || '';
    item.style.display = !q || title.includes(q) ? '' : 'none';
  });
}

/* --- MAIN DATA LOADING --- */

async function loadAnnonces() {
  try {
    const meRes = await fetch(API_BASE + '/me.php');
    const me = await meRes.json();
    if (me.error || me.role !== 'appariteur') { window.location.href = '/FasiChatClassRoom/login.html'; return; }
    const res = await fetch(API_BASE + '/dashboard.php?type=apparitaire');
    const data = await res.json();
    if (data.success) {
      renderAnnonces(data.annonces);
      updateStats(data.stats);
    }
  } catch (err) { console.error('Erreur chargement:', err); }
}

function renderAnnonces(annonces) {
  const list = document.getElementById('annoncesList');
  if (!list) return;
  const count = document.getElementById('annonceCount');
  const badge = document.getElementById('navBadge');
  if (count) count.textContent = annonces.length;
  if (badge) badge.textContent = annonces.length;
  list.innerHTML = '';
  const catIcons = { urgent:'🚨', convocation:'📅', information:'📢', academique:'🎓' };
  const catLabels = { urgent:'URGENT', convocation:'CONVOCATION', information:'INFORMATION', academique:'ACADÉMIQUE' };
  const catColors = { urgent:'#ef4444', convocation:'#d97706', information:'#16a34a', academique:'#6366f1' };
  for (const a of annonces) {
    const item = document.createElement('div');
    item.className = 'annonce-item';
    const icon = catIcons[a.categorie] || '📢';
    item.innerHTML = `
      <div class="ai-cat" style="background:rgba(239,68,68,0.1);">${icon}</div>
      <div class="ai-body">
        <div class="ai-cat-tag" style="color:${catColors[a.categorie] || '#16a34a'};">${catLabels[a.categorie] || 'INFORMATION'}</div>
        <div class="ai-title">${escapeHtml(a.titre)}</div>
        <div class="ai-preview">${escapeHtml((a.contenu||'').substring(0,80))}...</div>
        <div class="ai-meta">
          <span class="status-pill active-pill">● Actif</span>
          <span>${(a.created_at||'').slice(0,10)}</span>
          <span>👁 ${a.vues || 0} vues</span>
        </div>
      </div>
      <div class="ai-actions">
        <button class="ai-btn edit" onclick="openEditModal('${escapeHtml(a.titre)}')">✏</button>
        <button class="ai-btn del" onclick="deleteAnnonce(${a.id})">🗑</button>
      </div>`;
    list.appendChild(item);
  }
}

async function deleteAnnonce(id) {
  if (!confirm('Supprimer cette annonce du Valve ?')) return;
  const formData = new FormData();
  formData.append('action', 'delete');
  formData.append('id', id);
  try {
    const res = await fetch(API_BASE + '/valve.php', { method: 'POST', body: formData });
    const data = await res.json();
    if (data.success) { loadAnnonces(); }
    else alert(data.error);
  } catch (err) { alert('Erreur réseau.'); }
}

function updateStats(stats) {
  const nums = document.querySelectorAll('#dashboardView .sn');
  if (nums.length >= 4 && stats) {
    nums[0].textContent = stats.total || 0;
    nums[1].textContent = stats.urgences || 0;
    nums[2].textContent = stats.convocations || 0;
    nums[3].textContent = stats.vues || 0;
  }
}

/* --- STATS PAGE --- */

async function loadStatsData() {
  try {
    const [dashRes, statsRes] = await Promise.all([
      fetch(API_BASE + '/dashboard.php?type=apparitaire'),
      fetch(API_BASE + '/valve.php?action=stats')
    ]);
    const dashData = await dashRes.json();
    let detailedStats = null;
    try { detailedStats = await statsRes.json(); } catch (e) {}
    if (dashData.success) {
      renderStatsPage(dashData.stats, dashData.annonces, detailedStats);
    }
  } catch (err) { console.error('Erreur chargement stats:', err); }
}

function renderStatsPage(stats, annonces, detailed) {
  document.getElementById('statsTotal').textContent = stats.total || 0;
  document.getElementById('statsUrgent').textContent = stats.urgences || 0;
  document.getElementById('statsConvocation').textContent = stats.convocations || 0;
  document.getElementById('statsVues').textContent = stats.vues || 0;

  const total = stats.total || 1;

  const catCounts = { urgent: 0, convocation: 0, information: 0, academique: 0 };
  if (detailed && detailed.success && detailed.stats && detailed.stats.parCategorie) {
    for (const item of detailed.stats.parCategorie) {
      catCounts[item.categorie] = item.total;
    }
  }

  const catMap = [
    { key:'urgent', countEl:'catUrgentCount', barEl:'catUrgentBar', color:'#ef4444' },
    { key:'convocation', countEl:'catConvocationCount', barEl:'catConvocationBar', color:'#d97706' },
    { key:'information', countEl:'catInfoCount', barEl:'catInfoBar', color:'#16a34a' },
    { key:'academique', countEl:'catAcademiqueCount', barEl:'catAcademiqueBar', color:'#6366f1' },
  ];

  for (const cat of catMap) {
    const count = catCounts[cat.key] || 0;
    const el = document.getElementById(cat.countEl);
    if (el) el.textContent = count;
    const bar = document.getElementById(cat.barEl);
    if (bar) {
      const pct = Math.round((count / total) * 100);
      bar.style.width = pct + '%';
    }
  }

  const vues = stats.vues || 0;
  const tv = document.getElementById('totalViewsBig');
  if (tv) tv.textContent = vues;
  const avg = document.getElementById('avgViews');
  if (avg) avg.textContent = stats.total > 0 ? Math.round(vues / stats.total) : 0;

  const distLabels = document.getElementById('distLabels');
  if (distLabels) {
    distLabels.innerHTML = '';
    const icons = { urgent:'🚨', convocation:'📅', information:'📢', academique:'🎓' };
    const colors = { urgent:'#ef4444', convocation:'#d97706', information:'#16a34a', academique:'#6366f1' };
    distLabels.innerHTML = '<div style="font-size:11px;color:var(--gray-400);">Chargement...</div>';
    for (const cat of catMap) {
      const count = catCounts[cat.key] || 0;
      const pct = Math.round((count / total) * 100);
      if (count === 0 && pct === 0) continue;
      const div = document.createElement('div');
      div.className = 'dist-item';
      div.innerHTML = `
        <span>${icons[cat.key] || '📢'}</span>
        <span class="dist-label">${cat.key}</span>
        <div class="dist-bar"><div class="dist-fill" style="width:${pct}%;background:${colors[cat.key]};"></div></div>
        <span class="dist-pct">${pct}%</span>`;
      distLabels.appendChild(div);
    }
  }

  const activityEl = document.getElementById('recentActivityList');
  if (activityEl) {
    activityEl.innerHTML = '';
    if (annonces && annonces.length > 0) {
      const sorted = [...annonces].sort((a, b) => new Date(b.created_at || 0) - new Date(a.created_at || 0));
      const recent = sorted.slice(0, 10);
      const icons = { urgent:'🚨', convocation:'📅', information:'📢', academique:'🎓' };
      for (const a of recent) {
        const item = document.createElement('div');
        item.className = 'activity-item';
        const icon = icons[a.categorie] || '📢';
        const date = (a.created_at || '').slice(0, 10);
        const time = (a.created_at || '').slice(11, 16);
        item.innerHTML = `
          <div class="act-ico" style="background:rgba(99,102,241,0.1);">${icon}</div>
          <div class="act-t"><strong>${escapeHtml(a.titre)}</strong><p>${escapeHtml((a.contenu||'').substring(0,60))}</p></div>
          <div class="act-time">${date} ${time}</div>`;
        activityEl.appendChild(item);
      }
    } else {
      activityEl.innerHTML = '<div style="padding:20px;text-align:center;color:var(--gray-400);font-size:12px;">Aucune activité récente</div>';
    }
  }
}

function escapeHtml(text) {
  if (!text) return '';
  const d = document.createElement('div');
  d.textContent = text;
  return d.innerHTML;
}

window.addEventListener('load', loadAnnonces);
