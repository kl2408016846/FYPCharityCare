<?php
session_start();
include 'db.php';

// 1. Security & Timezone
date_default_timezone_set('Asia/Kuala_Lumpur');
$conn->query("SET time_zone = '+08:00'");

if (!isset($_SESSION['staff_id'])) {
    header("Location: staff_login.php");
    exit();
}
$current_staff_id = $_SESSION['staff_id'];

// 2. Fetch Staff Details
$staff_query = "SELECT full_name FROM staff WHERE staff_id = '$current_staff_id'";
$staff_res = $conn->query($staff_query);
$staff_data = $staff_res->fetch_assoc();
$staff_name = $staff_data['full_name'] ?? "Organizer";

// 3 & 5 COMBINED: Fetch LATEST Volunteer Program and ITS specific volunteer count
$latest_vol_query = "SELECT id, title FROM programs 
                     WHERE created_by = '$current_staff_id' AND type = 'Volunteer' 
                     ORDER BY created_at DESC LIMIT 1";
$latest_vol_res = $conn->query($latest_vol_query);

$total_volunteers = 0;
$display_vol_title = "No Volunteer Programs";

if ($latest_vol_res && $latest_vol_res->num_rows > 0) {
    $latest_vol_data = $latest_vol_res->fetch_assoc();
    $display_vol_title = $latest_vol_data['title'];
    $latest_prog_id = $latest_vol_data['id'];

    // Now count registrations ONLY for this specific latest program
    $vol_count_query = "SELECT COUNT(id) as count FROM registrations 
                        WHERE program_id = '$latest_prog_id' AND registration_type = 'Volunteer'";
    $vol_count_res = $conn->query($vol_count_query);
    $total_volunteers = ($vol_count_res) ? $vol_count_res->fetch_assoc()['count'] : 0;
}

// 4. Fetch LATEST Donation Data
$latest_donation_query = "SELECT title, current_amount, goal_amount FROM programs 
                          WHERE created_by = '$current_staff_id' AND type = 'Donation' 
                          ORDER BY created_at DESC LIMIT 1";
$donation_res = $conn->query($latest_donation_query);
$donation_data = $donation_res->fetch_assoc();

$total_funds = $donation_data['current_amount'] ?? 0;
$goal_amount = $donation_data['goal_amount'] ?? 0;
$display_donation_title = $donation_data['title'] ?? "No Donation Campaign";

// Financial Logic
$percent = ($goal_amount > 0) ? min(100, round(($total_funds / $goal_amount) * 100)) : 0;
$balance = ($goal_amount > 0) ? max(0, $goal_amount - $total_funds) : 0;

// 6. Fetch Recent Programs for List Box
$my_progs_query = "SELECT title FROM programs WHERE created_by = '$current_staff_id' ORDER BY created_at DESC LIMIT 4";
$my_progs_result = $conn->query($my_progs_query);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Organizer Dashboard | CharityCare+</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <link href="https://fonts.googleapis.com/css2?family=Dancing+Script:wght@600&family=Inter:wght@400;600;700;800&display=swap" rel="stylesheet">

    <style>
        :root { 
            --prof-blue: #1e3a8a; 
            --accent-blue: #2964c9; 
            --light-red: #ff7675; 
        }

        body { 
            font-family: 'Inter', sans-serif; 
            background-color: #ffffff; 
            margin: 0; 
            display: flex; 
            flex-direction: column; 
            min-height: 100vh; 
            overflow-x: hidden; 
        }

        .navbar { 
            background: white; 
            padding: 10px 50px; 
            border-bottom: 1px solid #eee; 
            box-shadow: 0 2px 10px rgba(0,0,0,0.05); 
        }

        .navbar-logo { 
            height: 48px; 
        }

        .slogan { 
            font-family: 'Dancing Script', cursive; 
            color: var(--accent-blue); 
            font-size: 1.2rem; 
        }

        .brand-name { 
            font-weight: 800; 
            font-size: 1.5rem; 
            color: #000; 
            letter-spacing: -0.5px; 
        }

        .title-bar { 
            background: var(--accent-blue); 
            padding: 15px 50px; 
            display: flex; 
            justify-content: space-between; 
            align-items: center; 
        }

        .title-bar h5 { 
            margin: 0; 
            font-weight: 700; 
            color: #ffffff; 
        }

        .profile-link {
            color: #ffffff;
            transition: 0.1s;
            text-decoration: none;
        }

        .profile-link:hover {
            color: #d5d5d5;
            transform: translateX(-5px);
            display: inline-block;
        }

        .btn-logout { 
            border: 2px solid #ffffff; 
            color: #ffffff; 
            border-radius: 30px; 
            padding: 6px 25px; 
            font-weight: 700; 
            text-decoration: none; 
            transition: 0.3s; 
        }

        .btn-logout:hover { 
            color: var(--accent-blue); 
            background: white; 
        }

        .dashboard-container { 
            flex: 1; 
            display: grid; 
            grid-template-columns: repeat(3, 1fr); 
            gap: 30px; 
            padding: 40px 50px; 
            max-width: 1400px; 
            margin: 0 auto; 
        }

        .dashboard-box { 
            background: white; 
            border-radius: 35px; 
            padding: 40px 30px; 
            display: flex; 
            flex-direction: column; 
            box-shadow: 0 15px 35px rgba(0, 0, 0, 0.08); 
            height: 100%; 
        }

        .box-header { 
            color: var(--light-red); 
            font-weight: 800; 
            font-size: 0.85rem; 
            text-transform: uppercase; 
            letter-spacing: 1.5px; 
            margin-bottom: 5px; 
        }

        .prog-title-small { 
            color: var(--accent-blue); 
            font-weight: 700; 
            font-size: 1.1rem; 
            margin-bottom: 25px; 
            min-height: 2.5rem; 
            line-height: 1.3; 
        }

        .stat-large { 
            font-size: 4rem; 
            font-weight: 900; 
            color: var(--prof-blue); 
            margin-bottom: 2px; 
            letter-spacing: -2px; 
        }

        .stat-label { 
            font-size: 0.85rem; 
            color: #64748b; 
            font-weight: 600; 
            margin-bottom: 20px; 
        }

        .btn-box { 
            background: var(--light-red); 
            color: white; 
            border: none; 
            border-radius: 18px; 
            padding: 14px; 
            font-weight: 700; 
            text-align: center; 
            text-decoration: none; 
            margin-top: auto; 
            transition: 0.3s; 
        }

        .btn-box:hover { 
            background: #e66767; 
            transform: translateY(-2px); 
        }

        .program-list-item { 
            display: flex; 
            align-items: center; 
            padding: 12px 15px; 
            border-radius: 15px; 
            background: #f8fafc; 
            margin-bottom: 12px; 
            border-left: 4px solid var(--accent-blue); 
            font-size: 0.9rem; 
        }
    </style>
</head>
<body>

    <nav class="navbar d-flex align-items-center justify-content-between">
        <div class="d-flex align-items-center gap-3">
            <img src="UPTM Logo.png" alt="UPTM Logo" class="navbar-logo">
            <span class="slogan d-none d-md-block">"Caring Together, Helping Each Other"</span>
        </div>
        <div class="brand-name">Charity<span style="color: var(--accent-blue);">Care+</span></div>
    </nav>

    <div class="title-bar">
        <h5>Organizer Overview</h5>
        <div class="d-flex align-items-center">
            <a href="organizer_profile.php" class="profile-link me-4">
                <span class="staff-name d-none d-sm-inline me-2">
                    Welcome, <?php echo htmlspecialchars($staff_name); ?>
                </span>
                <i class="fas fa-user-circle fa-2x" style="vertical-align: middle;"></i>
            </a>
            <a href="logout.php" class="btn-logout">Sign Out</a>
        </div>
    </div>

    <div class="dashboard-container">
        <div class="dashboard-box">
            <div class="box-header">Volunteer Tracking</div>
            <div class="prog-title-small text-truncate"><?php echo htmlspecialchars($display_vol_title); ?></div>
            <div class="stat-large"><?php echo number_format($total_volunteers); ?></div>
            <div class="stat-label">Total Volunteers Registered</div>
            <a href="create_volunteer.php" class="btn-box">New Volunteer Program</a> 
        </div>

        <div class="dashboard-box">
            <div class="box-header">Donation Tracking</div>
            <div class="prog-title-small text-truncate"><?php echo htmlspecialchars($display_donation_title); ?></div>
            <div class="stat-large" style="color: var(--accent-blue);"><small style="font-size: 1.5rem;">RM</small><?php echo number_format($total_funds, 2); ?></div>
            <div class="stat-label">Total Funds Collected</div>
            <div class="mt-2 mb-4">
                <div class="d-flex justify-content-between mb-1">
                    <small class="fw-800" style="color: var(--light-red); font-size: 0.7rem;">RM <?php echo number_format($balance, 2); ?> to Goal</small>
                    <small class="text-muted" style="font-size: 0.7rem;">Goal: RM <?php echo number_format($goal_amount, 2); ?></small>
                </div>
            </div>
            <a href="create_donation.php" class="btn-box">New Donation Campaign</a>
        </div>

        <div class="dashboard-box">
            <div class="box-header">Program Management</div>
            <div class="prog-title-small">My Recent Activities</div>
            <div class="mt-2 flex-grow-1">
                <?php if ($my_progs_result->num_rows > 0): ?>
                    <?php while ($prog = $my_progs_result->fetch_assoc()): ?>
                        <div class="program-list-item">
                            <i class="fas fa-check-circle me-3 text-success"></i>
                            <strong class="text-truncate"><?php echo htmlspecialchars($prog['title']); ?></strong>
                        </div>
                    <?php endwhile; ?>
                <?php else: ?>
                    <div class="text-center py-5"><p class="text-muted small">No campaigns launched yet.</p></div>
                <?php endif; ?>
            </div>
            <a href="organizer_program.php" class="btn-box" style="background: var(--prof-blue);">Manage All Programs</a>
        </div>
    </div>

    <script>
        setTimeout(function() {
            let alert = document.querySelector('.alert');
            if (alert) {
                let bsAlert = new bootstrap.Alert(alert);
                bsAlert.close();
            }
        }, 4000);
    </script>
</body>
</html>