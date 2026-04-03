// Teacher.js - Pure JS version without PHP embeds
const swal = (opts) => Swal.fire({
  background: 'rgba(10, 28, 10, 0.97)',
  color: '#e8f5e8',
  confirmButtonColor: '#12b929',
  iconColor: '#12b929',
  ...opts
});

// Global teacher ID from session
let teacherId = <?= $teacher_id ?> || 1;

async function loadDayData(day) {
  try {
    const schedRes = await fetch(`../backend/api/schedules.php?day=${day}&teacher_id=${teacherId}`, {credentials: 'same-origin'});
    const schedData = await schedRes.json();
    const slots = Array.isArray(schedData) ? schedData.length : 0;

    const reqRes = await fetch(`../backend/api/requests.php?day=${day}&teacher_id=${teacherId}`, {credentials: 'same-origin'});
    const reqData = await reqRes.json();
    const pending = Array.isArray(reqData) ? reqData.filter(r => r.status === 'pending').length : 0;

    return { slots, pending };
  } catch (err) {
    console.error('Load day error:', err);
    return { slots: 0, pending: 0 };
  }
}

async function updateAllDays() {
  const days = ['Monday', 'Tuesday', 'Wednesday', 'Thursday', 'Friday', 'Saturday'];
  for (const day of days) {
    const data = await loadDayData(day);
    const card = document.querySelector(`[onclick*="viewSchedule('${day}'"]`)?.closest('.day-card');
    if (card) {
      const link = card.querySelector('.schedule-link');
      if (link) link.innerHTML = `<i class="fa fa-calendar-alt"></i> View Slots (${data.slots})`;
      const badge = card.querySelector('.new-badge');
      if (badge) badge.textContent = `(${data.pending} Pending)`;
    }
  }
}

// View Schedule
async function viewSchedule(day) {
  const title = document.getElementById('scheduleTitle');
  if (title) title.textContent = `${day.toUpperCase()} Schedule`;
  
  const content = document.getElementById('scheduleContent');
  if (content) content.innerHTML = '<p>Loading...</p>';
  
  const modal = document.getElementById('scheduleModal');
  if (modal) modal.classList.add('active');

  try {
    const res = await fetch(`../backend/api/schedules.php?teacher_id=${teacherId}&day=${day}`, {credentials: 'same-origin'});
    const data = await res.json();
    if (Array.isArray(data) && data.length) {
      const table = `<table class="schedule-table">
        <thead><tr><th>Time</th><th>End</th><th>Description</th></tr></thead>
        <tbody>${data.map(s => `
          <tr>
            <td>${s.time_start}</td>
            <td>${s.time_end}</td>
            <td>${s.description || '-'}</td>
          </tr>`).join('')}</tbody></table>`;
      if (content) content.innerHTML = table;
    } else {
      if (content) content.innerHTML = '<p>No schedule for this day.</p>';
    }
  } catch (err) {
    if (content) content.innerHTML = '<p>Error loading schedule.</p>';
  }
}

function closeScheduleModal() {
  const modal = document.getElementById('scheduleModal');
  if (modal) modal.classList.remove('active');
}

async function viewRequests(day) {
  const title = document.getElementById('requestsTitle');
  if (title) title.textContent = `${day.toUpperCase()} Requests`;
  
  const content = document.getElementById('requestsContent');
  if (content) content.innerHTML = '<p>Loading...</p>';
  
  const modal = document.getElementById('requestsModal');
  if (modal) modal.classList.add('active');

  try {
    const res = await fetch(`../backend/api/requests.php?day=${day}&teacher_id=${teacherId}`, {credentials: 'same-origin'});
    const data = await res.json();
    if (Array.isArray(data) && data.length > 0) {
      const html = data.map(req => `
        <div class="request-item">
          <div>
            <strong>${req.student_name || 'Student'}</strong> - ${req.message || 'Request'}
            <br><small>${req.created_at || ''}</small>
          </div>
          <div class="request-actions">
            <button class="btn-approve" onclick="handleRequest(${req.id}, 'approved')">Approve</button>
            <button class="btn-reject" onclick="handleRequest(${req.id}, 'rejected')">Reject</button>
          </div>
        </div>
      `).join('');
      if (content) content.innerHTML = html;
    } else {
      if (content) content.innerHTML = '<p>No pending requests.</p>';
    }
  } catch (err) {
    if (content) content.innerHTML = '<p>Error loading requests.</p>';
  }
}

function closeRequestsModal() {
  const modal = document.getElementById('requestsModal');
  if (modal) modal.classList.remove('active');
}

async function handleRequest(id, status) {
  try {
    const res = await fetch('../backend/api/requests.php', {
      method: 'PUT',
      headers: { 'Content-Type': 'application/json' },
      body: JSON.stringify({ id, status })
    });
    const data = await res.json();
    if (data.success) {
      swal({ icon: 'success', title: 'Success!', text: `Request ${status}` });
      closeRequestsModal();
    } else {
      swal({ icon: 'error', title: 'Error', text: data.message });
    }
  } catch (err) {
    swal({ icon: 'error', title: 'Error', text: 'Network error' });
  }
}

function openSettings() {
  window.location.href = 'Settings.html?role=teacher';
}

function logout() {
  fetch('../backend/api/auth.php', {
    method: 'POST',
    headers: { 'Content-Type': 'application/json' },
    body: JSON.stringify({ action: 'logout' })
  }).then(() => {
    sessionStorage.clear();
    window.location.href = '../login.php';
  }).catch(() => {
    sessionStorage.clear();
    window.location.href = '../login.php';
  });
}

function toggleProfile() {
  const panel = document.getElementById('profilePanel');
  if (panel) panel.classList.toggle('active');
}

document.addEventListener('DOMContentLoaded', () => {
  updateAllDays();
  // Close modals on overlay click
  document.querySelectorAll('.modal-overlay').forEach(overlay => {
    overlay.addEventListener('click', e => {
      if (e.target === overlay) overlay.classList.remove('active');
    });
  });
});

