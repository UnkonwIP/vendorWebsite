<?php
session_start();
require_once __DIR__ . '/config.php';
date_default_timezone_set('Asia/Kuala_Lumpur');

// Only vendor or admin with accountID allowed
if (!isset($_SESSION['role'])) {
    header('HTTP/1.1 403 Forbidden');
    echo 'Forbidden';
    exit();
}

$accountID = $_SESSION['accountID'] ?? '';
if (empty($accountID)) {
    header('Location: index.php');
    exit();
}

// Fetch vendor row
$stmt = $conn->prepare('SELECT accountID, newCompanyRegistrationNumber, username, email, vendorType FROM vendoraccount WHERE accountID = ? LIMIT 1');
$stmt->bind_param('s', $accountID);
$stmt->execute();
$vendor = $stmt->get_result()->fetch_assoc();

$newCRN = $vendor['newCompanyRegistrationNumber'] ?? '';

// Load template
$templatePath = __DIR__ . '/templates/civil_registration_template.html';
if (!file_exists($templatePath)) {
    echo 'Template missing';
    exit();
}
$template = file_get_contents($templatePath);

// Fetch latest registration form for this CRN
// Allow optional form id parameter (GET 'id' or POST 'registrationFormID') to export a specific form
$registration = null;
$registrationFormID = null;
$requestedID = null;
if (!empty($_REQUEST['registrationFormID'])) $requestedID = intval($_REQUEST['registrationFormID']);
if (!empty($_GET['id'])) $requestedID = intval($_GET['id']);

if (!empty($requestedID)) {
    // fetch by ID and verify it belongs to this vendor
    $rstmt = $conn->prepare('SELECT * FROM registrationform WHERE registrationFormID = ? LIMIT 1');
    $rstmt->bind_param('i', $requestedID);
    $rstmt->execute();
    $registration = $rstmt->get_result()->fetch_assoc();
    if ($registration && ($registration['newCompanyRegistrationNumber'] ?? '') === $newCRN) {
        $registrationFormID = $registration['registrationFormID'];
    } else {
        // Invalid access
        header('HTTP/1.1 403 Forbidden');
        echo 'Forbidden';
        exit();
    }
} else {
    if (!empty($newCRN)) {
        $rstmt = $conn->prepare('SELECT * FROM registrationform WHERE newCompanyRegistrationNumber = ? ORDER BY registrationFormID DESC LIMIT 1');
        $rstmt->bind_param('s', $newCRN);
        $rstmt->execute();
        $registration = $rstmt->get_result()->fetch_assoc();
        $registrationFormID = $registration['registrationFormID'] ?? null;
    }
}

// helper to fetch rows
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
$currentProjects = $fetchRows('SELECT projectTitle, projectNature, location, clientName, projectValue, commencementDate, completionDate, progressOfTheWork FROM currentproject WHERE registrationFormID = ?', $registrationFormID);
$banks = $fetchRows('SELECT bankName, bankAddress, swiftCode FROM bank WHERE registrationFormID = ?', $registrationFormID);
$networth = $fetchRows('SELECT yearOf, totalLiabilities, totalAssets, netWorth, workingCapital FROM nettworth WHERE registrationFormID = ? ORDER BY yearOf DESC', $registrationFormID);
$staff = $fetchRows('SELECT name, designation, qualification, yearsOfExperience FROM staff WHERE registrationFormID = ?', $registrationFormID);
$management = $fetchRows('SELECT name, nationality, position, yearsInPosition, yearsInRelatedField FROM management WHERE registrationFormID = ?', $registrationFormID);
$credits = $fetchRows('SELECT typeOfCreditFacilities, financialInstitution, totalAmount, expiryDate, unutilisedAmountCurrentlyAvailable, asAtDate FROM creditfacilities WHERE registrationFormID = ?', $registrationFormID);
// equipment
// Fetch equipment with human-friendly type from equipmentused when available
$joinSql = "SELECT e.equipmentID, IFNULL(eu.equipmentType, CAST(e.equipmentID AS CHAR)) AS equipmentType, e.quantity, e.brand, e.rating, e.ownership, e.yearsOfManufacture, e.registrationNo FROM equipment e LEFT JOIN equipmentused eu ON e.equipmentID = eu.equipmentID WHERE e.registrationFormID = ?";
$equipmentStmt = $conn->prepare($joinSql);
if ($equipmentStmt !== false) {
    $equipment = $fetchRows($joinSql, $registrationFormID);
} else {
    // fallback: alias equipmentID as equipmentType
    $equipment = $fetchRows('SELECT equipmentID AS equipmentType, quantity, brand, rating, ownership, yearsOfManufacture, registrationNo FROM equipment WHERE registrationFormID = ?', $registrationFormID);
}

// contacts
$contacts = $fetchRows('SELECT contactPersonName, department, telephoneNumber, emailAddress, contactStatus FROM contacts WHERE registrationFormID = ?', $registrationFormID);

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

// rows-only builder for insertion into an existing table
$buildRows = function($rows, $cols) {
    if (empty($rows)) return '<tr><td colspan="' . count($cols) . '"><em>None</em></td></tr>';
    $html = '';
    foreach ($rows as $r) {
        $html .= '<tr>';
        foreach (array_keys($cols) as $k) {
            $val = $r[$k] ?? '';
            $html .= '<td>' . nl2br(htmlspecialchars((string)$val)) . '</td>';
        }
        $html .= '</tr>';
    }
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
    // include registration fields
    '{{telephoneNumber}}' => htmlspecialchars($registration['telephoneNumber'] ?? ''),
    '{{otherNames}}' => htmlspecialchars($registration['otherNames'] ?? ''),
    '{{taxRegistrationNumber}}' => htmlspecialchars($registration['taxRegistrationNumber'] ?? ''),
    '{{oldCompanyRegistrationNumber}}' => htmlspecialchars($registration['oldCompanyRegistrationNumber'] ?? ''),
    '{{faxNo}}' => htmlspecialchars($registration['faxNo'] ?? ''),
    '{{emailAddress}}' => htmlspecialchars($registration['emailAddress'] ?? ''),
    '{{countryOfIncorporation}}' => htmlspecialchars($registration['countryOfIncorporation'] ?? ''),
    '{{dateOfIncorporation}}' => htmlspecialchars($registration['dateOfIncorporation'] ?? ''),
    '{{website}}' => htmlspecialchars($registration['website'] ?? ''),
    '{{parentCompany}}' => htmlspecialchars($registration['parentCompany'] ?? $registration['ParentCompany'] ?? $registration['parent_company'] ?? ''),
    '{{parentCompanyCountry}}' => htmlspecialchars($registration['parentCompanyCountry'] ?? $registration['ParentCompanyCountry'] ?? $registration['parent_company_country'] ?? ''),
    '{{ultimateParentCompany}}' => htmlspecialchars($registration['ultimateParentCompany'] ?? $registration['UltimateParentCompany'] ?? $registration['ultimate_parent_company'] ?? ''),
    '{{ultimateParentCompanyCountry}}' => htmlspecialchars($registration['ultimateParentCompanyCountry'] ?? $registration['UltimateParentCompanyCountry'] ?? $registration['ultimate_parent_company_country'] ?? ''),
    '{{registeredAddress}}' => htmlspecialchars($registration['registeredAddress'] ?? ''),
    '{{BranchAddress}}' => htmlspecialchars($registration['BranchAddress'] ?? $registration['branchAddress'] ?? $registration['branchaddress'] ?? ''),
    '{{AuthorisedCapital}}' => htmlspecialchars($registration['AuthorisedCapital'] ?? $registration['authorisedCapital'] ?? $registration['Authorisedcapital'] ?? ''),
    '{{PaidUpCapital}}' => htmlspecialchars($registration['PaidUpCapital'] ?? $registration['paidUpCapital'] ?? $registration['paidupcapital'] ?? ''),
    '{{correspondenceAddress}}' => htmlspecialchars($registration['correspondenceAddress'] ?? ''),
    '{{typeOfOrganisation}}' => htmlspecialchars($registration['typeOfOrganisation'] ?? ''),
    '{{companyOrganisation}}' => htmlspecialchars($registration['companyOrganisation'] ?? ''),
    '{{natureAndLineOfBusiness}}' => htmlspecialchars($registration['natureAndLineOfBusiness'] ?? ''),
    '{{auditorCompanyName}}' => htmlspecialchars($registration['auditorCompanyName'] ?? ''),
    '{{auditorName}}' => htmlspecialchars($registration['auditorName'] ?? ''),
    '{{auditorPhone}}' => htmlspecialchars($registration['auditorPhone'] ?? ''),
    '{{auditorCompanyAddress}}' => htmlspecialchars($registration['auditorCompanyAddress'] ?? ''),
    '{{auditorEmail}}' => htmlspecialchars($registration['auditorEmail'] ?? ''),
    '{{auditorYearOfService}}' => htmlspecialchars($registration['auditorYearOfService'] ?? ''),
    '{{advocatesCompanyName}}' => htmlspecialchars($registration['advocatesCompanyName'] ?? ''),
    '{{advocatesName}}' => htmlspecialchars($registration['advocatesName'] ?? ''),
    '{{advocatesPhone}}' => htmlspecialchars($registration['advocatesPhone'] ?? ''),
    '{{advocatesCompanyAddress}}' => htmlspecialchars($registration['advocatesCompanyAddress'] ?? ''),
    '{{advocatesEmail}}' => htmlspecialchars($registration['advocatesEmail'] ?? ''),
    '{{advocatesYearOfService}}' => htmlspecialchars($registration['advocatesYearOfService'] ?? ''),
    '{{verifierName}}' => htmlspecialchars($registration['verifierName'] ?? ''),
    '{{verifierDesignation}}' => htmlspecialchars($registration['verifierDesignation'] ?? ''),
    '{{dateOfVerification}}' => htmlspecialchars($registration['dateOfVerification'] ?? ''),
    '{{BANKRUPTCY_DETAILS}}' => htmlspecialchars($registration['bankruptcy-details'] ?? $registration['bankruptcyDetails'] ?? $registration['bankruptcy_details'] ?? $registration['bankruptcy_detail'] ?? $registration['description'] ?? $registration['Description'] ?? ''),
    '{{SHAREHOLDERS}}' => $buildRows($shareholders, ['name' => 'Name', 'nationality' => 'Nationality / Jurisdiction', 'companyShareholderID' => 'ID', 'address' => 'Address', 'sharePercentage' => '% Shares']),
    '{{DIRECTORS}}' => $buildTable($directors, ['name'=>'Name','nationality'=>'Nationality','position'=>'Position','appointmentDate'=>'Appointment Date','dob'=>'DOB']),
    '{{PROJECTS_PAST}}' => $buildTable($projects, ['projectTitle'=>'Title','projectNature'=>'Nature','location'=>'Location','clientName'=>'Client','projectValue'=>'Value','commencementDate'=>'Start','completionDate'=>'End']),
    '{{PROJECTS_CURRENT}}' => $buildTable($currentProjects, ['projectTitle'=>'Title','projectNature'=>'Nature','location'=>'Location','clientName'=>'Client','projectValue'=>'Value','commencementDate'=>'Start','completionDate'=>'End','progressOfTheWork'=>'Progress (%)']),
    '{{BANKS}}' => $buildTable($banks, ['bankName'=>'Bank','bankAddress'=>'Address','swiftCode'=>'SWIFT']),
    '{{NETWORTH}}' => $buildTable($networth, ['yearOf'=>'Year','totalLiabilities'=>'Liabilities','totalAssets'=>'Assets','netWorth'=>'Net Worth','workingCapital'=>'Working Capital']),
    '{{STAFF}}' => $buildTable($staff, ['name'=>'Name','designation'=>'Designation','qualification'=>'Qualification','yearsOfExperience'=>'Years']),
    '{{MANAGEMENT}}' => $buildTable($management, ['name'=>'Name','nationality'=>'Nationality','position'=>'Position','yearsInPosition'=>'Years in Position','yearsInRelatedField'=>'Years in Field']),
    '{{CREDITFACILITIES}}' => $buildTable($credits, ['typeOfCreditFacilities'=>'Type','financialInstitution'=>'Institution','totalAmount'=>'Total','expiryDate'=>'Expiry','unutilisedAmountCurrentlyAvailable'=>'Unutilised','asAtDate'=>'As At']),
    '{{EQUIPMENT}}' => (!empty($equipment) ? $buildTable($equipment, ['equipmentType'=>'Equipment Type','quantity'=>'Qty','brand'=>'Brand','rating'=>'Rating','ownership'=>'Ownership','yearsOfManufacture'=>'Year of Mfg','registrationNo'=>'Reg No']) : '<p><em>None</em></p>'),
    '{{CONTACTS}}' => (!empty($contacts) ? $buildRows($contacts, ['contactPersonName'=>'Contact Person','department'=>'Department','telephoneNumber'=>'Telephone','emailAddress'=>'Email','contactStatus'=>'Status']) : '<tr><td colspan="5"><em>None</em></td></tr>'),
];

// Prefer contacts table values (contactStatus "Primary" / "Secondary"); fallback to registration fields
$primaryContact = null;
$secondaryContact = null;
foreach ($contacts as $c) {
    $status = strtolower(trim((string)($c['contactStatus'] ?? '')));
    if ($status === 'primary' || strpos($status, 'primary') !== false) {
        $primaryContact = $c; continue;
    }
    if ($status === 'secondary' || strpos($status, 'secondary') !== false) {
        $secondaryContact = $c; continue;
    }
}
// fallback: assign first/second if statuses not set
if ($primaryContact === null && isset($contacts[0])) $primaryContact = $contacts[0];
if ($secondaryContact === null && isset($contacts[1])) $secondaryContact = $contacts[1];

$replacements['{{PrimaryContactPerson}}'] = htmlspecialchars($primaryContact['contactPersonName'] ?? $registration['PrimaryContactPerson'] ?? $registration['primaryContactPerson'] ?? $registration['primary_contact_person'] ?? '');
$replacements['{{PrimaryDepartment}}'] = htmlspecialchars($primaryContact['department'] ?? $registration['PrimaryDepartment'] ?? $registration['primaryDepartment'] ?? $registration['primary_department'] ?? '');
$replacements['{{PrimaryTelephone}}'] = htmlspecialchars($primaryContact['telephoneNumber'] ?? $registration['PrimaryTelephone'] ?? $registration['PrimaryTelephoneNumber'] ?? $registration['primaryTelephone'] ?? '');
$replacements['{{PrimaryEmail}}'] = htmlspecialchars($primaryContact['emailAddress'] ?? $registration['PrimaryEmail'] ?? $registration['primaryEmail'] ?? $registration['PrimaryEmailAddress'] ?? '');

$replacements['{{SecondaryContactPerson}}'] = htmlspecialchars($secondaryContact['contactPersonName'] ?? $registration['SecondaryContactPerson'] ?? $registration['secondaryContactPerson'] ?? $registration['secondary_contact_person'] ?? '');
$replacements['{{SecondaryDepartment}}'] = htmlspecialchars($secondaryContact['department'] ?? $registration['SecondaryDepartment'] ?? $registration['secondaryDepartment'] ?? $registration['secondary_department'] ?? '');
$replacements['{{SecondaryTelephone}}'] = htmlspecialchars($secondaryContact['telephoneNumber'] ?? $registration['SecondaryTelephone'] ?? $registration['secondaryTelephone'] ?? $registration['SecondaryTelephoneNumber'] ?? '');
$replacements['{{SecondaryEmail}}'] = htmlspecialchars($secondaryContact['emailAddress'] ?? $registration['SecondaryEmail'] ?? $registration['secondaryEmail'] ?? $registration['SecondaryEmailAddress'] ?? '');

// CIDB / Technical Capability replacements
$replacements['{{CIDB_GRADE}}'] = htmlspecialchars($registration['cidbGrade'] ?? $registration['CIDBGrade'] ?? '');
$replacements['{{CIDB_SPECIALIZATION}}'] = htmlspecialchars($registration['cidbSpecialization'] ?? $registration['CIDBSpecialization'] ?? '');
$replacements['{{CIDB_VALIDITY}}'] = htmlspecialchars($registration['cidbValidationTill'] ?? $registration['CIDBValidityDate'] ?? $registration['CIDBValidity'] ?? '');
$rawTrades = $registration['trade'] ?? $registration['CIDBTrade'] ?? $registration['CIDBTradeFinal'] ?? '';
$tradeArr = array_filter(array_map('trim', preg_split('/[,;]+/', (string)$rawTrades)));
$otherTradeDetails = trim((string)($registration['otherTradeDetails'] ?? $registration['otherTradeDetails'] ?? ''));
$tradeOptions = ['ISP','OSP','O&M','M&E','Others'];
$tradeHtmlParts = [];
foreach ($tradeOptions as $opt) {
    $checked = in_array($opt, $tradeArr) ? 'checked' : '';
    $tradeHtmlParts[] = '<label style="margin-right:8px"><input type="checkbox" disabled ' . $checked . '> ' . htmlspecialchars($opt) . '</label>';
}
if (in_array('Others', $tradeArr) && $otherTradeDetails !== '') {
    $tradeHtmlParts[] = '<span style="font-style:italic">(' . htmlspecialchars($otherTradeDetails) . ')</span>';
}
$replacements['{{CIDB_TRADES}}'] = implode(' ', $tradeHtmlParts);

// Radio groups for ValueOfSimilarProject and ValueOfCurrentProject
$similarVal = trim((string)($registration['valueOfSimilarProject'] ?? $registration['ValueOfSimilarProject'] ?? $registration['ValueOfSimilarProjectRaw'] ?? ''));
$currentVal = trim((string)($registration['valueOfCurrentProject'] ?? $registration['ValueOfCurrentProject'] ?? $registration['ValueOfCurrentProjectRaw'] ?? ''));
$similarOptions = [
    '>15M' => 'More than RM15M',
    '10M-14.9M' => 'RM10M and More but less than RM14.9M',
    '5M-9.9M' => 'RM5M and more but less than RM9.9M',
    '1M-4.9M' => 'RM1M and more but less than RM4.9M',
    '<1M' => 'Less than RM1M'
];
$currentOptions = [
    '>5M' => 'More than RM5M',
    '2M-4.9M' => 'RM2M and more but less than RM4.9M',
    '0.5M-1.9M' => 'RM0.5M and more but less than RM1.9M',
    '<0.5M' => 'Less than RM0.5M'
];
$simHtml = [];
foreach ($similarOptions as $k => $label) {
    $chk = (strcasecmp($k, $similarVal) === 0) ? 'checked' : '';
    $simHtml[] = '<div style="margin-bottom:4px"><label><input type="radio" disabled ' . $chk . '> ' . htmlspecialchars($label) . '</label></div>';
}
$curHtml = [];
foreach ($currentOptions as $k => $label) {
    $chk = (strcasecmp($k, $currentVal) === 0) ? 'checked' : '';
    $curHtml[] = '<div style="margin-bottom:4px"><label><input type="radio" disabled ' . $chk . '> ' . htmlspecialchars($label) . '</label></div>';
}
$replacements['{{VALUE_SIMILAR_RADIOS}}'] = implode('', $simHtml);
$replacements['{{VALUE_CURRENT_RADIOS}}'] = implode('', $curHtml);
$replacements['{{ValueOfSimilarProject}}'] = htmlspecialchars($registration['valueOfSimilarProject'] ?? $registration['ValueOfSimilarProject'] ?? '');
$replacements['{{ValueOfCurrentProject}}'] = htmlspecialchars($registration['valueOfCurrentProject'] ?? $registration['ValueOfCurrentProject'] ?? '');
$replacements['{{ExperienceInIndustry}}'] = htmlspecialchars($registration['yearsOfExperienceInIndustry'] ?? $registration['ExperienceInIndustry'] ?? $registration['experienceInIndustry'] ?? '');

// Combined Grade + Specialisation token (e.g., "G7 B EE")
$grade = trim((string)($registration['cidbGrade'] ?? $registration['CIDBGrade'] ?? ''));
$spec = trim((string)($registration['cidbSpecialization'] ?? $registration['CIDBSpecialization'] ?? ''));
$combined = $grade;
if ($spec !== '') {
    // If spec contains multiple codes/words, normalize spacing
    $specNorm = preg_replace('/\s+/', ' ', $spec);
    $combined .= ($combined !== '' ? ' ' : '') . $specNorm;
}
$replacements['{{CIDB_GRADE_SPECIAL}}'] = htmlspecialchars($combined);

// Build NETWORTH table horizontally to match VendorUpdatePage (Item | Year cols)
if (empty($networth)) {
    $networthHtml = '<p><em>None</em></p>';
} else {
    $years = array_slice($networth, 0, 3);
    $headers = [];
    foreach ($years as $r) $headers[] = htmlspecialchars($r['yearOf']);
    for ($i = count($headers); $i < 3; $i++) $headers[] = '';

    $formatVal = function($v) {
        if ($v === null || $v === '') return '';
        if (is_numeric($v)) return number_format((float)$v, 2);
        return htmlspecialchars((string)$v);
    };

    $items = [
        'Total Liabilities' => 'totalLiabilities',
        'Total Assets' => 'totalAssets',
        'Net Worth (Assets - Liabilities)' => 'netWorth',
        'Working Capital' => 'workingCapital'
    ];

    $networthHtml = '<table border="1" cellpadding="6" cellspacing="0" style="border-collapse:collapse;width:100%">';
    $networthHtml .= '<thead><tr><th>Item</th>';
    foreach ($headers as $h) $networthHtml .= '<th>' . ($h !== '' ? $h . ' (RM)' : '') . '</th>';
    $networthHtml .= '</tr></thead><tbody>';

    foreach ($items as $label => $field) {
        $networthHtml .= '<tr>';
        $networthHtml .= '<td>' . htmlspecialchars($label) . '</td>';
        foreach ($years as $y) {
            $val = $y[$field] ?? '';
            $networthHtml .= '<td style="text-align:right">' . $formatVal($val) . '</td>';
        }
        for ($i = count($years); $i < 3; $i++) $networthHtml .= '<td></td>';
        $networthHtml .= '</tr>';
    }
    $networthHtml .= '</tbody></table>';
}

$replacements['{{NETWORTH}}'] = $networthHtml;

// Credit facilities radio state
$cfRaw = strtolower(trim((string)($registration['creditFacilitiesStatus'] ?? $registration['creditFacilities'] ?? $registration['CreditFacilitiesStatus'] ?? '')));
$replacements['{{CREDITFACILITIES_YES}}'] = '';
$replacements['{{CREDITFACILITIES_NO}}'] = '';
if ($cfRaw !== '') {
    if (strpos($cfRaw, 'y') === 0 || strpos($cfRaw, 'yes') !== false || in_array($cfRaw, ['1','true','t'])) {
        $replacements['{{CREDITFACILITIES_YES}}'] = 'checked';
    } else {
        $replacements['{{CREDITFACILITIES_NO}}'] = 'checked';
    }
} else {
    $replacements['{{CREDITFACILITIES_NO}}'] = 'checked';
}

// Build Credits table with full column names and (RM) on amount columns
if (empty($credits)) {
    $creditsHtml = '<p><em>None</em></p>';
} else {
    $formatVal = function($v) {
        if ($v === null || $v === '') return '';
        if (is_numeric($v)) return number_format((float)$v, 2);
        return htmlspecialchars((string)$v);
    };
    $creditsHtml = '<table border="1" cellpadding="6" cellspacing="0" style="border-collapse:collapse;width:100%">';
    $creditsHtml .= '<thead><tr>';
    $creditsHtml .= '<th>Type of Credit</th>';
    $creditsHtml .= '<th>Institution/Bank</th>';
    $creditsHtml .= '<th>Total Amount (RM)</th>';
    $creditsHtml .= '<th>Unutilised Amount Currently Available (RM)</th>';
    $creditsHtml .= '<th>Expiry Date</th>';
    $creditsHtml .= '<th>As At Date</th>';
    $creditsHtml .= '</tr></thead><tbody>';
    foreach ($credits as $c) {
        $creditsHtml .= '<tr>';
        $creditsHtml .= '<td>' . htmlspecialchars($c['typeOfCreditFacilities'] ?? '') . '</td>';
        $creditsHtml .= '<td>' . htmlspecialchars($c['financialInstitution'] ?? '') . '</td>';
        $creditsHtml .= '<td style="text-align:right">' . $formatVal($c['totalAmount'] ?? '') . '</td>';
        $creditsHtml .= '<td style="text-align:right">' . $formatVal($c['unutilisedAmountCurrentlyAvailable'] ?? '') . '</td>';
        $creditsHtml .= '<td>' . htmlspecialchars(($c['expiryDate'] ?? '') == '0000-00-00' ? '' : ($c['expiryDate'] ?? '')) . '</td>';
        $creditsHtml .= '<td>' . htmlspecialchars(($c['asAtDate'] ?? '') == '0000-00-00' ? '' : ($c['asAtDate'] ?? '')) . '</td>';
        $creditsHtml .= '</tr>';
    }
    $creditsHtml .= '</tbody></table>';
}

$replacements['{{CREDITFACILITIES}}'] = $creditsHtml;

// Company organisation radio checked state (follow registration.php options)
$orgVal = $registration['companyOrganisation'] ?? $registration['CompanyOrganisation'] ?? '';
$orgRaw = strtolower(trim((string)$orgVal));
$orgChecks = [
    'ORG_MORE_THAN_15' => '',
    'ORG_10_15' => '',
    'ORG_5_10' => '',
    'ORG_LESS_5' => '',
];
if ($orgRaw !== '') {
    if (strpos($orgRaw, 'more') !== false || strpos($orgRaw, 'more than') !== false) $orgChecks['ORG_MORE_THAN_15'] = 'checked';
    elseif (strpos($orgRaw, '10') !== false && strpos($orgRaw, '-') !== false) $orgChecks['ORG_10_15'] = 'checked';
    elseif (strpos($orgRaw, '5') !== false && strpos($orgRaw, '-') !== false) $orgChecks['ORG_5_10'] = 'checked';
    elseif (strpos($orgRaw, 'less') !== false) $orgChecks['ORG_LESS_5'] = 'checked';
    else {
        $num = filter_var($orgRaw, FILTER_SANITIZE_NUMBER_INT);
        if ($num !== '' && is_numeric($num)) {
            $n = (int)$num;
            if ($n > 15) $orgChecks['ORG_MORE_THAN_15'] = 'checked';
            elseif ($n >= 10) $orgChecks['ORG_10_15'] = 'checked';
            elseif ($n >= 5) $orgChecks['ORG_5_10'] = 'checked';
            else $orgChecks['ORG_LESS_5'] = 'checked';
        }
    }
}
foreach ($orgChecks as $k => $v) $replacements['{{' . $k . '}}'] = $v;

// TypeOfOrganisation radio checked state (match registration.php options)
$typeVal = $registration['typeOfOrganisation'] ?? $registration['TypeOfOrganisation'] ?? '';
$typeRaw = strtolower(trim((string)$typeVal));
$typeChecks = [
    'TYPE_BERHAD' => '',
    'TYPE_SDN' => '',
    'TYPE_SOLE' => '',
];
if ($typeRaw !== '') {
    if (strpos($typeRaw, 'berhad') !== false) $typeChecks['TYPE_BERHAD'] = 'checked';
    elseif (strpos($typeRaw, 'sdn') !== false) $typeChecks['TYPE_SDN'] = 'checked';
    elseif (strpos($typeRaw, 'sole') !== false || strpos($typeRaw, 'proprietor') !== false) $typeChecks['TYPE_SOLE'] = 'checked';
    else {
        if (strpos($typeRaw, 's') === 0) $typeChecks['TYPE_SDN'] = 'checked';
        else $typeChecks['TYPE_SOLE'] = 'checked';
    }
}
foreach ($typeChecks as $k => $v) $replacements['{{' . $k . '}}'] = $v;

// Bankruptcy radio checked state + details
$bankRaw = strtolower(trim((string)($registration['bankruptHistory'] ?? $registration['bankrupthistory'] ?? $registration['bankruptcy'] ?? $registration['Bankruptcy'] ?? $registration['bankruptcyStatus'] ?? $registration['bankruptcy_status'] ?? '')));
$replacements['{{BANKRUPTCY_YES}}'] = '';
$replacements['{{BANKRUPTCY_NO}}'] = '';
if ($bankRaw !== '') {
    if (strpos($bankRaw, 'y') === 0 || strpos($bankRaw, 'yes') !== false || in_array($bankRaw, ['1','true','t'])) {
        $replacements['{{BANKRUPTCY_YES}}'] = 'checked';
    } else {
        $replacements['{{BANKRUPTCY_NO}}'] = 'checked';
    }
} else {
    $replacements['{{BANKRUPTCY_NO}}'] = 'checked';
}

$html = strtr($template, $replacements);

// Render PDF
if (!file_exists(__DIR__ . '/vendor/autoload.php')) {
    echo 'PDF generation library missing. Run composer install.';
    exit();
}
require_once __DIR__ . '/vendor/autoload.php';

$dompdf = new \Dompdf\Dompdf();
$dompdf->loadHtml($html);
$dompdf->setPaper('A4', 'portrait');
$dompdf->render();

$pdfOutput = $dompdf->output();
$fileName = 'vendor_' . preg_replace('/[^a-zA-Z0-9_-]/', '_', $vendor['accountID']) . '.pdf';

header('Content-Type: application/pdf');
header('Content-Disposition: attachment; filename="' . $fileName . '"');
header('Content-Length: ' . strlen($pdfOutput));
echo $pdfOutput;
exit();

?>
