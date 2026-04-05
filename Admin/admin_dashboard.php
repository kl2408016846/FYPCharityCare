<?php
session_start();
include 'db.php';

// 1. Session Check: Verify if admin is logged in
if (!isset($_SESSION['admin_id'])) {
    header("Location: admin_login.php");
    exit();
}

// 2. Fetch Systematic Statistics
$total_organizers = $conn->query("SELECT COUNT(*) as count FROM staff")->fetch_assoc()['count'] ?? 0;
$active_campaigns = $conn->query("SELECT COUNT(*) as count FROM programs WHERE end_date >= CURDATE()")->fetch_assoc()['count'] ?? 0;
$total_students = $conn->query("SELECT COUNT(*) as count FROM students")->fetch_assoc()['count'] ?? 0;
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Admin Dashboard | CharityCare+</title>
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

        .header-top { 
            display: flex; 
            justify-content: space-between; 
            align-items: center; 
            margin-bottom: 45px; 
        }

        .header-top h2 { 
            color: #0f172a; 
            letter-spacing: -0.5px; 
        }
        
        .stat-card { 
            border-radius: 20px; 
            padding: 35px 30px; 
            border: 1px solid #e2e8f0; 
            background: white; 
            text-align: center; 
            box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.02); 
        }

        .stat-blue { 
            background: var(--navy-blue); 
            color: white; 
            border: none; 
            box-shadow: 0 10px 25px rgba(30, 58, 138, 0.2); 
        }

        .stat-value { 
            font-size: 2.5rem; 
            font-weight: 800; 
            margin: 8px 0; 
            letter-spacing: -1px; 
        }

        .stat-label { 
            font-size: 0.75rem; 
            text-transform: uppercase; 
            letter-spacing: 1px; 
            opacity: 0.8; 
        }

        .menu-card {
            background: white;
            border-radius: 20px;
            padding: 40px 30px;
            text-align: center;
            border: 2px solid #e2e8f0;
            transition: 0.2s ease-in-out;
            height: 100%;
            cursor: pointer;
            text-decoration: none;
            display: block;
            color: var(--text-dark);
        }

        .menu-card:hover {
            border-color: var(--navy-blue);
            background-color: #f8fafc;
        }

        .menu-icon {
            width: 65px;
            height: 65px;
            background: var(--navy-light);
            color: var(--navy-blue);
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 1.6rem;
            margin: 0 auto 20px auto;
        }

        .menu-card h4 {
            font-weight: 800;
            margin-bottom: 8px;
            font-size: 1.15rem;
        }

        .menu-card p {
            color: #64748b;
            font-size: 0.9rem;
            margin: 0;
            font-weight: 500;
            line-height: 1.4;
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
        <h5>Admin Dashboard</h5>
        <div class="d-flex align-items-center gap-3">
            <a href="admin_profile.php" class="btn-topbar">Profile</a>
            <a href="logout.php" class="btn-topbar">Sign Out</a>
        </div>
    </div>

    <main class="main-content">
        <div class="header-top">
            <div>
                <h2 class="fw-800 m-0">System Overview</h2>
                <p class="text-muted fw-bold mt-1">Welcome back, Administrator.</p>
            </div>
        </div>

        <div class="row g-4 mb-5">
            <div class="col-md-4">
                <div class="stat-card stat-blue">
                    <div class="stat-label fw-bold">Active Campaigns</div>
                    <div class="stat-value"><?php echo $active_campaigns; ?></div>
                    <small class="fw-bold">Live Programs</small>
                </div>
            </div>
            
            <div class="col-md-4">
                <div class="stat-card">
                    <div class="stat-label fw-bold text-muted">Total Organizers</div>
                    <div class="stat-value text-dark"><?php echo number_format($total_organizers); ?></div>
                    <small class="text-muted fw-bold">Active Staff Accounts</small>
                </div>
            </div>
            
            <div class="col-md-4">
                <div class="stat-card">
                    <div class="stat-label fw-bold text-muted">Total Students</div>
                    <div class="stat-value text-dark"><?php echo number_format($total_students); ?></div>
                    <small class="text-muted fw-bold">Registered Accounts</small>
                </div>
            </div>
        </div>

        <h4 class="fw-800 text-dark mb-4 mt-2">System Management</h4>
        <div class="row g-4">
            
            <div class="col-md-4">
                <a href="admin_program_list.php" class="text-decoration-none">
                    <div class="menu-card">
                        <div class="menu-icon"><i class="fas fa-folder-open"></i></div>
                        <h4>Program List</h4>
                        <p>Oversee all donation and volunteer campaigns created by staff.</p>
                    </div>
                </a>
            </div>

            <div class="col-md-4">
                <a href="admin_organizer_list.php" class="text-decoration-none">
                    <div class="menu-card">
                        <div class="menu-icon"><i class="fas fa-users-cog"></i></div>
                        <h4>Organizer List</h4>
                        <p>View, manage, or suspend registered university staff and MPP members.</p>
                    </div>
                </a>
            </div>

            <div class="col-md-4">
                <a href="admin_student_list.php" class="text-decoration-none">
                    <div class="menu-card">
                        <div class="menu-icon"><i class="fas fa-user-graduate"></i></div>
                        <h4>Student List</h4>
                        <p>View and manage registered student accounts and platform activity.</p>
                    </div>
                </a>
            </div>

        </div>
    </main>

</body>
</html>