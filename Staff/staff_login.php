<?php
session_start();
include 'db.php';

// Skip if already logged in as staff
if (isset($_SESSION['staff_id'])) {
    header("Location: organizer_dashboard.php");
    exit();
}

$message = "";
$messageClass = "";

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $staff_id = mysqli_real_escape_string($conn, $_POST['staff_id']);
    $password = $_POST['password'];

    // Verify against staff table
    $sql = "SELECT * FROM staff WHERE staff_id = '$staff_id'";
    $result = $conn->query($sql);

    if ($result->num_rows > 0) {
        $row = $result->fetch_assoc();

        // Check password (supports both hashed and plain text for FYP testing)
        if (password_verify($password, $row['password']) || $password === $row['password']) {
            
            // NEW: Account Status Check
            if (isset($row['account_status']) && $row['account_status'] === 'Suspended') {
                $message = "Your account has been suspended by the Admin. Please contact the office.";
                $messageClass = "alert-danger";
            } else {
                // Success: Store Staff Session
                $_SESSION['staff_id'] = $row['staff_id'];
                $_SESSION['full_name'] = $row['full_name'];
                $_SESSION['dept'] = $row['department'];

                // NEW: Success Validation Popup before redirect
                echo "<script>
                    alert('Login Successful! Welcome back, " . addslashes($row['full_name']) . ".');
                    window.location.href = 'organizer_dashboard.php';
                </script>";
                exit();
            }
            
        } else {
            $message = "Invalid password. Please try again.";
            $messageClass = "alert-danger";
        }
    } else {
        $message = "No organizer account found in the system.";
        $messageClass = "alert-danger";
    }
}
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Staff Login | CharityCare+</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <style>
        :root {
            --prof-blue: #1e3a8a;
            --accent-blue: #2964c9;
            --soft-bg: #f1f5f9;
        }

        body {
            font-family: 'Inter', sans-serif;
            background-color: var(--soft-bg);
            height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            margin: 0;
        }

        .login-card {
            background: white;
            padding: 40px;
            border-radius: 15px;
            box-shadow: 0 10px 25px rgba(0, 0, 0, 0.05);
            width: 100%;
            max-width: 400px;
        }

        .login-brand {
            text-align: center;
            margin-bottom: 30px;
        }

        .login-brand img {
            width: 100px;
            height: auto;
            margin-bottom: 15px;
        }

        .logo-text {
            font-size: 1.5rem;
            font-weight: 800;
            color: #000;
            letter-spacing: -1px;
        }

        .logo-accent {
            color: var(--accent-blue);
        }

        .form-label {
            font-weight: 600;
            font-size: 0.9rem;
            color: #475569;
        }

        .form-control {
            padding: 12px;
            border-radius: 8px;
            border: 1px solid #e2e8f0;
        }

        .forgot-link-container {
            text-align: right;
            margin-top: 5px;
        }

        .forgot-link {
            font-size: 0.8rem;
            color: #64748b;
            text-decoration: none;
            font-weight: 600;
        }

        .forgot-link:hover {
            color: var(--accent-blue);
        }

        .btn-login {
            background-color: var(--prof-blue);
            color: white;
            width: 100%;
            padding: 12px;
            border-radius: 8px;
            font-weight: bold;
            border: none;
            margin-top: 10px;
            transition: 0.3s;
        }

        .btn-login:hover {
            background-color: #172554;
            color: white;
        }

        .card-footer {
            text-align: center;
            margin-top: 30px;
            font-size: 0.9rem;
        }

        .card-footer a {
            color: var(--accent-blue);
            text-decoration: none;
            font-weight: 600;
        }
    </style>
</head>

<body>
    <div class="login-card shadow-lg">
        <div class="login-brand">
            <img src="UPTM Logo.png" alt="UPTM Logo">
            <div class="logo-text">Charity<span class="logo-accent">Care+</span></div>
            <p class="text-muted small mt-2">Organizer Portal - Login to Staff/Organizer Account.</p>
        </div>

        <?php if ($message != ""): ?>
            <div class="alert <?php echo $messageClass; ?> py-2 px-3 small text-center mb-4 border-0 rounded-3 shadow-sm fw-bold">
                <i class="fas fa-exclamation-circle me-1"></i>
                <?php echo htmlspecialchars($message); ?>
            </div>
        <?php endif; ?>

        <form method="POST">
            <div class="mb-3">
                <label class="form-label">Staff ID</label>
                <input type="text" class="form-control" name="staff_id" placeholder="Enter your Staff ID..." required>
            </div>
            <div class="mb-4">
                <label class="form-label">Password</label>
                <input type="password" class="form-control" name="password" placeholder="Enter password..." required>
                <div class="forgot-link-container">
                    <a href="forgot_password.php" class="forgot-link">Forgot Password?</a>
                </div>
            </div>
            <button type="submit" class="btn-login shadow-sm">Log In</button>
        </form>

        <div class="card-footer bg-transparent border-0 p-0">
            <p class="text-muted mb-2">Not a registered organizer? <a href="staff_signup.php">Create Account</a></p>
            <a href="../Student/home.php" class="text-muted small">< Return to Homepage</a>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>

</html>