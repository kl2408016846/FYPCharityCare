<?php
session_start();
include 'db.php';

if (!isset($_SESSION['student_id'])) {
    header("Location: login.php");
    exit();
}

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $student_id = $_SESSION['student_id'];
    $program_id = mysqli_real_escape_string($conn, $_POST['program_id']);
    $type = mysqli_real_escape_string($conn, $_POST['type']); // 'Volunteer' or 'Donation'
    $amount = isset($_POST['amount']) ? (float)$_POST['amount'] : 0.00;
    
    // Set initial statuses based on type
    $status = ($type === 'Donation') ? 'Completed' : 'Approved';
    $attendance = ($type === 'Volunteer') ? 'Absent' : NULL;

    // Duplicate Check for Volunteers
    if ($type === 'Volunteer') {
        $check_sql = "SELECT id FROM registrations 
                      WHERE student_id = '$student_id' 
                      AND program_id = '$program_id' 
                      AND registration_type = 'Volunteer'";
        $check_result = $conn->query($check_sql);

        if ($check_result->num_rows > 0) {
            echo "<script>alert('Localhost says: Already registered as a volunteer!'); window.location.href='dashboard.php';</script>";
            exit();
        }
    }

    // Database Insertion: Includes all critical columns
    $sql = "INSERT INTO registrations (student_id, program_id, registration_type, amount, status, attendance_status) 
            VALUES ('$student_id', '$program_id', '$type', '$amount', '$status', " . ($attendance ? "'$attendance'" : "NULL") . ")";

    if ($conn->query($sql) === TRUE) {
        // Update program total for donations
        if ($type === 'Donation') {
            $conn->query("UPDATE programs SET current_amount = current_amount + $amount WHERE id = '$program_id'");
        }
        echo "<script>alert('Localhost says: " . $type . " Registration Successful!'); window.location.href='myprograms.php';</script>";
    } else {
        $error = addslashes($conn->error);
        echo "<script>alert('Localhost says: Error: " . $error . "'); window.location.href='dashboard.php';</script>";
    }
    exit();
}
?>