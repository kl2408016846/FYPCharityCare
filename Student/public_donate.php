<?php
session_start();
include 'db.php';

// 1. System Configuration
date_default_timezone_set('Asia/Kuala_Lumpur');

$program_id = $_GET['id'] ?? null;
if (!$program_id) {
    header("Location: home.php");
    exit();
}

// Fetch program details for public display
$p_query = "SELECT * FROM programs WHERE id = '$program_id' AND type = 'Donation'";
$program = $conn->query($p_query)->fetch_assoc();

if (!$program) {
    die("Error: This donation program is not active or available for public access.");
}

// 2. State Management
$step = $_POST['step'] ?? 1;
$donor = $_SESSION['public_donor'] ?? null;

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    if (isset($_POST['go_to_step2'])) {
        // Carry Step 1 data into session
        $_SESSION['public_donor'] = [
            'name'   => mysqli_real_escape_string($conn, $_POST['full_name']),
            'email'  => mysqli_real_escape_string($conn, $_POST['email']),
            'amount' => $_POST['amount'],
            'method' => $_POST['payment_method']
        ];
        $step = 2;
    } 
    elseif (isset($_POST['confirm_payment'])) {
        $donor = $_SESSION['public_donor'] ?? null;
        
        if ($donor && $donor['amount'] > 0) {
            $amt  = $donor['amount'];
            $name = $donor['name'];
            $meth = $donor['method'];

            // Update campaign funding progress
            $update_sql = "UPDATE programs SET current_amount = current_amount + $amt WHERE id = '$program_id'";
            
            // Record as Public Donation (student_id = NULL)
            $status_msg = "Public Donor: " . $name;
            $insert_sql = "INSERT INTO registrations (student_id, program_id, registration_type, amount, status, payment_method, registered_at) 
                           VALUES (NULL, '$program_id', 'Donation', '$amt', '$status_msg', '$meth', NOW())";

            if ($conn->query($update_sql) && $conn->query($insert_sql)) {
                $step = 3;
                $trans_id = $conn->insert_id;
            }
        } else {
            $step = 1;
        }
    }
    elseif (isset($_POST['back_to_step1'])) {
        $step = 1;
    }
}

// Refresh local donor variable from session
$donor = $_SESSION['public_donor'] ?? null;
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Donation | CharityCare+</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;600;700;800&display=swap" rel="stylesheet">
    <style>
        :root {
            --primary-blue: #1e3a8a;
            --soft-gray: #f8fafc;
            --border-color: #e2e8f0;
        }
        body { background: #f1f5f9; font-family: 'Inter', sans-serif; display: flex; align-items: center; justify-content: center; min-height: 100vh; padding: 20px; }
        .card-custom { background: white; padding: 45px; border-radius: 24px; box-shadow: 0 20px 50px rgba(0,0,0,0.05); width: 100%; max-width: 520px; }
        .step-indicator { font-size: 0.75rem; text-transform: uppercase; font-weight: 800; color: var(--primary-blue); margin-bottom: 12px; display: block; letter-spacing: 1.5px; }
        
        /* Receipt & Info Boxes */
        .info-box { background: var(--soft-gray); border: 1px solid var(--border-color); padding: 30px; border-radius: 16px; margin-bottom: 25px; text-align: left; }
        .receipt-card { border: 1px solid #cbd5e1; padding: 35px; border-radius: 12px; background: #ffffff; text-align: left; }
        
        .receipt-line { display: flex; justify-content: space-between; margin-bottom: 15px; font-size: 0.95rem; border-bottom: 1px solid #f1f5f9; padding-bottom: 8px; }
        .receipt-line:last-child { border-bottom: none; }

        .btn-main { background: var(--primary-blue); color: white; border-radius: 50px; padding: 14px; font-weight: 700; border: none; transition: 0.3s; width: 100%; }
        .btn-main:hover { background: #2964c9; color: white; transform: translateY(-2px); }
        .form-control { border-radius: 10px; padding: 12px; border: 1px solid var(--border-color); }
    </style>
</head>
<body>

    <div class="card-custom text-center">
        <form method="POST" action="public_donate.php?id=<?php echo $program_id; ?>">

            <?php if ($step == 1): ?>
                <span class="step-indicator">Step 1: Information</span>
                <h3 class="fw-800 mb-2">Thank you for your kindness!</h3>
                <p class="text-muted small mb-4">Supporting: <?php echo htmlspecialchars($program['title']); ?></p>
                
                <div class="text-start">
                    <label class="small fw-bold mb-1">Full Name</label>
                    <input type="text" name="full_name" class="form-control mb-3" placeholder="Enter your name" value="<?php echo htmlspecialchars($donor['name'] ?? ''); ?>" required>
                    
                    <label class="small fw-bold mb-1">Email Address</label>
                    <input type="email" name="email" class="form-control mb-3" placeholder="Enter email address" value="<?php echo htmlspecialchars($donor['email'] ?? ''); ?>" required>
                    
                    <label class="small fw-bold mb-1">Payment Method</label>
                    <select name="payment_method" class="form-select mb-3" required>
                        <option value="Online Banking">FPX Online Banking</option>
                        <option value="Credit/Debit Card">Credit / Debit Card</option>
                        <option value="E-Wallet">E-Wallet (TNG/Grab)</option>
                    </select>

                    <label class="small fw-bold mb-1">Donation Amount (RM)</label>
                    <input type="number" step="0.01" name="amount" class="form-control mb-4 fs-5 fw-bold" placeholder="0.00" value="<?php echo $donor['amount'] ?? ''; ?>" required>
                </div>
                
                <button type="submit" name="go_to_step2" class="btn btn-main">Continue to Payment</button>
                <a href="home.php" class="btn btn-link w-100 mt-2 text-muted small text-decoration-none">Cancel</a>

            <?php elseif ($step == 2 && $donor): ?>
                <span class="step-indicator">Step 2: Verification</span>
                <h3 class="fw-800 mb-4">Payment Security</h3>

                <div class="info-box">
                    <div class="d-flex justify-content-between mb-3">
                        <span class="small text-muted text-uppercase fw-bold">Method</span>
                        <span class="badge bg-primary"><?php echo $donor['method']; ?></span>
                    </div>
                    
                    <?php if ($donor['method'] == 'Online Banking'): ?>
                        <select class="form-select mb-3" required><option value="">Select Your Bank</option><option>Maybank2u</option><option>CIMB Clicks</option><option>Bank Islam</option></select>
                        <input type="text" class="form-control mb-2" placeholder="Bank Username" required>
                        <input type="password" class="form-control" placeholder="Password" required>
                    <?php elseif ($donor['method'] == 'Credit/Debit Card'): ?>
                        <input type="text" class="form-control mb-3" placeholder="Card Number (16 Digits)" required>
                        <div class="row"><div class="col-6"><input type="text" class="form-control" placeholder="MM/YY" required></div><div class="col-6"><input type="text" class="form-control" placeholder="CVV" required></div></div>
                    <?php else: ?>
                        <div class="text-center py-2">
                            <img src="https://api.qrserver.com/v1/create-qr-code/?size=150x150&data=CHARITYCARE_GUEST" alt="QR Code">
                            <p class="small fw-bold mt-2 text-muted">Scan the QR with TNG or GrabPay</p>
                        </div>
                    <?php endif; ?>
                </div>

                <button type="submit" name="confirm_payment" class="btn btn-success btn-main border-0" style="background: #15803d;">Confirm RM<?php echo number_format($donor['amount'], 2); ?></button>
                <button type="submit" name="back_to_step1" formnovalidate class="btn btn-link w-100 mt-2 text-muted small text-decoration-none">Back to Details</button>

            <?php elseif ($step == 3 && $donor): ?>
                <div class="mb-4">
                    <div class="text-success mb-3">
                        <i class="fas fa-check-circle fa-4x"></i>
                    </div>
                    <h3 class="fw-800">Donation Complete</h3>
                    <p class="text-muted">Thank you for your generous support.</p>
                </div>

                <div class="receipt-card mb-4">
                    <div class="text-center mb-4">
                        <h6 class="fw-800 m-0">CHARITYCARE+ RECEIPT</h6>
                        <p class="text-muted small">UPTM Campus Welfare Initiative</p>
                    </div>
                    
                    <div class="receipt-line"><span>Receipt ID:</span><span class="fw-bold">#G-<?php echo $trans_id; ?></span></div>
                    <div class="receipt-line"><span>Donor Name:</span><span class="fw-bold"><?php echo htmlspecialchars($donor['name']); ?></span></div>
                    <div class="receipt-line"><span>Date & Time:</span><span class="fw-bold"><?php echo date('d M Y, h:i A'); ?></span></div>
                    <div class="receipt-line"><span>Campaign:</span><span class="fw-bold"><?php echo htmlspecialchars($program['title']); ?></span></div>
                    
                    <div class="d-flex justify-content-between mt-4 pt-3 border-top">
                        <span class="fw-800 text-uppercase" style="font-size: 0.8rem;">Total Donation</span>
                        <span class="fw-800 fs-4 text-primary">RM <?php echo number_format($donor['amount'], 2); ?></span>
                    </div>
                </div>

                <button type="button" class="btn btn-outline-secondary w-100 rounded-pill mb-3 fw-bold" onclick="window.print()">
                    <i class="fas fa-print me-2"></i>Print Official Receipt
                </button>
                
                <a href="home.php" class="btn btn-main" onclick="<?php unset($_SESSION['public_donor']); ?>">Return to Homepage</a>
            <?php endif; ?>

        </form>
    </div>

</body>
</html>