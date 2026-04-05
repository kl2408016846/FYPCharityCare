<?php
session_start();
include 'db.php';

// 1. Admin Security Check: Ensure only authorized administrators can access the student directory
if (!isset($_SESSION['admin_id'])) {
    header("Location: admin_login.php");
    exit();
}

// 2. Handle Status Toggle Logic (Deactivate/Activate student accounts)
if (isset($_POST['toggle_status'])) {
    $s_id = mysqli_real_escape_string($conn, $_POST['student_id']);
    $current_status = $_POST['current_status'];
    $new_status = ($current_status == 'Active') ? 'Suspended' : 'Active';

    $update_sql = "UPDATE students SET account_status = '$new_status' WHERE student_id = '$s_id'";
    if (mysqli_query($conn, $update_sql)) {
        $_SESSION['msg'] = "Student " . $s_id . " account status updated to " . $new_status . ".";
    }
    header("Location: admin_student_list.php");
    exit();
}

// 3. Fetch Students data
$search = isset($_GET['search']) ? mysqli_real_escape_string($conn, $_GET['search']) : '';

// SQL query to retrieve student details including phone number
$sql = "SELECT s.student_id, s.full_name, s.email, s.phone, s.profile_img, s.account_status FROM students s";

// Append search filter if user has entered a keyword
if (!empty($search)) {
    $sql .= " WHERE s.full_name LIKE '%$search%' OR s.student_id LIKE '%$search%' OR s.email LIKE '%$search%' OR s.phone LIKE '%$search%'";
}

// Order by the most recent registration date
$sql .= " ORDER BY s.created_at DESC";

$students = mysqli_query($conn, $sql);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Student List | CharityCare+ Admin</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800&family=Dancing+Script:wght@600&display=swap" rel="stylesheet">
    
    <style>
        :root {
            --navy-blue: #1e3a8a;
            --navy-light: #e0e7ff;
            --soft-bg: #f8fafc;
            --text-dark: #1e293b;
        }

        body {
            background-color: var(--soft-bg);
            font-family: 'Inter', sans-serif;
            margin: 0;
            display: flex;
            flex-direction: column;
            min-height: 100vh;
        }

        .navbar {
            background: white;
            padding: 10px 50px;
            border-bottom: 1px solid #eee;
            display: flex;
            align-items: center;
            justify-content: space-between;
        }

        .navbar-logo {
            height: 48px;
        }

        .slogan {
            font-family: 'Dancing Script', cursive;
            color: var(--navy-blue);
            font-size: 1.2rem;
        }

        .brand-name {
            font-weight: 800;
            font-size: 1.5rem;
            color: #000;
            letter-spacing: -0.5px;
        }

        .brand-accent {
            color: var(--navy-blue);
        }

        .title-bar {
            background: var(--navy-blue);
            padding: 15px 50px;
            display: flex;
            justify-content: space-between;
            align-items: center;
        }

        .title-bar h5 {
            margin: 0;
            font-weight: 700;
            color: #ffffff;
            display: flex;
            align-items: center;
            gap: 15px;
        }

        .back-link {
            color: #ffffff;
            text-decoration: none;
            transition: 0.2s;
        }

        .back-link:hover {
            color: #d5d5d5;
            transform: translateX(-3px);
        }

        .btn-topbar {
            border: 2px solid #ffffff;
            color: #ffffff;
            border-radius: 30px;
            padding: 6px 25px;
            font-weight: 700;
            text-decoration: none;
            transition: 0.2s;
            background: transparent;
        }

        .btn-topbar:hover {
            color: var(--navy-blue);
            background: white;
        }

        .main-content {
            flex-grow: 1;
            padding: 50px;
            max-width: 1200px;
            margin: 0 auto;
            width: 100%;
        }

        .search-bar {
            border-radius: 30px;
            padding: 12px 25px;
            border: 1px solid #e2e8f0;
            width: 350px;
            outline: none;
            transition: 0.3s;
            font-weight: 500;
            font-size: 0.9rem;
        }

        .search-bar:focus {
            border-color: var(--navy-blue);
            box-shadow: 0 0 0 4px rgba(30, 58, 138, 0.1);
        }

        .table-container {
            background: white;
            border-radius: 35px;
            border: 1px solid #e2e8f0;
            box-shadow: 0 10px 30px rgba(0,0,0,0.03);
            padding: 40px;
            margin-bottom: 40px;
        }

        .table thead th {
            border: none;
            font-size: 0.85rem;
            text-transform: uppercase;
            letter-spacing: 0.5px;
            padding-bottom: 15px;
            border-bottom: 2px solid #e2e8f0;
            font-weight: 800;
        }

        .table tbody td {
            padding: 20px 10px;
            border-bottom: 1px solid #f8fafc;
            vertical-align: middle;
        }

        .table tbody tr:hover td {
            background-color: #fafbfc;
        }

        .table tbody tr:last-child td {
            border-bottom: none;
        }

        .student-avatar {
            width: 55px;
            height: 55px;
            border-radius: 50%;
            object-fit: cover;
            border: 2px solid #e2e8f0;
        }

        .student-name {
            font-size: 1.05rem;
            font-weight: 700;
            color: #0f172a;
            margin-bottom: 2px;
        }

        .student-matric {
            font-size: 0.8rem;
            color: #64748b;
            font-weight: 700;
            background: #f1f5f9;
            padding: 4px 10px;
            border-radius: 6px;
            display: inline-block;
        }

        .student-email {
            font-size: 0.9rem;
            color: #475569;
            font-weight: 500;
        }

        .status-badge {
            font-size: 0.75rem;
            font-weight: 700;
            padding: 6px 16px;
            border-radius: 20px;
            text-transform: uppercase;
            letter-spacing: 0.5px;
            display: inline-block;
        }

        .status-active {
            background: #dcfce7;
            color: #15803d;
            border: 1px solid rgba(21, 128, 61, 0.2);
        }

        .status-suspended {
            background: #fee2e2;
            color: #b91c1c;
            border: 1px solid rgba(185, 28, 28, 0.2);
        }
    </style>
</head>
<body>

    <nav class="navbar">
        <div class="d-flex align-items-center gap-3">
            <img src="UPTM Logo.png" alt="UPTM Logo" class="navbar-logo">
            <span class="slogan d-none d-md-block">"Caring Together, Helping Each Other"</span>
        </div>
        <div class="brand-name">Charity<span class="brand-accent">Care+</span></div>
    </nav>

    <div class="title-bar">
        <h5>
            <a href="admin_dashboard.php" class="back-link"><i class="fas fa-arrow-left"></i></a>
            Registered Students
        </h5>
        <div class="d-flex align-items-center gap-3">
            <a href="admin_profile.php" class="btn-topbar">Profile</a>
            <a href="logout.php" class="btn-topbar">Sign Out</a>
        </div>
    </div>

    <main class="main-content">
        <div class="d-flex justify-content-between align-items-end mb-5">
            <div>
                <h2 class="fw-800 m-0" style="color: #0f172a;">Student Directory</h2>
                <p class="text-muted fw-bold small mt-1">View student profiles and manage access.</p>
            </div>
            <form method="GET">
                <div class="position-relative">
                    <input type="text" name="search" class="search-bar" placeholder="Search name, ID or phone..." value="<?php echo htmlspecialchars($search); ?>">
                    <i class="fas fa-search position-absolute" style="right: 20px; top: 15px; color: #94a3b8;"></i>
                </div>
            </form>
        </div>

        <?php if (isset($_SESSION['msg'])): ?>
            <div class="alert alert-success bg-success bg-opacity-10 border-0 rounded-4 fw-bold text-success py-3 mb-4" style="border: 1px solid rgba(25, 135, 84, 0.2);">
                <i class="fas fa-check-circle me-2"></i> <?php echo $_SESSION['msg']; unset($_SESSION['msg']); ?>
            </div>
        <?php endif; ?>

        <div class="table-container">
            <div class="table-responsive">
                <table class="table table-borderless align-middle mb-0">
                    <thead>
                        <tr>
                            <th width="35%">STUDENT PROFILE</th>
                            <th width="20%">EMAIL ADDRESS</th>
                            <th width="15%" class="text-center">PHONE NUMBER</th>
                            <th width="15%" class="text-center">STATUS</th>
                            <th width="15%" class="text-end">ACTION</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if (mysqli_num_rows($students) > 0): ?>
                            <?php while($row = mysqli_fetch_assoc($students)): ?>
                            <tr>
                                <td>
                                    <div class="d-flex align-items-center gap-3">
                                        <?php 
                                            $avatar = !empty($row['profile_img']) ? '../student/' . $row['profile_img'] : 'https://cdn-icons-png.flaticon.com/512/149/149071.png'; 
                                        ?>
                                        <img src="<?php echo htmlspecialchars($avatar); ?>" alt="Avatar" class="student-avatar">
                                        <div>
                                            <div class="student-name"><?php echo htmlspecialchars($row['full_name']); ?></div>
                                            <div class="student-matric mt-1">ID: <?php echo htmlspecialchars($row['student_id']); ?></div>
                                        </div>
                                    </div>
                                </td>
                                <td>
                                    <div class="student-email">
                                        <?php echo htmlspecialchars($row['email']); ?>
                                    </div>
                                </td>
                                <td class="text-center">
                                    <div class="text-dark" style="font-size: 0.95rem;">
                                        <?php echo !empty($row['phone']) ? htmlspecialchars($row['phone']) : 'N/A'; ?>
                                    </div>
                                </td>
                                <td class="text-center">
                                    <?php $s_class = ($row['account_status'] == 'Active') ? 'status-active' : 'status-suspended'; ?>
                                    <span class="status-badge <?php echo $s_class; ?>">
                                        <?php echo htmlspecialchars($row['account_status']); ?>
                                    </span>
                                </td>
                                <td class="text-end">
                                    <form method="POST" class="m-0" onsubmit="return confirm('Are you sure you want to change this student\'s status?');">
                                        <input type="hidden" name="student_id" value="<?php echo htmlspecialchars($row['student_id']); ?>">
                                        <input type="hidden" name="current_status" value="<?php echo htmlspecialchars($row['account_status']); ?>">
                                        
                                        <?php if ($row['account_status'] == 'Active'): ?>
                                            <button type="submit" name="toggle_status" class="btn btn-sm btn-outline-danger fw-bold px-3 py-2 rounded-3" style="font-size: 0.8rem;">
                                                <i class="fas fa-ban me-1"></i> Suspend
                                            </button>
                                        <?php else: ?>
                                            <button type="submit" name="toggle_status" class="btn btn-sm btn-success fw-bold px-3 py-2 rounded-3 text-white" style="font-size: 0.8rem;">
                                                <i class="fas fa-check me-1"></i> Activate
                                            </button>
                                        <?php endif; ?>
                                    </form>
                                </td>
                            </tr>
                            <?php endwhile; ?>
                        <?php else: ?>
                            <tr><td colspan="5" class="text-center py-5 text-muted fw-bold">No students found.</td></tr>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </main>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>