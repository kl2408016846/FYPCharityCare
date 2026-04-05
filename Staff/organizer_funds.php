<?php
session_start();
include 'db.php';

// 1. Timezone & Security Settings
date_default_timezone_set('Asia/Kuala_Lumpur');

// Redirect to login if the staff session is not active
if (!isset($_SESSION['staff_id'])) {
    header("Location: staff_login.php");
    exit();
}

// Get the program ID from the URL; redirect if missing
$program_id = $_GET['id'] ?? null;
if (!$program_id) {
    header("Location: organizer_program.php");
    exit();
}

// 2. Retrieve Program Financial Information from the database
$program_query = $conn->query("SELECT * FROM programs WHERE id = '$program_id'");
$program = $program_query->fetch_assoc();

// 3. Financial Calculations
$current_amount = $program['current_amount'];
$goal_amount = $program['goal_amount'];

// Calculate Balance: Determine how much more is needed (ensure it stops at 0)
$balance = max(0, $goal_amount - $current_amount);

// Calculate Percentage: Calculate progress towards the target goal
$calc_goal = $goal_amount > 0 ? $goal_amount : 1;
$percent = min(100, round(($current_amount / $calc_goal) * 100));
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <title>Funds Report | CharityCare+</title>
    
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <link href="https://fonts.googleapis.com/css2?family=Dancing+Script:wght@600&family=Inter:wght@400;600;700;800&display=swap" rel="stylesheet">

    <style>
        :root {
            --prof-blue: #1e3a8a;
            --accent-blue: #2964c9;
            --light-red: #ef4444;
        }

        body {
            font-family: 'Inter', sans-serif;
            background-color: #ffffff;
            margin: 0;
            min-height: 100vh;
        }

        /* Navigation Styling */
        .navbar {
            background: white;
            padding: 10px 50px;
            border-bottom: 1px solid #f1f5f9;
            display: flex;
            align-items: center;
            justify-content: space-between;
        }

        .navbar-logo {
            height: 48px;
        }

        .slogan {
            color: var(--accent-blue);
            font-family: 'Dancing Script', cursive;
            font-size: 1.2rem;
        }

        .brand-name {
            font-weight: 800;
            font-size: 1.5rem;
            color: #000;
            letter-spacing: -0.5px;
        }

        .brand-accent {
            color: var(--accent-blue);
        }

        /* Title Bar Styling */
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

        .back {
            color: #ffffff;
            text-decoration: none;
            font-weight: 600;
        }

        .back:hover {
            color: #d5d5d5;
            transform: translateX(-5px);
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

        /* Content Layout */
        .main-content {
            padding: 60px 50px;
            max-width: 1100px;
            margin: auto;
        }

        /* Statistical Card Styling */
        .stat-card {
            background: #f8fafc;
            border: 1px solid #e2e8f0;
            padding: 30px 20px;
            border-radius: 20px;
            text-align: center;
            height: 100%;
        }

        .stat-label {
            font-size: 0.75rem;
            font-weight: 800;
            color: #64748b;
            text-transform: uppercase;
            margin-bottom: 10px;
            letter-spacing: 1px;
        }

        .stat-value {
            font-size: 1.8rem;
            font-weight: 800;
            color: var(--prof-blue);
        }

        /* Visual Progress Components */
        .progress {
            height: 12px;
            border-radius: 20px;
            background-color: #f1f5f9;
            margin-top: 10px;
        }

        .progress-bar {
            background-color: var(--accent-blue) !important;
            border-radius: 20px;
        }

        /* Action Buttons */
        .btn-theme {
            background: var(--accent-blue);
            color: #fff;
            border-radius: 12px;
            font-weight: 700;
            padding: 12px 40px;
            text-decoration: none;
            border: none;
        }

        /* Print Media Adjustments */
        @media print {
            .navbar,
            .title-bar,
            .btn-section {
                display: none !important;
            }

            .main-content {
                padding: 0;
                max-width: 100%;
            }

            body {
                background: white;
            }

            .stat-card {
                border: 1px solid #000;
            }
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
        <h5>Funds Report</h5>
        <div class="d-flex align-items-center gap-4">
            <a href="logout.php" class="btn-logout">Sign Out</a>
        </div>
    </div>

    <main class="main-content">
        <div class="text-center mb-5">
            <h2 class="fw-800" style="color: var(--prof-blue);"><?php echo htmlspecialchars($program['title']); ?></h2>
            <p class="text-muted fw-bold">Systematic Financial Summary</p>
            <hr style="width: 50px; border: 2px solid var(--accent-blue); margin: 20px auto;">
        </div>

        <div class="row g-4">
            <div class="col-md-4">
                <div class="stat-card">
                    <div class="stat-label">Total Collected</div>
                    <div class="stat-value">RM <?php echo number_format($current_amount, 2); ?></div>
                </div>
            </div>
            
            <div class="col-md-4">
                <div class="stat-card">
                    <div class="stat-label">Remaining Balance</div>
                    <div class="stat-value" style="color: var(--accent-blue);">RM <?php echo number_format($balance, 2); ?></div>
                </div>
            </div>

            <div class="col-md-4">
                <div class="stat-card">
                    <div class="stat-label">Target Goal</div>
                    <div class="stat-value" style="color: #64748b;">RM <?php echo number_format($goal_amount, 2); ?></div>
                </div>
            </div>
        </div>

        <div class="mt-5 px-2">
            <div class="d-flex justify-content-between mb-2">
                <span class="fw-800 small text-muted">COLLECTION PROGRESS</span>
                <span class="fw-800 small" style="color: var(--accent-blue);"><?php echo $percent; ?>% Complete</span>
            </div>
            <div class="progress">
                <div class="progress-bar progress-bar-striped progress-bar-animated" style="width: <?php echo $percent; ?>%"></div>
            </div>
        </div>

        <div class="btn-section text-center mt-5 d-flex justify-content-center gap-3">
            <button onclick="window.print()" class="btn-theme shadow-sm">
                <i class="fas fa-print me-2"></i> Print Report
            </button>
            <a href="organizer_program.php" class="btn btn-outline-primary rounded-pill px-5 fw-bold" style="padding-top: 10px; border-width: 2px;">Finish View</a>
        </div>
    </main>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>

</html>