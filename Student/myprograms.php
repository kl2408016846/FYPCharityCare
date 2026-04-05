<?php
session_start();
include 'db.php';

date_default_timezone_set('Asia/Kuala_Lumpur');

if (!isset($_SESSION['student_id'])) {
    header("Location: login.php");
    exit();
}

$current_student_id = $_SESSION['student_id'];

// Fetch student profile for sidebar
$student = $conn->query("SELECT * FROM students WHERE student_id = '$current_student_id'")->fetch_assoc();

// Fetch ALL programs joined (No time filter here to show history)
$my_programs_query = "SELECT p.*, s.full_name as organizer_name, r.status, r.attendance_status, r.registered_at, r.amount as donated_amount 
                      FROM programs p 
                      JOIN registrations r ON p.id = r.program_id 
                      LEFT JOIN staff s ON p.created_by = s.staff_id 
                      WHERE r.student_id = '$current_student_id' 
                      ORDER BY r.registered_at DESC";
$my_programs_result = $conn->query($my_programs_query);
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>My Programs | CharityCare+</title>
    
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
            background: linear-gradient(135deg, #fce7f3 0%, #e0f2fe 100%);;
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
        }

        .profile-section {
            text-align: center;
            padding-bottom: 30px;
            border-bottom: 1px solid #f1f5f9;
            margin-bottom: 25px;
        }

        .sidebar-menu {
            flex: 1;
            display: flex;
            flex-direction: column;
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
            margin-top: auto;
            color: var(--light-red);
        }

        .content-area {
            flex: 1;
            padding: 40px;
            overflow-y: auto;
            height: 100%;
        }

        .my-program-card {
            background: white;
            border-radius: 20px;
            padding: 25px;
            margin-bottom: 20px;
            border: 1px solid rgba(0, 0, 0, 0.05);
            box-shadow: 0 4px 12px rgba(0, 0, 0, 0.05);
        }

        .status-badge {
            padding: 6px 15px;
            border-radius: 30px;
            font-size: 0.8rem;
            font-weight: 700;
        }

        .attendance-badge {
            padding: 6px 15px;
            border-radius: 30px;
            font-size: 0.75rem;
            font-weight: 700;
            margin-left: 10px;
        }

        .btn-qr {
            background: #f8fafc;
            border: 1px solid #e2e8f0;
            border-radius: 12px;
            padding: 10px 20px;
            color: var(--prof-blue);
            font-weight: 700;
            font-size: 0.85rem;
            transition: 0.3s;
        }

        .btn-qr:hover {
            background: var(--prof-blue);
            color: white;
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
            <div class="dashboard-label">My Programs</div>
        </div>
    </nav>

    <div class="main-container">
        <aside class="sidebar">
            <div class="profile-section">
                <h6 class="fw-bold mb-1"><?php echo htmlspecialchars($student['full_name']); ?></h6>
                <small class="text-muted">Student ID: <?php echo htmlspecialchars($student['student_id']); ?></small>
            </div>
            <div class="sidebar-menu">
                <a href="dashboard.php" class="menu-item"><i class="fas fa-th-large"></i> Overview</a>
                <a href="profile.php" class="menu-item"><i class="fas fa-user-circle"></i> My Profile</a>
                <a href="myprograms.php" class="menu-item active"><i class="fas fa-tasks"></i> My Programs</a>
                <a href="mycertificates.php" class="menu-item"><i class="fas fa-award"></i> My Certificates</a>
                <a href="logout.php" class="menu-item logout"><i class="fas fa-sign-out-alt"></i> Logout</a>
            </div>
        </aside>

        <main class="content-area">
             <?php if ($my_programs_result->num_rows > 0): ?>
                <?php while ($row = $my_programs_result->fetch_assoc()): ?>
    <div class="my-program-card d-flex align-items-center justify-content-between">
        <div class="d-flex align-items-center gap-4">
            
            <?php 
                $image_raw = $row['image_path'];
                // Exit Student folder and enter Staff folder to find the image
                $final_img_path = "../Staff/" . ( (strpos($image_raw, 'uploads/') === false) ? 'uploads/' . $image_raw : $image_raw );
            ?>
            <img src="<?php echo $final_img_path; ?>" 
                 style="width: 110px; height: 110px; border-radius: 15px; object-fit: cover;" 
                 onerror="this.src='../Staff/default_campaign.jpg';">
            
            <div>
                <div class="d-flex align-items-center gap-2 mb-2">
                    <span class="badge" style="color: #434343"><?php echo $row['type']; ?></span>
                    <?php if ($row['type'] == 'Volunteer'): ?>
                        <span class="attendance-badge bg-<?php echo ($row['attendance_status'] == 'Present') ? 'success' : 'light text-muted'; ?>">
                            <i class="fas fa-check-circle me-1"></i> <?php echo $row['attendance_status']; ?>
                        </span>
                    <?php endif; ?>
                </div>

                <h4 class="fw-bold mb-1" style="color: var(--prof-blue);"><?php echo htmlspecialchars($row['title']); ?></h4>
                
                <?php if ($row['type'] == 'Donation'): ?>
                    <p class="mb-1 fw-bold" style="color: #2ecc71;">Amount Contributed: RM<?php echo number_format($row['donated_amount'], 2); ?></p>
                <?php endif; ?>

                <p class="small mb-2">Organizer: <?php echo !empty($row['organizer_name']) ? htmlspecialchars($row['organizer_name']) : 'UPTM Admin'; ?></p>
                <small class="text-muted d-block"><i class="far fa-calendar-alt me-2"></i><?php echo date('d M Y', strtotime($row['start_date'])); ?> | <?php echo date('h:i A', strtotime($row['start_time'])); ?></small>
            </div>
        </div>

        <div class="text-end">
            <?php if ($row['type'] == 'Volunteer' && $row['attendance_status'] == 'Absent'): ?>
                <button class="btn-qr shadow-sm mb-2" onclick="openAttendanceModal('<?php echo $row['id']; ?>', '<?php echo addslashes($row['title']); ?>')">
                    <i class="fas fa-qrcode me-2"></i> Attendance
                </button>
            <?php endif; ?>
            <div class="mt-2">
                <span class="status-badge bg-<?php echo ($row['status'] == 'Approved' || $row['status'] == 'Completed') ? 'success' : 'warning'; ?> text-white">
                    <?php echo $row['status']; ?>
                </span>
            </div>
        </div>
    </div>
<?php endwhile; ?>
            <?php else: ?>
                <div class="text-center p-5 bg-white rounded-5 shadow-sm">
                    <i class="fas fa-folder-open fa-3x mb-3 text-muted"></i>
                    <h4>You haven't joined any programs yet.</h4>
                    <a href="dashboard.php" class="btn btn-primary mt-3 rounded-pill px-4" style="background: var(--accent-blue); border:none;">Browse Now</a>
                </div>
            <?php endif; ?>
        </main>
    </div>

    <div class="modal fade" id="attendanceModal" tabindex="-1">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content rounded-5 border-0 shadow-lg">
                <div class="modal-header border-0 pt-4 px-4">
                    <h5 class="fw-bold" id="attendanceTitle">Verify Attendance</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body text-center p-5">
                    <div class="qr-placeholder mx-auto p-3 bg-light rounded-4 mb-4" style="width: 200px; height: 200px; border: 2px dashed #cbd5e1; display: flex; align-items: center; justify-content: center;">
                        <img id="qrImage" src="" alt="Scan Me" style="max-width: 100%; display: none;">
                        <i class="fas fa-qrcode fa-4x text-muted" id="qrIcon"></i>
                    </div>
                    <h6 class="fw-bold mb-1"><?php echo htmlspecialchars($student['full_name']); ?></h6>
                    <span class="badge bg-primary rounded-pill px-3"><?php echo htmlspecialchars($student['student_id']); ?></span>
                </div>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    
    <script>
        let poll;

function openAttendanceModal(programId, title) {
    const studentId = "<?php echo $current_student_id; ?>";
    const qrUrl = "https://api.qrserver.com/v1/create-qr-code/?size=200x200&data=" + 
                  encodeURIComponent("STUDENT:" + studentId + "|PROG:" + programId);
    
    document.getElementById('qrImage').src = qrUrl;
    document.getElementById('qrImage').style.display = "block";
    document.getElementById('qrIcon').style.display = "none";
    document.getElementById('attendanceTitle').innerText = title;

    // Start asking the server every 2 seconds
    poll = setInterval(() => {
        fetch(`check_status.php?s_id=${studentId}&p_id=${programId}`)
            .then(res => res.json())
            .then(data => {
                if (data.status === 'Present') {
                    clearInterval(poll); // Stop asking

                    // SUCCESS VALIDATION UI
                    document.getElementById('qrImage').style.display = "none";
                    document.getElementById('qrIcon').className = "fas fa-check-circle fa-5x text-success";
                    document.getElementById('qrIcon').style.display = "block";
                    document.getElementById('attendanceTitle').innerHTML = "<span class='text-success'>Attendance Verified!</span>";

                    // Refresh main page after 2 seconds so the list updates
                    setTimeout(() => { location.reload(); }, 2000);
                }
            });
    }, 2000);

    new bootstrap.Modal(document.getElementById('attendanceModal')).show();
}

// Stop checking if they close the modal early
document.getElementById('attendanceModal').addEventListener('hidden.bs.modal', () => clearInterval(poll));
    </script>
</body>

</html>