<?php
session_start();
include 'db.php';

// Redirect if already logged in
if (isset($_SESSION['admin_id'])) {
    header("Location: admin_dashboard.php");
    exit();
}

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $user = mysqli_real_escape_string($conn, $_POST['username']);
    $pass = $_POST['password'];

    $result = $conn->query("SELECT * FROM admin WHERE username='$user'");
    
    if ($result->num_rows > 0) {
        $admin = $result->fetch_assoc();
        // For simplicity in FYP, plain text or password_verify can be used
        if ($pass == $admin['password']) {
            $_SESSION['admin_id'] = $admin['admin_id'];
            $_SESSION['admin_name'] = $admin['full_name'];
            
            // NEW: Success Validation Popup before redirect
            echo "<script>
                alert('Login Successful! Welcome back, Administrator " . addslashes($admin['full_name']) . ".');
                window.location.href = 'admin_dashboard.php';
            </script>";
            exit();
        }
    }
    $error = "Invalid administrator credentials.";
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Admin Login | CharityCare+</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;600;700;800&display=swap" rel="stylesheet">
    
    <style>
        :root {
            --navy-blue: #1e3a8a;
        }

        body { 
            font-family: 'Inter', sans-serif;
            background: #f8fafc; 
            height: 100vh; 
            display: flex; 
            align-items: center; 
            justify-content: center; 
            margin: 0;
        }

        .login-card { 
            background: white; 
            padding: 45px 40px; 
            border-radius: 25px; 
            box-shadow: 0 10px 30px rgba(0,0,0,0.05); 
            border: 1px solid #e2e8f0;
            width: 100%; 
            max-width: 400px; 
        }

        .brand-accent { 
            color: var(--navy-blue); 
        }

        .btn-login { 
            background-color: var(--navy-blue); 
            color: white; 
            transition: 0.3s; 
            border: none; 
        }

        .btn-login:hover { 
            background-color: #172554; 
            color: white; 
        }
    </style>
</head>
<body>
    <div class="login-card">
        <div class="text-center mb-4">
            <img src="UPTM Logo.png" alt="UPTM Logo" style="height: 50px; margin-bottom: 15px;">
            <h4 class="fw-bold m-0" style="color: #000;">Charity<span class="brand-accent">Care+</span></h4>
            <small class="text-muted fw-bold" style="letter-spacing: 1px; font-size: 0.7rem;">ADMINISTRATOR PORTAL</small>
        </div>
        
        <?php if(isset($error)): ?>
            <div class="alert alert-danger small py-2 fw-bold text-center border-0 bg-danger bg-opacity-10 text-danger rounded-3">
                <?php echo $error; ?>
            </div>
        <?php endif; ?>

        <form method="POST">
            <div class="mb-3">
                <label class="small fw-bold text-muted mb-1">Username</label>
                <input type="text" name="username" class="form-control rounded-3 py-2" required>
            </div>
            <div class="mb-4">
                <label class="small fw-bold text-muted mb-1">Password</label>
                <input type="password" name="password" class="form-control rounded-3 py-2" required>
            </div>
            <button type="submit" class="btn btn-login w-100 py-2 fw-bold rounded-pill">Secure Login</button>
        </form>
    </div>
</body>
</html>