// Student.js - Search Teachers & Request Schedule
const swal = (opts) => Swal.fire({
  background: 'rgba(10, 28, 10, 0.97)',
  color: '#e8f5e8',
  confirmButtonColor: '#12b929',
  iconColor: '#12b929',
  ...opts
});

let studentId = <?= $student_id ?> || 1;
let allTeachers = [];

// Load all teachers
async function loadTeachers() {
  try {
    const res = await fetch('../backend/api/users.php?role=teacher', {credentials: 'same-origin'});
    const data = await res.json();
    allTeachers = data;
    renderTeachers(allTeachers);
  } catch (err) {
    console.error('Load teachers error:', err);
  }
}

// Render teachers grid
function renderTeachers(teachers) {
  const grid = document.getElementById('teacherGrid');
  const noResults = document.querySelector('.no-results');
  
  if (teachers.length === 0) {
    grid.innerHTML = '';
    noResults.style.display = 'block';
    return;
  }
  
  noResults.style.display = 'none';
  grid.innerHTML = teachers.map(teacher => `
    <div class="teacher-card" data-teacher-id="${teacher.id}">
      <div class="teacher-avatar">${teacher.full_name.charAt(0).toUpperCase()}</div>
      <div class="teacher-info">
        <div class="teacher-name">${teacher.full_name}</div>
        <div class="teacher-course">${teacher.email}</div>
        <button class="btn-view" onclick="viewTeacherSchedule(${teacher.id})">
          <i class="fa fa-calendar"></i> View Schedule
        </button>
        <button class="btn-request" onclick="requestSchedule(${teacher.id}, '${teacher.full_name}')">
          <i class="fa fa-plus"></i> Request Time
        </button>
      </div>
    </div>
  `).join('');
}

// Search teachers
function searchTeachers() {
  const query = document.getElementById('teacherSearch').value.toLowerCase();
  const filtered = allTeachers.filter(t => 
    t.full_name.toLowerCase().includes(query) || 
    t.email.toLowerCase().includes(query)
  );
  renderTeachers(filtered);
}

// View teacher schedule
async function viewTeacherSchedule(teacherId) {
  swal({
    title: 'Teacher Schedule',
    html: '<div id="scheduleLoader">Loading schedule...</div>',
    showConfirmButton: false
  });

  try {
    const res = await fetch(`../backend/api/schedules.php?user_id=${teacherId}`, {credentials: 'same-origin'});
    const data = await res.json();
    
    let html = '<div class="schedule-list">';
    if (data.length === 0) {
      html += '<p>No schedule available.</p>';
    } else {
      html += data.map(s => `
        <div class="schedule-slot">
          <strong>${s.day}</strong>: ${s.time_start} - ${s.time_end} (${s.description || 'Free'})
        </div>
      `).join('');
    }
    html += '</div>';
    
    Swal.update({ html });
  } catch (err) {
    Swal.update({ html: '<p>Error loading schedule.</p>' });
  }
}

// Request schedule slot
async function requestSchedule(teacherId, teacherName) {
  const { value: message } = await swal({
    title: `Request schedule with ${teacherName}`,
    input: 'textarea',
    inputLabel: 'Message/Preferred time (e.g. "Monday 10AM Math help")',
    inputPlaceholder: 'Enter details...',
    showCancelButton: true,
    confirmButtonText: 'Send Request'
  });

  if (!message) return;

  try {
    const res = await fetch('../backend/api/requests.php', {
      method: 'POST',
      headers: { 'Content-Type': 'application/json' },
      body: JSON.stringify({
        teacher_id: teacherId,
        message: message
      })
    });
    const data = await res.json();
    
    if (data.success) {
      swal({ icon: 'success', title: 'Request Sent!', text: 'Teacher will review your request.' });
    } else {
      swal({ icon: 'error', title: 'Error', text: data.message });
    }
  } catch (err) {
    swal({ icon: 'error', title: 'Error', text: 'Failed to send request.' });
  }
}

// Load my requests for FAB
async function loadMyRequests() {
  try {
    const res = await fetch('../backend/api/requests.php', {credentials: 'same-origin'});
    const data = await res.json();
    
    const list = document.getElementById('myRequestsList');
    if (data.length === 0) {
      list.innerHTML = '<div class="fab-popup-empty"><i class="fa fa-inbox"></i><span>No requests yet</span></div>';
    } else {
      list.innerHTML = data.map(req => `
        <div class="request-item">
          <div>${req.message}</div>
          <div class="status-badge ${req.status}">${req.status || 'Pending'}</div>
        </div>
      `).join('');
    }
  } catch (err) {
    console.error('Load requests error:', err);
  }
}

// Toggle FAB popup
function toggleFabPopup() {
  document.getElementById('fabPopup').classList.toggle('show');
}

// Logout
function logout() {
  fetch('../backend/api/auth.php', {
    method: 'POST',
    headers: { 'Content-Type': 'application/json' },
    body: JSON.stringify({ action: 'logout' })
  }).then(() => {
    sessionStorage.clear();
    window.location.href = '../login.php';
  });
}

// Init
document.addEventListener('DOMContentLoaded', () => {
  loadTeachers();
  loadMyRequests();
});

