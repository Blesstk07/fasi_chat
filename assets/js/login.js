const API_BASE = '/FasiChatClassRoom/api';

function setRole(btn) {
  document.querySelectorAll('.role-btn').forEach(b => b.classList.remove('active'));
  btn.classList.add('active');
}

document.getElementById('loginForm').addEventListener('submit', async function(e) {
  e.preventDefault();
  const formData = new FormData(this);
  try {
    const res = await fetch(API_BASE + '/login.php', { method: 'POST', body: formData });
    const data = await res.json();
    if (data.success) {
      window.location.href = data.redirect;
    } else {
      alert(data.error || 'Identifiants incorrects.');
    }
  } catch (err) {
    alert('Erreur de connexion au serveur.');
  }
});