<?php
session_start();
include 'db.php';

// 1. Admin Security Check: Ensure only logged-in administrators can access this page
if (!isset($_SESSION['admin_id'])) {
    header("Location: admin_login.php");
    exit();
}

$admin_id = $_SESSION['admin_id'];

// 2. Handle Profile Update Logic: Processes changes to Name, Email, and Username
if (isset($_POST['update_profile'])) {
    $name = mysqli_real_escape_string($conn, $_POST['full_name']);
    $email = mysqli_real_escape_string($conn, $_POST['email']);
    $username = mysqli_real_escape_string($conn, $_POST['username']);

    $update_sql = "UPDATE admin SET full_name='$name', email='$email', username='$username' WHERE admin_id='$admin_id'";
    
    if (mysqli_query($conn, $update_sql)) {
        $_SESSION['admin_name'] = $name; // Sync session name with new database value
        $_SESSION['msg'] = "Profile details updated successfully.";
        $_SESSION['msg_type'] = "success";
    }
    header("Location: admin_profile.php");
    exit();
}

// 3. Handle Password Update Logic: Processes administrative password changes
if (isset($_POST['update_password'])) {
    $new_pass = $_POST['new_password'];
    $confirm_pass = $_POST['confirm_password'];

    // Basic validation to ensure both password fields match
    if ($new_pass === $confirm_pass) {
        $pass_sql = "UPDATE admin SET password='$new_pass' WHERE admin_id='$admin_id'";
        if (mysqli_query($conn, $pass_sql)) {
            $_SESSION['msg'] = "Password changed successfully.";
            $_SESSION['msg_type'] = "success";
        }
    } else {
        $_SESSION['msg'] = "Error: Passwords do not match. Please try again.";
        $_SESSION['msg_type'] = "danger";
    }
    header("Location: admin_profile.php");
    exit();
}

// 4. Fetch Current Admin Data: Retrieve fresh data to populate form fields
$sql_fetch = "SELECT * FROM admin WHERE admin_id='$admin_id'";
$result = mysqli_query($conn, $sql_fetch);
$admin_data = mysqli_fetch_assoc($result);
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <title>Admin Profile | CharityCare+</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800&family=Dancing+Script:wght@600&display=swap" rel="stylesheet">

    <style>
        :root {
            --navy-blue: #1e3a8a;
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

        /* Navbar Styling */
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

        /* Title Bar Styling */
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

        /* Main Content Layout */
        .main-content {
            flex-grow: 1;
            padding: 50px;
            background: #f8fafc;
            display: flex;
            flex-direction: column;
            align-items: center;
        }

        .profile-container {
            background: white;
            border-radius: 16px;
            border: 1px solid #e2e8f0;
            box-shadow: 0 4px 6px rgba(0, 0, 0, 0.05);
            padding: 40px;
            max-width: 850px;
            width: 100%;
        }

        /* Form Styling */
        .section-header {
            border-bottom: 2px solid #f1f5f9;
            margin-bottom: 25px;
            padding-bottom: 10px;
            color: #0f172a;
            font-weight: 800;
            font-size: 1.1rem;
        }

        .form-label {
            font-weight: 700;
            color: #475569;
            font-size: 0.85rem;
            margin-bottom: 8px;
            display: block;
        }

        .form-control {
            border-radius: 8px;
            padding: 12px 15px;
            border: 1px solid #cbd5e1;
            background-color: #f8fafc;
            font-weight: 500;
            color: #1e293b;
            margin-bottom: 20px;
        }

        .form-control:focus {
            border-color: var(--navy-blue);
            box-shadow: 0 0 0 3px rgba(30, 58, 138, 0.15);
            background-color: white;
        }

        .btn-save {
            background: var(--navy-blue);
            color: white;
            border-radius: 8px;
            padding: 12px 30px;
            font-weight: 700;
            border: none;
            transition: 0.3s;
        }

        .btn-save:hover {
            background: #172554;
        }

        .btn-dark-save {
            background: #1e293b;
            color: white;
            border-radius: 8px;
            padding: 12px 30px;
            font-weight: 700;
            border: none;
            transition: 0.3s;
        }

        .btn-dark-save:hover {
            background: #0f172a;
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
            Admin Profile
        </h5>
        <div class="d-flex align-items-center gap-3">
            <a href="logout.php" class="btn-topbar">Sign Out</a>
        </div>
    </div>

    <main class="main-content">

        <?php if (isset($_SESSION['msg'])): ?>
            <div class="alert <?php echo ($_SESSION['msg_type'] == 'danger') ? 'alert-danger bg-danger text-danger' : 'alert-success bg-success text-success'; ?> bg-opacity-10 border-0 rounded-3 fw-bold py-3 mb-4 w-100" style="max-width: 850px;">
                <i class="fas <?php echo ($_SESSION['msg_type'] == 'danger') ? 'fa-exclamation-circle' : 'fa-check-circle'; ?> me-2"></i>
                <?php
                echo $_SESSION['msg'];
                unset($_SESSION['msg']);
                unset($_SESSION['msg_type']);
                ?>
            </div>
        <?php endif; ?>

        <div class="profile-container">
            <div class="d-flex justify-content-between align-items-start mb-4">
                <div>
                    <h4 class="fw-800 m-0" style="color: #0f172a;">Account Settings</h4>
                    <p class="text-muted small fw-bold mt-1">Manage your administrative credentials.</p>
                </div>
            </div>

            <form method="POST" class="mb-5">
                <h6 class="section-header">Account Information</h6>
                <div class="row">
                    <div class="col-md-6">
                        <label class="form-label">Full Name</label>
                        <input type="text" name="full_name" class="form-control" value="<?php echo htmlspecialchars($admin_data['full_name']); ?>" required>
                    </div>
                    <div class="col-md-6">
                        <label class="form-label">Username</label>
                        <input type="text" name="username" class="form-control" value="<?php echo htmlspecialchars($admin_data['username']); ?>" required>
                    </div>
                    <div class="col-md-12">
                        <label class="form-label">Email Address</label>
                        <input type="email" name="email" class="form-control" value="<?php echo htmlspecialchars($admin_data['email']); ?>" required>
                    </div>
                </div>
                <div class="text-end mt-2">
                    <button type="submit" name="update_profile" class="btn-save">Update Details</button>
                </div>
            </form>

            <form method="POST">
                <h6 class="section-header">Security Settings</h6>
                <div class="row">
                    <div class="col-md-6">
                        <label class="form-label">New Password</label>
                        <input type="password" name="new_password" class="form-control" placeholder="Enter new password" required>
                    </div>
                    <div class="col-md-6">
                        <label class="form-label">Confirm New Password</label>
                        <input type="password" name="confirm_password" class="form-control" placeholder="Repeat new password" required>
                    </div>
                </div>
                <div class="text-end mt-2">
                    <button type="submit" name="update_password" class="btn-dark-save">Change Password</button>
                </div>
            </form>

        </div>
    </main>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>

</html>