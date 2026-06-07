function setRole(btn) {
  document.querySelectorAll('.role-btn').forEach(b => b.classList.remove('active'));
  btn.classList.add('active');
  const role = btn.textContent.trim();
  const form = btn.closest('.right-panel').querySelector('form');
  if (role === 'Enseignant' || role === 'Assistant') {
    form.action = 'dashboard_enseignant.html';
  } else {
    form.action = 'dashboard_etudiant.html';
  }
}
// Checkbox toggle
document.querySelectorAll('.checkbox-wrap').forEach(wrap => {
  wrap.addEventListener('click', () => {
    const cb = wrap.querySelector('input');
    cb.checked = !cb.checked;
  });
});