<?php
include 'db.php';
use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

// Path to your vendor folder (Change this if your folder structure is different!)
require '../../vendor/autoload.php'; 

$message = "";
$messageClass = "";

if (isset($_POST['send_otp'])) {
    $email = mysqli_real_escape_string($conn, $_POST['email']);
    $otp = rand(100000, 999999); // Generate 6-digit OTP
    $expiry = date("Y-m-d H:i:s", strtotime("+10 minutes")); // Valid for 10 mins

    // Automated Search: Detect if Student or Staff
    $student = $conn->query("SELECT email FROM students WHERE email='$email'");
    $staff = $conn->query("SELECT email FROM staff WHERE email='$email'");

    if ($student->num_rows > 0) { $table = 'students'; }
    elseif ($staff->num_rows > 0) { $table = 'staff'; }
    else { $table = null; }

    if ($table) {
        // Save OTP to database
        $conn->query("UPDATE $table SET otp_code='$otp', otp_expiry='$expiry' WHERE email='$email'");

        $mail = new PHPMailer(true);
        try {
            // SMTP Settings
            $mail->isSMTP();
            $mail->Host       = 'smtp.gmail.com';
            $mail->SMTPAuth   = true;
            $mail->Username   = 'pluscharitycare@gmail.com';
            $mail->Password   = 'tcxmdkuukylmnwgw'; // Your 16-character App Password
            $mail->SMTPSecure = 'tls';
            $mail->Port       = 587;
            
            // Critical SSL Fix for XAMPP
            $mail->SMTPOptions = array('ssl' => array('verify_peer' => false, 'verify_peer_name' => false, 'allow_self_signed' => true));

            $mail->setFrom('pluscharitycare@gmail.com', 'CharityCare+ System');
            $mail->addAddress($email);
            $mail->isHTML(true);
            $mail->Subject = 'Password Recovery OTP';
            $mail->Body    = "Your OTP code is <b>$otp</b>. It expires in 10 minutes.";

            if($mail->send()) {
                // Success: Redirect to verification page
                header("Location: verify_otp.php?email=" . urlencode($email) . "&role=" . $table);
                exit();
            }
        } catch (Exception $e) {
            $message = "Failed to send email. Error: " . $mail->ErrorInfo; // Shows the real error
            $messageClass = "alert-danger";
        }
    } else {
        $message = "Email address not found.";
        $messageClass = "alert-danger";
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Forgot Password | CharityCare+</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        body { background: #f1f5f9; height: 100vh; display: flex; align-items: center; justify-content: center; font-family: 'Inter', sans-serif; }
        .card { border-radius: 20px; border: none; width: 400px; padding: 40px; box-shadow: 0 10px 25px rgba(0,0,0,0.05); }
        .btn-send { background: #1e3a8a; color: white; border-radius: 30px; font-weight: 700; width: 100%; border: none; padding: 12px; transition: 0.3s; }
        .btn-send:hover { background: #172554; }
    </style>
</head>
<body>
    <div class="card bg-white">
        <h4 class="fw-bold text-center mb-4">Recover Password</h4>
        
        <?php if($message): ?>
            <div class="alert <?php echo $messageClass; ?> small"><?php echo $message; ?></div>
        <?php endif; ?>

        <form method="POST">
            <div class="mb-4">
                <label class="small fw-bold text-muted mb-1">Enter Your Email</label>
                <input type="email" name="email" class="form-control rounded-3" placeholder="e.g. example@gmail.com" required>
            </div>
            <button type="submit" name="send_otp" class="btn-send">Send OTP Code</button>
            <div class="text-center mt-3">
                <a href="staff_login.php" class="small text-decoration-none text-muted">Back to Login</a>
            </div>
        </form>
    </div>
</body>
</html>