<?php 
session_start();
if (!isset($_SESSION['role']) || $_SESSION['role'] !== 'student') {
  header('Location: ../login.php');
  exit;
}
$student_id = $_SESSION['user_id'] ?? 1;
$student_name = $_SESSION['full_name'] ?? 'Student Name';
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0"/>
  <title>Search Teacher - Student</title>
  <link href="https://fonts.googleapis.com/css2?family=Outfit:wght@300;400;500;600;700&display=swap" rel="stylesheet"/>
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css"/>
  <link rel="stylesheet" href="../CSS/Student.css"/>

</head>
<body>

<div class="topbar">
  <div class="hamburger" onclick="toggleSidebar()"><i class="fa fa-bars"></i></div>
  <div class="topbar-title">S T U D E N T</div>
  <div class="avatar-btn" onclick="toggleProfile()"><i class="fa fa-user"></i></div>
</div>

<div class="layout">

  <div class="sidebar" id="sidebar">
    <a class="nav-item" href="student.php"><div class="nav-icon"><i class="fa fa-search"></i></div>Search Teachers</a>
    <a class="nav-item" href="#"><div class="nav-icon"><i class="fa fa-history"></i></div>My Requests</a>
    <a class="nav-item" href="#"><div class="nav-icon"><i class="fa fa-calendar"></i></div>My Schedule</a>
  </div>

  <div class="main">
    <div class="search-label">Search Teachers</div>
    <div class="search-wrap">
      <i class="fa fa-search"></i>
      <input type="text" id="teacherSearch" placeholder="Search by name, subject or course..." oninput="searchTeachers()">
    </div>

    <div class="teacher-grid" id="teacherGrid">
      <!-- Dynamic teachers loaded here -->
      <div class="no-results" style="display: none;">
        <i class="fa fa-search"></i>
        <p>No teachers found. Try different search terms.</p>
      </div>
    </div>
  </div>

  <div class="profile-panel" id="profilePanel">
    <div class="profile-avatar"><i class="fa fa-user"></i></div>
    <div class="profile-name"><?php echo htmlspecialchars($student_name); ?></div>
    <div class="profile-info-card">
      <div>Role: <span>Student</span></div>
      <div>ID: <span><?php echo $student_id; ?></span></div>
    </div>
    <div class="profile-divider"></div>
    <div class="profile-action">
      <i class="fa fa-gear"></i>
      <a href="Settings.html?role=student">Settings</a>
    </div>
    <div class="profile-action" onclick="logout()">
      <i class="fa fa-sign-out"></i> Logout
    </div>
  </div>

</div>

<!-- FAB with popup for requests -->
<div class="fab-wrap">
  <div class="fab-popup" id="fabPopup">
    <div class="fab-popup-item"><i class="fa fa-history"></i> My Requests</div>
    <div class="fab-popup-divider"></div>
    <div class="fab-popup-body">
      <div id="myRequestsList">
        <div class="fab-popup-empty">
          <i class="fa fa-inbox"></i>
          <span>No requests yet</span>
        </div>
      </div>
    </div>
  </div>
  <button class="fab" onclick="toggleFabPopup()">
    <i class="fa fa-calendar-check"></i>
  </button>
</div>

<script src="../script/Navigaton.js"></script>
<script src="../script/student.js"></script>

</body>
</html>

