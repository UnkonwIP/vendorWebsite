<?php
session_start();
require_once __DIR__ . '/config.php';
date_default_timezone_set('Asia/Kuala_Lumpur');

// Admin-only endpoint to export any vendor by accountID
if (!isset($_SESSION['role']) || $_SESSION['role'] !== 'admin') {
    header('HTTP/1.1 403 Forbidden');
    echo 'Forbidden';
    exit();
}

$accountID = $_GET['accountID'] ?? '';
if (empty($accountID)) {
    echo 'Missing accountID';
    exit();
}

// Fetch vendor row
$stmt = $conn->prepare('SELECT accountID, newCompanyRegistrationNumber, username, email, vendorType FROM vendoraccount WHERE accountID = ? LIMIT 1');
$stmt->bind_param('s', $accountID);
$stmt->execute();
$vendor = $stmt->get_result()->fetch_assoc();
if (!$vendor) { echo 'Vendor not found'; exit(); }

$newCRN = $vendor['newCompanyRegistrationNumber'] ?? '';

$templatePath = __DIR__ . '/templates/vendor_report_template.html';
if (!file_exists($templatePath)) { echo 'Template missing'; exit(); }
$template = file_get_contents($templatePath);

$registration = null; $registrationFormID = null;
if (!empty($newCRN)) {
    $rstmt = $conn->prepare('SELECT * FROM registrationform WHERE newCompanyRegistrationNumber = ? ORDER BY registrationFormID DESC LIMIT 1');
    $rstmt->bind_param('s', $newCRN);
    $rstmt->execute();
    $registration = $rstmt->get_result()->fetch_assoc();
    $registrationFormID = $registration['registrationFormID'] ?? null;
}

$fetchRows = function($sql, $id) use ($conn) {
    $rows = [];
    if (!$id) return $rows;
    $s = $conn->prepare($sql);
    $s->bind_param('i', $id);
    $s->execute();
    $res = $s->get_result();
    while ($r = $res->fetch_assoc()) $rows[] = $r;
    return $rows;
};

$shareholders = $fetchRows('SELECT companyShareholderID, name, nationality, address, sharePercentage FROM shareholders WHERE registrationFormID = ?', $registrationFormID);
$directors = $fetchRows('SELECT name, nationality, position, appointmentDate, dob FROM directorandsecretary WHERE registrationFormID = ?', $registrationFormID);
$projects = $fetchRows('SELECT projectTitle, projectNature, location, clientName, projectValue, commencementDate, completionDate FROM projecttrackrecord WHERE registrationFormID = ?', $registrationFormID);
$banks = $fetchRows('SELECT bankName, bankAddress, swiftCode FROM bank WHERE registrationFormID = ?', $registrationFormID);
$networth = $fetchRows('SELECT yearOf, totalLiabilities, totalAssets, netWorth, workingCapital FROM nettworth WHERE registrationFormID = ? ORDER BY yearOf DESC', $registrationFormID);
$staff = $fetchRows('SELECT name, designation, qualification, yearsOfExperience FROM staff WHERE registrationFormID = ?', $registrationFormID);
$management = $fetchRows('SELECT name, nationality, position, yearsInPosition, yearsInRelatedField FROM management WHERE registrationFormID = ?', $registrationFormID);
$credits = $fetchRows('SELECT typeOfCreditFacilities, financialInstitution, totalAmount, expiryDate, unutilisedAmountCurrentlyAvailable, asAtDate FROM creditfacilities WHERE registrationFormID = ?', $registrationFormID);

$buildTable = function($rows, $cols) {
    if (empty($rows)) return '<p><em>None</em></p>';
    $html = '<table border="1" cellpadding="6" cellspacing="0" style="border-collapse:collapse;width:100%">';
    $html .= '<thead><tr>';
    foreach ($cols as $c) $html .= '<th>'.htmlspecialchars($c).'</th>';
    $html .= '</tr></thead><tbody>';
    foreach ($rows as $r) {
        $html .= '<tr>';
        foreach (array_keys($cols) as $k) {
            $val = $r[$k] ?? '';
            $html .= '<td>' . nl2br(htmlspecialchars((string)$val)) . '</td>';
        }
        $html .= '</tr>';
    }
    $html .= '</tbody></table>';
    return $html;
};

$replacements = [
    '{{ACCOUNT_ID}}' => htmlspecialchars($vendor['accountID'] ?? ''),
    '{{USERNAME}}' => htmlspecialchars($vendor['username'] ?? ''),
    '{{EMAIL}}' => htmlspecialchars($vendor['email'] ?? ''),
    '{{VENDOR_TYPE}}' => htmlspecialchars($vendor['vendorType'] ?? ''),
    '{{CRN}}' => htmlspecialchars($newCRN ?? ''),
    '{{COMPANY_NAME}}' => htmlspecialchars($registration['companyName'] ?? ''),
    '{{SUBMISSION_DATE}}' => htmlspecialchars($registration['formFirstSubmissionDate'] ?? ''),
    '{{SHAREHOLDERS}}' => $buildTable($shareholders, ['companyShareholderID' => 'ID', 'name' => 'Name', 'nationality' => 'Nationality', 'address' => 'Address', 'sharePercentage' => '% Shares']),
    '{{DIRECTORS}}' => $buildTable($directors, ['name'=>'Name','nationality'=>'Nationality','position'=>'Position','appointmentDate'=>'Appointment Date','dob'=>'DOB']),
    '{{PROJECTS}}' => $buildTable($projects, ['projectTitle'=>'Title','projectNature'=>'Nature','location'=>'Location','clientName'=>'Client','projectValue'=>'Value','commencementDate'=>'Start','completionDate'=>'End']),
    '{{BANKS}}' => $buildTable($banks, ['bankName'=>'Bank','bankAddress'=>'Address','swiftCode'=>'SWIFT']),
    '{{NETWORTH}}' => $buildTable($networth, ['yearOf'=>'Year','totalLiabilities'=>'Liabilities','totalAssets'=>'Assets','netWorth'=>'Net Worth','workingCapital'=>'Working Capital']),
    '{{STAFF}}' => $buildTable($staff, ['name'=>'Name','designation'=>'Designation','qualification'=>'Qualification','yearsOfExperience'=>'Years']),
    '{{MANAGEMENT}}' => $buildTable($management, ['name'=>'Name','nationality'=>'Nationality','position'=>'Position','yearsInPosition'=>'Years in Position','yearsInRelatedField'=>'Years in Field']),
    '{{CREDITFACILITIES}}' => $buildTable($credits, ['typeOfCreditFacilities'=>'Type','financialInstitution'=>'Institution','totalAmount'=>'Total','expiryDate'=>'Expiry','unutilisedAmountCurrentlyAvailable'=>'Unutilised','asAtDate'=>'As At']),
];

$html = strtr($template, $replacements);

if (!file_exists(__DIR__ . '/vendor/autoload.php')) { echo 'PDF library missing'; exit(); }
require_once __DIR__ . '/vendor/autoload.php';

$dompdf = new \Dompdf\Dompdf();
$dompdf->loadHtml($html);
$dompdf->setPaper('A4','portrait');
$dompdf->render();

$pdfOutput = $dompdf->output();
$fileName = 'vendor_' . preg_replace('/[^a-zA-Z0-9_-]/', '_', $vendor['accountID']) . '.pdf';

header('Content-Type: application/pdf');
header('Content-Disposition: attachment; filename="' . $fileName . '"');
header('Content-Length: ' . strlen($pdfOutput));
echo $pdfOutput;
exit();

?>
