<?php
$conn = new mysqli('localhost', 'root', '', 'vendor_information');

if ($conn->connect_error) {
    http_response_code(500);
    exit("DB connection failed");
}

$table = $_POST['Table'];


if ($table === 'Shareholders'){
    $sql = "
    INSERT INTO Shareholders
    (ShareHolderID, registrationFormID, time, nationality, name, address, share)
    VALUES (?, ?, ?, ?, ?, ?, ?)
    ";

    $stmt = $conn->prepare($sql);

    $stmt->bind_param(
        "iissssd",
        $_POST['ShareHolderID'],
        $_POST['NewCompanyRegistration'],
        $_POST['time'],
        $_POST['nationality'],
        $_POST['name'],
        $_POST['address'],
        $_POST['share']
    );

    if ($stmt->execute()) {
        echo json_encode([
            "success" => true,
            "id" => $_POST['ShareHolderID']
        ]);
    } else {
        echo json_encode([
            "success" => false,
            "error" => $stmt->error
        ]);
    }
} else if($table === 'DirectorAndSecretary'){
    $sql = "
    INSERT INTO DirectorAndSecretary
    (registrationFormID, time, nationality, name, position, appointmentDate, DOB)
    VALUES (?, ?, ?, ?, ?, ?, ?)
    ";

    $stmt = $conn->prepare($sql);
    $stmt->bind_param(
        "issssss",
        $_POST['NewCompanyRegistration'],
        $_POST['time'],
        $_POST['nationality'],
        $_POST['name'],
        $_POST['position'],
        $_POST['appointmentDate'],
        $_POST['DOB']
    );

    if ($stmt->execute()) {
        echo json_encode([
            "success" => true,
        ]);
    } else {
        echo json_encode([
            "success" => false,
            "error" => $stmt->error
        ]);
    }
} else if($table === 'Management'){
    $sql = "
    INSERT INTO management
    (registrationFormID, time, nationality, name, position, yearsInPosition, yearsInRelatedField)
    VALUES (?, ?, ?, ?, ?, ?, ?)
    ";

    $stmt = $conn->prepare($sql);
    $stmt->bind_param(
        "issssii",
        $_POST['NewCompanyRegistration'],
        $_POST['time'],
        $_POST['nationality'],
        $_POST['name'],
        $_POST['position'],
        $_POST['yearsInPosition'],
        $_POST['yearsInRelatedField']
    );

    if ($stmt->execute()) {
        echo json_encode([
            "success" => true,
        ]);
    } else {
        echo json_encode([
            "success" => false,
            "error" => $stmt->error
        ]);
    }
} else if($table === 'Bank'){
    $sql = "
    INSERT INTO bank
    (registrationFormID, time, BankID, BankName, BankAddress, SWIFTCode)
    VALUES (?, ?, ?, ?, ?, ?)
    ";

    $stmt = $conn->prepare($sql);
    $stmt->bind_param(
        "isisss",
        $_POST['NewCompanyRegistration'],
        $_POST['time'],
        $_POST['BankID'],
        $_POST['BankName'],
        $_POST['BankAddress'],
        $_POST['SWIFTCode']
    );

    if ($stmt->execute()) {
        echo json_encode([
            "success" => true,
        ]);
    } else {
        echo json_encode([
            "success" => false,
            "error" => $stmt->error
        ]);
    }
} else if($table === 'Staff'){
    $sql = "
    INSERT INTO staff
    (staffNO, registrationFormID, time, name, designation, qualification, yearsOfExperience, employmentStatus, skills, RelevantCertification)
    VALUES (?,?,?,?,?,?,?,?,?,?)
    ";

    $stmt = $conn->prepare($sql);
    $stmt->bind_param(
        "iissssisss",
        $_POST['staffNO'],
        $_POST['NewCompanyRegistration'],
        $_POST['time'],
        $_POST['name'],
        $_POST['designation'],
        $_POST['qualification'],
        $_POST['yearsOfExperience'],
        $_POST['employmentStatus'],
        $_POST['skills'],
        $_POST['RelevantCertification']
    );

    if ($stmt->execute()) {
        echo json_encode([
            "success" => true,
            "id" => $_POST['staffNO']
        ]);
    } else {
        echo json_encode([
            "success" => false,
            "error" => $stmt->error
        ]);
    }
} else if($table === 'ProjectTrackRecord'){
    $sql = "
    INSERT INTO ProjectTrackRecord
    (projectRecordNo, registrationFormID, time, projectTitle, projectNature, location, clientName, projectValue, commencement, completionDate)
    VALUES (?,?,?,?,?,?,?,?,?,?)
    ";

    $stmt = $conn->prepare($sql);
    $stmt->bind_param(
        "iisssssdss",
        $_POST['projectRecordNo'],
        $_POST['NewCompanyRegistration'],
        $_POST['time'],
        $_POST['projectTitle'],
        $_POST['projectNature'],
        $_POST['location'],
        $_POST['clientName'],
        $_POST['projectValue'],
        $_POST['commencement'],
        $_POST['completionDate']
    );

    if ($stmt->execute()) {
        echo json_encode([
            "success" => true,
            "id" => $_POST['projectRecordNo']
        ]);
    } else {
        echo json_encode([
            "success" => false,
            "error" => $stmt->error
        ]);
    }
} else if($table === 'CurrentProject'){
    $sql = "
    INSERT INTO currentproject
    (CurrentProjectNo, registrationFormID, time, projectTitle, projectNature, location, clientName, projectValue, commencement, completionDate,progressOfTheWork)
    VALUES (?,?,?,?,?,?,?,?,?,?,?)
    ";

    $stmt = $conn->prepare($sql);
    $stmt->bind_param(
        "iisssssdssi",
        $_POST['CurrentProjectNo'],
        $_POST['NewCompanyRegistration'],
        $_POST['time'],
        $_POST['projectTitle'],
        $_POST['projectNature'],
        $_POST['location'],
        $_POST['clientName'],
        $_POST['projectValue'],
        $_POST['commencement'],
        $_POST['completionDate'],
        $_POST['progressOfTheWork']
    );

    if ($stmt->execute()) {
        echo json_encode([
            "success" => true,
            "id" => $_POST['CurrentProjectNo']
        ]);
    } else {
        echo json_encode([
            "success" => false,
            "error" => $stmt->error
        ]);
    }
}

