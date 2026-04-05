<?php
session_start();
include 'db.php'; // Ensure this file has your $conn connection

// 1. Security Check: Redirect if not logged in
if (!isset($_SESSION['student_id'])) {
    header("Location: login.php");
    exit();
}

$current_student_id = $_SESSION['student_id'];
$success_msg = "";
$error_msg = "";

// 2. Handle Profile Update (POST Request)
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $fullname = mysqli_real_escape_string($conn, $_POST['fullname']);
    $email = mysqli_real_escape_string($conn, $_POST['email']);
    $phone = mysqli_real_escape_string($conn, $_POST['phone']);

    // Handle Profile Picture Upload
    $profile_img = $_POST['current_img'];
    if (!empty($_FILES['avatar']['name'])) {
        $target_dir = "uploads/";
        if (!is_dir($target_dir)) mkdir($target_dir);

        $file_ext = pathinfo($_FILES["avatar"]["name"], PATHINFO_EXTENSION);
        $new_filename = "profile_" . $current_student_id . "_" . time() . "." . $file_ext;
        $target_file = $target_dir . $new_filename;

        if (move_uploaded_file($_FILES["avatar"]["tmp_name"], $target_file)) {
            $profile_img = $target_file;
        }
    }

    $update_sql = "UPDATE students SET full_name='$fullname', email='$email', phone='$phone', profile_img='$profile_img' WHERE student_id='$current_student_id'";

    if ($conn->query($update_sql) === TRUE) {
        $success_msg = "Profile updated successfully!";
    } else {
        $error_msg = "Error updating profile: " . $conn->error;
    }
}

// 3. Fetch logged-in student details from the database
$student_query = "SELECT * FROM students WHERE student_id = '$current_student_id'";
$student_result = $conn->query($student_query);
$student = $student_result->fetch_assoc();

// FIX: Replaced 'library4.jpg' with a standard professional blank avatar
$profile_pic = !empty($student['profile_img']) ? $student['profile_img'] : 'dp.webp';
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Profile Management | CharityCare+</title>

    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <link href="https://fonts.googleapis.com/css2?family=Dancing+Script:wght@600&family=Inter:wght@400;700;800&display=swap" rel="stylesheet">

    <style>
        :root {
            --prof-blue: #1e3a8a;
            --accent-blue: #2964c9;
            --light-red: #ef4444;
        }

        body {
            font-family: 'Inter', sans-serif;
            background: linear-gradient(135deg, #fce7f3 0%, #e0f2fe 100%);
            margin: 0;
            display: flex;
            flex-direction: column;
            height: 100vh;
            overflow: hidden;
        }

        .navbar {
            background-color: white;
            border-bottom: 3px solid var(--prof-blue);
            padding: 10px 0;
            height: 85px;
            z-index: 1000;
        }

        .navbar-logo {
            height: 55px;
            width: auto;
            display: block;
        }

        .brand-text {
            border-left: 1px solid #ddd;
            padding-left: 15px;
            margin-left: 15px;
        }

        .logo-text {
            color: #000;
            font-weight: 800;
            font-size: 1.4rem;
            letter-spacing: -1px;
        }

        .logo-accent {
            color: var(--accent-blue);
        }

        .slogan-text {
            color: var(--accent-blue);
            font-family: 'Dancing Script', cursive;
            font-size: 1.5rem;
            text-align: center;
            flex-grow: 1;
        }

        .dashboard-label {
            font-weight: 800;
            color: var(--prof-blue);
            letter-spacing: 1px;
            font-size: 1rem;
        }

        .main-container {
            display: flex;
            flex: 1;
            height: calc(100vh - 85px);
            overflow: hidden;
        }

        .sidebar {
            width: 280px;
            background: white;
            border-right: 1px solid #e2e8f0;
            display: flex;
            flex-direction: column;
            padding: 40px 20px;
            flex-shrink: 0;
        }

        .profile-section {
            text-align: center;
            padding-bottom: 30px;
            border-bottom: 1px solid #f1f5f9;
            margin-bottom: 25px;
        }

        .sidebar-menu {
            flex: 1;
            display: flex;
            flex-direction: column;
        }

        .menu-item {
            display: flex;
            align-items: center;
            padding: 12px 18px;
            color: #475569;
            text-decoration: none;
            font-weight: 600;
            border-radius: 12px;
            margin-bottom: 5px;
            transition: 0.3s;
        }

        .menu-item i {
            width: 25px;
            margin-right: 10px;
        }

        .menu-item:hover,
        .menu-item.active {
            background: #eff6ff;
            color: var(--accent-blue);
        }

        .menu-item.logout {
            margin-top: auto;
            color: var(--light-red);
        }

        .content-area {
            flex: 1;
            padding: 40px;
            overflow-y: auto;
            height: 100%;
        }

        .profile-card {
            background: white;
            border-radius: 25px;
            padding: 40px;
            box-shadow: 0 5px 15px rgba(0, 0, 0, 0.05);
            border: 1px solid rgba(0, 0, 0, 0.05);
            max-width: 900px;
        }

        .avatar-edit-wrapper {
            position: relative;
            width: fit-content;
        }

        .profile-img-big {
            width: 140px;
            height: 140px;
            border-radius: 50%;
            object-fit: cover;
            border: 4px solid var(--accent-blue);
            background-color: #f1f5f9; /* Fallback background for transparent PNGs */
        }

        .btn-camera {
            position: absolute;
            bottom: 5px;
            right: 5px;
            background: var(--prof-blue);
            color: white;
            border: none;
            width: 38px;
            height: 38px;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            cursor: pointer;
            border: 3px solid white;
            transition: 0.3s;
        }

        .btn-camera:hover {
            background: var(--accent-blue);
            transform: scale(1.1);
        }

        .form-label {
            font-weight: 700;
            color: var(--prof-blue);
            font-size: 0.9rem;
            margin-bottom: 8px;
        }

        .form-control {
            border-radius: 12px;
            padding: 12px 18px;
            border: 1px solid #e2e8f0;
            font-size: 0.95rem;
        }

        .form-control:focus {
            border-color: var(--accent-blue);
            box-shadow: 0 0 0 4px rgba(41, 100, 201, 0.1);
        }

        .btn-save-profile {
            background: var(--accent-blue);
            color: white;
            border: none;
            padding: 12px 45px;
            border-radius: 12px;
            font-weight: 800;
            transition: 0.3s;
            box-shadow: 0 4px 12px rgba(41, 100, 201, 0.2);
        }

        .btn-save-profile:hover {
            background: var(--prof-blue);
            transform: translateY(-2px);
        }
    </style>
</head>

<body>

    <nav class="navbar">
        <div class="container-fluid px-5 d-flex align-items-center">
            <div class="d-flex align-items-center">
                <img src="UPTM Logo.png" alt="UPTM Logo" class="navbar-logo">
                <div class="brand-text">
                    <div class="logo-text">Charity<span class="logo-accent">Care+</span></div>
                </div>
            </div>
            <div class="slogan-text d-none d-lg-block">"Caring Together, Helping Each Other"</div>
            <div class="dashboard-label">My Profile</div>
        </div>
    </nav>

    <div class="main-container">
        <aside class="sidebar">
            <div class="profile-section">
                <h6 class="fw-bold mb-1"><?php echo htmlspecialchars($student['full_name']); ?></h6>
                <small class="text-muted">Student ID: <?php echo htmlspecialchars($student['student_id']); ?></small>
            </div>
            <div class="sidebar-menu">
                <a href="dashboard.php" class="menu-item"><i class="fas fa-th-large"></i> Overview</a>
                <a href="profile.php" class="menu-item active"><i class="fas fa-user-circle"></i> My Profile</a>
                <a href="myprograms.php" class="menu-item"><i class="fas fa-tasks"></i> My Programs</a>
                <a href="mycertificates.php" class="menu-item"><i class="fas fa-award"></i> My Certificates</a>
                <a href="logout.php" class="menu-item logout"><i class="fas fa-sign-out-alt"></i> Logout</a>
            </div>
        </aside>

        <main class="content-area">

            <?php if ($success_msg): ?>
                <div class="alert alert-success border-0 shadow-sm rounded-4 mb-4">
                    <i class="fas fa-check-circle me-2"></i> <?php echo $success_msg; ?>
                </div>
            <?php endif; ?>

            <?php if ($error_msg): ?>
                <div class="alert alert-danger border-0 shadow-sm rounded-4 mb-4">
                    <i class="fas fa-exclamation-triangle me-2"></i> <?php echo $error_msg; ?>
                </div>
            <?php endif; ?>

            <div class="profile-card">
                <form action="profile.php" method="POST" enctype="multipart/form-data">
                    <input type="hidden" name="current_img" value="<?php echo htmlspecialchars($profile_pic); ?>">

                    <div class="row align-items-center mb-5">
                        <div class="col-auto">
                            <div class="avatar-edit-wrapper">
                                <img src="<?php echo htmlspecialchars($profile_pic); ?>" alt="Avatar Preview" id="avatarPreview" class="profile-img-big">
                                <label for="avatarUpload" class="btn-camera shadow-sm">
                                    <i class="fas fa-camera"></i>
                                </label>
                                <input type="file" id="avatarUpload" name="avatar" hidden accept="image/*">
                            </div>
                        </div>
                        <div class="col">
                            <h4 class="fw-bold mb-1" style="color: var(--prof-blue);"><?php echo htmlspecialchars($student['full_name']); ?></h4>
                            <p class="text-muted mb-0">Update your profile details and photo.</p>
                        </div>
                    </div>

                    <div class="row g-4">
                        <div class="col-md-6">
                            <label class="form-label">Full Name</label>
                            <input type="text" name="fullname" class="form-control" value="<?php echo htmlspecialchars($student['full_name']); ?>" required>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">ID</label>
                            <input type="text" class="form-control" value="<?php echo htmlspecialchars($student['student_id']); ?>" readonly style="background: #f8fafc; color: #94a3b8;">
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">UPTM Email Address</label>
                            <input type="email" name="email" class="form-control" value="<?php echo htmlspecialchars($student['email']); ?>" required>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">Phone Number</label>
                            <input type="text" name="phone" class="form-control" value="<?php echo htmlspecialchars($student['phone'] ?? ''); ?>" placeholder="+60 1x-xxxxxxx">
                        </div>
                        <div class="col-12 mt-5">
                            <button type="submit" class="btn-save-profile">Save Changes</button>
                        </div>
                    </div>
                </form>
            </div>
        </main>
    </div>

    <script>
        // Real-time image preview
        document.getElementById('avatarUpload').onchange = function(evt) {
            const [file] = this.files;
            if (file) {
                document.getElementById('avatarPreview').src = URL.createObjectURL(file);
            }
        }
    </script>
</body>

</html>