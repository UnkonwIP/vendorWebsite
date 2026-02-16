/* VendorUpdateScript.js */

// --- PASTE THIS AT THE VERY TOP OF THE FILE ---
document.addEventListener("DOMContentLoaded", function() {
        // Auto-number No columns on page load
        autoNumberAllTables();
    // 1. Re-open the accordion that was active
    const openAccordionId = sessionStorage.getItem('openAccordion');
    if (openAccordionId) {
        const el = document.getElementById(openAccordionId);
        if (el && window.bootstrap) {
            // Use Bootstrap API to open it properly without animation delay
            new bootstrap.Collapse(el, { show: true, toggle: false });
        } else if (el) {
            // Fallback if Bootstrap API isn't ready
            el.classList.add('show'); 
        }
        sessionStorage.removeItem('openAccordion');
    }

    // 2. Scroll to the saved position (with a tiny delay to let the layout settle)
    const scrollPos = sessionStorage.getItem('scrollPosition');
    if (scrollPos) {
        setTimeout(function() {
            window.scrollTo(0, parseInt(scrollPos));
            sessionStorage.removeItem('scrollPosition');
        }, 100); // 100ms delay ensures the accordion is fully open first
    }
});
// ----------------------------------------------

const formID = document.getElementById("registrationFormID").value;

function showLoading() { 
    if(!document.getElementById('loadingOverlay')) {
        const div = document.createElement('div');
        div.id = 'loadingOverlay';
        div.style.cssText = 'display:none; position:fixed; top:0; left:0; width:100%; height:100%; background:rgba(255,255,255,0.7); z-index:9999; text-align:center; padding-top:20%; font-weight:bold;';
        div.innerText = 'Saving...';
        document.body.appendChild(div);
    }
    document.getElementById('loadingOverlay').style.display = 'block'; 
}

function hideLoading() { 
    const el = document.getElementById('loadingOverlay');
    if(el) el.style.display = 'none'; 
}

/** Single Field Edit */
function editField(button, inputId, tableName) {
    const input = document.getElementById(inputId);
    const dbField = input.dataset.field;
    if (input.readOnly) {
        input.readOnly = false;
        input.classList.add("bg-white", "border-primary");
        button.textContent = "Save";
        button.classList.replace("btn-outline-primary", "btn-success");
    } else {
        input.readOnly = true;
        input.classList.remove("bg-white", "border-primary");
        button.textContent = "Edit";
        button.classList.replace("btn-success", "btn-outline-primary");
        updateField(dbField, input.value, tableName);
    }
}

function updateField(dbField, value, tableName) {
    showLoading();
    // CHANGED: File name
    fetch("APIUpdateField.php", {
        method: "POST",
        headers: { "Content-Type": "application/x-www-form-urlencoded" },
        body: new URLSearchParams({ "field": dbField, "value": value, "registrationFormID": formID, "Table": tableName })
    })
    .then(res => res.text())
    .then(data => { hideLoading(); if(data.trim() !== "Updated") alert("Error Saving: " + data); })
    .catch(err => { hideLoading(); alert("Network Error: " + err); });
}

/** Radio Group Edit */
function editRadioGroup(button, groupId, tableName) {
    const group = document.getElementById(groupId);
    const radios = group.querySelectorAll("input[type='radio']");
    const dbField = group.dataset.field;
    if (radios[0].disabled) {
        radios.forEach(r => r.disabled = false);
        button.textContent = "Save";
        button.classList.replace("btn-secondary", "btn-success");
    } else {
        const selected = [...radios].find(r => r.checked);
        if (!selected) return alert("Please select an option.");
        radios.forEach(r => r.disabled = true);
        button.textContent = "Edit";
        button.classList.replace("btn-success", "btn-secondary");
        updateField(dbField, selected.value, tableName);
    }
}

/** Table Row Edit */
function editTableRow(button, tableName, idName) {
    const container = button.closest("[data-id]");
    if(!container) return;
    const inputs = container.querySelectorAll("input, textarea");
    const rowId = container.dataset.id;
    const extraYear = container.dataset.year || "";
    const extraTypeId = container.dataset.typeId || "";
    const isEditing = button.textContent.trim() === "Save" || button.innerHTML.includes("check");

    if (!isEditing) {
        inputs.forEach(i => i.readOnly = false);
        button.textContent = "Save";
        button.classList.remove("btn-outline-primary", "btn-outline-secondary");
        button.classList.add("btn-success");
    } else {
        inputs.forEach(i => i.readOnly = true);
        button.textContent = "Edit"; 
        if(tableName === 'NetWorth' || tableName === 'Equipment') button.textContent = "Edit"; 
        button.classList.remove("btn-success");
        button.classList.add("btn-outline-primary");
        inputs.forEach(input => {
            updateTableField(tableName, rowId, input.dataset.field, input.value, idName, extraYear, extraTypeId, container);        
        });
        // After saving edits, re-number No column for relevant tables
        if (["Staff", "ProjectTrackRecord", "CurrentProject"].includes(tableName)) {
            const tableId = tableName === "Staff" ? "StaffTeamTable" : (tableName === "ProjectTrackRecord" ? "ProjectRecordTable" : "CurrentProjTable");
            const table = document.getElementById(tableId);
            if (table && table.tBodies[0]) autoNumberTable(table.tBodies[0]);
        }
    }
}

function updateTableField(tableName, rowId, dbField, value, idName, extraYear, extraTypeId, container) {
    // CHANGED: File name
    fetch("APIUpdateTable.php", {
        method: "POST",
        headers: { "Content-Type": "application/x-www-form-urlencoded" },
        body: new URLSearchParams({
            "field": dbField, "value": value, "registrationFormID": formID,
            "Table": tableName, "rowId": rowId, "idName": idName,
            "extraYear": extraYear, "extraTypeId": extraTypeId 
        })
    })
    .then(res => res.text()).then(data => {
        if(data.startsWith("INSERTED:")) container.dataset.id = data.split(":")[1]; 
    });
}

/** Delete Row with Protection */
function deleteEditRow(button, tableName, idName) {
    const row = button.closest("tr");
    const tbody = row.closest("tbody");
    // NEW: Check row count. If 1 or less, block delete.
    if(tbody && tbody.children.length <= 1) {
        alert("You cannot delete the only remaining row.");
        return;
    }
    if(!confirm("Are you sure you want to delete this record?")) return;
    const id = row.dataset.id;
    if(!id || id === "0") { row.remove(); autoNumberTable(tbody); return; }
    fetch("APIDeleteTableRow.php", {
        method: "POST",
        headers: { "Content-Type": "application/x-www-form-urlencoded" },
        body: new URLSearchParams({ "ID": id, "idName": idName, "registrationFormID": formID, "Table": tableName })
    }).then(res => res.text()).then(data => { 
        if(data.trim()==="Deleted") { row.remove(); autoNumberTable(tbody); }
        else alert("Error deleting: " + data);
    });
}

/** Add Row Logic General use */ 
function addEditShareholders(tableName, tableId) {
    // 1. Ask for confirmation
    if(!confirm("Create a new blank row?")) return;
    
    // 2. Prepare Data
    const params = new URLSearchParams();
    params.append("Table", tableName);
    params.append("registrationFormID", formID);
    
    // (Your existing params setup matches APIAddTableRow.php defaults)
    const today = new Date().toISOString().split('T')[0];

    if (tableName === 'Shareholders') {
        params.append("companyShareholderID", "000"); params.append("name", "New Shareholder");
        params.append("nationality", "Malaysia"); params.append("address", "-"); params.append("sharePercentage", 0);
    } else if (tableName === 'DirectorAndSecretary') {
        params.append("name", "New Director"); params.append("nationality", "Malaysia");
        params.append("position", "Director"); params.append("appointmentDate", today); params.append("dob", today);
    } else if (tableName === 'Management') {
        params.append("name", "New Manager"); params.append("nationality", "Malaysia");
        params.append("position", "Manager"); params.append("yearsInPosition", 0); params.append("yearsInRelatedField", 0);
    } else if (tableName === 'Bank') {
        params.append("bankName", "New Bank"); params.append("bankAddress", "-"); params.append("swiftCode", "-");
    } else if (tableName === 'Staff') {
        params.append("name", "New Staff"); params.append("designation", "-");
        params.append("qualification", "-"); params.append("yearsOfExperience", 0); params.append("employmentStatus", "Permanent");
        params.append("skills", "-"); params.append("relevantCertification", "-");
    } else if (tableName === 'ProjectTrackRecord') {
        params.append("projectTitle", "New Project"); params.append("projectNature", "OSP");
        params.append("location", "-"); params.append("clientName", "-"); params.append("projectValue", 0);
        params.append("commencementDate", today); params.append("completionDate", today);
    } else if (tableName === 'CurrentProject') {
        params.append("projectTitle", "New Current Project"); params.append("projectNature", "OSP");
        params.append("location", "-"); params.append("clientName", "-"); params.append("projectValue", 0);
        params.append("commencementDate", today); params.append("completionDate", today); params.append("progressOfTheWork", 0);
    } else if (tableName === 'CreditFacilities') {
        params.append("typeOfCreditFacilities", "Loan"); params.append("financialInstitution", "-");
        params.append("totalAmount", 0); params.append("expiryDate", today);
        params.append("unutilisedAmountCurrentlyAvailable", 0); params.append("asAtDate", today);
    }

    // 3. Send AJAX Request
    showLoading(); // Optional: Show a spinner
    fetch("APIAddTableRow.php", { method: "POST", body: params })
    .then(res => res.json())
    .then(data => {
        hideLoading(); // Hide spinner
        if(data.success) {
            // STOP! Do not reload. Instead, draw the row manually.
            renderNewRow(tableName, tableId, data.id, today);
        } else {
            alert("Error adding row: " + (data.error || "Unknown error"));
        }
    })
    .catch(err => {
        hideLoading();
        console.error(err);
        alert("Error: Check console.");
    });
}

/**
 * Helper function to generate HTML for the new row without reloading
 */
function renderNewRow(tableName, tableId, newId, todayDate) {
    const tableBody = document.querySelector(`#${tableId} tbody`);
    if (!tableBody) return alert("Table body not found!");

    const newRow = document.createElement("tr");
    newRow.dataset.id = newId;
    let html = "";
    // Insert No column for Staff, ProjectTrackRecord, CurrentProject
    if (tableName === 'Staff') {
        html = `<td></td>` + `
            <td><input type="text" data-field="name" class="form-control" value="New Staff" readonly></td>
            <td><input type="text" data-field="designation" class="form-control" value="-" readonly></td>
            <td><input type="text" data-field="qualification" class="form-control" value="-" readonly></td>
            <td><input type="number" data-field="yearsOfExperience" class="form-control" value="0" readonly></td>
            <td><input type="text" data-field="employmentStatus" class="form-control" value="Permanent" readonly></td>
            <td><input type="text" data-field="skills" class="form-control" value="-" readonly></td>
            <td><input type="text" data-field="relevantCertification" class="form-control" value="-" readonly></td>
            <td>${getActionButtons(tableName, 'staffID')}</td>
        `;
    } else if (tableName === 'ProjectTrackRecord') {
        html = `<td></td>
            <td><input type="text" data-field="projectTitle" class="form-control" value="New Project" readonly></td>
            <td><input type="text" data-field="projectNature" class="form-control" value="OSP" readonly></td>
            <td><input type="text" data-field="location" class="form-control" value="-" readonly></td>
            <td><input type="text" data-field="clientName" class="form-control" value="-" readonly></td>
            <td><input type="number" data-field="projectValue" class="form-control" value="0" readonly></td>
            <td><input type="date" data-field="commencementDate" class="form-control" value="${todayDate}" readonly></td>
            <td><input type="date" data-field="completionDate" class="form-control" value="${todayDate}" readonly></td>
            <td>${getActionButtons(tableName, 'projectRecordID')}</td>
        `;
    } else if (tableName === 'CurrentProject') {
        html = `<td></td>
            <td><input type="text" data-field="projectTitle" class="form-control" value="New Current Project" readonly></td>
            <td><input type="text" data-field="projectNature" class="form-control" value="OSP" readonly></td>
            <td><input type="text" data-field="location" class="form-control" value="-" readonly></td>
            <td><input type="text" data-field="clientName" class="form-control" value="-" readonly></td>
            <td><input type="number" data-field="projectValue" class="form-control" value="0" readonly></td>
            <td><input type="date" data-field="commencementDate" class="form-control" value="${todayDate}" readonly></td>
            <td><input type="date" data-field="completionDate" class="form-control" value="${todayDate}" readonly></td>
            <td><input type="number" data-field="progressOfTheWork" class="form-control" value="0" readonly></td>
            <td>${getActionButtons(tableName, 'currentProjectID')}</td>
        `;
    } else {
        // All other tables (Shareholders, Management, etc.)
        // ...existing code...
        if (tableName === 'Shareholders') {
            html = `
                <td><input type="text" data-field="companyShareholderID" class="form-control" value="000" readonly></td>
                <td><input type="text" data-field="name" class="form-control" value="New Shareholder" readonly></td>
                <td><input type="text" data-field="nationality" class="form-control" value="Malaysia" readonly></td>
                <td><input type="text" data-field="address" class="form-control" value="-" readonly></td>
                <td><input type="number" data-field="sharePercentage" class="form-control" value="0" step="0.01" readonly></td>
                <td>${getActionButtons(tableName, 'shareholderID')}</td>
            `;
        } else if (tableName === 'DirectorAndSecretary') {
            html = `
                <td><input type="text" data-field="name" class="form-control" value="New Director" readonly></td>
                <td><input type="text" data-field="nationality" class="form-control" value="Malaysia" readonly></td>
                <td><input type="text" data-field="position" class="form-control" value="Director" readonly></td>
                <td><input type="date" data-field="appointmentDate" class="form-control" value="${todayDate}" readonly></td>
                <td><input type="date" data-field="dob" class="form-control" value="${todayDate}" readonly></td>
                <td>${getActionButtons(tableName, 'directorID')}</td>
            `;
        } else if (tableName === 'Management') {
            html = `
                <td><input type="text" data-field="name" class="form-control" value="New Manager" readonly></td>
                <td><input type="text" data-field="nationality" class="form-control" value="Malaysia" readonly></td>
                <td><input type="text" data-field="position" class="form-control" value="Manager" readonly></td>
                <td><input type="number" data-field="yearsInPosition" class="form-control" value="0" readonly></td>
                <td><input type="number" data-field="yearsInRelatedField" class="form-control" value="0" readonly></td>
                <td>${getActionButtons(tableName, 'managementID')}</td>
            `;
        } else if (tableName === 'Bank') {
            html = `
                <td><input type="text" data-field="bankName" class="form-control" value="New Bank" readonly></td>
                <td><input type="text" data-field="bankAddress" class="form-control" value="-" readonly></td>
                <td><input type="text" data-field="swiftCode" class="form-control" value="-" readonly></td>
                <td>${getActionButtons(tableName, 'bankID')}</td>
            `;
        } else if (tableName === 'CreditFacilities') {
            html = `
                <td><input type="text" data-field="typeOfCreditFacilities" class="form-control" value="Loan" readonly></td>
                <td><input type="text" data-field="financialInstitution" class="form-control" value="-" readonly></td>
                <td><input type="number" data-field="totalAmount" class="form-control" value="0" readonly></td>
                <td><input type="number" data-field="unutilisedAmountCurrentlyAvailable" class="form-control" value="0" readonly></td>
                <td><input type="date" data-field="expiryDate" class="form-control" value="${todayDate}" readonly></td>
                <td><input type="date" data-field="asAtDate" class="form-control" value="${todayDate}" readonly></td>
                <td>${getActionButtons(tableName, 'facilityID')}</td>
            `;
        }
    }
    newRow.innerHTML = html;
    tableBody.appendChild(newRow);
    // Re-number No column for these tables
    if (["Staff","ProjectTrackRecord","CurrentProject"].includes(tableName)) {
        autoNumberTable(tableBody);
    }
}

function autoNumberTable(tbody) {
    if (!tbody) return;
    let n = 1;
    for (const row of tbody.rows) {
        // Find the first cell (No column)
        const noCell = row.cells[0];
        if (noCell) {
            // If it contains an input, replace with plain number
            if (noCell.querySelector('input')) {
                noCell.innerHTML = n;
            } else {
                noCell.textContent = n;
            }
        }
        n++;
    }
}

// Auto-numbering helpers
function autoNumberAllTables() {
    ["StaffTeamTable","ProjectRecordTable","CurrentProjTable"].forEach(id => {
        const table = document.getElementById(id);
        if (table && table.tBodies[0]) autoNumberTable(table.tBodies[0]);
    });
}

// Helper to generate the Edit/Delete buttons dynamically
function getActionButtons(tableName, idName) {
    return `
        <div>
            <button class="btn btn-outline-primary btn-sm" onclick="editTableRow(this, '${tableName}', '${idName}')">Edit</button>
            <button class="btn btn-outline-danger btn-sm" onclick="deleteEditRow(this, '${tableName}', '${idName}')">Delete</button>
        </div>
    `;
}
function editSpecialRow(btn, table, idName) { editTableRow(btn, table, idName); }