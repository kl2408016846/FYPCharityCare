<?php
include 'db.php'; 
session_start();

// Security check: Redirect if already logged in
if (isset($_SESSION['student_id'])) {
    header("Location: dashboard.php");
    exit();
}

$message = "";
$messageClass = "";

// Registration Logic
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $fullname = mysqli_real_escape_string($conn, $_POST['fullname']);
    $student_id = mysqli_real_escape_string($conn, $_POST['id_number']);
    $email = mysqli_real_escape_string($conn, $_POST['email']);
    $phone = mysqli_real_escape_string($conn, $_POST['phone']); // NEW: Get phone number
    $password = $_POST['password'];
    $confirm_password = $_POST['confirm_password'];

    // 1. Check if passwords match
    if ($password !== $confirm_password) {
        $message = "Passwords do not match!";
        $messageClass = "alert-danger";
    } 
    // 2. Validate Password Strength (8 chars, 1 Upper, 1 Lower, 1 Number, 1 Special)
    else if (
        strlen($password) < 8 || 
        !preg_match('@[A-Z]@', $password) || 
        !preg_match('@[a-z]@', $password) || 
        !preg_match('@[0-9]@', $password) || 
        !preg_match('@[^\w]@', $password)
    ) {
        $message = "Password does not meet the security requirements!";
        $messageClass = "alert-danger";
    }
    else {
        // 3. Securely hash the password
        $hashed_password = password_hash($password, PASSWORD_DEFAULT);

        // 4. Check for existing duplicates
        $checkQuery = "SELECT * FROM students WHERE student_id = '$student_id' OR email = '$email'";
        $result = $conn->query($checkQuery);

        if ($result->num_rows > 0) {
            $message = "Error: Student ID or Email already registered!";
            $messageClass = "alert-danger";
        } else {
            // 5. Insert into the database (Including phone number)
            $sql = "INSERT INTO students (student_id, full_name, email, phone, password) 
                    VALUES ('$student_id', '$fullname', '$email', '$phone', '$hashed_password')";

            if ($conn->query($sql) === TRUE) {
                echo "<script>alert('Registration Successful!'); window.location.href='login.php';</script>";
                exit();
            } else {
                $message = "Registration failed: " . $conn->error;
                $messageClass = "alert-danger";
            }
        }
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Sign Up | CharityCare+</title>
    
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
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            margin: 0;
            padding: 40px 20px;
        }

        .signup-card {
            background: white;
            padding: 45px;
            border-radius: 20px;
            box-shadow: 0 15px 35px rgba(0, 0, 0, 0.08);
            width: 100%;
            max-width: 750px; 
        }

        .brand-header { 
            text-align: center; 
            margin-bottom: 35px; 
        }

        .brand-header img { 
            width: 110px; 
            height: auto; 
            margin-bottom: 15px; 
        }

        .logo-text {
            font-size: 1.7rem;
            font-weight: 800;
            color: #000000;
            letter-spacing: -1px;
        }

        .logo-accent { 
            color: var(--accent-blue); 
        }

        .form-label { 
            font-weight: 600; 
            font-size: 0.9rem; 
            color: #475569; 
            margin-bottom: 6px; 
        }

        .form-control {
            padding: 12px 15px;
            border-radius: 10px;
            border: 1px solid #e2e8f0;
            background-color: #f8fafc;
            font-size: 0.95rem;
        }

        .form-control:focus {
            border-color: var(--accent-blue);
            background-color: #fff;
            box-shadow: 0 0 0 4px rgba(41, 100, 201, 0.1);
        }

        .req-notice {
            background-color: #f0f7ff;
            border-left: 4px solid var(--accent-blue);
            padding: 15px;
            border-radius: 8px;
            margin-bottom: 20px;
        }

        .req-notice ul {
            padding-left: 20px;
            margin-bottom: 0;
            font-size: 0.82rem;
            color: #1e40af;
        }

        .btn-register {
            background-color: var(--prof-blue);
            color: white;
            width: 100%;
            padding: 14px;
            border-radius: 10px;
            font-weight: 700;
            border: none;
            margin-top: 15px;
            transition: 0.3s;
            text-transform: uppercase;
            letter-spacing: 0.5px;
        }

        .btn-register:hover { 
            background-color: #172554; 
            transform: translateY(-1px); 
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

        .divider { 
            display: flex; 
            align-items: center; 
            text-align: center; 
            margin: 25px 0; 
            color: #cbd5e1; 
            font-weight: 600; 
            font-size: 0.8rem; 
        }

        .divider::before, .divider::after { 
            content: ''; 
            flex: 1; 
            border-bottom: 1px solid #e2e8f0; 
        }

        .divider:not(:empty)::before { 
            margin-right: 1em; 
        }

        .divider:not(:empty)::after { 
            margin-left: 1em; 
        }
    </style>
</head>
<body>

    <div class="signup-card">
        <div class="brand-header">
            <img src="UPTM Logo.png" alt="UPTM Logo">
            <div class="logo-text">Charity<span class="logo-accent">Care+</span></div>
            <p class="text-muted small mt-2">Create account</p>
        </div>

        <?php if ($message != ""): ?>
            <div class="alert <?php echo $messageClass; ?> py-2 px-3 small text-center mb-4 border-0 shadow-sm">
                <i class="fas fa-info-circle me-2"></i> <?php echo htmlspecialchars($message); ?>
            </div>
        <?php endif; ?>

        <form action="signup.php" method="POST">
            <div class="row g-3 mb-3">
                <div class="col-md-6">
                    <label class="form-label">Full Name</label>
                    <input type="text" class="form-control" name="fullname" placeholder="Enter your full name" required>
                </div>
                <div class="col-md-6">
                    <label class="form-label">Student ID</label>
                    <input type="text" class="form-control" name="id_number" placeholder="Enter your student ID" minlength="5" required>
                </div>
            </div>

            <div class="row g-3 mb-3">
                <div class="col-md-6">
                    <label class="form-label">UPTM Email Address</label>
                    <input type="email" class="form-control" name="email" placeholder="student@uptm.edu.my" required>
                </div>
                <div class="col-md-6">
                    <label class="form-label">Phone Number</label>
                    <input type="text" class="form-control" name="phone" placeholder="e.g. 012-3456789" required>
                </div>
            </div>

            <div class="row g-3 mb-4">
                <div class="col-md-6">
                    <label class="form-label">Create Password</label>
                    <input type="password" class="form-control" name="password" 
                           pattern="(?=.*\d)(?=.*[a-z])(?=.*[A-Z])(?=.*[\W_]).{8,}" 
                           title="Must contain at least 8 characters, including uppercase, lowercase, number, and special character." 
                           required>
                </div>
                <div class="col-md-6">
                    <label class="form-label">Confirm Password</label>
                    <input type="password" class="form-control" name="confirm_password" required>
                </div>
            </div>

            <div class="req-notice shadow-sm">
                <div class="fw-bold mb-1 small text-primary">Your password must include:</div>
                <ul>
                    <li>At least 8 characters long</li>
                    <li>At least ONE uppercase and ONE lowercase letter</li>
                    <li>At least ONE number and ONE special character (e.g., @, #, !)</li>
                </ul>
            </div>

            <button type="submit" class="btn-register shadow-sm">Register Account</button>
        </form>

        <div class="divider">OR</div>

        <div class="card-footer">
            <p class="text-muted">Already Register? <a href="login.php">Sign In Here</a></p>
            <a href="home.php" class="text-muted small">< Return to Homepage</a>
        </div>
    </div>

</body>
</html>