<?php
session_start();
include 'db.php';

// 1. Security Check: Redirect if not logged in
if (!isset($_SESSION['staff_id'])) {
    header("Location: staff_login.php");
    exit();
}

$staff_id = $_SESSION['staff_id'];

// 2. Profile Update Logic: Processes when the form is submitted
if (isset($_POST['update_profile'])) {
    $full_name = mysqli_real_escape_string($conn, $_POST['full_name']);
    $email = mysqli_real_escape_string($conn, $_POST['email']);
    
    // REVERTED: Using 'phone_number' to match your database exactly
    $phone_number = mysqli_real_escape_string($conn, $_POST['phone_number']);

    // Build the base update query
    $update_sql = "UPDATE staff SET full_name='$full_name', email='$email', phone_number='$phone_number' ";

    // Handle profile image upload if a new file is chosen
    if (!empty($_FILES['profile_img']['name'])) {
        $img_name = time() . '_' . $_FILES['profile_img']['name'];
        $target_dir = "uploads/";
        $target_file = $target_dir . basename($img_name);

        // Create the directory if it doesn't exist
        if (!is_dir($target_dir)) { 
            mkdir($target_dir, 0777, true); 
        }

        if (move_uploaded_file($_FILES['profile_img']['tmp_name'], $target_file)) {
            $update_sql .= ", profile_img='$target_file' ";
        }
    }

    $update_sql .= " WHERE staff_id='$staff_id'";

    if ($conn->query($update_sql)) {
        $_SESSION['msg'] = "Profile updated successfully!";
    } else {
        $_SESSION['msg'] = "Error: " . $conn->error;
    }
    
    // Redirect back to the same page to prevent form resubmission and show new data
    header("Location: organizer_profile.php");
    exit();
}

// 3. Data Fetching: Retrieve the latest data to populate the form
$query = "SELECT * FROM staff WHERE staff_id = '$staff_id'";
$staff = $conn->query($query)->fetch_assoc();
$profile_img = !empty($staff['profile_img']) ? $staff['profile_img'] : 'default_staff.jpg';
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Organizer Profile | CharityCare+</title>
    
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <link href="https://fonts.googleapis.com/css2?family=Dancing+Script:wght@600&family=Inter:wght@400;600;700;800&display=swap" rel="stylesheet">

    <style>
        :root {
            --prof-blue: #1e3a8a;
            --accent-blue: #2964c9;
            --light-red: #ff7675;
            --card-shadow: 0 15px 35px rgba(0, 0, 0, 0.1);
        }

        body {
            font-family: 'Inter', sans-serif;
            background-color: #ffffff;
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
            color: var(--accent-blue);
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
            flex: 1;
            display: flex;
            justify-content: center;
            align-items: flex-start;
            padding: 60px 20px;
        }

        .single-profile-card {
            background: white;
            width: 100%;
            max-width: 900px;
            border-radius: 40px;
            padding: 50px;
            box-shadow: var(--card-shadow);
            display: flex;
            align-items: flex-start;
            gap: 40px;
            flex-wrap: wrap; 
        }

        .profile-left {
            text-align: center;
            width: 220px; 
            flex-shrink: 0; 
        }

        .profile-name {
            word-wrap: break-word;
            overflow-wrap: break-word;
            line-height: 1.3;
            margin-top: 15px;
        }

        .profile-img-container {
            position: relative;
            display: inline-block;
        }

        .profile-img-lg {
            width: 180px;
            height: 180px;
            border-radius: 50%;
            object-fit: cover;
            border: 5px solid #f8fafc;
            box-shadow: 0 4px 15px rgba(0,0,0,0.1);
        }

        .camera-badge {
            position: absolute;
            bottom: 10px;
            right: 10px;
            background: var(--prof-blue);
            color: white;
            width: 40px;
            height: 40px;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            cursor: pointer;
            border: 3px solid white;
            transition: 0.3s;
        }

        .profile-right {
            flex: 1;
            min-width: 300px; 
        }

        .info-label {
            font-weight: 800;
            color: #64748b;
            font-size: 0.75rem;
            text-transform: uppercase;
            display: block;
            margin-bottom: 8px;
            padding-left: 5px;
            text-align: left;
        }

        .info-input {
            width: 100%;
            background: #f8fafc;
            padding: 14px 20px;
            border-radius: 15px;
            border: 1px solid #e2e8f0;
            color: #1e293b;
            font-weight: 600;
            margin-bottom: 20px;
            outline: none;
        }

        .info-input:focus {
            border-color: var(--accent-blue);
            background: white;
        }

        .btn-save {
            background: var(--accent-blue);
            color: white;
            border: none;
            border-radius: 20px;
            padding: 15px 50px;
            font-weight: 800;
            transition: 0.3s;
            margin-top: 10px;
            width: 100%;
        }

        .btn-save:hover {
            background: var(--prof-blue);
            transform: translateY(-2px);
            box-shadow: 0 5px 15px rgba(30, 58, 138, 0.2);
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
        <div style="display: flex; align-items: center;">
            <a href="organizer_dashboard.php" class="back-link"><i class="fas fa-arrow-left fa-lg"></i></a>
            <h5>Organizer Profile</h5>
        </div>
        <div class="d-flex align-items-center gap-4">
            <a href="logout.php" class="btn-logout">Sign Out</a>
        </div>
    </div>

    <main class="main-content">
        <form action="" method="POST" enctype="multipart/form-data" style="width: 100%; display: flex; justify-content: center;">
            <div class="single-profile-card">
                
                <div class="profile-left">
                    <div class="profile-img-container">
                        <img src="<?php echo htmlspecialchars($profile_img); ?>" class="profile-img-lg" id="preview" alt="Staff Avatar">
                        <label for="file-input" class="camera-badge">
                            <i class="fas fa-camera"></i>
                        </label>
                        <input type="file" name="profile_img" id="file-input" style="display:none;" accept="image/*" onchange="previewImage(this)">
                    </div>
                    <h5 class="fw-bold profile-name"><?php echo htmlspecialchars($staff['full_name']); ?></h5>
                    <p class="text-muted small"><?php echo htmlspecialchars($staff['department']); ?></p>
                </div>

                <div class="profile-right">
                    <div class="row text-start">
                        <div class="col-md-6">
                            <label class="info-label">Staff ID</label>
                            <input type="text" class="info-input" value="<?php echo htmlspecialchars($staff['staff_id']); ?>" readonly style="background-color: #f1f5f9; cursor: not-allowed;">
                        </div>
                        <div class="col-md-6">
                            <label class="info-label">Full Name</label>
                            <input type="text" name="full_name" class="info-input" value="<?php echo htmlspecialchars($staff['full_name']); ?>" required>
                        </div>
                    </div>

                    <div class="row text-start">
                        <div class="col-md-6">
                            <label class="info-label">Email Address</label>
                            <input type="email" name="email" class="info-input" value="<?php echo htmlspecialchars($staff['email']); ?>" required>
                        </div>
                        <div class="col-md-6">
                            <label class="info-label">Phone Number</label>
                            <input type="text" name="phone_number" class="info-input" value="<?php echo htmlspecialchars($staff['phone_number'] ?? ''); ?>">
                        </div>
                    </div>

                    <div class="text-start">
                        <label class="info-label">Department</label>
                        <input type="text" class="info-input" value="<?php echo htmlspecialchars($staff['department']); ?>" readonly style="background-color: #f1f5f9; cursor: not-allowed;">
                    </div>
                    
                    <button type="submit" name="update_profile" class="btn-save">Save Changes</button>
                </div>

            </div>
        </form>
    </main>

    <script>
        function previewImage(input) {
            if (input.files && input.files[0]) {
                var reader = new FileReader();
                reader.onload = function(e) { 
                    document.getElementById('preview').src = e.target.result; 
                }
                reader.readAsDataURL(input.files[0]);
            }
        }
    </script>

    <?php if (isset($_SESSION['msg'])): ?>
        <script>
            alert("<?php echo $_SESSION['msg']; ?>");
        </script>
        <?php unset($_SESSION['msg']); ?>
    <?php endif; ?>

</body>
</html>