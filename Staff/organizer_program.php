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

$staff_id = $_SESSION['staff_id'];
$program_id = $_GET['id'] ?? null;
$search_query = isset($_GET['search']) ? mysqli_real_escape_string($conn, $_GET['search']) : '';

// 2. CRUD Logic (Delete & Update)
// Handle program deletion with a JavaScript confirmation fallback
if (isset($_POST['delete_program'])) {
    $p_id = $_POST['program_id'];
    $delete_sql = "DELETE FROM programs WHERE id='$p_id' AND created_by='$staff_id'";
    
    if ($conn->query($delete_sql)) {
        echo "<script>
            alert('Success! Program successfully deleted.');
            window.location.href = 'organizer_program.php';
        </script>";
    } else {
        echo "<script>
            alert('Database Error: " . addslashes($conn->error) . "');
            window.location.href = 'organizer_program.php';
        </script>";
    }
    exit();
}

// Handle program details update including file management
if (isset($_POST['update_program'])) {
    $p_id = $_POST['program_id'];
    $title = mysqli_real_escape_string($conn, $_POST['title']);
    $desc = mysqli_real_escape_string($conn, $_POST['description']);
    $s_date = $_POST['start_date']; 
    $e_date = $_POST['end_date'];
    $s_time = $_POST['start_time']; 
    $e_time = $_POST['end_time'];
    $goal = $_POST['goal_amount'] ?? 0;
    $slots = $_POST['volunteer_slots'] ?? 0;

    // Fetch existing file paths to prevent overwriting with empty values
    $existing_data = $conn->query("SELECT image_path, support_file FROM programs WHERE id='$p_id'")->fetch_assoc();
    $image_path = $existing_data['image_path'];
    $support_file_path = $existing_data['support_file'];

    // Process new image banner upload
    if (isset($_FILES['image']) && $_FILES['image']['error'] === UPLOAD_ERR_OK) {
        $image_name = time() . "_" . basename($_FILES['image']['name']);
        $target_file = "uploads/" . $image_name;
        if (move_uploaded_file($_FILES['image']['tmp_name'], $target_file)) {
            $image_path = $target_file; 
        }
    }

    // Process new support document upload
    if (isset($_FILES['support_file']) && $_FILES['support_file']['error'] === UPLOAD_ERR_OK) {
        $file_name = "DOC_" . time() . "_" . basename($_FILES['support_file']['name']);
        $target_doc = "uploads/" . $file_name;
        if (move_uploaded_file($_FILES['support_file']['tmp_name'], $target_doc)) {
            $support_file_path = $target_doc; 
        }
    }

    $update_sql = "UPDATE programs SET 
        title='$title', 
        description='$desc', 
        start_date='$s_date', 
        end_date='$e_date', 
        start_time='$s_time', 
        end_time='$e_time', 
        goal_amount='$goal', 
        volunteer_slots='$slots',
        image_path='$image_path',
        support_file='$support_file_path'
        WHERE id='$p_id' AND created_by='$staff_id'";

    if ($conn->query($update_sql)) {
        echo "<script>
            alert('Success! Changes saved successfully.');
            window.location.href = 'organizer_program.php';
        </script>";
    } else {
        echo "<script>
            alert('Database Error: " . addslashes($conn->error) . "');
            window.location.href = 'organizer_program.php?id=$p_id';
        </script>";
    }
    exit();
}

// 3. Fetch Program Data 
$edit_mode = $program_id ? true : false;
$program_data = $edit_mode ? $conn->query("SELECT * FROM programs WHERE id='$program_id' AND created_by='$staff_id'")->fetch_assoc() : null;

// Query to retrieve programs and count current registrations
$sql = "SELECT p.*, 
        (SELECT COUNT(*) FROM registrations r WHERE r.program_id = p.id) as joined_count 
        FROM programs p 
        WHERE p.created_by='$staff_id'";

// Apply search filter if applicable
if (!empty($search_query)) {
    $sql .= " AND (p.title LIKE '%$search_query%' OR p.id LIKE '%$search_query%')";
}

$sql .= " ORDER BY p.id DESC";
$my_programs = $conn->query($sql);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Manage Programs | CharityCare+</title>
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
            height: 48px;
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
            display: flex;
            align-items: center;
            gap: 15px;
        }

        /* Back Arrow Styling */
        .back-link {
            color: #ffffff;
            text-decoration: none;
            transition: 0.2s;
            font-weight: 600;
            margin-right: 15px;
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
            color: var(--accent-blue);
            background: white;
        }

        .main-content {
            padding: 60px 50px;
            max-width: 1300px;
            margin: auto;
        }

        .search-container {
            max-width: 400px;
            position: relative;
        }

        .search-input {
            width: 100%;
            border: none;
            border-bottom: 2px solid #e2e8f0;
            padding: 10px 40px 10px 0;
            font-weight: 600;
            outline: none;
            transition: 0.3s;
            border-radius: 0;
        }

        .search-input:focus {
            border-bottom-color: var(--accent-blue);
        }

        .search-icon {
            position: absolute;
            right: 10px;
            top: 50%;
            transform: translateY(-50%);
            color: #94a3b8;
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
            padding: 25px 0;
            border-bottom: 1px solid #f1f5f9;
        }

        .prog-title {
            font-weight: 700;
            color: #000;
            font-size: 1.05rem;
            margin-bottom: 2px;
        }

        .prog-type {
            font-size: 0.65rem;
            font-weight: 800;
            color: var(--accent-blue);
            text-transform: uppercase;
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

        .btn-manage {
            background: var(--accent-blue);
            color: #fff;
            border-radius: 8px;
            font-size: 0.75rem;
            font-weight: 700;
            padding: 8px 20px;
            text-decoration: none;
            border: none;
            transition: 0.2s;
        }

        .btn-manage:hover {
            background: var(--prof-blue);
            transform: translateY(-2px);
            color: white;
        }

        .action-link {
            font-size: 0.8rem;
            font-weight: 700;
            text-decoration: none;
            margin-left: 20px;
            transition: 0.2s;
        }

        .link-edit {
            color: #64748b;
        }

        .link-edit:hover {
            color: #000;
        }

        .link-delete {
            color: #ef4444;
        }

        .info-label {
            font-weight: 800;
            color: #1e293b;
            font-size: 0.75rem;
            text-transform: uppercase;
            margin-bottom: 10px;
            display: block;
        }

        .info-input {
            width: 100%;
            background: #ffffff;
            padding: 12px 0;
            border: none;
            border-bottom: 2px solid #e2e8f0;
            color: #000;
            font-weight: 600;
            margin-bottom: 30px;
            outline: none;
            border-radius: 0;
        }

        .info-input:focus {
            border-bottom-color: var(--accent-blue);
        }

        .file-input {
            width: 100%;
            border: 1px solid #e2e8f0;
            padding: 10px;
            border-radius: 8px;
            font-size: 0.85rem;
            margin-bottom: 5px;
            background: #f8fafc;
        }

        .current-file {
            font-size: 0.75rem;
            color: #64748b;
            font-weight: 600;
            display: block;
            margin-bottom: 25px;
            word-break: break-all;
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
            <?php if ($edit_mode): ?>
                <a href="organizer_program.php" class="back-link"><i class="fas fa-arrow-left fa-lg"></i></a>
                Edit Program
            <?php else: ?>
                <a href="organizer_dashboard.php" class="back-link"><i class="fas fa-arrow-left fa-lg"></i></a>
                Program Management
            <?php endif; ?>
        </h5>
        <div class="d-flex align-items-center gap-4">
            <a href="logout.php" class="btn-topbar">Sign Out</a>
        </div>
    </div>

    <main class="main-content">
        <?php if (!$edit_mode): ?>
            <div class="d-flex justify-content-between align-items-center mb-5">
                <div>
                    <h2 class="fw-800 m-0">Campaigns List</h2>
                    <p class="text-muted small fw-bold mt-1">View your volunteer and donation programs.</p>
                </div>
                <form action="organizer_program.php" method="GET" class="search-container">
                    <input type="text" name="search" class="search-input" placeholder="Search by Name or ID..." value="<?php echo htmlspecialchars($search_query); ?>">
                    <i class="fas fa-search search-icon"></i>
                </form>
            </div>
            
            <table class="table align-middle">
                <thead>
                    <tr>
                        <th width="5%">ID</th>
                        <th width="40%">Program Details</th>
                        <th width="15%">Current Status</th>
                        <th width="15%">Engagement</th>
                        <th width="25%" class="text-end">Management</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if ($my_programs->num_rows > 0): ?>
                        <?php while($row = $my_programs->fetch_assoc()): ?>
                        <tr>
                            <td class="text-muted fw-bold small">#<?php echo $row['id']; ?></td>
                            <td>
                                <div class="prog-title"><?php echo htmlspecialchars($row['title']); ?></div>
                                <div class="prog-type"><?php echo $row['type']; ?></div>
                            </td>
                            <td>
                                <?php
                                $now = date('Y-m-d H:i:s');
                                $start = $row['start_date'] . ' ' . $row['start_time'];
                                $end = $row['end_date'] . ' ' . $row['end_time'];
                                
                                if ($now < $start) echo '<span class="status-pill" style="color:#b45309;">Upcoming</span>';
                                elseif ($now >= $start && $now <= $end) echo '<span class="status-pill" style="color:#15803d;">Active</span>';
                                else echo '<span class="status-pill" style="color:#64748b;">Completed</span>';
                                ?>
                            </td>
                            <td>
                                <span class="fw-bold small">
                                    <?php 
                                    if ($row['type'] == 'Donation') {
                                        echo 'RM '.number_format($row['current_amount'], 2);
                                    } else {
                                        echo $row['joined_count'] . ' / ' . $row['volunteer_slots'] . ' Joined';
                                    }
                                    ?>
                                </span>
                            </td>
                            <td class="text-end">
                                <?php if ($row['type'] == 'Volunteer'): ?>
                                    <a href="organizer_volunteers.php?id=<?php echo $row['id']; ?>" class="btn-manage">Students</a>
                                <?php else: ?>
                                    <a href="organizer_funds.php?id=<?php echo $row['id']; ?>" class="btn-manage">Donors</a>
                                <?php endif; ?>

                                <a href="organizer_program.php?id=<?php echo $row['id']; ?>" class="action-link link-edit">Edit</a>
                                
                                <form method="POST" class="d-inline" onsubmit="return confirm('Are you sure you want to permanently delete this program?');">
                                    <input type="hidden" name="program_id" value="<?php echo $row['id']; ?>">
                                    <button type="submit" name="delete_program" class="btn btn-link action-link link-delete p-0 border-0 bg-transparent fw-bold">Delete</button>
                                </form>
                            </td>
                        </tr>
                        <?php endwhile; ?>
                    <?php else: ?>
                        <tr>
                            <td colspan="5" class="text-center py-5 text-muted fw-bold small">No programs found matching your search.</td>
                        </tr>
                    <?php endif; ?>
                </tbody>
            </table>

        <?php else: ?>
            <div style="max-width: 800px;">
                <h2 class="fw-800 mb-5">Edit Program Details</h2>
                
                <form method="POST" enctype="multipart/form-data">
                    <input type="hidden" name="program_id" value="<?php echo $program_data['id']; ?>">
                    <div class="row g-5">
                        <div class="col-md-8">
                            <label class="info-label">Program Title</label>
                            <input type="text" name="title" class="info-input" value="<?php echo htmlspecialchars($program_data['title']); ?>" required>
                        </div>
                        <div class="col-md-4">
                            <label class="info-label"><?php echo ($program_data['type'] == 'Donation') ? 'Goal Amount (RM)' : 'Available Slots'; ?></label>
                            <input type="number" name="<?php echo ($program_data['type'] == 'Donation') ? 'goal_amount' : 'volunteer_slots'; ?>" class="info-input" value="<?php echo ($program_data['type'] == 'Donation') ? $program_data['goal_amount'] : $program_data['volunteer_slots']; ?>">
                        </div>
                    </div>
                    <label class="info-label">Description (Detail/Location)</label>
                    <textarea name="description" class="info-input" rows="2"><?php echo htmlspecialchars($program_data['description']); ?></textarea>
                    
                    <div class="row g-5 mt-2">
                        <div class="col-md-3"><label class="info-label">Start Date</label><input type="date" name="start_date" class="info-input" value="<?php echo $program_data['start_date']; ?>"></div>
                        <div class="col-md-3"><label class="info-label">End Date</label><input type="date" name="end_date" class="info-input" value="<?php echo $program_data['end_date']; ?>"></div>
                        <div class="col-md-3"><label class="info-label">Start Time</label><input type="time" name="start_time" class="info-input" value="<?php echo $program_data['start_time']; ?>"></div>
                        <div class="col-md-3"><label class="info-label">End Time</label><input type="time" name="end_time" class="info-input" value="<?php echo $program_data['end_time']; ?>"></div>
                    </div>

                    <div class="row g-5 mt-2">
                        <div class="col-md-6">
                            <label class="info-label">Update Banner Image</label>
                            <input type="file" name="image" class="file-input" accept="image/*">
                            <span class="current-file">
                                Current: <?php echo !empty($program_data['image_path']) ? basename($program_data['image_path']) : 'None'; ?>
                                <br><small>(Leave empty to keep current image)</small>
                            </span>
                        </div>
                        <div class="col-md-6">
                            <label class="info-label">Update Support Document</label>
                            <input type="file" name="support_file" class="file-input" accept=".pdf,.doc,.docx">
                            <span class="current-file">
                                Current: <?php echo !empty($program_data['support_file']) ? basename($program_data['support_file']) : 'None'; ?>
                                <br><small>(Leave empty to keep current document)</small>
                            </span>
                        </div>
                    </div>
                    
                    <div class="d-flex gap-4 mt-4">
                        <button type="submit" name="update_program" class="btn-manage" style="padding: 15px 40px; font-size: 0.9rem;">Save All Changes</button>
                        <button type="button" onclick="window.history.back();" class="btn btn-link text-muted fw-bold text-decoration-none pt-3">Cancel</button>
                    </div>
                </form>
            </div>
        <?php endif; ?>
    </main>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>