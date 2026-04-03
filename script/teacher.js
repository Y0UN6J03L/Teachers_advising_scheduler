// Teacher.js - Dynamic Schedule & Requests
const swal = (opts) => Swal.fire({
  background: 'rgba(10, 28, 10, 0.97)',
  color: '#e8f5e8',
  confirmButtonColor: '#12b929',
  iconColor: '#12b929',
  ...opts
});

let teacherId = <?= $teacher_id ?>; // From PHP

async function loadDayData(day) {
  try {
    // Schedules count for day
    const schedRes = await fetch(`../backend/api/schedules.php?day=${day}&teacher_id=${teacherId}`, {credentials: 'same-origin'});
    const schedData = await schedRes.json();
    const slots = Array.isArray(schedData) ? schedData.length : 0;

    // Requests count for day
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
    const card = document.querySelector(`[data-day="${day}"]`);
    if (card) {
      card.querySelector('.schedule-link').textContent = `VIEW SCHEDULE (${data.slots} slots)`;
      card.querySelector('.new-badge').textContent = `(${data.pending} new)`;
    }
  }
}

// View Schedule
async function viewSchedule(day) {
  document.getElementById('scheduleTitle').textContent = `${day.toUpperCase()} Schedule`;
  const content = document.getElementById('scheduleContent');
  content.innerHTML = '<p>Loading...</p>';
  document.getElementById('scheduleModal').classList.add('active');

  try {
    const res = await fetch(`../backend/api/schedules.php?teacher_id=${teacherId}&day=${day}`, {credentials: 'same-origin'});
    const data = await res.json();
    if (Array.isArray(data) && data.length) {
      const table = `<table class="schedule-table">
        <thead><tr><th>Time</th><th>End</th><th>Description</th><th>Actions</th></tr></thead>
        <tbody>${data.map(s => `
          <tr>
            <td>${s.time_start}</td>
            <td>${s.time_end}</td>
            <td>${s.description || '-'}</td>
            <td>
              <button onclick="editSchedule(${s.id})" class="btn-edit">Edit</button>
              <button onclick="deleteSchedule(${s.id})" class="btn-delete">Delete</button>
            </td>
          </tr>
        `).join('')}</tbody></table>`;
      content.innerHTML = table;
    } else {
      content.innerHTML = '<p>No schedule for this day. <button onclick="openAddScheduleModal(\\'' + day + '\\')" class="btn-add-small">Add Slot</button></p>';
    }
  } catch (err) {
    content.innerHTML = '<p>Error loading schedule.</p>';
  }
}

function closeScheduleModal() {
  document.getElementById('scheduleModal').classList.remove('active');
}

async function viewRequests(day) {
  document.getElementById('requestsTitle').textContent = `${day.toUpperCase()} Requests`;
  const content = document.getElementById('requestsContent');
  content.innerHTML = '<p>Loading...</p>';
  document.getElementById('requestsModal').classList.add('active');

  try {
    const res = await fetch(`../backend/api/requests.php?day=${day}&teacher_id=${teacherId}`, {credentials: 'same-origin'});
    const data = await res.json();
    if (Array.isArray(data) && data.length > 0) {
      content.innerHTML = data.map(req => `
        <div class="request-item">
          <div>
            <strong>${req.student_name}</strong> - ${req.subject || req.message} (${req.time || 'TBD'})
            <br><small>${req.message || 'No message'}</small>
          </div>
          <div class="request-actions">
            <button class="btn-approve" onclick="handleRequest(${req.id}, 'approve')">Approve</button>
            <button class="btn-reject" onclick="handleRequest(${req.id}, 'reject')">Reject</button>
          </div>
        </div>
      `).join('');
    } else {
      content.innerHTML = '<p>No pending requests.</p>';
    }
  } catch (err) {
    content.innerHTML = '<p>Error loading requests.</p>';
  }
}

function closeRequestsModal() {
  document.getElementById('requestsModal').classList.remove('active');
}

async function handleRequest(requestId, action) {
  try {
    const res = await fetch('../backend/api/requests.php', {
      method: 'PUT',
      headers: { 'Content-Type': 'application/json' },
      body: JSON.stringify({ id: requestId, status: action })
    });
    const data = await res.json();
    if (data.success) {
      swal({ icon: 'success', title: 'Updated!', text: `Request ${action}d.` });
      closeRequestsModal();
    } else {
      swal({ icon: 'error', title: 'Error', text: data.message });
    }
  } catch (err) {
    swal({ icon: 'error', title: 'Error', text: 'Failed to update request.' });
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
  });
}

function toggleProfile() {
  document.getElementById('profilePanel').classList.toggle('active');
}


// FAB Add Schedule
function openAddScheduleModal(day = '') {
  document.getElementById('addScheduleDay').value = day;
  document.getElementById('addScheduleModal').classList.add('active');
}

async function submitAddSchedule() {
  const formData = {
    day: document.getElementById('addScheduleDay').value,
    time_start: document.getElementById('addScheduleTimeStart').value,
    time_end: document.getElementById('addScheduleTimeEnd').value,
    description: document.getElementById('addScheduleDesc').value
  };

  try {
    const res = await fetch('../backend/api/schedules.php', {
      method: 'POST',
      headers: {'Content-Type': 'application/json'},
      body: JSON.stringify(formData)
    });
    const data = await res.json();
    if (data.success) {
      swal({icon: 'success', title: 'Added!'});
      closeAddScheduleModal();
      updateAllDays();
    } else {
      swal({icon: 'error', text: data.message});
    }
  } catch (err) {
    swal({icon: 'error', text: 'Failed to add'});
  }
}

function closeAddScheduleModal() {
  document.getElementById('addScheduleModal').classList.remove('active');
  // reset form
}

function editSchedule(id) {
  // impl similar to admin edit
}

function deleteSchedule(id) {
  // impl POST DELETE
}

// Logout etc (existing)

document.addEventListener('DOMContentLoaded', () => {
  updateAllDays();
});

