<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0"/>
  <title>User Management</title>
  <link href="https://fonts.googleapis.com/css2?family=Outfit:wght@300;400;500;600;700&display=swap" rel="stylesheet"/>
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css"/>
  <link rel="stylesheet" href="../CSS/Admin.css"/>
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/sweetalert2/11.23.0/sweetalert2.min.css">
</head>
<body>

<?php 
session_start(); 
if (!isset($_SESSION['role']) || $_SESSION['role'] !== 'admin') {
  header('Location: ../login.php');
  exit;
}
?>

<!-- Topbar -->
<div class="topbar">
  <div class="hamburger" onclick="toggleSidebar()">
    <i class="fa fa-bars"></i>
  </div>
  <div class="topbar-title">A D M I N</div>
  <div class="avatar-btn" onclick="toggleProfile()">
    <i class="fa fa-user"></i>
  </div>
</div>

<div class="layout">

  <!-- Sidebar -->
  <div class="sidebar" id="sidebar">
    <a class="nav-item" href="#">
      <div class="nav-icon"><i class="fa fa-globe"></i></div>
      Admin
    </a>
    <a class="nav-item" href="#">
      <div class="nav-icon"><i class="fa fa-users"></i></div>
      User
    </a>
    <a class="nav-item" href="#">
      <div class="nav-icon"><i class="fa fa-chart-bar"></i></div>
      Reports
    </a>
  </div>

  <!-- Main Content -->
  <div class="main">
    <div class="page-header">
      <div class="page-title">User Management</div>
      <button class="btn-add" onclick="openAddModal()">
        <i class="fa fa-plus"></i>
        Add User
      </button>
    </div>

    <div class="search-filter">
      <div class="search-wrap">
        <i class="fa fa-search"></i>
        <input type="text" id="searchInput" placeholder="Search Users..." oninput="searchUsers()">
      </div>
      <div class="filter-btns">
        <button class="filter-btn active" data-role="all" onclick="filterUsers('all')">All</button>
        <button class="filter-btn" data-role="teacher" onclick="filterUsers('teacher')">Teacher</button>
        <button class="filter-btn" data-role="student" onclick="filterUsers('student')">Student</button>
      </div>
    </div>

    <div class="table-wrap">
      <div class="table-head">
        <span>Name</span>
        <span>E-Mail</span>
        <span>Role</span>
        <span>Actions</span>
      </div>
<div id="tableBody">
        <?php
        require '../backend/config/database.php';
        try {
          $stmt = $pdo->query("SELECT id, email, full_name, role FROM users WHERE role != 'admin' ORDER BY created_at DESC");
          $users = $stmt->fetchAll(PDO::FETCH_ASSOC);
          if (empty($users)) {
            echo '<div class="table-empty">No users found. Add some teachers/students!</div>';
          } else {
            foreach ($users as $user) {
              $initial = strtoupper(substr($user['full_name'], 0, 1));
              $roleClass = $user['role'] === 'teacher' ? 'teacher' : '';
              echo "
              <div class='table-row' data-role='{$user['role']}'>
                <div class='user-cell'>
                  <div class='user-avatar'>{$initial}</div>
                  <div class='user-name'>{$user['full_name']}</div>
                </div>
                <div class='user-email'>{$user['email']}</div>
                <div><span class='badge {$roleClass}'>" . strtoupper($user['role']) . "</span></div>
                <div><button class='btn-schedule' onclick='viewSchedule({$user['id']})'>View Schedule</button></div>
              </div>";
            }
          }
        } catch (PDOException $e) {
          echo '<div class="table-empty">DB Error: ' . $e->getMessage() . '</div>';
        }
        ?>
      </div>
    </div>
  </div>

  <!-- Profile Panel -->
  <div class="profile-panel" id="profilePanel">
    <div class="profile-avatar">
      <i class="fa fa-user"></i>
    </div>
    <div class="profile-name"><?php echo $_SESSION['full_name'] ?? 'Admin'; ?></div>
    <div class="profile-info-card">
      <div>Role : <span>Admin</span></div>
      <div>Admin Info : <span>——</span></div>
    </div>
    <div class="profile-divider"></div>
    <div class="profile-action">
      <i class="fa fa-gear"></i>
      <a href="Settings.html?role=admin">Settings</a>
    </div>
    <div class="profile-action" onclick="logout()">
      <i class="fa fa-sign-out"></i>
      Logout
    </div>
  </div>

</div>

<!-- Add User Modal -->
<div class="modal-overlay" id="addModal" style="display: none;">
  <div class="modal">
    <div class="modal-header">
      <div class="modal-title">Add New User</div>
      <button class="modal-close" onclick="closeAddModal()">×</button>
    </div>
    <form id="addUserForm">
      <div class="form-group">
        <label>Full Name</label>
        <input type="text" id="userName" required>
      </div>
      <div class="form-group">
        <label>Email</label>
        <input type="email" id="userEmail" required>
      </div>
      <div class="form-group">
        <label>Password</label>
        <input type="password" id="userPassword" minlength="6" required>
      </div>
      <div class="form-group">
        <label>Role</label>
        <select id="userRole" required>
          <option value="teacher">Teacher</option>
          <option value="student">Student</option>
        </select>
      </div>
      <button type="submit" class="modal-submit">Create User</button>
    </form>
  </div>
</div>

<script src="../script/Navigaton.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/sweetalert2/11.23.0/sweetalert2.min.js"></script>
<script>
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

// Load users from API
async function loadUsers() {
  try {
    console.log('Loading users via API...');
    const res = await fetch('../backend/api/users.php', {credentials: 'same-origin'});
    const data = await res.json();
    console.log('API response:', data);
    if (Array.isArray(data)) {
      users = data;
      renderTable(users);
    } else {
      console.error('Invalid API response:', data);
    }
  } catch (err) {
    console.error('API Error:', err);
    // Keep server-rendered table
  }
}

// Render table rows
function renderTable(filteredUsers = users) {
  const tbody = document.getElementById('tableBody');
  if (filteredUsers.length === 0) {
    tbody.innerHTML = '<div class="table-empty">No users found</div>';
    return;
  }
  
  tbody.innerHTML = filteredUsers.map(user => `
    <div class="table-row" data-role="${user.role}">
      <div class="user-cell">
        <div class="user-avatar">${user.full_name.charAt(0).toUpperCase()}</div>
        <div class="user-name">${user.full_name}</div>
      </div>
      <div class="user-email">${user.email}</div>
      <div><span class="badge ${user.role === 'teacher' ? 'teacher' : ''}">${user.role.toUpperCase()}</span></div>
      <div><button class="btn-schedule" onclick="viewSchedule(${user.id})">View Schedule</button></div>
    </div>
  `).join('');
}

// Filter users by role
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

// Search users
function searchUsers() {
  const query = document.getElementById('searchInput').value.toLowerCase();
  const filtered = users.filter(u => 
    u.full_name.toLowerCase().includes(query) || 
    u.email.toLowerCase().includes(query)
  );
  renderTable(filtered);
}

// Add user modal
function openAddModal() {
  document.getElementById('addModal').style.display = 'flex';
}

function closeAddModal() {
  document.getElementById('addModal').style.display = 'none';
  document.getElementById('addUserForm').reset();
}

// Add user form submit
document.getElementById('addUserForm').addEventListener('submit', async (e) => {
  e.preventDefault();
  const formData = {
    email: document.getElementById('userEmail').value,
    password: document.getElementById('userPassword').value,
    full_name: document.getElementById('userName').value,
    role: document.getElementById('userRole').value
  };

  try {
    const res = await fetch('../backend/api/users.php', {
      method: 'POST',
      headers: { 'Content-Type': 'application/json' },
      body: JSON.stringify(formData)
    });
    const data = await res.json();
    
    if (data.success) {
      swal({ icon: 'success', title: 'Success!', text: 'User created successfully!' });
      closeAddModal();
      loadUsers(); // Refresh table
    } else {
      swal({ icon: 'error', title: 'Error', text: data.message });
    }
  } catch (err) {
    swal({ icon: 'error', title: 'Error', text: 'Failed to create user' });
  }
});

// View schedule (placeholder)
function viewSchedule(userId) {
  swal({
    icon: 'info',
    title: 'View Schedule',
    text: `Schedule for user ID: ${userId} (Feature coming soon)`,
    footer: '<button class="btn-schedule">Full Schedule View</button>'
  });
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
  loadUsers();
});
</script>

<style>
/* Basic Modal Styles (enhance with Admin.css if needed) */
.modal-overlay {
  position: fixed;
  top: 0;
  left: 0;
  width: 100%;
  height: 100%;
  background: rgba(0,0,0,0.5);
  display: flex;
  align-items: center;
  justify-content: center;
  z-index: 1000;
  backdrop-filter: blur(5px);
}
.modal {
  background: white;
  border-radius: 16px;
  padding: 24px;
  max-width: 500px;
  width: 90%;
  max-height: 90vh;
  overflow-y: auto;
}
.modal-header {
  display: flex;
  justify-content: space-between;
  align-items: center;
  margin-bottom: 20px;
}
.modal-title {
  font-size: 1.5rem;
  font-weight: 600;
}
.modal-close {
  background: none;
  border: none;
  font-size: 24px;
  cursor: pointer;
  color: #666;
}
.form-group {
  margin-bottom: 16px;
}
.form-group label {
  display: block;
  margin-bottom: 6px;
  font-weight: 500;
}
.form-group input, .form-group select {
  width: 100%;
  padding: 12px;
  border: 1px solid #ddd;
  border-radius: 8px;
  font-size: 16px;
}
.modal-submit {
  width: 100%;
  padding: 14px;
  background: #12b929;
  color: white;
  border: none;
  border-radius: 8px;
  font-size: 16px;
  font-weight: 600;
  cursor: pointer;
}
.table-empty {
  grid-column: 1 / -1;
  text-align: center;
  padding: 40px;
  color: #666;
  font-style: italic;
}
</style>

</body>
</html>

