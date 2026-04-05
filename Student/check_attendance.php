<?php
include 'db.php';

$student_id = $_GET['student_id'] ?? '';
$program_id = $_GET['program_id'] ?? '';

if ($student_id && $program_id) {
    $query = "SELECT attendance_status FROM registrations 
              WHERE student_id = '$student_id' AND program_id = '$program_id'";
    $result = $conn->query($query);
    
    if ($row = $result->fetch_assoc()) {
        echo json_encode(['status' => $row['attendance_status']]);
        exit();
    }
}
echo json_encode(['status' => 'Absent']);