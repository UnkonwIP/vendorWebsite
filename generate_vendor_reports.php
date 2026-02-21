<?php
// generate_vendor_reports.php
// Usage (CLI): php generate_vendor_reports.php [--pdf]

require_once __DIR__ . '/config.php';
date_default_timezone_set('Asia/Kuala_Lumpur');

$outDir = __DIR__ . '/output/vendors';
if (!is_dir($outDir)) mkdir($outDir, 0755, true);

$enablePdf = in_array('--pdf', $argv ?? []);

function safeFetchAssoc($stmt) {
    $stmt->execute();
    $res = $stmt->get_result();
    return $res && $res->num_rows ? $res->fetch_assoc() : null;
}

// Prepare statements
$vendorStmt = $conn->prepare("SELECT accountID, newCompanyRegistrationNumber, username, email, role, vendorType FROM vendoraccount WHERE role = 'vendor'");
$regStmt = $conn->prepare("SELECT * FROM registrationform WHERE newCompanyRegistrationNumber = ? ORDER BY registrationFormID DESC LIMIT 1");
$shareholdersStmt = $conn->prepare("SELECT companyShareholderID, name, nationality, address, sharePercentage FROM shareholders WHERE registrationFormID = ?");
$directorsStmt = $conn->prepare("SELECT name, nationality, position, appointmentDate, dob FROM directorandsecretary WHERE registrationFormID = ?");
$projectsStmt = $conn->prepare("SELECT projectTitle, projectNature, location, clientName, projectValue, commencementDate, completionDate FROM projecttrackrecord WHERE registrationFormID = ?");
$bankStmt = $conn->prepare("SELECT bankName, bankAddress, swiftCode FROM bank WHERE registrationFormID = ?");
$networthStmt = $conn->prepare("SELECT yearOf, totalLiabilities, totalAssets, netWorth, workingCapital FROM nettworth WHERE registrationFormID = ? ORDER BY yearOf DESC");
$staffStmt = $conn->prepare("SELECT name, designation, qualification, yearsOfExperience FROM staff WHERE registrationFormID = ?");
$managementStmt = $conn->prepare("SELECT name, nationality, position, yearsInPosition, yearsInRelatedField FROM management WHERE registrationFormID = ?");
$creditStmt = $conn->prepare("SELECT typeOfCreditFacilities, financialInstitution, totalAmount, expiryDate, unutilisedAmountCurrentlyAvailable, asAtDate FROM creditfacilities WHERE registrationFormID = ?");

$templatePath = __DIR__ . '/templates/civil_registration_template.html';
$template = file_exists($templatePath) ? file_get_contents($templatePath) : null;
if (!$template) {
    echo "Template not found at templates/vendor_report_template.html\n";
    exit(1);
}

$vendorStmt->execute();
$vendors = $vendorStmt->get_result();
if (!$vendors) {
    echo "No vendors found or query failed.\n";
    exit(0);
}

while ($v = $vendors->fetch_assoc()) {
    $accountID = $v['accountID'];
    $newCRN = $v['newCompanyRegistrationNumber'] ?? '';

    // Fetch registration form if available
    $registration = null;
    $registrationFormID = null;
    if (!empty($newCRN)) {
        $regStmt->bind_param('s', $newCRN);
        $registration = safeFetchAssoc($regStmt);
        $registrationFormID = $registration['registrationFormID'] ?? null;
    }

    // Helper to fetch table rows
    $fetchRows = function($stmt, $id) {
        $rows = [];
        if (!$id) return $rows;
        $stmt->bind_param('i', $id);
        $stmt->execute();
        $res = $stmt->get_result();
        while ($r = $res->fetch_assoc()) $rows[] = $r;
        return $rows;
    };

    $shareholders = $fetchRows($shareholdersStmt, $registrationFormID);
    $directors = $fetchRows($directorsStmt, $registrationFormID);
    $projects = $fetchRows($projectsStmt, $registrationFormID);
    $banks = $fetchRows($bankStmt, $registrationFormID);
    $networth = $fetchRows($networthStmt, $registrationFormID);
    $staff = $fetchRows($staffStmt, $registrationFormID);
    $management = $fetchRows($managementStmt, $registrationFormID);
    $credits = $fetchRows($creditStmt, $registrationFormID);

    // equipment
    $equipmentStmt = $conn->prepare("SELECT equipmentID, quantity, brand, rating, ownership, yearsOfManufacture, registrationNo FROM equipment WHERE registrationFormID = ?");
    $equipment = [];
    if ($registrationFormID) {
        $equipment = $fetchRows($equipmentStmt, $registrationFormID);
    }

    // Build replacements
    $replacements = [
        '{{ACCOUNT_ID}}' => htmlspecialchars($accountID),
        '{{USERNAME}}' => htmlspecialchars($v['username'] ?? ''),
        '{{EMAIL}}' => htmlspecialchars($v['email'] ?? ''),
        '{{VENDOR_TYPE}}' => htmlspecialchars($v['vendorType'] ?? ''),
        '{{CRN}}' => htmlspecialchars($newCRN ?? ''),
        '{{COMPANY_NAME}}' => htmlspecialchars($registration['companyName'] ?? ''),
        '{{SUBMISSION_DATE}}' => htmlspecialchars($registration['formFirstSubmissionDate'] ?? ''),
        // common registration fields
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
    ];

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
            // fallback: if numeric and >15
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
    $typeVal = $registration['typeOfOrganisation'] ?? $registration['TypeOfOrganisation'] ?? $registration['TypeOfOrganisation'] ?? '';
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
            // fallback: try to interpret numeric or other variants
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

    // Build HTML for each section
    $buildTable = function($rows, $cols) {
        if (empty($rows)) return '<p><em>None</em></p>';
        $html = '<table class="table" border="1" cellpadding="6" cellspacing="0" style="border-collapse:collapse;width:100%">';
        $html .= '<thead><tr>';
        foreach ($cols as $c) $html .= "<th>".htmlspecialchars($c)."</th>";
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

    // build rows-only (no table wrapper) for use when template contains table header
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

    $shareholdersHtml = $buildRows($shareholders, ['name' => 'Name', 'nationality' => 'Nationality / Jurisdiction', 'companyShareholderID' => 'ID', 'address' => 'Address', 'sharePercentage' => '% Shares']);
    $directorsHtml = $buildTable($directors, ['name'=>'Name','nationality'=>'Nationality','position'=>'Position','appointmentDate'=>'Appointment Date','dob'=>'DOB']);
    $projectsHtml = $buildTable($projects, ['projectTitle'=>'Title','projectNature'=>'Nature','location'=>'Location','clientName'=>'Client','projectValue'=>'Value','commencementDate'=>'Start','completionDate'=>'End']);
    $banksHtml = $buildTable($banks, ['bankName'=>'Bank','bankAddress'=>'Address','swiftCode'=>'SWIFT']);
    // Build networth table to match VendorUpdatePage layout: Item | Year-1 (RM) | Year-2 (RM) | Year-3 (RM)
    if (empty($networth)) {
        $networthHtml = '<p><em>None</em></p>';
    } else {
        // networth rows come ordered by year DESC; take up to 3 most recent
        $years = array_slice($networth, 0, 3);
        // ensure consistent order left->right from most recent to older
        $headers = [];
        foreach ($years as $r) $headers[] = htmlspecialchars($r['yearOf']);
        // if less than 3, pad with empty headers
        for ($i = count($headers); $i < 3; $i++) $headers[] = '';

        $formatVal = function($v) {
            if ($v === null || $v === '') return '';
            if (is_numeric($v)) return number_format((float)$v, 2);
            return htmlspecialchars((string)$v);
        };

        $rowsByYear = [];
        foreach ($years as $r) {
            $rowsByYear[$r['yearOf']] = $r;
        }

        $items = [
            'Total Liabilities' => 'totalLiabilities',
            'Total Assets' => 'totalAssets',
            'Net Worth (Assets - Liabilities)' => 'netWorth',
            'Working Capital' => 'workingCapital'
        ];

        $networthHtml = '<table class="table" border="1" cellpadding="6" cellspacing="0" style="border-collapse:collapse;width:100%">';
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
            // pad if fewer than 3 years
            for ($i = count($years); $i < 3; $i++) $networthHtml .= '<td></td>';
            $networthHtml .= '</tr>';
        }
        $networthHtml .= '</tbody></table>';
    }
    $staffHtml = $buildTable($staff, ['name'=>'Name','designation'=>'Designation','qualification'=>'Qualification','yearsOfExperience'=>'Years']);
    $managementHtml = $buildTable($management, ['name'=>'Name','nationality'=>'Nationality','position'=>'Position','yearsInPosition'=>'Years in Position','yearsInRelatedField'=>'Years in Field']);
    // Build Credits table with full column names and (RM) on amount columns
    if (empty($credits)) {
        $creditsHtml = '<p><em>None</em></p>';
    } else {
        $formatVal = function($v) {
            if ($v === null || $v === '') return '';
            if (is_numeric($v)) return number_format((float)$v, 2);
            return htmlspecialchars((string)$v);
        };
        $creditsHtml = '<table class="table" border="1" cellpadding="6" cellspacing="0" style="border-collapse:collapse;width:100%">';
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

    $replacements['{{SHAREHOLDERS}}'] = $shareholdersHtml;
    $replacements['{{DIRECTORS}}'] = $directorsHtml;
    $replacements['{{PROJECTS}}'] = $projectsHtml;
    $replacements['{{BANKS}}'] = $banksHtml;
    $replacements['{{NETWORTH}}'] = $networthHtml;
    $replacements['{{STAFF}}'] = $staffHtml;
    $replacements['{{MANAGEMENT}}'] = $managementHtml;
    $replacements['{{CREDITFACILITIES}}'] = $creditsHtml;
    // equipment table
    $equipmentHtml = '';
    if (!empty($equipment)) {
        $equipmentHtml = $buildTable($equipment, ['equipmentID'=>'ID','quantity'=>'Qty','brand'=>'Brand','rating'=>'Rating','ownership'=>'Ownership','yearsOfManufacture'=>'Year of Mfg','registrationNo'=>'Reg No']);
    } else {
        $equipmentHtml = '<p><em>None</em></p>';
    }
    $replacements['{{EQUIPMENT}}'] = $equipmentHtml;

    $html = strtr($template, $replacements);

    $outHtmlPath = $outDir . "/{$accountID}.html";
    file_put_contents($outHtmlPath, $html);
    echo "Wrote: {$outHtmlPath}\n";

    // PDF generation (optional)
    if ($enablePdf) {
        // Prefer Dompdf if available
        if (file_exists(__DIR__ . '/vendor/autoload.php')) {
            require_once __DIR__ . '/vendor/autoload.php';
            $dompdf = new \Dompdf\Dompdf();
            $dompdf->loadHtml($html);
            $dompdf->setPaper('A4', 'portrait');
            $dompdf->render();
            $pdfPath = $outDir . "/{$accountID}.pdf";
            file_put_contents($pdfPath, $dompdf->output());
            echo "Wrote PDF: {$pdfPath}\n";
        } else {
            echo "Dompdf not found. Install dompdf via composer or run wkhtmltopdf manually.\n";
        }
    }
}

echo "Done. HTML outputs are in output/vendors/\n";
if (!$enablePdf) echo "Run with --pdf to attempt PDF generation (requires dompdf).\n";

?>
