<?php
session_start();
include 'db.php';

date_default_timezone_set('Asia/Kuala_Lumpur');

if (!isset($_SESSION['student_id'])) {
    header("Location: login.php");
    exit();
}

$student_id = $_SESSION['student_id'];
$program_id = $_GET['id'] ?? null;
$type = $_GET['type'] ?? null;

$student_query = "SELECT full_name FROM students WHERE student_id = '$student_id'";
$student_result = $conn->query($student_query);
$student_data = $student_result->fetch_assoc();

$p_query = "SELECT * FROM programs WHERE id = '$program_id'";
$program = $conn->query($p_query)->fetch_assoc();

$step = 1;
$selected_method = $_POST['payment_method'] ?? '';
$amount = $_POST['amount'] ?? 0;

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    if (isset($_POST['go_to_step2'])) {
        $step = 2;
    } elseif (isset($_POST['confirm_payment'])) {
        if ($type == 'Donation') {
            // Explicitly sets registration_type
            $update_sql = "UPDATE programs SET current_amount = current_amount + $amount WHERE id = '$program_id'";
            $insert_sql = "INSERT INTO registrations (student_id, program_id, registration_type, amount, status, payment_method, attendance_status) 
                           VALUES ('$student_id', '$program_id', 'Donation', '$amount', 'Completed', '$selected_method', NULL)";
            
            if ($conn->query($update_sql) && $conn->query($insert_sql)) {
                $step = 3;
                $trans_id = $conn->insert_id;
            }
        } else {
            // --- NEW: Backend Capacity Check ---
            // 1. Fetch the max slots and the current total joined for this specific program
            $check_query = "SELECT volunteer_slots, 
                           (SELECT COUNT(*) FROM registrations WHERE program_id = '$program_id' AND registration_type = 'Volunteer') as total_joined 
                           FROM programs WHERE id = '$program_id'";
            $check_result = $conn->query($check_query)->fetch_assoc();
            
            // 2. If it's a volunteer program with a limit, check if it's full
            if ($check_result['volunteer_slots'] > 0 && $check_result['total_joined'] >= $check_result['volunteer_slots']) {
                // System strictly rejects the registration
                echo "<script>
                        alert('Registration Failed: This volunteer program is already full!'); 
                        window.location.href='dashboard.php';
                      </script>";
                exit();
            }

            // 3. If space is available, proceed to save registration
            $insert_sql = "INSERT INTO registrations (student_id, program_id, registration_type, amount, status, attendance_status) 
                           VALUES ('$student_id', '$program_id', 'Volunteer', 0, 'Approved', 'Absent')";
            
            if ($conn->query($insert_sql)) {
                echo "<script>
                        alert('CharityCare+: Successfully registered as a volunteer! Your QR Code has been generated.'); 
                        window.location.href='myprograms.php';
                      </script>";
                exit();
            }
        }
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Support | CharityCare+</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <style>
        body { background: #f1f5f9; font-family: 'Inter', sans-serif; display: flex; align-items: center; justify-content: center; min-height: 100vh; padding: 20px; }
        .card-custom { background: white; padding: 40px; border-radius: 30px; box-shadow: 0 15px 40px rgba(0,0,0,0.08); width: 100%; max-width: 550px; }
        .step-indicator { font-size: 0.7rem; text-transform: uppercase; font-weight: 800; color: #1e3a8a; margin-bottom: 10px; display: block; letter-spacing: 1px; }
        .bank-login-box { background: #f8fafc; border: 1px solid #e2e8f0; padding: 20px; border-radius: 15px; margin-bottom: 20px; }
        .receipt-line { display: flex; justify-content: space-between; margin-bottom: 12px; border-bottom: 1px dashed #e2e8f0; padding-bottom: 8px; }
    </style>
</head>
<body>

    <div class="card-custom">
        <?php if ($type !== 'Donation'): ?>
            <h4 class="fw-bold mb-4 text-center">Join: <?php echo htmlspecialchars($program['title']); ?></h4>
            <p class="text-muted text-center mb-4">You are registering as a volunteer. Your attendance will be tracked via QR code.</p>
            <form method="POST">
                <button type="submit" name="confirm_payment" class="btn btn-primary w-100 py-3 rounded-pill fw-bold" style="background:#1e3a8a">Confirm Joining</button>
                <a href="dashboard.php" class="btn btn-link w-100 mt-2 text-muted small text-decoration-none text-center d-block">Cancel</a>
            </form>

        <?php else: ?>
            
            <?php if ($step == 1): ?>
                <span class="step-indicator">Step 1 of 3: Method & Amount</span>
                <h4 class="fw-bold mb-4">Supporting: <?php echo htmlspecialchars($program['title']); ?></h4>
                <form method="POST">
                    <div class="mb-3">
                        <label class="form-label fw-bold">Select Payment Method</label>
                        <select name="payment_method" class="form-select form-select-lg" required>
                            <option value="Online Banking">FPX Online Banking</option>
                            <option value="Credit/Debit Card">Credit / Debit Card</option>
                            <option value="E-Wallet">E-Wallet (TNG/Grab)</option>
                        </select>
                    </div>
                    <div class="mb-4">
                        <label class="form-label fw-bold">Amount (RM)</label>
                        <input type="number" step="0.01" name="amount" class="form-control form-control-lg" placeholder="10.00" required>
                    </div>
                    <button type="submit" name="go_to_step2" class="btn btn-primary w-100 py-3 rounded-pill fw-bold" style="background:#1e3a8a">Continue to Payment</button>
                    <a href="dashboard.php" class="btn btn-link w-100 mt-2 text-muted small text-decoration-none text-center d-block">Cancel</a>
                </form>

            <?php elseif ($step == 2): ?>
                <span class="step-indicator">Step 2 of 3: Verification</span>
                <h4 class="fw-bold mb-4 text-center">Payment via <?php echo $selected_method; ?></h4>
                <form method="POST">
                    <input type="hidden" name="payment_method" value="<?php echo $selected_method; ?>">
                    <input type="hidden" name="amount" value="<?php echo $amount; ?>">

                    <?php if ($selected_method == 'Online Banking'): ?>
                        <div class="bank-login-box text-start">
                            <label class="small fw-bold">Select Your Bank</label>
                            <select class="form-select mb-3" required><option value="">-- Choose Bank --</option><option>Maybank2u</option><option>CIMB Clicks</option><option>Bank Islam</option></select>
                            <input type="text" class="form-control mb-3" placeholder="Bank Username" required>
                            <input type="password" class="form-control mb-3" placeholder="Bank Password" required>
                            <small class="text-muted" style="font-size: 0.7rem;">Your bank credentials are not stored by CharityCare+.</small>
                        </div>
                    <?php elseif ($selected_method == 'Credit/Debit Card'): ?>
                        <div class="bank-login-box">
                            <input type="text" class="form-control mb-3" placeholder="0000 0000 0000 0000" required>
                            <div class="row">
                                <div class="col-6"><input type="text" class="form-control" placeholder="MM/YY" required></div>
                                <div class="col-6"><input type="text" class="form-control" placeholder="CVV" required></div>
                                <small class="text-muted" style="font-size: 0.7rem;">Your payment is processed through a secure gateway.</small>
                            </div>
                        </div>
                    <?php else: ?>
                        <div class="text-center py-4 bg-light rounded mb-4">
                            <i class="fas fa-qrcode fa-5x mb-2 text-primary"></i><p class="small fw-bold">Scan to pay with TNG/Grab</p>
                        </div>
                    <?php endif; ?>

                    <button type="submit" name="confirm_payment" class="btn btn-success w-100 py-3 rounded-pill fw-bold">Confirm RM<?php echo number_format($amount, 2); ?></button>
                    <a href="register_program.php?id=<?php echo $program_id; ?>&type=Donation" class="btn btn-link w-100 mt-2 text-muted small text-decoration-none text-center d-block">Back</a>
                </form>

            <?php elseif ($step == 3): ?>
                <div class="text-center mb-4"><div class="bg-success text-white rounded-circle d-inline-flex align-items-center justify-content-center mb-3" style="width: 50px; height: 50px;"><i class="fas fa-check"></i></div><h4 class="fw-bold">Payment Successful</h4></div>
                <div class="receipt-details mb-4">
                    <div class="receipt-line"><span>Transaction ID</span><span class="fw-bold">#CC-<?php echo $trans_id; ?></span></div>
                    <div class="receipt-line"><span>Donated By</span><span class="fw-bold"><?php echo htmlspecialchars($student_data['full_name']); ?></span></div>
                    <div class="receipt-line"><span>Program</span><span class="fw-bold text-end ms-3"><?php echo htmlspecialchars($program['title']); ?></span></div>
                    <div class="receipt-line"><span>Date</span><span class="fw-bold"><?php echo date('d M Y, h:i A'); ?></span></div>
                    <div class="d-flex justify-content-between mt-4 p-3 bg-light rounded-3"><span class="fw-bold">Total Contribution</span><span class="fw-bold text-primary">RM <?php echo number_format($amount, 2); ?></span></div>
                </div>
                <button class="btn btn-outline-secondary w-100 mb-2 rounded-pill" onclick="window.print()"><i class="fas fa-print me-2"></i>Print Receipt</button>
                <a href="dashboard.php" class="btn btn-primary w-100 py-3 rounded-pill fw-bold" style="background:#1e3a8a">Finish</a>
            <?php endif; ?>
        <?php endif; ?>
    </div>

</body>
</html>