<?php
include 'db.php';

// Force Malaysia Timezone to fix the "Expired" error
date_default_timezone_set('Asia/Kuala_Lumpur');
$conn->query("SET time_zone = '+08:00'");

// Get details from URL (Sent from verify_otp.php)
$email = $_GET['email'] ?? '';
$role = $_GET['role'] ?? '';
$status = $_GET['status'] ?? '';

$message = "";
$messageClass = "";

// SECURITY: Kick the user out if they didn't come from the OTP page
if ($status !== 'verified' || empty($email)) {
    header("Location: forgot_password.php");
    exit();
}

if (isset($_POST['change_password'])) {
    $password = $_POST['password'];
    $confirm_password = $_POST['confirm_password'];
    
    // 1. Complexity Validation
    $uppercase = preg_match('@[A-Z]@', $password);
    $lowercase = preg_match('@[a-z]@', $password);
    $number    = preg_match('@[0-9]@', $password);
    $specialChars = preg_match('@[^\w]@', $password);

    if (!$uppercase || !$lowercase || !$number || !$specialChars || strlen($password) < 8) {
        $message = "Password must be at least 8 characters and include uppercase, lowercase, a number, and a special character.";
        $messageClass = "alert-danger";
    } 
    elseif ($password !== $confirm_password) {
        $message = "Passwords do not match!";
        $messageClass = "alert-danger";
    } else {
        // 2. Hash and Update the Password
        $new_pass = password_hash($password, PASSWORD_DEFAULT);
        
        // We update the password and RESET the OTP so it can't be used again
        $update = "UPDATE $role SET password='$new_pass', otp_code=NULL, otp_expiry=NULL WHERE email='$email'";
        
        if ($conn->query($update)) {
            $message = "Success! Your password has been updated. You can now login.";
            $messageClass = "alert-success";
        } else {
            $message = "Error updating database.";
            $messageClass = "alert-danger";
        }
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Reset Password | CharityCare+</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <style>
        body { background: #f1f5f9; height: 100vh; display: flex; align-items: center; justify-content: center; font-family: 'Inter', sans-serif; }
        .card { border-radius: 20px; border: none; width: 100%; max-width: 400px; padding: 40px; }
        .btn-save { background: #2964c9; color: white; border-radius: 30px; font-weight: 700; width: 100%; border: none; padding: 12px; transition: 0.3s; }
        .btn-save:hover { background: #1e3a8a; }
        .requirement-list { font-size: 0.75rem; color: #64748b; margin-top: 10px; }
    </style>
</head>
<body>
    <div class="card shadow-lg">
        <div class="text-center mb-4">
            <h4 class="fw-bold">Set New Password</h4>
            <p class="text-muted small">Please choose a strong password for your account.</p>
        </div>

        <?php if($message != ""): ?>
            <div class="alert <?php echo $messageClass; ?> small text-center">
                <?php echo $message; ?> 
                <?php if($messageClass == "alert-success"): ?>
                    <br><a href="staff_login.php" class="fw-bold text-decoration-none">Login Now</a>
                <?php endif; ?>
            </div>
        <?php endif; ?>

        <?php if($messageClass != "alert-success"): ?>
        <form method="POST">
            <div class="mb-3">
                <label class="small fw-bold text-muted">New Password</label>
                <input type="password" name="password" class="form-control rounded-3" required>
                <div class="requirement-list">
                    Must include: 8+ chars, Uppercase, Number, & Special Char.
                </div>
            </div>
            <div class="mb-4">
                <label class="small fw-bold text-muted">Confirm New Password</label>
                <input type="password" name="confirm_password" class="form-control rounded-3" required>
            </div>
            <button type="submit" name="change_password" class="btn-save">Update Password</button>
        </form>
        <?php endif; ?>
    </div>
</body>
</html>