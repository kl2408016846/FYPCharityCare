<?php
session_start();
include 'db.php';

// 1. CRITICAL: Set Timezone
date_default_timezone_set('Asia/Kuala_Lumpur');

if (!isset($_SESSION['student_id'])) {
    header("Location: login.php");
    exit();
}

$current_student_id = $_SESSION['student_id'];

// 2. Fetch Student Details
$student_query = "SELECT * FROM students WHERE student_id = '$current_student_id'";
$student_result = $conn->query($student_query);
$student = $student_result->fetch_assoc();

// 3. THE FIX: Fetch programs WITH slot counts
$program_query = "SELECT p.*, s.full_name as organizer_name, 
                  (SELECT COUNT(*) FROM registrations r WHERE r.program_id = p.id AND r.student_id = '$current_student_id') as is_registered,
                  (SELECT COUNT(*) FROM registrations r WHERE r.program_id = p.id AND r.registration_type = 'Volunteer') as total_joined 
                  FROM programs p 
                  LEFT JOIN staff s ON p.created_by = s.staff_id 
                  WHERE p.end_date >= CURDATE() 
                  ORDER BY p.id DESC"; 

$program_result = $conn->query($program_query);

if (!$program_result) {
    die("Query Error: " . $conn->error);
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>CharityCare+ Dashboard</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <link href="https://fonts.googleapis.com/css2?family=Dancing+Script:wght@600&family=Inter:wght@400;700;800&display=swap" rel="stylesheet">

    <style>
        :root {
            --prof-blue: #1e3a8a;
            --accent-blue: #2964c9;
            --light-red: #ef4444;
        }

        body {
            font-family: 'Inter', sans-serif;
            background: linear-gradient(135deg, #fce7f3 0%, #e0f2fe 100%);
            margin: 0;
            display: flex;
            flex-direction: column;
            height: 100vh;
            overflow: hidden;
        }

        .navbar {
            background-color: white;
            border-bottom: 3px solid var(--prof-blue);
            height: 85px;
            z-index: 1000;
            flex-shrink: 0;
        }

        .navbar-logo {
            height: 55px;
            width: auto;
        }

        .brand-text {
            border-left: 1px solid #ddd;
            padding-left: 15px;
            margin-left: 15px;
        }

        .logo-text {
            color: #000;
            font-weight: 800;
            font-size: 1.4rem;
            letter-spacing: -1px;
        }

        .logo-accent {
            color: var(--accent-blue);
        }

        .slogan-text {
            color: var(--accent-blue);
            font-family: 'Dancing Script', cursive;
            font-size: 1.5rem;
            text-align: center;
            flex-grow: 1;
        }

        .dashboard-label {
            font-weight: 800;
            color: var(--prof-blue);
            font-size: 1rem;
        }

        .main-container {
            display: flex;
            flex: 1;
            height: calc(100vh - 85px);
            overflow: hidden;
        }

        .sidebar {
            width: 280px;
            background: white;
            border-right: 1px solid #e2e8f0;
            display: flex;
            flex-direction: column;
            padding: 40px 20px;
            flex-shrink: 0;
            height: 100%;
        }

        .sidebar-menu {
            flex: 1;
            display: flex;
            flex-direction: column;
        }

        .profile-section {
            text-align: center;
            padding-bottom: 30px;
            border-bottom: 1px solid #f1f5f9;
            margin-bottom: 25px;
        }

        .menu-item {
            display: flex;
            align-items: center;
            padding: 12px 18px;
            color: #475569;
            text-decoration: none;
            font-weight: 600;
            border-radius: 12px;
            margin-bottom: 5px;
            transition: 0.3s;
        }

        .menu-item i {
            width: 25px;
            margin-right: 10px;
        }

        .menu-item:hover,
        .menu-item.active {
            background: #eff6ff;
            color: var(--accent-blue);
        }

        .menu-item.logout {
            margin-top: auto !important;
            color: var(--light-red) !important;
            font-weight: 700;
        }

        .content-area {
            flex: 1;
            padding: 40px;
            overflow-y: auto;
            height: 100%;
        }

        .search-wrapper {
            background: white;
            border-radius: 15px;
            padding: 5px 20px;
            display: flex;
            align-items: center;
            max-width: 550px;
            box-shadow: 0 4px 12px rgba(0, 0, 0, 0.05);
            border: 1px solid #f1f5f9;
            margin-bottom: 35px;
        }

        .search-wrapper input {
            border: none;
            outline: none;
            padding: 12px 15px;
            width: 100%;
            font-size: 0.95rem;
            background: transparent;
        }

        #programsList {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(320px, 1fr));
            gap: 25px;
            padding-bottom: 40px;
        }

        .program-card {
            background: white;
            border-radius: 20px;
            display: flex;
            flex-direction: column;
            overflow: hidden;
            box-shadow: 0 8px 15px rgba(0, 0, 0, 0.05);
            border: 1px solid rgba(0, 0, 0, 0.03);
            transition: transform 0.3s ease;
            height: 100%;
        }

        .program-img {
            width: 100%;
            height: 180px;
            object-fit: cover;
        }

        .program-body {
            padding: 20px;
            flex: 1;
            display: flex;
            flex-direction: column;
        }

        .program-title {
            font-weight: 800;
            font-size: 1.25rem;
            color: var(--prof-blue);
            margin: 5px 0 10px 0;
            line-height: 1.3;
        }

        .donation-section {
            margin-bottom: 15px;
        }

        .raised-text {
            color: var(--prof-blue);
            font-weight: 800;
            font-size: 0.95rem;
        }

        .goal-text {
            color: #64748b;
            font-size: 0.8rem;
        }

        .progress {
            height: 8px;
            border-radius: 20px;
            background-color: #f1f5f9;
            margin: 4px 0;
        }

        .progress-bar {
            background-color: var(--accent-blue) !important;
        }

        .program-meta {
            display: flex;
            flex-direction: column;
            gap: 8px;
            margin-bottom: 15px;
        }

        .meta-item {
            font-size: 0.85rem;
            color: #64748b;
            font-weight: 600;
            display: flex;
            align-items: center;
        }

        .meta-item i {
            color: var(--accent-blue);
            margin-right: 8px;
            width: 15px;
        }

        .filter-tabs {
            display: flex;
            gap: 10px;
            margin-bottom: 25px;
            overflow-x: auto;
            padding-bottom: 5px;
        }

        .btn-filter {
            background: white;
            border: 1px solid #e2e8f0;
            border-radius: 30px;
            padding: 6px 20px;
            font-size: 0.8rem;
            font-weight: 700;
            color: #64748b;
            white-space: nowrap;
        }

        .btn-filter.active {
            background: var(--accent-blue);
            color: white;
            border-color: var(--accent-blue);
        }

        .card-actions {
            margin-top: auto;
            display: flex;
            justify-content: space-between;
            gap: 10px;
        }

        .btn-card {
            border-radius: 10px;
            padding: 8px 15px;
            font-size: 0.85rem;
            font-weight: 700;
            transition: 0.3s;
            flex: 1;
        }

        .btn-detail {
            border: 2px solid #e2e8f0;
            color: #475569;
            background: transparent;
        }

        .btn-register {
            background: var(--accent-blue);
            color: white;
            border: none;
        }

        .btn-detail:hover {
            background-color: #f8fafc;
            border-color: var(--accent-blue);
            color: var(--accent-blue);
            transform: translateY(-2px);
            box-shadow: 0 4px 12px rgba(0, 0, 0, 0.05);
        }

        .btn-register:hover {
            background-color: var(--prof-blue) !important;
            transform: translateY(-2px);
            box-shadow: 0 6px 15px rgba(41, 100, 201, 0.3);
        }

        .btn-joined:disabled:hover {
            transform: none;
            cursor: not-allowed;
        }
    </style>
</head>

<body>

    <nav class="navbar">
        <div class="container-fluid px-5 d-flex align-items-center">
            <div class="d-flex align-items-center">
                <img src="UPTM Logo.png" alt="UPTM Logo" class="navbar-logo">
                <div class="brand-text">
                    <div class="logo-text">Charity<span class="logo-accent">Care+</span></div>
                </div>
            </div>
            <div class="slogan-text d-none d-lg-block">"Caring Together, Helping Each Other"</div>
            <div class="dashboard-label">Dashboard</div>
        </div>
    </nav>

    <div class="main-container">
        <aside class="sidebar">
            <div class="profile-section">
                <h6 class="fw-bold mb-1"><?php echo htmlspecialchars($student['full_name']); ?></h6>
                <small class="text-muted">Student ID: <?php echo htmlspecialchars($student['student_id']); ?></small>
            </div>
            <nav class="sidebar-menu">
                <a href="dashboard.php" class="menu-item active"><i class="fas fa-th-large"></i> Overview</a>
                <a href="profile.php" class="menu-item"><i class="fas fa-user-circle"></i> My Profile</a>
                <a href="myprograms.php" class="menu-item"><i class="fas fa-tasks"></i> My Programs</a>
                <a href="mycertificates.php" class="menu-item"><i class="fas fa-award"></i> My Certificates</a>
                <a href="logout.php" class="menu-item logout"><i class="fas fa-sign-out-alt"></i> Logout</a>
            </nav>
        </aside>

        <main class="content-area">
            <div class="search-wrapper">
                <i class="fas fa-search"></i>
                <input type="text" id="searchInput" placeholder="Search for campaigns..." onkeyup="filterPrograms()">
            </div>

            <div class="filter-tabs">
                <button class="btn-filter active" onclick="setFilter('All', this)">All Activity</button>
                <button class="btn-filter" onclick="setFilter('Volunteer', this)">Volunteers</button>
                <button class="btn-filter" onclick="setFilter('Donation', this)">Donations</button>
            </div>

            <div id="programsList">
                <?php while ($row = $program_result->fetch_assoc()): ?>
                    <div class="program-card" data-type="<?php echo $row['type']; ?>" data-title="<?php echo strtolower($row['title']); ?>">
                        
                        <?php 
                            $image_raw = $row['image_path'];
                            $final_img_path = "../Staff/" . ( (strpos($image_raw, 'uploads/') === false) ? 'uploads/' . $image_raw : $image_raw );
                        ?>
                        <img src="<?php echo $final_img_path; ?>" class="program-img" alt="Program" onerror="this.src='../Staff/default_campaign.jpg';">

                        <div class="program-body">
                            <small class="text-muted fw-bold text-uppercase" style="color: var(--accent-blue); font-size: 0.7rem; letter-spacing: 0.5px;"><?php echo htmlspecialchars($row['type']); ?></small>
                            <h3 class="program-title"><?php echo htmlspecialchars($row['title']); ?></h3>

                            <?php if ($row['type'] === 'Donation'): ?>
                                <?php
                                    $goal = (float)$row['goal_amount'];
                                    $current = (float)$row['current_amount'];
                                    $percent = ($goal > 0) ? min(100, round(($current / $goal) * 100)) : 0;
                                    $balance = max(0, $goal - $current);
                                ?>
                                <div class="donation-section">
                                    <div class="d-flex justify-content-between align-items-center">
                                        <span class="raised-text">RM<?php echo number_format($current, 2); ?></span>
                                        <span class="small fw-bold text-primary"><?php echo $percent; ?>%</span>
                                    </div>
                                    <div class="progress">
                                        <div class="progress-bar progress-bar-striped progress-bar-animated" style="width: <?php echo $percent; ?>%;"></div>
                                    </div>
                                    <div class="d-flex justify-content-between">
                                        <span class="small fw-bold" style="font-size: 0.75rem;">RM<?php echo number_format($balance, 2); ?> to reach goal</span>
                                        <span class="goal-text">Goal: RM<?php echo number_format($goal, 2); ?></span>
                                    </div>
                                </div>
                                
                            <?php elseif ($row['type'] === 'Volunteer' && $row['volunteer_slots'] > 0): ?>
                                <div class="donation-section">
                                    <span class="raised-text"><?php echo $row['total_joined']; ?> / <?php echo $row['volunteer_slots']; ?> Joined</span>
                                </div>
                            <?php endif; ?>

                            <div class="program-meta">
                                <div class="meta-item"><i class="far fa-calendar-alt"></i> <?php echo date('d M Y', strtotime($row['start_date'])); ?></div>
                                <div class="meta-item">
                                    <i class="far fa-clock"></i> 
                                    <?php 
                                        echo date('h:i A', strtotime($row['start_time'])); 
                                    ?>
                                </div>
                                <div class="meta-item"><i class="fas fa-user-tie"></i> <?php echo htmlspecialchars($row['organizer_name'] ?? 'UPTM Admin'); ?></div>
                            </div>

                            <div class="card-actions">
                                <?php 
                                    // NEW FIX: Securely translate the text so symbols and 'Enter' line breaks don't crash JS!
                                    $safe_title = htmlspecialchars(json_encode($row['title']), ENT_QUOTES, 'UTF-8');
                                    $safe_desc = htmlspecialchars(json_encode($row['description']), ENT_QUOTES, 'UTF-8');
                                ?>
                                
                                <button class="btn-card btn-detail" onclick='showDetail(<?php echo $safe_title; ?>, <?php echo $safe_desc; ?>)'>Details</button>

                                <?php 
                                    $is_full = false;
                                    if ($row['type'] === 'Volunteer' && $row['volunteer_slots'] > 0) {
                                        if ($row['total_joined'] >= $row['volunteer_slots']) {
                                            $is_full = true;
                                        }
                                    }
                                ?>

                                <?php if ($row['is_registered'] > 0 && $row['type'] === 'Volunteer'): ?>
                                    <button class="btn-card btn-joined shadow-sm" disabled style="background: #f1f5f9; color: #94a3b8; border: 1px solid #e2e8f0;">Joined</button>
                                
                                <?php elseif ($is_full): ?>
                                    <button class="btn-card shadow-sm" disabled style="background: #fee2e2; color: #ef4444; border: 1px solid #fca5a5; cursor: not-allowed;">
                                        Slot Full
                                    </button>
                                    
                                <?php else: ?>
                                    <button class="btn-card btn-register shadow-sm" onclick='openRegisterModal(<?php echo $row['id']; ?>, "<?php echo $row['type']; ?>", <?php echo $safe_title; ?>)'>
                                        <?php echo ($row['type'] == 'Donation') ? 'Donate' : 'Register'; ?>
                                    </button>
                                <?php endif; ?>
                            </div>
                        </div>
                    </div>
                <?php endwhile; ?>
            </div>
        </main>
    </div>

    <div class="modal fade" id="detailModal" tabindex="-1">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content shadow-lg p-3" style="border-radius: 20px;">
                <div class="modal-header border-0">
                    <h5 class="fw-bold" id="detailTitle"></h5><button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body text-muted" id="detailBody" style="line-height: 1.6; font-size: 0.9rem; white-space: pre-wrap;"></div>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    
    <?php if (isset($_SESSION['msg'])): ?>
        <script>alert("<?php echo $_SESSION['msg']; ?>");</script>
        <?php unset($_SESSION['msg']); ?>
    <?php endif; ?>

    <script>
        let currentFilter = 'All';
        function filterPrograms() {
            const search = document.getElementById('searchInput').value.toLowerCase();
            document.querySelectorAll('.program-card').forEach(card => {
                const title = card.getAttribute('data-title');
                const type = card.getAttribute('data-type');
                card.style.display = (title.includes(search) && (currentFilter === 'All' || type === currentFilter)) ? 'flex' : 'none';
            });
        }
        function setFilter(type, btn) {
            currentFilter = type;
            document.querySelectorAll('.btn-filter').forEach(b => b.classList.remove('active'));
            btn.classList.add('active');
            filterPrograms();
        }
        function showDetail(title, desc) {
            document.getElementById('detailTitle').innerText = title;
            document.getElementById('detailBody').innerText = desc;
            new bootstrap.Modal(document.getElementById('detailModal')).show();
        }
        function openRegisterModal(id, type, title) {
            window.location.href = "register_program.php?id=" + id + "&type=" + type;
        }
    </script>
</body>
</html>