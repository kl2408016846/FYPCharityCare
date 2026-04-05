<?php
include 'db.php';
$s_id = $_GET['s_id'];
$p_id = $_GET['p_id'];

// Check the database for this specific student and program
$result = $conn->query("SELECT attendance_status FROM registrations WHERE student_id='$s_id' AND program_id='$p_id'");
$row = $result->fetch_assoc();

// Send the status back to the phone as JSON
echo json_encode(['status' => $row['attendance_status']]);
?>