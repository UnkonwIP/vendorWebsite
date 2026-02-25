<?php
header('Content-Type: application/json'); // important: tell browser it's JSON

require_once "config.php";

if ($conn->connect_error) {
    http_response_code(500);
    echo json_encode(["error" => "Connection failed"]);
    exit;
}

$registration = $_POST['registration'] ?? '';

$stmt = $conn->prepare("SELECT time FROM registrationform WHERE newCompanyRegistrationNumber = ?");
$stmt->bind_param("s", $registration);
$stmt->execute();
$result = $stmt->get_result();

$data = [];

if ($result->num_rows > 0) {
    while ($row = $result->fetch_assoc()) {
        // Push each row as an object
        $data[] = [
            "time" => $row['time'],              // for dropdown value/text
        ];
    }
}

// return JSON (empty array if no results)
echo json_encode($data);
