function setNav(el) {
  document.querySelectorAll('.nav-item').forEach(n => n.classList.remove('active'));
  el.classList.add('active');
}
function openConvocModal() { document.getElementById('convocModal').classList.add('open'); }
function closeConvocModal() { document.getElementById('convocModal').classList.remove('open'); }
function closeModalOutside(e) { if (e.target.id === 'convocModal') closeConvocModal(); }
function sendConvocModal() {
  const obj = document.getElementById('convocObj').value.trim();
  if (!obj) { alert('Veuillez saisir l\'objet de la réunion.'); return; }
  closeConvocModal();
  alert('Convocation envoyée avec succès à 30 destinataires !');
}
function sendConvoc() {
  const obj = document.querySelector('.convoc-form .form-input').value.trim();
  if (!obj) { alert('Veuillez saisir l\'objet de la réunion.'); return; }
  alert('Convocation envoyée avec succès à tous les enseignants et assistants !');
}