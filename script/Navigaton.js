function toggleSidebar() {
    var sidebar = document.getElementById('sidebar');
    if (sidebar.classList.contains('open')) {
      sidebar.classList.remove('open');
    } else {
      sidebar.classList.add('open');
    }
  }

  function toggleProfile() {
    var panel = document.getElementById('profilePanel');
    if (panel.classList.contains('open')) {
      panel.classList.remove('open');
    } else {
      panel.classList.add('open');
    }
  }