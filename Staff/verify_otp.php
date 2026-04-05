<?php
include 'db.php';

// 1. Force Malaysia Timezone to match your system clock
date_default_timezone_set('Asia/Kuala_Lumpur');
$conn->query("SET time_zone = '+08:00'");

$email = $_GET['email'] ?? '';
$role = $_GET['role'] ?? ''; // Table name (students or staff)
$message = "";

if (isset($_POST['verify_otp'])) {
    // 2. Clean the input to remove accidental spaces
    $user_otp = mysqli_real_escape_string($conn, trim($_POST['otp']));

    // 3. Validation Query
    $query = "SELECT * FROM $role WHERE email='$email' AND otp_code='$user_otp' AND otp_expiry > NOW()";
    $result = $conn->query($query);

    if ($result->num_rows > 0) {
        // Success! Redirect with verified status
        header("Location: reset_password.php?email=$email&role=$role&status=verified");
        exit();
    } else {
        // 4. Enhanced Debugging
        $debug_check = $conn->query("SELECT otp_code, otp_expiry, NOW() as current_db_time FROM $role WHERE email='$email'");
        if ($row = $debug_check->fetch_assoc()) {
            if ($row['otp_code'] !== $user_otp) {
                $message = "Code mismatch. You entered: $user_otp but DB has: " . $row['otp_code'];
            } elseif ($row['otp_expiry'] <= $row['current_db_time']) {
                $message = "OTP expired. DB Now: " . $row['current_db_time'] . " | Expiry: " . $row['otp_expiry'];
            } else {
                $message = "Unknown error. Check database column types.";
            }
        } else {
            $message = "Email not found during verification.";
        }
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Verify OTP | CharityCare+</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        body { 
            background: #f1f5f9; 
            height: 100vh; 
            display: flex; 
            align-items: center; 
            justify-content: center; 
            font-family: 'Inter', sans-serif; 
        }

        .card { 
            border-radius: 20px; 
            border: none; 
            width: 100%; 
            max-width: 400px; 
            padding: 40px; 
            text-align: center; 
        }

        .otp-input { 
            letter-spacing: 15px; 
            font-size: 28px; 
            text-align: center; 
            font-weight: 800; 
            border-radius: 12px; 
        }

        .btn-verify { 
            background: #1e3a8a; 
            color: white; 
            border-radius: 30px; 
            font-weight: 700; 
            width: 100%; 
            padding: 12px; 
            border: none; 
            margin-top: 20px; 
            transition: 0.3s; 
        }

        .btn-verify:hover { 
            background: #172554; 
            transform: translateY(-1px); 
        }
    </style>
</head>
<body>
    <div class="card shadow-lg bg-white">
        <h4 class="fw-bold mb-1">Verify OTP</h4>
        <p class="text-muted small mb-4">Enter the 6-digit code sent to<br><b><?php echo htmlspecialchars($email); ?></b></p>

        <?php if($message): ?>
            <div class="alert alert-danger py-2 small" style="font-size: 0.75rem;"><?php echo $message; ?></div>
        <?php endif; ?>

        <form method="POST">
            <div class="mb-3">
                <input type="text" name="otp" class="form-control otp-input shadow-sm" maxlength="6" placeholder="000000" required autofocus autocomplete="off">
            </div>
            <button type="submit" name="verify_otp" class="btn-verify shadow-sm">Verify & Continue</button>
            <div class="mt-4">
                <a href="forgot_password.php" class="small text-decoration-none text-muted fw-bold">Didn't get the code? <span style="color: #1e3a8a;">Resend</span></a>
            </div>
        </form>
    </div>
</body>
</html>