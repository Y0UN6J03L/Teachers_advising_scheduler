function toggleSidebar() {
    document.getElementById('sidebar').classList.toggle('open');
    document.getElementById('profilePanel').classList.remove('open');
  }
  function toggleProfile() {
    document.getElementById('profilePanel').classList.toggle('open');
    document.getElementById('sidebar').classList.remove('open');
  }
  function togglePopup() {
    document.getElementById('fabPopup').classList.toggle('show');
  }
  document.addEventListener('click', function(e) {
    const wrap = document.querySelector('.fab-wrap');
    if (!wrap.contains(e.target)) {
      document.getElementById('fabPopup').classList.remove('show');
    }
  });