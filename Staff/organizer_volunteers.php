<?php
session_start();
include 'db.php';

// 1. Timezone & Security Settings
date_default_timezone_set('Asia/Kuala_Lumpur');

// Redirect to login if the staff session is not active
if (!isset($_SESSION['staff_id'])) {
    header("Location: staff_login.php");
    exit();
}

$program_id = $_GET['id'] ?? null;
if (!$program_id) {
    die("Program ID missing.");
}

// 2. Auto-Attendance Logic via Scanner Input
if (isset($_POST['scanner_input']) && !empty($_POST['scanner_input'])) {
    $raw_data = trim($_POST['scanner_input']);
    
    // Logic to handle specific QR format: STUDENT:ID|PROG:ID
    if (strpos($raw_data, 'STUDENT:') !== false) {
        $parts = preg_split('/[:|]/', $raw_data);
        $student_id = mysqli_real_escape_string($conn, $parts[1]);
        $scanned_program_id = mysqli_real_escape_string($conn, $parts[3]);
        
        // Security check: Ensure the QR is for the current program
        if ($scanned_program_id != $program_id) {
            $_SESSION['msg'] = "Error: QR is for a different program.";
            $_SESSION['msg_type'] = "danger";
            header("Location: organizer_volunteers.php?id=" . $program_id);
            exit();
        }
    } else {
        $student_id = mysqli_real_escape_string($conn, $raw_data);
    }
    
    // Update status to 'Present' for the specific student and program
    $update_sql = "UPDATE registrations 
                   SET attendance_status = 'Present' 
                   WHERE student_id = '$student_id' 
                   AND program_id = '$program_id' 
                   AND registration_type = 'Volunteer'";
    
    if ($conn->query($update_sql)) {
        if ($conn->affected_rows > 0) {
            $_SESSION['msg'] = "Attendance marked for ID: $student_id";
            $_SESSION['msg_type'] = "success";
        } else {
            $_SESSION['msg'] = "ID $student_id not found or not registered for this program.";
            $_SESSION['msg_type'] = "danger";
        }
    }
    // Redirect to prevent form resubmission
    header("Location: organizer_volunteers.php?id=" . $program_id);
    exit();
}

// 3. Student Removal Logic
if (isset($_POST['remove_student'])) {
    $reg_id = $_POST['registration_id'];
    $delete_sql = "DELETE FROM registrations WHERE id = '$reg_id' AND registration_type = 'Volunteer'";
    
    if ($conn->query($delete_sql)) {
        $_SESSION['msg'] = "Student removed successfully.";
        $_SESSION['msg_type'] = "success";
    }
    header("Location: organizer_volunteers.php?id=" . $program_id);
    exit();
}

// 4. Data Retrieval for Volunteer List (JOIN with students table to get name & phone)
$query = "SELECT r.*, s.full_name, s.phone 
          FROM registrations r 
          JOIN students s ON r.student_id = s.student_id 
          WHERE r.program_id = '$program_id' AND r.registration_type = 'Volunteer' 
          ORDER BY r.attendance_status DESC, r.id DESC";
$volunteers = $conn->query($query);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Volunteer Management | CharityCare+</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <link href="https://fonts.googleapis.com/css2?family=Dancing+Script:wght@600&family=Inter:wght@400;600;700;800&display=swap" rel="stylesheet">
    
    <style>
        :root { 
            --prof-blue: #1e3a8a; 
            --accent-blue: #2964c9; 
        }

        body { 
            font-family: 'Inter', sans-serif; 
            background-color: #ffffff; 
            margin: 0; 
            min-height: 100vh; 
        }

        .navbar { 
            background: white; 
            padding: 10px 50px; 
            border-bottom: 1px solid #f1f5f9; 
            display: flex; 
            align-items: center; 
            justify-content: space-between; 
        }

        .navbar-logo { 
            height: 45px; 
            width: auto; 
        }

        .slogan { 
            color: var(--accent-blue); 
            font-family: 'Dancing Script', cursive; 
            font-size: 1.2rem; 
        }

        .brand-name { 
            font-weight: 800; 
            font-size: 1.5rem; 
            color: #000; 
            letter-spacing: -0.5px; 
        }

        .brand-accent { 
            color: var(--accent-blue); 
        }

        .title-bar { 
            background: var(--accent-blue); 
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

        /* Back Arrow Styling */
        .back-link {
            color: #ffffff;
            text-decoration: none;
            transition: 0.2s;
            margin-right: 15px;
        }

        .back-link:hover {
            color: #dbeafe;
            transform: translateX(-3px);
        }

        .btn-logout { 
            border: 2px solid #ffffff; 
            color: #ffffff; 
            border-radius: 30px; 
            padding: 6px 25px; 
            font-weight: 700; 
            text-decoration: none; 
            transition: 0.3s; 
        }

        .btn-logout:hover { 
            color: var(--accent-blue); 
            background: white; 
        }

        .main-content { 
            padding: 60px 50px; 
            max-width: 1200px; 
            margin: auto; 
        }

        /* Hidden input for scanner to target */
        .scanner-box { 
            opacity: 0; 
            position: absolute; 
            pointer-events: none; 
        }

        .table thead th { 
            border: none; 
            color: #94a3b8; 
            font-size: 0.7rem; 
            text-transform: uppercase; 
            letter-spacing: 1px; 
            padding-bottom: 15px; 
            border-bottom: 2px solid #f1f5f9; 
        }

        .table tbody td { 
            padding: 20px 0; 
            border-bottom: 1px solid #f1f5f9; 
            vertical-align: middle;
        }

        .status-pill { 
            font-size: 0.7rem; 
            font-weight: 800; 
            padding: 5px 14px; 
            border-radius: 8px; 
            text-transform: uppercase; 
            border: 1px solid currentColor; 
            background: transparent; 
        }

        .link-delete { 
            color: #ef4444; 
            font-size: 0.8rem; 
            font-weight: 700; 
            text-decoration: none; 
            transition: 0.2s; 
        }

        .link-delete:hover { 
            color: #b91c1c; 
        }
    </style>
</head>
<body onload="document.getElementById('scanner').focus();">

    <nav class="navbar">
        <div class="d-flex align-items-center gap-3">
            <img src="UPTM Logo.png" alt="UPTM Logo" class="navbar-logo">
            <span class="slogan d-none d-md-block">"Caring Together, Helping Each Other"</span>
        </div>
        <div class="brand-name">Charity<span class="brand-accent">Care+</span></div>
    </nav>

    <div class="title-bar">
        <div style="display: flex; align-items: center;">
            <a href="organizer_program.php" class="back-link"><i class="fas fa-arrow-left fa-lg"></i></a>
            <h5>Volunteer Management</h5>
        </div>
        <div class="d-flex align-items-center gap-4">
            <a href="logout.php" class="btn-logout">Sign Out</a>
        </div>
    </div>

    <main class="main-content">
        <form method="POST" id="scanForm">
            <input type="text" name="scanner_input" id="scanner" class="scanner-box" onblur="this.focus()" autofocus autocomplete="off">
        </form>

        <div class="d-flex justify-content-between align-items-end mb-5">
            <div>
                <h2 class="fw-800 m-0">Participant List</h2>
                <p class="text-muted small fw-bold mt-1">Scan student ID to mark attendance automatically.</p>
            </div>
            <div class="text-success small fw-800">
                <i class="fas fa-circle me-1" style="font-size: 0.6rem;"></i> SCANNER READY
            </div>
        </div>

        <?php if (isset($_SESSION['msg'])): ?>
            <div class="alert alert-light border shadow-sm alert-dismissible fade show mb-5" style="border-radius:12px;">
                <b class="<?php echo ($_SESSION['msg_type'] == 'success') ? 'text-success' : 'text-danger'; ?>">
                    <?php echo $_SESSION['msg']; unset($_SESSION['msg']); ?>
                </b>
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            </div>
        <?php endif; ?>

        <table class="table align-middle">
            <thead>
                <tr>
                    <th width="5%">NO</th>
                    <th width="50%">STUDENT DETAILS</th>
                    <th width="25%">ATTENDANCE STATUS</th>
                    <th width="20%" class="text-end">ACTION</th>
                </tr>
            </thead>
            <tbody>
                <?php if ($volunteers->num_rows > 0): $no = 1; ?>
                    <?php while($v = $volunteers->fetch_assoc()): ?>
                    <tr>
                        <td class="text-muted fw-bold small"><?php echo $no++; ?></td>
                        
                        <td>
                            <div class="fw-bold text-dark" style="font-size: 1rem;">
                                <?php echo htmlspecialchars($v['full_name']); ?>
                            </div>
                            <div class="text-muted small mt-1 fw-bold">
                                ID: <?php echo htmlspecialchars($v['student_id']); ?>
                                <span class="mx-2 text-light-gray">|</span>
                                PHONE: <?php echo htmlspecialchars($v['phone'] ?? 'N/A'); ?>
                            </div>
                        </td>

                        <td>
                            <?php if($v['attendance_status'] == 'Present'): ?>
                                <span class="status-pill" style="color:#15803d;">Present</span>
                            <?php else: ?>
                                <span class="status-pill" style="color:#64748b;">Absent</span>
                            <?php endif; ?>
                        </td>
                        <td class="text-end">
                            <form method="POST" class="d-inline" onsubmit="return confirm('Are you sure you want to remove this volunteer?');">
                                <input type="hidden" name="registration_id" value="<?php echo $v['id']; ?>">
                                <button type="submit" name="remove_student" class="btn btn-link link-delete p-0 border-0 bg-transparent">Remove</button>
                            </form>
                        </td>
                    </tr>
                    <?php endwhile; ?>
                <?php else: ?>
                    <tr>
                        <td colspan="4" class="text-center py-5 text-muted fw-bold small">No volunteers registered yet.</td>
                    </tr>
                <?php endif; ?>
            </tbody>
        </table>
    </main>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        let scanTimer;
        const scannerInput = document.getElementById('scanner');

        scannerInput.addEventListener('input', function() {
            clearTimeout(scanTimer);
            // Wait for 300ms of "silence" from the scanner before submitting
            scanTimer = setTimeout(() => {
                if (this.value.length > 5) { 
                    document.getElementById('scanForm').submit();
                }
            }, 300); 
        });

        // Always keep the focus on the input box
        document.addEventListener('click', () => scannerInput.focus());

        // Automatically submit the form once the scanner inputs data (Enter key)
        document.getElementById('scanner').addEventListener('keypress', function(e) {
            if (e.key === 'Enter') {
                e.preventDefault(); // Prevent double submission
                document.getElementById('scanForm').submit();
            }
        });
    </script>
</body>
</html>