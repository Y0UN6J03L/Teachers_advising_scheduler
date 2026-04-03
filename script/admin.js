// Admin.js - User Management + Schedules View
// Global state
let users = [];
let currentFilter = 'all';

// SweetAlert2 theme
const swal = (opts) => Swal.fire({
  background: 'rgba(10, 28, 10, 0.97)',
  color: '#e8f5e8',
  confirmButtonColor: '#12b929',
  iconColor: '#12b929',
  ...opts
});

// Load users from API (non-admins)
async function loadUsers() {
  const tbody = document.getElementById('tableBody');
  tbody.innerHTML = '<div class="table-empty">Loading...</div>';
  
  try {
    const res = await fetch('../backend/api/users.php', { 
      credentials: 'same-origin',
      headers: { 'Content-Type': 'application/json' }
    });
    const data = await res.json();
    
    if (Array.isArray(data)) {
      users = data.filter(u => u.role !== 'admin'); // Client filter if API not updated
      renderTable(users);
    } else {
      throw new Error(data.message || 'Invalid response');
    }
  } catch (err) {
    console.error('Load users error:', err);
    tbody.innerHTML = '<div class="table-empty">Failed to load users. Check console.</div>';
  }
}

// Render table rows with edit/delete
function renderTable(filteredUsers = users) {
  const tbody = document.getElementById('tableBody');
  if (filteredUsers.length === 0) {
    tbody.innerHTML = '<div class="table-empty">No users found</div>';
    return;
  }
  
  tbody.innerHTML = filteredUsers.map(user => `
    <div class="table-row" data-id="${user.id}" data-role="${user.role}">
      <div class="user-cell">
        <div class="user-avatar">${user.full_name.charAt(0).toUpperCase()}</div>
        <div class="user-name">${user.full_name}</div>
      </div>
      <div class="user-email">${user.email}</div>
      <div><span class="badge ${user.role === 'teacher' ? 'teacher' : ''}">${user.role.toUpperCase()}</span></div>
      <div class="action-cell">
        <button class="btn-schedule" onclick="viewSchedule(${user.id})" title="View Schedule">Schedule</button>
        <button class="btn-edit" onclick="editUser(${user.id})" title="Edit">Edit</button>
        <button class="btn-delete" onclick="deleteUser(${user.id})" title="Delete">Delete</button>
      </div>
    </div>
  `).join('');
}

// Filter users
function filterUsers(role) {
  currentFilter = role;
  document.querySelectorAll('.filter-btn').forEach(btn => btn.classList.remove('active'));
  event.target.classList.add('active');
  
  let filtered = users;
  if (role !== 'all') {
    filtered = users.filter(u => u.role === role);
  }
  renderTable(filtered);
}

// Search
function searchUsers() {
  const query = document.getElementById('searchInput').value.toLowerCase();
  const filtered = users.filter(u => 
    u.full_name.toLowerCase().includes(query) || 
    u.email.toLowerCase().includes(query)
  );
  renderTable(filtered);
}

// Modals
function openAddModal() {
  document.getElementById('addModal').style.display = 'flex';
  document.getElementById('addUserForm').reset();
}

function closeModal(modalId) {
  document.getElementById(modalId).style.display = 'none';
}

// User forms submit
async function submitUserForm(formData, isEdit = false) {
  try {
    const res = await fetch('../backend/api/users.php', {
      method: isEdit ? 'PUT' : 'POST',
      headers: { 'Content-Type': 'application/json' },
      body: JSON.stringify(formData)
    });
    const data = await res.json();
    
    if (data.success) {
      swal({ icon: 'success', title: 'Success!', text: `User ${isEdit ? 'updated' : 'created'}!` });
      closeModal(isEdit ? 'editModal' : 'addModal');
      loadUsers();
    } else {
      swal({ icon: 'error', title: 'Error', text: data.message });
    }
  } catch (err) {
    swal({ icon: 'error', title: 'Error', text: 'Request failed' });
  }
}

document.addEventListener('DOMContentLoaded', () => {
  // Add form
  document.getElementById('addUserForm').addEventListener('submit', (e) => {
    e.preventDefault();
    const formData = {
      email: document.getElementById('userEmail').value,
      password: document.getElementById('userPassword').value,
      full_name: document.getElementById('userName').value,
      role: document.getElementById('userRole').value
    };
    submitUserForm(formData);
  });
  
  // Init
  loadUsers();
});

// Edit user
function editUser(id) {
  const user = users.find(u => u.id == id);
  if (!user) return;
  
  document.getElementById('editId').value = user.id;
  document.getElementById('editName').value = user.full_name;
  document.getElementById('editEmail').value = user.email;
  document.getElementById('editRole').value = user.role;
  document.getElementById('editModal').style.display = 'flex';
}

document.getElementById('editUserForm')?.addEventListener('submit', (e) => {
  e.preventDefault();
  const formData = {
    id: document.getElementById('editId').value,
    email: document.getElementById('editEmail').value,
    full_name: document.getElementById('editName').value,
    role: document.getElementById('editRole').value
  };
  submitUserForm(formData, true);
});

// Delete
async function deleteUser(id) {
  const user = users.find(u => u.id == id);
  if (!user || !confirm(`Delete ${user.full_name}?`)) return;
  
  try {
    const res = await fetch('../backend/api/users.php', {
      method: 'DELETE',
      headers: { 'Content-Type': 'application/json' },
      body: JSON.stringify({ id })
    });
    const data = await res.json();
    if (data.success) {
      loadUsers();
    } else {
      swal({ icon: 'error', text: data.message });
    }
  } catch (err) {
    swal({ icon: 'error', text: 'Delete failed' });
  }
}

// View schedule
async function viewSchedule(userId) {
  try {
    const res = await fetch(`../backend/api/schedules.php?user_id=${userId}`, {
      credentials: 'same-origin'
    });
    const schedules = await res.json();
    
    if (!schedules.length) {
      return swal({ icon: 'info', title: 'No Schedule', text: 'No schedules found' });
    }
    
    const table = schedules.map(s => `
      <tr>
        <td>${s.day}</td>
        <td>${s.time_start} - ${s.time_end}</td>
        <td>${s.description || '-'}</td>
      </tr>
    `).join('');
    
    swal({
      title: 'Schedule',
      html: `<table style="width:100%;border-collapse:collapse;"><thead><tr><th>Day</th><th>Time</th><th>Description</th></tr></thead><tbody>${table}</tbody></table>`,
      icon: 'info'
    });
  } catch (err) {
    swal({ icon: 'error', text: 'Failed to load schedule' });
  }
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

