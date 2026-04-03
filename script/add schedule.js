 function toggleSidebar() {
    document.getElementById('sidebar').classList.toggle('open');
    document.getElementById('profilePanel').classList.remove('open');
  }
  function toggleProfile() {
    document.getElementById('profilePanel').classList.toggle('open');
    document.getElementById('sidebar').classList.remove('open');
  }
  function openModal() {
    document.getElementById('modalOverlay').classList.add('open');
  }
  function closeModal() {
    document.getElementById('modalOverlay').classList.remove('open');
    document.getElementById('schedDay').value = '';
    document.getElementById('schedTimeStart').value = '';
    document.getElementById('schedTimeEnd').value = '';
    document.getElementById('schedDesc').value = '';
  }
  function handleOverlayClick(e) {
    if (e.target === document.getElementById('modalOverlay')) closeModal();
  }