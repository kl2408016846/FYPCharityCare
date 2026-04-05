<?php
session_start();
include 'db.php';

// 1. Admin Security Check: Ensure only authorized admins can access this oversight page
if (!isset($_SESSION['admin_id'])) {
    header("Location: admin_login.php");
    exit();
}

// 2. Handle Admin Deletion Logic: Allows top-level administrative removal of any program
if (isset($_POST['delete_program'])) {
    $p_id = mysqli_real_escape_string($conn, $_POST['program_id']);
    
    $delete_sql = "DELETE FROM programs WHERE id = '$p_id'";
    if (mysqli_query($conn, $delete_sql)) {
        $_SESSION['msg'] = "Program has been successfully deleted.";
    }
    header("Location: admin_program_list.php");
    exit();
}

// 3. Fetch All Programs with Organizer Details and Participant Counts
$search = isset($_GET['search']) ? mysqli_real_escape_string($conn, $_GET['search']) : '';

$sql = "SELECT p.*, s.full_name as organizer_name, s.staff_id,
        (SELECT COUNT(*) FROM registrations r WHERE r.program_id = p.id) as total_participants
        FROM programs p
        LEFT JOIN staff s ON p.created_by = s.staff_id";

if ($search != '') {
    $sql .= " WHERE p.title LIKE '%$search%' OR s.full_name LIKE '%$search%' OR p.type LIKE '%$search%'";
}
$sql .= " ORDER BY p.created_at DESC";

$programs = mysqli_query($conn, $sql);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Global Program List | CharityCare+ Admin</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800&family=Dancing+Script:wght@600&display=swap" rel="stylesheet">
    
    <style>
        :root {
            --navy-blue: #1e3a8a;
            --navy-light: #e0e7ff;
            --soft-bg: #f8fafc;
            --text-dark: #1e293b;
        }

        body {
            background-color: var(--soft-bg);
            font-family: 'Inter', sans-serif;
            margin: 0;
            display: flex;
            flex-direction: column;
            min-height: 100vh;
        }

        .navbar {
            background: white;
            padding: 10px 50px;
            border-bottom: 1px solid #eee;
            display: flex;
            align-items: center;
            justify-content: space-between;
        }

        .navbar-logo {
            height: 48px;
        }

        .slogan {
            font-family: 'Dancing Script', cursive;
            color: var(--navy-blue);
            font-size: 1.2rem;
        }

        .brand-name {
            font-weight: 800;
            font-size: 1.5rem;
            color: #000;
            letter-spacing: -0.5px;
        }

        .brand-accent {
            color: var(--navy-blue);
        }

        .title-bar {
            background: var(--navy-blue);
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

        .back-link {
            color: #ffffff;
            text-decoration: none;
            transition: 0.2s;
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
            color: var(--navy-blue);
            background: white;
        }

        .main-content {
            flex-grow: 1;
            padding: 50px;
            max-width: 1200px;
            margin: 0 auto;
            width: 100%;
        }

        .search-bar {
            border-radius: 30px;
            padding: 12px 25px;
            border: 1px solid #e2e8f0;
            width: 350px;
            outline: none;
            transition: 0.3s;
            font-weight: 500;
            font-size: 0.9rem;
        }

        .search-bar:focus {
            border-color: var(--navy-blue);
            box-shadow: 0 0 0 4px rgba(30, 58, 138, 0.1);
        }

        .table-container {
            background: white;
            border-radius: 35px;
            border: 1px solid #e2e8f0;
            box-shadow: 0 10px 30px rgba(0,0,0,0.03);
            padding: 40px;
            margin-bottom: 40px;
        }

        .table thead th {
            border: none;
            font-size: 0.85rem;
            text-transform: uppercase;
            letter-spacing: 0.5px;
            padding-bottom: 15px;
            border-bottom: 2px solid #e2e8f0;
            font-weight: 800;
        }

        .table tbody td {
            padding: 25px 10px;
            border-bottom: 1px solid #f8fafc;
            vertical-align: middle;
        }

        .table tbody tr:hover td {
            background-color: #fafbfc;
        }

        .table tbody tr:last-child td {
            border-bottom: none;
        }

        .prog-title {
            font-size: 1.05rem;
            font-weight: 700;
            color: #0f172a;
            margin-bottom: 4px;
        }

        .org-tag {
            font-size: 0.8rem;
            color: #64748b;
            font-weight: 500;
        }

        .btn-view {
            background: #f1f5f9;
            color: #0f172a;
            border: none;
            border-radius: 8px;
            font-weight: 700;
            font-size: 0.8rem;
            padding: 8px 16px;
            transition: 0.2s;
        }

        .btn-view:hover {
            background: var(--navy-light);
            color: var(--navy-blue);
        }

        .btn-delete {
            color: #ef4444;
            background: none;
            border: none;
            font-weight: 700;
            font-size: 0.8rem;
            padding: 8px 16px;
            transition: 0.2s;
            border-radius: 8px;
        }

        .btn-delete:hover {
            background: #fef2f2;
        }

        .modal-content {
            border-radius: 30px;
            border: none;
            overflow: hidden;
        }

        .modal-header {
            background: #f8fafc;
            border-bottom: 1px solid #e2e8f0;
            padding: 25px 30px;
        }

        .modal-body {
            padding: 35px;
        }

        .modal-banner {
            width: 100%;
            height: 220px;
            object-fit: cover;
            border-radius: 20px;
            margin-bottom: 25px;
            border: 1px solid #e2e8f0;
        }

        .detail-label {
            font-size: 0.75rem;
            text-transform: uppercase;
            color: #94a3b8;
            font-weight: 700;
            letter-spacing: 1px;
            margin-bottom: 5px;
        }

        .detail-text {
            font-size: 0.95rem;
            color: #1e293b;
            font-weight: 600;
            margin-bottom: 20px;
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
            <a href="admin_dashboard.php" class="back-link"><i class="fas fa-arrow-left"></i></a>
            Program List
        </h5>
        <div class="d-flex align-items-center gap-3">
            <a href="admin_profile.php" class="btn-topbar">Profile</a>
            <a href="logout.php" class="btn-topbar">Sign Out</a>
        </div>
    </div>

    <main class="main-content">
        <div class="d-flex justify-content-between align-items-end mb-5">
            <div>
                <h2 class="fw-800 m-0" style="color: #0f172a;">Program Oversight</h2>
                <p class="text-muted fw-bold small mt-1">Review and manage all active campaigns and activities.</p>
            </div>
            <form method="GET">
                <div class="position-relative">
                    <input type="text" name="search" class="search-bar" placeholder="Search activities or staff..." value="<?php echo htmlspecialchars($search); ?>">
                    <i class="fas fa-search position-absolute" style="right: 20px; top: 15px; color: #94a3b8;"></i>
                </div>
            </form>
        </div>

        <?php if (isset($_SESSION['msg'])): ?>
            <div class="alert alert-success bg-success bg-opacity-10 border-0 rounded-4 fw-bold text-success py-3 mb-4" style="border: 1px solid rgba(25, 135, 84, 0.2);">
                <i class="fas fa-check-circle me-2"></i> <?php echo $_SESSION['msg']; unset($_SESSION['msg']); ?>
            </div>
        <?php endif; ?>

        <div class="table-container">
            <div class="table-responsive">
                <table class="table table-borderless align-middle mb-0">
                    <thead>
                        <tr>
                            <th width="35%">PROGRAM DETAILS</th>
                            <th width="15%">TYPE</th>
                            <th width="20%">ENGAGEMENT</th> 
                            <th width="10%">STATUS</th>
                            <th width="20%" class="text-end">MANAGEMENT</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if (mysqli_num_rows($programs) > 0): ?>
                            <?php while($row = mysqli_fetch_assoc($programs)): ?>
                            <tr>
                                <td>
                                    <div class="prog-title"><?php echo htmlspecialchars($row['title']); ?></div>
                                    <div class="org-tag">Organizer: <?php echo htmlspecialchars($row['organizer_name'] ?? 'System Admin'); ?> 
                                        <span class="mx-1 text-light-gray">|</span> ID: <?php echo htmlspecialchars($row['staff_id'] ?? 'N/A'); ?>
                                    </div>
                                </td>
                                <td>
                                    <span class="badge" style="background-color: var(--navy-light); color: var(--navy-blue); padding: 6px 12px; border-radius: 6px; font-weight: 700; letter-spacing: 0.5px; text-transform: uppercase;">
                                        <?php echo htmlspecialchars($row['type']); ?>
                                    </span>
                                </td>
                                <td>
                                    <?php if ($row['type'] == 'Donation'): ?>
                                        <div class="fw-800 text-success" style="font-size: 0.95rem;">RM <?php echo number_format($row['current_amount'], 2); ?></div>
                                        <div class="text-muted fw-bold" style="font-size: 0.75rem;">Target: RM <?php echo number_format($row['goal_amount'], 2); ?></div>
                                    <?php else: ?>
                                        <div class="fw-bold text-primary" style="font-size: 0.95rem;">
                                            <?php echo $row['total_participants']; ?> / <?php echo $row['volunteer_slots']; ?> Joined
                                        </div>
                                    <?php endif; ?>
                                </td>
                                <td>
                                    <?php 
                                        $is_active = (strtotime($row['end_date']) >= time());
                                        if($is_active) {
                                            echo '<span class="badge bg-success bg-opacity-10 text-success rounded-pill px-3 py-2 fw-bold" style="border: 1px solid rgba(25, 135, 84, 0.2);">Active</span>';
                                        } else {
                                            echo '<span class="badge bg-secondary bg-opacity-10 text-secondary rounded-pill px-3 py-2 fw-bold" style="border: 1px solid rgba(108, 117, 125, 0.2);">Closed</span>';
                                        }
                                    ?>
                                </td>
                                <td class="text-end">
                                    <div class="d-flex justify-content-end gap-2">
                                        <button type="button" class="btn-view" data-bs-toggle="modal" data-bs-target="#viewModal<?php echo $row['id']; ?>">
                                            View
                                        </button>
                                        
                                        <form method="POST" class="m-0" onsubmit="return confirm('ADMIN ACTION: Are you sure you want to delete this program?');">
                                            <input type="hidden" name="program_id" value="<?php echo $row['id']; ?>">
                                            <button type="submit" name="delete_program" class="btn-delete">Delete</button>
                                        </form>
                                    </div>
                                </td>
                            </tr>

                            <div class="modal fade" id="viewModal<?php echo $row['id']; ?>" tabindex="-1" aria-hidden="true">
                                <div class="modal-dialog modal-dialog-centered modal-lg modal-dialog-scrollable">
                                    <div class="modal-content">
                                        <div class="modal-header">
                                            <h5 class="modal-title fw-800 text-dark">Program Details</h5>
                                            <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                                        </div>
                                        <div class="modal-body">
                                            <div class="row">
                                                <div class="col-12">
                                                    <?php 
                                                        $base_folder = '../staff/'; 
                                                        $banner = !empty($row['image_path']) ? $base_folder . $row['image_path'] : $base_folder . 'default_campaign.jpg';
                                                    ?>
                                                    <img src="<?php echo htmlspecialchars($banner); ?>" alt="Program Banner" class="modal-banner">
                                                </div>
                                                
                                                <div class="col-md-7">
                                                    <div class="detail-label">Program Title</div>
                                                    <div class="detail-text" style="font-size: 1.2rem;"><?php echo htmlspecialchars($row['title']); ?></div>
                                                    
                                                    <div class="detail-label">Description</div>
                                                    <div class="detail-text fw-normal text-muted" style="line-height: 1.6;"><?php echo nl2br(htmlspecialchars($row['description'])); ?></div>
                                                </div>
                                                
                                                <div class="col-md-5">
                                                    <div class="p-4 bg-light rounded-4 border h-100">
                                                        <div class="detail-label">Duration</div>
                                                        <div class="detail-text mb-3" style="font-size: 0.85rem;">
                                                            <?php echo date('d M Y', strtotime($row['start_date'])); ?> - <?php echo date('d M Y', strtotime($row['end_date'])); ?><br>
                                                            <?php echo date('h:i A', strtotime($row['start_time'])); ?> - <?php echo date('h:i A', strtotime($row['end_time'])); ?>
                                                        </div>

                                                        <div class="detail-label">Organizer</div>
                                                        <div class="detail-text mb-0" style="font-size: 0.85rem;">
                                                            <?php echo htmlspecialchars($row['organizer_name'] ?? 'System Admin'); ?>
                                                        </div>
                                                    </div>
                                                </div>

                                                <div class="col-12 mt-4 pt-4 border-top">
                                                    <div class="detail-label mb-3">Support Document</div>
                                                    <?php if (!empty($row['support_file'])): 
                                                        $safe_file_path = $base_folder . str_replace(' ', '%20', $row['support_file']);
                                                    ?>
                                                        <div class="p-4 text-center border rounded-4" style="background-color: #f8fafc;">
                                                            <i class="fas fa-file-alt fs-2 mb-3" style="color: var(--navy-blue);"></i><br>
                                                            <a href="<?php echo htmlspecialchars($safe_file_path); ?>" target="_blank" class="btn btn-primary fw-bold px-4 py-2 rounded-pill shadow-sm" style="background-color: var(--navy-blue); border: none;">
                                                                 View / Download Document
                                                            </a>
                                                        </div>
                                                    <?php else: ?>
                                                        <div class="p-4 text-center border rounded-4 text-muted fw-bold" style="background-color: #f8fafc;">
                                                            <i class="fas fa-file-excel fs-3 mb-2"></i><br>No document was uploaded for this program.
                                                        </div>
                                                    <?php endif; ?>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <?php endwhile; ?>
                        <?php else: ?>
                            <tr><td colspan="5" class="text-center py-5 text-muted fw-bold">No programs found.</td></tr>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </main>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>