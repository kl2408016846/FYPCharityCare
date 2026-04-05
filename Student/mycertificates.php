<?php
session_start();
include 'db.php';

date_default_timezone_set('Asia/Kuala_Lumpur');

if (!isset($_SESSION['student_id'])) {
    header("Location: login.php");
    exit();
}

$current_student_id = $_SESSION['student_id'];

$student = $conn->query("SELECT * FROM students WHERE student_id = '$current_student_id'")->fetch_assoc();

$completed_programs_query = "SELECT p.title, p.start_date, r.registered_at, p.id as program_id 
                             FROM programs p 
                             JOIN registrations r ON p.id = r.program_id 
                             WHERE r.student_id = '$current_student_id' 
                             AND r.attendance_status = 'Present' 
                             ORDER BY p.start_date DESC";
$completed_programs_result = $conn->query($completed_programs_query);
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>My Certificates | CharityCare+</title>
    
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <link href="https://fonts.googleapis.com/css2?family=Dancing+Script:wght@600&family=Inter:wght@400;700;800&family=Montserrat:wght@400;700;800&family=Playfair+Display:ital,wght@0,400;0,700;1,400&display=swap" rel="stylesheet">

    <style>
        :root {
            --prof-blue: #1e3a8a;
            --accent-blue: #2964c9;
            --light-red: #ef4444;
            --gold: #d4af37;
            --dark-slate: #1e293b;
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

        .certificate-card {
            background-color: white;
            background-image: radial-gradient(rgba(212, 175, 55, 0.05) 2px, transparent 2px), radial-gradient(rgba(212, 175, 55, 0.05) 2px, transparent 2px);
            background-size: 32px 32px;
            background-position: 0 0, 16px 16px;
            width: 100%;
            max-width: 1050px;
            aspect-ratio: 1.414 / 1;
            margin: 0 auto 50px;
            position: relative;
            border: 8px solid #f8f9fa;
            outline: 2px solid var(--gold);
            outline-offset: -10px;
            padding: 50px 70px;
            display: flex;
            flex-direction: column;
            overflow: hidden;
            box-shadow: 0 10px 30px rgba(0,0,0,0.1);
        }

        .cert-watermark {
            position: absolute;
            top: 50%;
            left: 50%;
            transform: translate(-50%, -50%);
            opacity: 0.03;
            width: 50%;
            pointer-events: none;
            z-index: 0;
        }

        .cert-content-wrapper {
            position: relative;
            z-index: 1;
            display: flex;
            flex-direction: column;
            height: 100%;
        }

        .cert-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 25px;
        }

        .logo-left {
            display: flex;
            align-items: center;
        }

        .logo-right {
            display: flex;
            align-items: center;
            gap: 15px;
        }

        .logo-sec {
            height: 45px;
            width: auto;
            object-fit: contain;
        }

        .cert-body {
            text-align: center;
            flex-grow: 1;
            display: flex;
            flex-direction: column;
            justify-content: center;
        }

        .cert-main-title {
            font-family: 'Playfair Display', serif;
            font-size: 3.5rem;
            font-weight: 700;
            color: var(--prof-blue);
            margin: 0;
            letter-spacing: 2px;
        }

        .cert-given-text {
            font-family: 'Montserrat', sans-serif;
            font-size: 1rem;
            color: #64748b;
            margin-top: 10px;
            text-transform: uppercase;
            letter-spacing: 2px;
        }

        .cert-recipient-name {
            font-family: 'Montserrat', sans-serif;
            font-size: 2.2rem;
            font-weight: 800;
            color: #b8860b;
            margin: 15px auto;
            border-bottom: 2px solid var(--gold);
            display: inline-block;
            padding: 5px 50px;
            text-transform: uppercase;
            letter-spacing: 1px;
        }

        .cert-description {
            font-family: 'Montserrat', sans-serif;
            font-size: 1.1rem;
            color: #475569;
            margin-top: 15px;
            line-height: 1.8;
        }

        .cert-footer {
            display: flex;
            justify-content: space-between;
            align-items: flex-end;
            margin-top: 30px;
        }

        .auto-generated-note {
            font-family: 'Inter', sans-serif;
            font-size: 0.75rem;
            color: #94a3b8;
            font-style: italic;
            max-width: 300px;
            line-height: 1.4;
        }

        .verification-seal {
            width: 115px;
            height: 115px;
            background: radial-gradient(circle, var(--gold) 0%, #b8860b 100%);
            border-radius: 50%;
            display: flex;
            flex-direction: column;
            align-items: center;
            justify-content: center;
            border: 4px double white;
            box-shadow: 0 0 0 5px var(--gold), 0 8px 15px rgba(0,0,0,0.1);
            color: white;
            font-family: 'Montserrat', sans-serif;
        }

        .seal-check {
            font-size: 2rem;
            margin-bottom: -4px;
        }

        .seal-text {
            font-size: 0.6rem;
            font-weight: 800;
            letter-spacing: 1px;
            text-transform: uppercase;
        }

        .seal-id {
            font-size: 0.5rem;
            font-weight: 700;
            opacity: 0.9;
        }

        .btn-print-action {
            background: var(--accent-blue);
            color: white;
            border: none;
            padding: 10px 25px;
            border-radius: 30px;
            font-weight: 700;
            transition: 0.3s;
            margin-bottom: 20px;
        }

        .btn-print-action:hover {
            background: var(--prof-blue);
        }

        @media print {
            @page {
                size: landscape;
                margin: 0;
            }
            .navbar,
            .sidebar,
            .btn-print-wrapper,
            .dashboard-label {
                display: none !important;
            }
            body {
                background: white !important;
            }
            .main-container {
                height: auto !important;
                display: block !important;
            }
            .content-area {
                padding: 0 !important;
                overflow: visible !important;
            }
            .certificate-card {
                margin: 0 !important;
                box-shadow: none !important;
                max-width: none !important;
                border: 8px solid #f8f9fa !important;
                width: 100% !important;
                height: 100vh !important;
                page-break-after: always;
            }
            * {
                -webkit-print-color-adjust: exact !important;
                print-color-adjust: exact !important;
            }
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
            <div class="dashboard-label">My Certificates</div>
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
                <a href="myprograms.php" class="menu-item"><i class="fas fa-tasks"></i> My Programs</a>
                <a href="mycertificates.php" class="menu-item active"><i class="fas fa-award"></i> My Certificates</a>
                <a href="logout.php" class="menu-item logout"><i class="fas fa-sign-out-alt"></i> Logout</a>
            </div>
        </aside>

        <main class="content-area">
            <?php if ($completed_programs_result->num_rows > 0): ?>
                <?php while ($row = $completed_programs_result->fetch_assoc()): ?>

                    <div class="text-center btn-print-wrapper">
                        <button class="btn-print-action shadow-sm" onclick="window.print()">
                            <i class="fas fa-print me-2"></i> Download Certificate
                        </button>
                    </div>

                    <div class="certificate-card">
                        <img src="UPTM Logo.png" class="cert-watermark" alt="Watermark">
                        
                        <div class="cert-content-wrapper">
                            <div class="cert-header">
                                <div class="logo-left">
                                    <img src="UPTM Logo.png" class="logo-sec" alt="UPTM">
                                </div>
                                <div class="logo-right">
                                    <img src="Malaysia_Logo.svg" class="logo-sec" alt="Madani">
                                    <img src="MARA_Logo.png" class="logo-sec" alt="MARA">
                                    <img src="KPTM_Logo.png" class="logo-sec" alt="KPTM">
                                    <img src="MARA_Corp.png" class="logo-sec" alt="MARA Corp">
                                </div>
                            </div>

                            <div class="cert-body">
                                <h1 class="cert-main-title">Certificate of Participation</h1>
                                <p class="cert-given-text">This certificate is proudly awarded to</p>

                                <div class="cert-recipient-name">
                                    <?php echo htmlspecialchars($student['full_name']); ?>
                                </div>

                                <p class="cert-description">
                                    for successfully completing the volunteer program<br>
                                    <strong style="color: var(--dark-slate); font-size: 1.3rem;"><?php echo htmlspecialchars($row['title']); ?></strong><br>
                                    organized by <strong>CharityCare+ UPTM</strong> on <strong><?php echo date('d F Y', strtotime($row['start_date'])); ?></strong>.
                                </p>
                            </div>

                            <div class="cert-footer">
                                <div class="auto-generated-note">
                                    This is a computer-generated certificate created by the CharityCare+ system.
                                </div>
                                
                                <div class="verification-seal">
                                    <i class="fas fa-award seal-check"></i>
                                    <span class="seal-text">Verified</span>
                                    <span class="seal-id">#CC-<?php echo strtoupper(substr(md5($current_student_id . $row['program_id']), 0, 6)); ?></span>
                                </div>
                            </div>
                        </div>
                    </div>

                <?php endwhile; ?>
            <?php else: ?>
                <div class="text-center p-5 bg-white rounded-5 shadow-sm">
                    <i class="fas fa-award fa-3x mb-3 text-muted" style="opacity: 0.3;"></i>
                    <h4>No certificates earned yet.</h4>
                    <p class="text-muted">Join a program and ensure your attendance is marked 'Present' to unlock your certificate.</p>
                </div>
            <?php endif; ?>
        </main>
    </div>

</body>
</html>