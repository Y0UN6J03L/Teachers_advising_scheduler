const params = new URLSearchParams(window.location.search);
  const role = params.get('role');

  const destinations = {
    teacher: 'Teacher.html',
    student: 'Student.html',
    admin: 'Admin.html'
  };

  const backBtn = document.getElementById('backBtn');
  backBtn.href = destinations[role] || 'index.html';