const API_BASE = '/FasiChatClassRoom/api';
let currentUser = null;
let allAnnonces = [];
let currentCategorie = null;

document.querySelectorAll('.filter-chip').forEach(c => {
  c.addEventListener('click', () => {
    document.querySelectorAll('.filter-chip').forEach(x => x.classList.remove('active'));
    c.classList.add('active');
    document.querySelectorAll('.cat-item').forEach(x => x.classList.remove('active'));
    const cat = c.textContent.trim();
    const catMap = { '🚨 Urgences':'urgent', '📅 Convocations':'convocation', '📢 Infos':'information', '🎓 Académique':'academique' };
    currentCategorie = catMap[cat] || null;
    applyFilters();
  });
});

document.querySelectorAll('.cat-item:not([data-nav])').forEach(c => {
  c.addEventListener('click', function() {
    document.querySelectorAll('.cat-item').forEach(x => x.classList.remove('active'));
    this.classList.add('active');
    document.querySelectorAll('.filter-chip').forEach(x => x.classList.remove('active'));
    const catName = this.querySelector('.cat-name')?.textContent.trim();
    const catMap = { 'Urgences':'urgent', 'Convocations':'convocation', 'Informations':'information', 'Académique':'academique' };
    currentCategorie = catName === 'Toutes les annonces' ? null : (catMap[catName] || null);
    applyFilters();
  });
});

document.getElementById('search-input')?.addEventListener('input', applyFilters);

function openModal() { document.getElementById('modal').classList.add('open'); }
function closeModal() { document.getElementById('modal').classList.remove('open'); }
function closeModalOutside(e) { if (e.target === document.getElementById('modal')) closeModal(); }

async function loadAnnonces() {
  try {
    const res = await fetch(API_BASE + '/valve.php');
    const data = await res.json();
    if (data.success) {
      allAnnonces = data.annonces;
      applyFilters();
      updateSidebarCounts();
    }
  } catch (err) { console.error('Erreur chargement annonces:', err); }
}

function applyFilters() {
  let filtered = allAnnonces;
  if (currentCategorie) {
    filtered = filtered.filter(a => a.categorie === currentCategorie);
  }
  const q = document.getElementById('search-input')?.value?.toLowerCase().trim() || '';
  if (q) {
    filtered = filtered.filter(a =>
      (a.titre && a.titre.toLowerCase().includes(q)) ||
      (a.contenu && a.contenu.toLowerCase().includes(q))
    );
  }
  renderAnnonces(filtered);
  updateHeroStats();
}

function updateHeroStats() {
  const total = allAnnonces.length;
  const urgents = allAnnonces.filter(a => a.categorie === 'urgent').length;
  const convocations = allAnnonces.filter(a => a.categorie === 'convocation').length;
  const heroNums = document.querySelectorAll('.hero-stat .n');
  if (heroNums.length >= 3) {
    heroNums[0].textContent = total;
    heroNums[1].textContent = urgents;
    heroNums[2].textContent = convocations;
  }
}

function updateSidebarCounts() {
  const total = allAnnonces.length;
  const counts = {
    'Urgences': allAnnonces.filter(a => a.categorie === 'urgent').length,
    'Convocations': allAnnonces.filter(a => a.categorie === 'convocation').length,
    'Informations': allAnnonces.filter(a => a.categorie === 'information').length,
    'Académique': allAnnonces.filter(a => a.categorie === 'academique').length,
  };
  document.querySelectorAll('.cat-item:not([data-nav]) .cat-count').forEach(el => {
    const name = el.closest('.cat-item')?.querySelector('.cat-name')?.textContent?.trim();
    if (name === 'Toutes les annonces') {
      el.textContent = total + ' publication' + (total > 1 ? 's' : '');
    } else if (name && counts[name] !== undefined) {
      el.textContent = counts[name] + ' publication' + (counts[name] > 1 ? 's' : '');
    }
  });
}

function renderAnnonces(annonces) {
  const grid = document.querySelector('.annonces-grid');
  if (!grid) return;
  grid.innerHTML = '';
  for (const a of annonces) {
    const urgentClass = a.categorie === 'urgent' ? ' urgent' : '';
    const convocClass = a.categorie === 'convocation' ? ' convocation' : '';
    const catIcons = { urgent:'🚨', convocation:'📅', information:'📢', academique:'🎓' };
    const catColors = { urgent:'#ef4444', convocation:'#d97706', information:'#16a34a', academique:'#6366f1' };
    const catLabels = { urgent:'URGENT', convocation:'CONVOCATION', information:'INFORMATION', academique:'ACADÉMIQUE' };
    const card = document.createElement('div');
    card.className = `annonce-card${urgentClass}${convocClass}`;
    if (a.categorie === 'urgent') card.style.gridColumn = '1/-1';
    card.innerHTML = `
      <div class="ac-header">
        <div class="ac-cat-icon" style="background:rgba(239,68,68,0.12);">${catIcons[a.categorie] || '📢'}</div>
        <div class="ac-meta">
          <div class="ac-cat-label" style="color:${catColors[a.categorie] || '#16a34a'};">${catLabels[a.categorie] || 'INFORMATION'}</div>
          <div class="ac-title">${escapeHtml(a.titre)}</div>
        </div>
      </div>
      <div class="ac-body">
        <div class="ac-text">${escapeHtml(a.contenu)}</div>
        <div class="ac-footer">
          <div class="ac-author">
            <div class="ac-author-ava" style="background:linear-gradient(135deg,#6366f1,#4f46e5);">${(a.prenom?.charAt(0)||'')+(a.nom?.charAt(0)||'')}</div>
            <div><div class="ac-author-name">${escapeHtml(a.prenom+' '+a.nom)}</div></div>
          </div>
          <div class="ac-date">${(a.created_at||'').slice(0,10)}</div>
        </div>
      </div>`;
    grid.appendChild(card);
  }
  if (annonces.length === 0) {
    grid.innerHTML = '<div class="empty-state"><div class="icon">📭</div><h3>Aucune annonce</h3><p>Aucune publication pour le moment.</p></div>';
  }
}

async function publishAnnonce() {
  const title = document.querySelector('.modal-body .form-input').value.trim();
  const content = document.querySelector('.modal-body .form-textarea').value.trim();
  if (!title || !content) { alert('Veuillez remplir le titre et le contenu.'); return; }
  const cat = document.querySelector('.modal-body .form-select')?.value || 'information';
  const formData = new FormData();
  formData.append('action', 'create');
  formData.append('titre', title);
  formData.append('contenu', content);
  formData.append('categorie', cat);
  try {
    const res = await fetch(API_BASE + '/valve.php', { method: 'POST', body: formData });
    const data = await res.json();
    if (data.success) { closeModal(); alert(data.message); loadAnnonces(); }
    else { alert(data.error); }
  } catch (err) { alert('Erreur réseau.'); }
}

function escapeHtml(text) {
  if (!text) return '';
  const d = document.createElement('div');
  d.textContent = text;
  return d.innerHTML;
}

async function init() {
  try {
    const res = await fetch(API_BASE + '/me.php');
    currentUser = await res.json();
    if (currentUser.error) {
      document.getElementById('valve-auth-wall').style.display = 'flex';
      document.querySelector('.valve-topbar').style.display = 'none';
      document.querySelector('.filter-bar').style.display = 'none';
      document.querySelector('.valve-content').style.display = 'none';
      return;
    }
    document.getElementById('profile-name').textContent = currentUser.prenom + ' ' + currentUser.nom;
    const roleLabels = { etudiant:'Étudiant', enseignant:'Enseignant', assistant:'Assistant', doyen:'Doyen', vice_doyen:'Vice-Doyen', apparitaire:'Apparitaire · Faculté' };
    document.getElementById('profile-role').textContent = roleLabels[currentUser.role] || currentUser.role;

    const isAdmin = currentUser.role === 'apparitaire';
    document.getElementById('btn-statistiques').style.display = isAdmin ? '' : 'none';
    document.getElementById('btn-new-annonce').style.display = isAdmin ? '' : 'none';

    const adminTab = document.getElementById('nav-tab-admin');
    if (adminTab && !['apparitaire','doyen','vice_doyen'].includes(currentUser.role)) {
      adminTab.style.display = 'none';
    }

    const chatTab = document.getElementById('nav-tab-chat');
    if (chatTab) {
      const chatLinks = { etudiant:'dashboard_etudiant.html', enseignant:'dashboard_enseignant.html', assistant:'dashboard_enseignant.html', doyen:'dashboard_admin.html', vice_doyen:'dashboard_vicedoyen.html', apparitaire:'dashboard_apparitaire.html' };
      chatTab.onclick = function() { location.href = chatLinks[currentUser.role] || 'chat.html'; };
    }

    const navEtudiant = document.querySelector('.cat-item[data-nav="etudiant"]');
    const navEnseignant = document.querySelector('.cat-item[data-nav="enseignant"]');
    if (navEtudiant) navEtudiant.style.display = currentUser.role === 'etudiant' ? '' : 'none';
    if (navEnseignant) navEnseignant.style.display = ['enseignant','assistant'].includes(currentUser.role) ? '' : 'none';

    loadAnnonces();
  } catch (err) {
    console.error('Erreur init:', err);
    document.getElementById('valve-auth-wall').style.display = 'flex';
    document.querySelector('.valve-topbar').style.display = 'none';
    document.querySelector('.filter-bar').style.display = 'none';
    document.querySelector('.valve-content').style.display = 'none';
  }
}

window.addEventListener('load', init);
