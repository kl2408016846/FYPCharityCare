<?php
session_start();
include 'db.php'; 
if (!$conn) { die("Database connection failed. Check db.php."); }

date_default_timezone_set('Asia/Kuala_Lumpur');

if (!isset($_SESSION['staff_id'])) {
    header("Location: staff_login.php");
    exit();
}

$current_staff_id = $_SESSION['staff_id'];

if (isset($_POST['submit_volunteer'])) {
    $title = mysqli_real_escape_string($conn, $_POST['title']);
    $description = mysqli_real_escape_string($conn, $_POST['description']);
    $volunteer_slots = (int)$_POST['volunteer_slots']; 
    $start_date = $_POST['start_date'];
    $end_date = $_POST['end_date']; 
    $start_time = $_POST['start_time'];
    $end_time = $_POST['end_time']; 
    $type = "Volunteer";

    $errors = [];
    $today = date('Y-m-d');

    if ($volunteer_slots <= 0) {
        $errors[] = "- Available slots must be at least 1.";
    }
    if ($start_date < $today) {
        $errors[] = "- Start Date cannot be in the past.";
    }
    if ($end_date < $start_date) {
        $errors[] = "- End Date cannot be earlier than the Start Date.";
    }
    if ($start_date === $end_date && $end_time <= $start_time) {
        $errors[] = "- End Time must be later than Start Time if on the same day.";
    }

    if (!empty($errors)) {
        $error_msg = implode('\\n', $errors);
        echo "<script>
            alert('Validation Failed! Please fix the following:\\n\\n$error_msg');
            window.history.back();
        </script>";
        exit();
    }

    $image_path = "default_campaign.jpg";

    if (isset($_FILES['image']) && $_FILES['image']['error'] === UPLOAD_ERR_OK) {
        $img_size = $_FILES['image']['size'];
        $img_ext = strtolower(pathinfo($_FILES['image']['name'], PATHINFO_EXTENSION));
        $allowed_img_exts = ['jpg', 'jpeg', 'png', 'gif'];

        if (!in_array($img_ext, $allowed_img_exts)) {
            echo "<script>alert('Invalid image format! Only JPG, PNG, or GIF allowed.'); window.history.back();</script>"; exit();
        }
        if ($img_size > 5000000) {
            echo "<script>alert('Image file is too large! Maximum size is 5MB.'); window.history.back();</script>"; exit();
        }

        $image_name = time() . "_img_" . basename($_FILES['image']['name']);
        $target_file = "uploads/" . $image_name;

        if (move_uploaded_file($_FILES['image']['tmp_name'], $target_file)) {
            $image_path = $target_file;
        } else {
            echo "<script>alert('ERROR: Could not save image. Check folder permissions.'); window.history.back();</script>"; exit();
        }
    }

    $support_file_path = NULL;
    if (!empty($_FILES['support_file']['name']) && $_FILES['support_file']['error'] === UPLOAD_ERR_OK) {
        $doc_size = $_FILES['support_file']['size'];
        $doc_ext = strtolower(pathinfo($_FILES['support_file']['name'], PATHINFO_EXTENSION));
        $allowed_doc_exts = ['pdf', 'doc', 'docx'];

        if (!in_array($doc_ext, $allowed_doc_exts)) {
            echo "<script>alert('Invalid document format! Only PDF or Word documents allowed.'); window.history.back();</script>"; exit();
        }
        if ($doc_size > 5000000) {
            echo "<script>alert('Document is too large! Maximum size is 5MB.'); window.history.back();</script>"; exit();
        }

        $file_name = "DOC_" . time() . "_" . basename($_FILES['support_file']['name']);
        if (move_uploaded_file($_FILES['support_file']['tmp_name'], "uploads/" . $file_name)) {
            $support_file_path = "uploads/" . $file_name;
        }
    }

    $sql = "INSERT INTO programs (title, description, type, image_path, volunteer_slots, current_amount, start_date, end_date, start_time, end_time, support_file, created_by) 
            VALUES ('$title', '$description', '$type', '$image_path', '$volunteer_slots', 0.00, '$start_date', '$end_date', '$start_time', '$end_time', '$support_file_path', '$current_staff_id')";

    if ($conn->query($sql)) {
        echo "<script>
            alert('Success! Your volunteer program has been launched.');
            window.location.href = 'organizer_dashboard.php';
        </script>";
    } else {
        echo "<script>
            alert('Database Error: " . addslashes($conn->error) . "');
            window.history.back();
        </script>";
    }
    exit();
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Create Volunteer Program | CharityCare+</title>
    
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <link href="https://fonts.googleapis.com/css2?family=Dancing+Script:wght@600&family=Inter:wght@400;500;600;700;800&display=swap" rel="stylesheet">

    <style>
        :root {
            --prof-blue: #1e3a8a;
            --accent-blue: #2964c9;
            --light-red: #ff7675;
            --card-shadow: 0 15px 35px rgba(0, 0, 0, 0.08);
            --input-border: #cbd5e1;
            --input-focus-ring: rgba(41, 100, 201, 0.15);
        }

        body { 
            font-family: 'Inter', sans-serif; 
            background-color: #f8fafc; 
            margin: 0; 
            display: flex; 
            flex-direction: column; 
            min-height: 100vh; 
        }
        
        .navbar { 
            background: white; 
            padding: 10px 50px; 
            border-bottom: 1px solid #eee; 
            box-shadow: 0 2px 10px rgba(0, 0, 0, 0.05); 
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

        .back { 
            color: #ffffff; 
            text-decoration: none; 
            transition: 0.2s; 
        }

        .back:hover { 
            color: #dbeafe; 
            transform: translateX(-5px); 
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
        
        .main-container { 
            flex: 1; 
            display: flex; 
            justify-content: center; 
            padding: 50px 20px; 
        }

        .form-card { 
            background: white; 
            width: 100%; 
            max-width: 950px; 
            border-radius: 24px; 
            padding: 50px; 
            box-shadow: var(--card-shadow); 
            border: 1px solid #f1f5f9; 
        }
        
        .form-label { 
            font-weight: 700; 
            color: #64748b; 
            font-size: 0.75rem; 
            text-transform: uppercase; 
            letter-spacing: 0.5px; 
            margin-bottom: 10px; 
        }
        
        .form-control { 
            background: #ffffff; 
            border-radius: 12px; 
            border: 1.5px solid var(--input-border); 
            padding: 14px 20px; 
            font-weight: 500; 
            font-size: 0.95rem;
            color: #1e293b;
            transition: all 0.2s ease;
            box-shadow: 0 2px 4px rgba(0,0,0,0.01);
        }
        
        .form-control::placeholder { 
            color: #94a3b8; 
            font-weight: 400; 
        }

        .form-control:hover { 
            border-color: #94a3b8; 
        }
        
        .form-control:focus { 
            border-color: var(--accent-blue); 
            box-shadow: 0 0 0 4px var(--input-focus-ring); 
            background: #ffffff; 
        }

        .form-control::file-selector-button {
            background: #f1f5f9;
            border: none;
            border-radius: 8px;
            padding: 8px 16px;
            margin-right: 15px;
            color: #475569;
            font-weight: 700;
            font-size: 0.85rem;
            cursor: pointer;
            transition: 0.2s;
        }

        .form-control::file-selector-button:hover { 
            background: #e2e8f0; 
            color: var(--accent-blue); 
        }

        textarea.form-control { 
            resize: vertical; 
            min-height: 120px; 
        }
        
        .btn-submit { 
            background: var(--accent-blue); 
            color: white; 
            border: none; 
            border-radius: 14px; 
            padding: 16px 40px; 
            font-weight: 800; 
            font-size: 1rem; 
            letter-spacing: 0.5px; 
            transition: 0.3s; 
            width: 100%; 
            margin-top: 25px; 
        }

        .btn-submit:hover { 
            background: var(--prof-blue); 
            transform: translateY(-3px); 
            box-shadow: 0 8px 20px rgba(30, 58, 138, 0.25); 
        }
    </style>
</head>

<body>

    <nav class="navbar">
        <div class="d-flex align-items-center gap-3">
            <img src="UPTM Logo.png" alt="UPTM Logo" class="navbar-logo">
            <span class="slogan d-none d-md-block">"Caring Together, Helping Each Other"</span>
        </div>
        <div class="brand-name">Charity<span style="color: var(--accent-blue);">Care+</span></div>
    </nav>

    <div class="title-bar">
        <div style="display: flex; align-items: center; gap: 20px;">
            <a href="organizer_dashboard.php" class="back"><i class="fas fa-arrow-left fa-lg"></i></a>
            <h5>Create New Volunteer Program</h5>
        </div>
        <div class="d-flex align-items-center gap-4">
            <a href="logout.php" class="btn-logout">Sign Out</a>
        </div>
    </div>

    <main class="main-container">
        <div class="form-card">
            <form action="" method="POST" enctype="multipart/form-data">
                <div class="row">
                    <div class="col-md-8 mb-4">
                        <label class="form-label">Program Title</label>
                        <input type="text" name="title" class="form-control" placeholder="Enter the program title" required>
                    </div>
                    
                    <div class="col-md-4 mb-4">
                        <label class="form-label">Available Slots</label>
                        <input type="number" name="volunteer_slots" class="form-control" placeholder="0" required>
                    </div>

                    <div class="col-12 mb-4">
                        <label class="form-label">Program Description (Detail/Location)</label>
                        <textarea name="description" class="form-control" placeholder="Detail and place the purpose of this volunteer event and what students will be doing..." required></textarea>
                    </div>

                    <div class="col-md-3 mb-4">
                        <label class="form-label">Start Date</label>
                        <input type="date" name="start_date" class="form-control" required>
                    </div>
                    <div class="col-md-3 mb-4">
                        <label class="form-label">End Date</label>
                        <input type="date" name="end_date" class="form-control" required>
                    </div>
                    <div class="col-md-3 mb-4">
                        <label class="form-label">Start Time</label>
                        <input type="time" name="start_time" class="form-control" required>
                    </div>
                    <div class="col-md-3 mb-4">
                        <label class="form-label">End Time</label>
                        <input type="time" name="end_time" class="form-control" required>
                    </div>

                    <div class="col-md-6 mb-4">
                        <label class="form-label">Program Banner (Image)</label>
                        <input type="file" name="image" class="form-control" accept="image/jpeg, image/png, image/gif">
                        <small class="text-muted" style="font-size: 0.75rem;">Max size: 5MB (JPG, PNG, GIF)</small>
                    </div>

                    <div class="col-md-6 mb-4">
                        <label class="form-label">Support Document (Optional)</label>
                        <input type="file" name="support_file" class="form-control" accept=".pdf,.doc,.docx">
                        <small class="text-muted" style="font-size: 0.75rem;">Max size: 5MB (PDF, DOCX)</small>
                    </div>
                </div>

                <button type="submit" name="submit_volunteer" class="btn-submit">Launch Program</button>
            </form>
        </div>
    </main>

</body>
</html>