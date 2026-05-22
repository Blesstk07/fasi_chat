document.querySelectorAll('.filter-chip').forEach(c => {
  c.addEventListener('click', () => {
    document.querySelectorAll('.filter-chip').forEach(x => x.classList.remove('active'));
    c.classList.add('active');
  });
});
document.querySelectorAll('.cat-item').forEach(c => {
  c.addEventListener('click', function() {
    document.querySelectorAll('.cat-item').forEach(x => x.classList.remove('active'));
    this.classList.add('active');
  });
});
function openModal() { document.getElementById('modal').classList.add('open'); }
function closeModal() { document.getElementById('modal').classList.remove('open'); }
function closeModalOutside(e) { if (e.target === document.getElementById('modal')) closeModal(); }
function publishAnnonce() {
  const title = document.querySelector('.modal-body .form-input').value.trim();
  if (!title) { alert('Veuillez saisir un titre.'); return; }
  closeModal();
  alert('Annonce publiée avec succès sur le Valve !');
}