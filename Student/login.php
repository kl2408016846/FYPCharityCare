<?php
session_start();
include 'db.php'; 

// 1. Session Check: Redirect if already logged in
if (isset($_SESSION['student_id'])) {
    header("Location: dashboard.php");
    exit();
}

$message = "";
$messageClass = "";

// 2. Flash Message Handling (e.g., from signup.php or forgot_password.php)
if (isset($_SESSION['msg'])) {
    $message = $_SESSION['msg'];
    $messageClass = ($_SESSION['msg_type'] == 'success') ? "alert-success" : "alert-danger";
    
    unset($_SESSION['msg']);
    unset($_SESSION['msg_type']);
}

// 3. Authentication Logic
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $student_id = mysqli_real_escape_string($conn, $_POST['id']);
    $password = $_POST['password'];

    $sql = "SELECT * FROM students WHERE student_id = '$student_id'";
    $result = $conn->query($sql);

    if ($result->num_rows > 0) {
        $row = $result->fetch_assoc();
        
        if (password_verify($password, $row['password'])) {
            
            if (isset($row['account_status']) && $row['account_status'] == 'Suspended') {
                $message = "Access Denied: Your account has been suspended. Please contact the Administrator.";
                $messageClass = "alert-danger";
            } else {
                // If Active, log them in successfully
                $_SESSION['student_id'] = $row['student_id'];
                $_SESSION['full_name'] = $row['full_name'];
                $_SESSION['msg'] = "Login successful! Welcome back.";
                
                header("Location: dashboard.php");
                exit();
            }

        } else {
            $message = "Login failed: Invalid password.";
            $messageClass = "alert-danger";
        }
    } else {
        $message = "Login failed: No account found with that Student ID.";
        $messageClass = "alert-danger";
    }
}
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login | CharityCare+</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <style>
        :root {
            --prof-blue: #1e3a8a;
            --accent-blue: #2964c9;
            --light-red: #ef4444;
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
        }

        .divider {
            display: flex;
            align-items: center;
            text-align: center;
            margin: 20px 0;
            color: #cbd5e1;
        }

        .divider::before,
        .divider::after {
            content: '';
            flex: 1;
            border-bottom: 1px solid #e2e8f0;
        }

        .divider:not(:empty)::before {
            margin-right: .5em;
        }

        .divider:not(:empty)::after {
            margin-left: .5em;
        }

        .forgot-link-container {
            text-align: right;
            margin-top: 8px;
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

        .login-footer {
            text-align: center;
            margin-top: 25px;
            font-size: 0.85rem;
        }

        .login-footer a {
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
            <p class="text-muted small mt-2">Welcome back! Please login to your account.</p>
        </div>

        <?php if ($message != ""): ?>
            <div class="alert <?php echo $messageClass; ?> alert-dismissible fade show py-2 px-3 small text-center mb-4" role="alert">
                <i class="fas <?php echo ($messageClass == 'alert-success') ? 'fa-check-circle' : 'fa-exclamation-circle'; ?> me-1"></i>
                <?php echo htmlspecialchars($message); ?>
                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close" style="padding: 0.75rem;"></button>
            </div>
        <?php endif; ?>

        <form action="login.php" method="POST">
            <div class="mb-3">
                <label for="id" class="form-label">Student ID</label>
                <input type="text" class="form-control" id="id" name="id" placeholder="Enter your UPTM ID..." required>
            </div>
            
            <div class="mb-4">
                <label for="password" class="form-label">Password</label>
                <input type="password" class="form-control" id="password" name="password" placeholder="Enter password..." required>
                <div class="forgot-link-container">
                    <a href="forgot_password.php" class="forgot-link">Forgot Password?</a>
                </div>
            </div>
            
            <button type="submit" class="btn-login shadow-sm">Log In</button>
        </form>

        <div class="divider">OR</div>

        <div class="login-footer">
            <p class="text-muted">Don't have an account? <a href="signup.php">Create Account</a></p>
            <a href="home.php" class="text-muted">< Back to Homepage</a>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>

</html>