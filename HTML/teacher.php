<?php 
// 1. ALWAYS start the session at the absolute top of the file!
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// 2. Include the database connection before running checks
require '../backend/config/database.php';

// 3. Run the security check. If they aren't a teacher, stop right here.
if (!isset($_SESSION['role']) || $_SESSION['role'] !== 'teacher') {
?>
  <!DOCTYPE html>
  <html lang="en">
  <head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0"/>
    <title>Unauthorized Access</title>
    <style>
      body { 
        background: #111; 
        color: white; 
        font-family: Arial, sans-serif; 
        text-align: center; 
        display: flex;
        flex-direction: column;
        justify-content: center;
        align-items: center;
        min-height: 100vh;
        margin: 0;
      }
      h1 { font-size: 2rem; margin-bottom: 10px; }
      p { color: #ccc; margin-bottom: 20px; }
      a { color: #12b929; text-decoration: none; font-weight: bold; border: 1px solid #12b929; padding: 10px 20px; border-radius: 5px; transition: background 0.2s; }
      a:hover { background: #12b929; color: white; }
    </style>
  </head>
  <body>
    <h1>Not logged in as teacher.</h1>
    <p>Current session role: <strong><?php echo isset($_SESSION['role']) ? htmlspecialchars($_SESSION['role']) : 'none'; ?></strong></p>
    <a href="../login.php">Click here to Login</a>
  </body>
  </html>
<?php 
exit;
}

// 4. Set up variables for successful validation
$teacher_id = $_SESSION['user_id'] ?? 1;
$teacher_name = $_SESSION['full_name'] ?? 'Teacher Name';
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0"/>
  <title>Teacher - Schedule & Requests</title>
  <link href="https://fonts.googleapis.com/css2?family=Barlow+Condensed:wght@700;800&family=Barlow:wght@400;500;600&display=swap" rel="stylesheet"/>
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css"/>
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/sweetalert2/11.23.0/sweetalert2.min.css">
  <link rel="stylesheet" href="../CSS/Teacher.css">
  <style>
    *, *::before, *::after { box-sizing: border-box; margin: 0; padding: 0; }

    :root {
      --green-dark:   #1a4d2e;
      --green-mid:    #2d7a4f;
      --green-bright: #3a9a60;
      --green-card:   #236b3f;
      --btn-bg:       #e8f0eb;
      --btn-hover:    #d0e4d8;
      --text-white:   #ffffff;
      --text-muted:   rgba(255,255,255,0.75);
      --shadow:       0 8px 32px rgba(0,0,0,0.35);
      --radius:       18px;
    }

    body {
      font-family: 'Barlow', sans-serif;
      min-height: 100vh;
      background: #111;
      overflow-x: hidden;
    }

    /* ── BACKGROUND ── */
    .bg {
      position: fixed;
      inset: 0;
      background: url('../img/Bg_img.jpg') center / cover no-repeat;
      filter: brightness(0.38) saturate(0.7);
      z-index: 0;
    }

    /* ── NAVBAR ── */
    nav {
      position: relative;
      z-index: 10;
      display: flex;
      align-items: center;
      justify-content: space-between;
      padding: 0 28px;
      height: 68px;
      background: rgba(255,255,255,0.97);
      box-shadow: 0 2px 12px rgba(0,0,0,0.18);
    }

    .nav-hamburger {
      display: flex;
      flex-direction: column;
      gap: 5px;
      cursor: pointer;
      padding: 6px;
    }
    .nav-hamburger span {
      display: block;
      width: 26px;
      height: 3px;
      background: var(--green-dark);
      border-radius: 2px;
      transition: transform 0.3s;
    }

    .nav-title {
      font-family: 'Barlow Condensed', sans-serif;
      font-size: 1.7rem;
      font-weight: 800;
      letter-spacing: 0.12em;
      color: var(--green-dark);
    }

    .nav-avatar {
      width: 42px;
      height: 42px;
      border-radius: 50%;
      background: var(--green-dark);
      display: flex;
      align-items: center;
      justify-content: center;
      cursor: pointer;
      transition: background 0.2s;
    }
    .nav-avatar:hover { background: var(--green-mid); }
    .nav-avatar i { font-size: 20px; color: #fff; }

    /* ── MAIN ── */
    main {
      position: relative;
      z-index: 5;
      padding: 40px 36px 60px;
      max-width: 1200px;
      margin: 0 auto;
    }

    /* ── SECTION HEADER ── */
    .section-header {
      display: inline-block;
      background: var(--green-dark);
      color: var(--text-white);
      font-family: 'Barlow Condensed', sans-serif;
      font-size: 1.35rem;
      font-weight: 800;
      letter-spacing: 0.1em;
      padding: 10px 24px;
      border-radius: 10px;
      margin-bottom: 32px;
      box-shadow: 0 4px 16px rgba(0,0,0,0.3);
      animation: fadeDown 0.5s ease both;
    }

    /* ── GRID ── */
    .grid {
      display: grid;
      grid-template-columns: repeat(3, 1fr);
      gap: 20px;
    }

    /* ── DAY CARD ── */
    .day-card {
      background: var(--green-card);
      border-radius: var(--radius);
      box-shadow: var(--shadow);
      display: flex;
      align-items: stretch;
      overflow: hidden;
      border: 1.5px solid rgba(255,255,255,0.12);
      animation: fadeUp 0.5s ease both;
      transition: transform 0.22s, box-shadow 0.22s;
      cursor: pointer;
    }
    .day-card:hover {
      transform: translateY(-4px) scale(1.015);
      box-shadow: 0 14px 40px rgba(0,0,0,0.45);
    }

    .day-info {
      flex: 1;
      padding: 22px 20px;
      display: flex;
      flex-direction: column;
      justify-content: center;
      gap: 6px;
    }

    .day-name {
      font-family: 'Barlow Condensed', sans-serif;
      font-size: 2rem;
      font-weight: 800;
      color: var(--text-white);
      letter-spacing: 0.04em;
      line-height: 1;
    }

    .schedule-link {
      font-size: 0.72rem;
      font-weight: 600;
      letter-spacing: 0.1em;
      color: var(--text-muted);
      text-transform: uppercase;
      cursor: pointer;
      transition: color 0.2s;
      display: flex;
      align-items: center;
      gap: 7px;
    }
    .schedule-link:hover { color: #fff; }

    .divider {
      width: 1.5px;
      background: rgba(255,255,255,0.2);
      margin: 14px 0;
    }

    .request-col {
      display: flex;
      flex-direction: column;
      align-items: center;
      justify-content: center;
      padding: 16px 18px;
      gap: 6px;
    }

    .request-btn-main {
      background: var(--btn-bg);
      border: none;
      border-radius: 12px;
      padding: 10px 16px;
      cursor: pointer;
      display: flex;
      flex-direction: column;
      align-items: center;
      gap: 5px;
      width: 100%;
      transition: background 0.18s, transform 0.15s;
      box-shadow: 0 2px 8px rgba(0,0,0,0.18);
    }
    .request-btn-main:hover {
      background: var(--btn-hover);
      transform: scale(1.04);
    }
    .request-btn-main:active { transform: scale(0.97); }

    .request-label {
      font-family: 'Barlow Condensed', sans-serif;
      font-size: 0.8rem;
      font-weight: 800;
      letter-spacing: 0.08em;
      color: var(--green-dark);
      text-align: center;
      line-height: 1.2;
    }

    .new-badge {
      font-size: 0.7rem;
      font-weight: 600;
      color: var(--green-dark);
      letter-spacing: 0.04em;
    }

    /* Profile Panel */
    .profile-panel {
      position: fixed;
      top: 80px;
      right: -320px;
      width: 300px;
      height: calc(100vh - 80px);
      background: rgba(255,255,255,0.98);
      backdrop-filter: blur(20px);
      box-shadow: -4px 0 24px rgba(0,0,0,0.25);
      transition: right 0.3s cubic-bezier(0.25,0.46,0.45,0.94);
      z-index: 100;
      padding: 32px 24px;
      display: flex;
      flex-direction: column;
    }
    .profile-panel.active { right: 0; }

    .profile-avatar { 
      width: 80px; height: 80px; border-radius: 50%; 
      background: var(--green-dark); color: white; 
      display: flex; align-items: center; justify-content: center; 
      font-size: 32px; margin: 0 auto 16px; 
    }
    .profile-name { font-size: 1.4rem; font-weight: 700; text-align: center; margin-bottom: 12px; }
    .profile-info { font-size: 0.95rem; color: #666; text-align: center; margin-bottom: 24px; }
    .profile-divider { height: 1px; background: #eee; margin: 20px 0; }
    .profile-action { 
      display: flex; align-items: center; gap: 12px; 
      padding: 12px 0; cursor: pointer; font-weight: 500; 
      transition: color 0.2s; 
    }
    .profile-action:hover { color: var(--green-dark); }
    .profile-action i { width: 20px; }

    /* Modal */
    .modal-overlay {
      position: fixed;
      top: 0;
      left: 0;
      width: 100%;
      height: 100%;
      background: rgba(0,0,0,0.7);
      display: none;
      align-items: center;
      justify-content: center;
      z-index: 1000;
      backdrop-filter: blur(8px);
    }
    .modal-overlay.active { display: flex; }
    .modal {
      background: white;
      border-radius: 20px;
      padding: 32px;
      max-width: 600px;
      width: 90%;
      max-height: 80vh;
      overflow-y: auto;
    }
    .modal-header { 
      display: flex; justify-content: space-between; align-items: center; 
      margin-bottom: 24px; 
    }
    .modal-title { font-size: 1.5rem; font-weight: 700; }
    .modal-close { 
      background: none; border: none; font-size: 24px; cursor: pointer; 
      color: #999; width: 36px; height: 36px; border-radius: 50%; 
      display: flex; align-items: center; justify-content: center; 
    }
    .modal-close:hover { background: #f0f0f0; color: #333; }

    @keyframes fadeDown {
      from { opacity: 0; transform: translateY(-16px); }
      to   { opacity: 1; transform: translateY(0); }
    }
    @keyframes fadeUp {
      from { opacity: 0; transform: translateY(20px); }
      to   { opacity: 1; transform: translateY(0); }
    }

    @media (max-width: 860px) {
      .grid { grid-template-columns: repeat(2, 1fr); }
    }
    @media (max-width: 540px) {
      .grid { grid-template-columns: 1fr; }
      main { padding: 24px 16px 48px; }
      .day-name { font-size: 1.6rem; }
    }
  </style>
</head>
<body>

<div class="bg"></div>

<nav>
  <div class="nav-hamburger">
    <span></span><span></span><span></span>
  </div>
  <div class="nav-title">TEACHER</div>
  <div class="nav-avatar" onclick="toggleProfile()">
    <i class="fa fa-user"></i>
  </div>
</nav>

<main>
  <div class="section-header">SCHEDULE & REQUESTS</div>
  <div class="grid" id="dayGrid">
    <?php
    $days = ['Monday', 'Tuesday', 'Wednesday', 'Thursday', 'Friday', 'Saturday'];
    foreach ($days as $index => $day) {
      // Querying the database for real schedule count
      $stmt = $pdo->prepare("SELECT COUNT(*) FROM schedules WHERE teacher_id = ? AND day = ?");
      $stmt->execute([$teacher_id, $day]);
      $slots = $stmt->fetchColumn();

      // Demo fallback for pending requests
      $pending = rand(1, 5); 
      ?>
      <div class="day-card" style="animation-delay: <?php echo ($index * 0.05); ?>s">
<div class="day-info" onclick="viewSchedule('<?php echo $day; ?>')">
          <div class="day-name"><?php echo $day; ?></div>
          <div class="schedule-link">
            <i class="fa fa-calendar-alt"></i> View Slots (<?php echo $slots; ?>)
          </div>
        </div>
        <div class="divider"></div>
        <div class="request-col">
<button class="request-btn-main" onclick="viewRequests('<?php echo $day; ?>')">
            <span class="request-label">REQUESTS</span>
            <span class="new-badge"><?php echo $pending; ?> Pending</span>
          </button>
        </div>
      </div>
    <?php } ?>
  </div>
</main>

<div class="profile-panel" id="profilePanel">
  <div class="profile-avatar"><i class="fa fa-user"></i></div>
  <div class="profile-name"><?php echo htmlspecialchars($teacher_name); ?></div>
  <div class="profile-info">
    <div>Role: <strong>Teacher</strong></div>
    <div>ID: <?php echo htmlspecialchars($teacher_id); ?></div>
  </div>
  <div class="profile-divider"></div>
  <div class="profile-action" onclick="openSettings()">
    <i class="fa fa-gear"></i>
    <span>Settings</span>
  </div>
  <div class="profile-action" onclick="logout()">
    <i class="fa fa-sign-out-alt"></i>
    <span>Logout</span>
  </div>
</div>

<div class="modal-overlay" id="scheduleModal">
  <div class="modal">
    <div class="modal-header">
      <div class="modal-title" id="scheduleTitle">Schedule</div>
      <button class="modal-close" onclick="closeScheduleModal()">&times;</button>
    </div>
    <div id="scheduleContent">
      <p>Loading schedule...</p>
    </div>
  </div>
</div>

<div class="modal-overlay" id="requestsModal">
  <div class="modal">
    <div class="modal-header">
      <div class="modal-title" id="requestsTitle">Requests</div>
      <button class="modal-close" onclick="closeRequestsModal()">&times;</button>
    </div>
    <div id="requestsContent">
      <p>Loading requests...</p>
    </div>
  </div>
</div>

<script src="../script/Navigaton.js"></script>
<script src="../script/teacher.js"></script>

</body>
</html>