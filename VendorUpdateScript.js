// Renumber UI-only 'No' column for Staff, ProjectTrackRecord, and CurrentProject tables
function renumberTableRows(tableId) {
    const table = document.getElementById(tableId);
    if (!table) return;
    let tbody = table.querySelector('tbody');
    if (!tbody) tbody = table;
    let rows = Array.from(tbody.querySelectorAll('tr'));
    rows.forEach((row, idx) => {
        const noCell = row.querySelector('td.no-col');
        if (noCell) noCell.textContent = idx + 1;
    });
}
/* VendorUpdateScript.js */
// Import renumberTableRows function
// <script src="renumberTableRows.js"></script> (for HTML) or import if using modules
// If not using modules, ensure this file is included in HTML after renumberTableRows.js
const formID = document.getElementById("registrationFormID").value;
const VENDOR_CAN_EDIT = (typeof window.VENDOR_CAN_EDIT !== 'undefined') ? window.VENDOR_CAN_EDIT : false;
function ensureEditable() {
    if (!VENDOR_CAN_EDIT) {
        alert('Edits are locked. You can only edit the form if it was rejected by admin.');
        return false;
    }
    return true;
}
function showLoading() { document.getElementById('loadingOverlay').style.display = 'block'; }
function hideLoading() { document.getElementById('loadingOverlay').style.display = 'none'; }

/** Single Field Edit */
function editField(button, inputId, tableName) {
    if (!ensureEditable()) return;
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
    fetch("APIUpdateField.php", {
        method: "POST",
        headers: { "Content-Type": "application/x-www-form-urlencoded" },
        body: new URLSearchParams({ "field": dbField, "value": value, "registrationFormID": formID, "Table": tableName })
    }).then(res => res.text()).then(data => hideLoading());
}

/** Radio Group Edit */
function editRadioGroup(button, groupId, tableName) {
    if (!ensureEditable()) return;
    const group = document.getElementById(groupId);
    const radios = group.querySelectorAll("input[type='radio']");
    const dbField = group.dataset.field;
    if (radios[0].disabled) {
        radios.forEach(r => r.disabled = false);
        button.textContent = "Save";
        button.classList.replace("btn-outline-primary", "btn-success");
    } else {
        const selected = [...radios].find(r => r.checked);
        if (!selected) return alert("Select an option");
        radios.forEach(r => r.disabled = true);
        button.textContent = "Edit";
        button.classList.replace("btn-success", "btn-outline-primary");
        updateField(dbField, selected.value, tableName);
    }
}

/** Table Row Edit */
function editTableRow(button, tableName, idName) {
    if (!ensureEditable()) return;
    const container = button.closest("tr") || button.closest(".row-container");
    const inputs = container.querySelectorAll("input");
    const rowId = container.dataset.id;
    const extraYear = container.dataset.year || "";
    const extraTypeId = container.dataset.typeId || "";

    if (button.textContent.trim() === "Edit") {
        inputs.forEach(i => i.readOnly = false);
        button.textContent = "Save";
        button.classList.replace("btn-outline-primary", "btn-success");
        if(button.classList.contains("btn-outline-secondary")) button.classList.replace("btn-outline-secondary", "btn-success");
    } else {
        inputs.forEach(i => i.readOnly = true);
        button.textContent = "Edit";
        button.classList.replace("btn-success", "btn-outline-primary");
        inputs.forEach(input => {
            updateTableField(tableName, rowId, input.dataset.field, input.value, idName, extraYear, extraTypeId, container);
        });
    }
}

function updateTableField(tableName, rowId, dbField, value, idName, extraYear, extraTypeId, container) {
    fetch("APIUpdateTable.php", {
        method: "POST",
        headers: { "Content-Type": "application/x-www-form-urlencoded" },
        body: new URLSearchParams({
            "field": dbField, "value": value, "registrationFormID": formID,
            "Table": tableName, "rowId": rowId, "idName": idName,
            "extraYear": extraYear, "extraTypeId": extraTypeId
        })
    }).then(res => res.text()).then(data => {
        if(data.startsWith("INSERTED:")) {
            container.dataset.id = data.split(":")[1]; // Update DOM ID
        }
    });
}

/** Delete Row */
function deleteEditRow(button, tableName, idName) {
    if(!ensureEditable()) return;
    if(!confirm("Delete this record?")) return;
    const row = button.closest("tr");
    const table = row.closest('table');
    fetch("APIDeleteTableRow.php", {
        method: "POST",
        headers: { "Content-Type": "application/x-www-form-urlencoded" },
        body: new URLSearchParams({ "ID": row.dataset.id, "idName": idName, "registrationFormID": formID, "Table": tableName })
    }).then(res => res.text()).then(data => {
        if(data.trim()==="Deleted") {
            row.remove();
            if(["Staff","ProjectTrackRecord","CurrentProject"].includes(tableName) && table && table.id) {
                renumberTableRows(table.id);
            }
        }
    });
}

/** Add Row Logic */
function addEditShareholders(tableName, tableId) {
    if(!ensureEditable()) return;
    if(!confirm("Create new blank row?")) return;
    const params = new URLSearchParams();
    params.append("Table", tableName);
    params.append("registrationFormID", formID);
    const today = new Date().toISOString().split('T')[0];

    // Build Default Params based on Table
    if (tableName === 'Shareholders') {
        params.append("companyShareholderID", "000"); params.append("name", "New Shareholder");
        params.append("nationality", "Malaysia"); params.append("address", "-"); params.append("sharePercentage", 0);
    } 
    else if (tableName === 'DirectorAndSecretary') {
        params.append("name", "New Director"); params.append("nationality", "Malaysia");
        params.append("position", "Director"); params.append("appointmentDate", today); params.append("dob", today);
    }
    else if (tableName === 'Management') {
        params.append("name", "New Manager"); params.append("nationality", "Malaysia");
        params.append("position", "Manager"); params.append("yearsInPosition", 0); params.append("yearsInRelatedField", 0);
    }
    else if (tableName === 'Bank') {
        params.append("bankName", "New Bank"); params.append("bankAddress", "-"); params.append("swiftCode", "-");
    }
    else if (tableName === 'Staff') {
        params.append("name", "New Staff"); params.append("designation", "-");
        params.append("qualification", "-"); params.append("yearsOfExperience", 0); params.append("employmentStatus", "Permanent");
        params.append("skills", "-"); params.append("relevantCertification", "-");
    }
    else if (tableName === 'ProjectTrackRecord') {
        params.append("projectTitle", "New Project"); params.append("projectNature", "OSP");
        params.append("location", "-"); params.append("clientName", "-"); params.append("projectValue", 0);
        params.append("commencementDate", today); params.append("completionDate", today);
    }
    else if (tableName === 'CurrentProject') {
        params.append("projectTitle", "New Current Project"); params.append("projectNature", "OSP");
        params.append("location", "-"); params.append("clientName", "-"); params.append("projectValue", 0);
        params.append("commencementDate", today); params.append("completionDate", today); params.append("progressOfTheWork", 0);
    }
    else if (tableName === 'CreditFacilities') {
        params.append("typeOfCreditFacilities", "Loan"); params.append("financialInstitution", "-");
        params.append("totalAmount", 0); params.append("expiryDate", today);
        params.append("unutilisedAmountCurrentlyAvailable", 0); params.append("asAtDate", today);
    }

    fetch("APIAddTableRow.php", { method: "POST", body: params })
    .then(res => res.json())
    .then(data => {
        if(data.success) {
            const table = tableId ? document.getElementById(tableId) : null;
            let tr;
            if(table && tableName === 'Shareholders') {
                const tbody = table.querySelector('tbody') || table;
                tr = document.createElement('tr');
                tr.setAttribute('data-id', data.id);
                tr.innerHTML = `
                    <td><input type="text" data-field="companyShareholderID" class="form-control" value="000" readonly></td>
                    <td><input type="text" data-field="name" class="form-control" value="New Shareholder" readonly></td>
                    <td><input type="text" data-field="nationality" class="form-control" value="Malaysia" readonly></td>
                    <td><input type="text" data-field="address" class="form-control" value="-" readonly></td>
                    <td><input type="number" data-field="sharePercentage" class="form-control" value="0" step="0.01" readonly></td>
                    <td>
                        <button type="button" class="btn btn-outline-primary btn-sm" onclick="editTableRow(this, 'Shareholders', 'shareholderID')">Edit</button>
                        <button type="button" class="btn btn-outline-danger btn-sm" onclick="deleteEditRow(this, 'Shareholders', 'shareholderID')">Delete</button>
                    </td>
                `;
                tbody.appendChild(tr);
            } else if(table && tableName === 'DirectorAndSecretary') {
                const tbody = table.querySelector('tbody') || table;
                tr = document.createElement('tr');
                tr.setAttribute('data-id', data.id);
                tr.innerHTML = `
                    <td><input type="text" data-field="name" class="form-control" value="New Director" readonly></td>
                    <td><input type="text" data-field="nationality" class="form-control" value="Malaysia" readonly></td>
                    <td><input type="text" data-field="position" class="form-control" value="Director" readonly></td>
                    <td><input type="date" data-field="appointmentDate" class="form-control" value="${today}" readonly></td>
                    <td><input type="date" data-field="dob" class="form-control" value="${today}" readonly></td>
                    <td>
                        <button type="button" class="btn btn-outline-primary btn-sm" onclick="editTableRow(this, 'DirectorAndSecretary', 'directorID')">Edit</button>
                        <button type="button" class="btn btn-outline-danger btn-sm" onclick="deleteEditRow(this, 'DirectorAndSecretary', 'directorID')">Delete</button>
                    </td>
                `;
                tbody.appendChild(tr);
            } else if(table && tableName === 'Management') {
                const tbody = table.querySelector('tbody') || table;
                tr = document.createElement('tr');
                tr.setAttribute('data-id', data.id);
                tr.innerHTML = `
                    <td><input type="text" data-field="name" class="form-control" value="New Manager" readonly></td>
                    <td><input type="text" data-field="nationality" class="form-control" value="Malaysia" readonly></td>
                    <td><input type="text" data-field="position" class="form-control" value="Manager" readonly></td>
                    <td><input type="number" data-field="yearsInPosition" class="form-control" value="0" readonly></td>
                    <td><input type="number" data-field="yearsInRelatedField" class="form-control" value="0" readonly></td>
                    <td>
                        <button type="button" class="btn btn-outline-primary btn-sm" onclick="editTableRow(this, 'Management', 'managementID')">Edit</button>
                        <button type="button" class="btn btn-outline-danger btn-sm" onclick="deleteEditRow(this, 'Management', 'managementID')">Delete</button>
                    </td>
                `;
                tbody.appendChild(tr);
            } else if(table && tableName === 'Bank') {
                const tbody = table.querySelector('tbody') || table;
                tr = document.createElement('tr');
                tr.setAttribute('data-id', data.id);
                tr.innerHTML = `
                    <td><input type="text" data-field="bankName" class="form-control" value="New Bank" readonly></td>
                    <td><input type="text" data-field="bankAddress" class="form-control" value="-" readonly></td>
                    <td><input type="text" data-field="swiftCode" class="form-control" value="-" readonly></td>
                    <td>
                        <button type="button" class="btn btn-outline-primary btn-sm" onclick="editTableRow(this, 'Bank', 'bankID')">Edit</button>
                        <button type="button" class="btn btn-outline-danger btn-sm" onclick="deleteEditRow(this, 'Bank', 'bankID')">Delete</button>
                    </td>
                `;
                tbody.appendChild(tr);
            } else if(table && tableName === 'Staff') {
                const tbody = table.querySelector('tbody') || table;
                tr = document.createElement('tr');
                tr.setAttribute('data-id', data.id);
                tr.innerHTML = `
                    <td class="no-col"></td>
                    <td><input type="text" data-field="name" class="form-control" value="New Staff" readonly></td>
                    <td><input type="text" data-field="designation" class="form-control" value="-" readonly></td>
                    <td><input type="text" data-field="qualification" class="form-control" value="-" readonly></td>
                    <td><input type="number" data-field="yearsOfExperience" class="form-control" value="0" readonly></td>
                    <td><input type="text" data-field="employmentStatus" class="form-control" value="Permanent" readonly></td>
                    <td><input type="text" data-field="skills" class="form-control" value="-" readonly></td>
                    <td><input type="text" data-field="relevantCertification" class="form-control" value="-" readonly></td>
                    <td>
                        <button type="button" class="btn btn-outline-primary btn-sm" onclick="editTableRow(this, 'Staff', 'staffID')">Edit</button>
                        <button type="button" class="btn btn-outline-danger btn-sm" onclick="deleteEditRow(this, 'Staff', 'staffID')">Delete</button>
                    </td>
                `;
                tbody.appendChild(tr);
                renumberTableRows(tableId);
            } else if(table && tableName === 'ProjectTrackRecord') {
                const tbody = table.querySelector('tbody') || table;
                tr = document.createElement('tr');
                tr.setAttribute('data-id', data.id);
                tr.innerHTML = `
                    <td class="no-col"></td>
                    <td><input type="text" data-field="projectTitle" class="form-control" value="New Project" readonly></td>
                    <td><input type="text" data-field="projectNature" class="form-control" value="OSP" readonly></td>
                    <td><input type="text" data-field="location" class="form-control" value="-" readonly></td>
                    <td><input type="text" data-field="clientName" class="form-control" value="-" readonly></td>
                    <td><input type="number" data-field="projectValue" class="form-control" value="0" readonly></td>
                    <td><input type="date" data-field="commencementDate" class="form-control" value="${today}" readonly></td>
                    <td><input type="date" data-field="completionDate" class="form-control" value="${today}" readonly></td>
                    <td>
                        <button type="button" class="btn btn-outline-primary btn-sm" onclick="editTableRow(this, 'ProjectTrackRecord', 'projectRecordID')">Edit</button>
                        <button type="button" class="btn btn-outline-danger btn-sm" onclick="deleteEditRow(this, 'ProjectTrackRecord', 'projectRecordID')">Delete</button>
                    </td>
                `;
                tbody.appendChild(tr);
                renumberTableRows(tableId);
            } else if(table && tableName === 'CurrentProject') {
                const tbody = table.querySelector('tbody') || table;
                tr = document.createElement('tr');
                tr.setAttribute('data-id', data.id);
                tr.innerHTML = `
                    <td class="no-col"></td>
                    <td><input type="text" data-field="projectTitle" class="form-control" value="New Current Project" readonly></td>
                    <td><input type="text" data-field="projectNature" class="form-control" value="OSP" readonly></td>
                    <td><input type="text" data-field="location" class="form-control" value="-" readonly></td>
                    <td><input type="text" data-field="clientName" class="form-control" value="-" readonly></td>
                    <td><input type="number" data-field="projectValue" class="form-control" value="0" readonly></td>
                    <td><input type="date" data-field="commencementDate" class="form-control" value="${today}" readonly></td>
                    <td><input type="date" data-field="completionDate" class="form-control" value="${today}" readonly></td>
                    <td><input type="number" data-field="progressOfTheWork" class="form-control" value="0" readonly></td>
                    <td>
                        <button type="button" class="btn btn-outline-primary btn-sm" onclick="editTableRow(this, 'CurrentProject', 'currentProjectID')">Edit</button>
                        <button type="button" class="btn btn-outline-danger btn-sm" onclick="deleteEditRow(this, 'CurrentProject', 'currentProjectID')">Delete</button>
                    </td>
                `;
                tbody.appendChild(tr);
                renumberTableRows(tableId);
            } else if(table && tableName === 'CreditFacilities') {
                const tbody = table.querySelector('tbody') || table;
                tr = document.createElement('tr');
                tr.setAttribute('data-id', data.id);
                tr.innerHTML = `
                    <td><input type="text" data-field="typeOfCreditFacilities" class="form-control" value="Loan" readonly></td>
                    <td><input type="text" data-field="financialInstitution" class="form-control" value="-" readonly></td>
                    <td><input type="number" data-field="totalAmount" class="form-control" value="0" readonly></td>
                    <td><input type="date" data-field="expiryDate" class="form-control" value="${today}" readonly></td>
                    <td><input type="number" data-field="unutilisedAmountCurrentlyAvailable" class="form-control" value="0" readonly></td>
                    <td><input type="date" data-field="asAtDate" class="form-control" value="${today}" readonly></td>
                    <td>
                        <button type="button" class="btn btn-outline-primary btn-sm" onclick="editTableRow(this, 'CreditFacilities', 'facilityID')">Edit</button>
                        <button type="button" class="btn btn-outline-danger btn-sm" onclick="deleteEditRow(this, 'CreditFacilities', 'facilityID')">Delete</button>
                    </td>
                `;
                tbody.appendChild(tr);
            } else {
                window.location.reload();
            }
        } else {
            alert("Error adding row: " + data.error);
        }
    });
}
function editSpecialRow(btn, table, idName) { editTableRow(btn, table, idName); }

function toggleOthersDetails() {
    const othersCheckbox = document.getElementById('CIDBOthers');
    const detailsDiv = document.getElementById('CIDBOthersDetails');
    const othersInput = document.getElementById('CIDBOthersInput');
    if (othersCheckbox && othersCheckbox.checked) {
        detailsDiv.style.display = 'inline-block';
        othersInput.removeAttribute('readonly');
    } else {
        detailsDiv.style.display = 'none';
        othersInput.setAttribute('readonly', 'readonly');
        othersInput.value = '';
    }
}

function toggleTradeEditSave(e) {
    e.preventDefault();
    const btn = document.getElementById('tradeEditSaveBtn');
    if (!ensureEditable()) return;
    const isEditing = btn.textContent === 'Save';
    const checkboxes = document.querySelectorAll('#TradeGroup input[type="checkbox"]');
    const othersInput = document.getElementById('CIDBOthersInput');
    const othersCheckbox = document.getElementById('CIDBOthers');
    if (!isEditing) {
        // Switch to edit mode
        checkboxes.forEach(cb => cb.removeAttribute('disabled'));
        othersInput.removeAttribute('readonly');
        toggleOthersDetails();
        btn.textContent = 'Save';
        btn.classList.remove('btn-outline-primary');
        btn.classList.add('btn-success');
    } else {
        // Save mode
        let trades = [];
        checkboxes.forEach(cb => {
            if(cb.checked) trades.push(cb.value);
        });
        // Remove 'Others' from array if unchecked
        if (!othersCheckbox.checked) {
            trades = trades.filter(val => val !== 'Others');
        }
        let othersVal = '';
        if(othersCheckbox.checked && othersInput && othersInput.value.trim()) {
            othersVal = othersInput.value.trim();
        }
        // AJAX update for trade (standard options, including 'Others' if checked)
        const formID = document.getElementById('registrationFormID').value;
        const params1 = new URLSearchParams();
        params1.append('field', 'trade');
        params1.append('value', trades.join(','));
        params1.append('registrationFormID', formID);
        fetch('APIUpdateField.php', {
            method: 'POST',
            headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
            body: params1
        })
        .then(res => res.text())
        .then(() => {
            // Now update otherTradeDetails
            const params2 = new URLSearchParams();
            params2.append('field', 'otherTradeDetails');
            params2.append('value', othersCheckbox.checked ? othersVal : '');
            params2.append('registrationFormID', formID);
            if (!othersCheckbox.checked) {
                othersInput.value = '';
            }
            return fetch('APIUpdateField.php', {
                method: 'POST',
                headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
                body: params2
            });
        })
        .then(() => {
            alert('Trade updated successfully!');
            checkboxes.forEach(cb => cb.setAttribute('disabled', 'disabled'));
            othersInput.setAttribute('readonly', 'readonly');
            btn.textContent = 'Edit';
            btn.classList.remove('btn-success');
            btn.classList.add('btn-outline-primary');
            toggleOthersDetails();
        })
        .catch(() => alert('Error updating trade.'));
    }
}

// Resubmit handler (if present on the page)
document.addEventListener('DOMContentLoaded', function() {
    // If vendor cannot edit, hide all edit/delete/add buttons to match server rules
    if (!VENDOR_CAN_EDIT) {
        const btnSelectors = [
            "button[onclick^=\"editField\"]",
            "button[onclick^=\"editRadioGroup\"]",
            "button[onclick^=\"editTableRow\"]",
            "button[onclick^=\"deleteEditRow\"]",
            "button[onclick^=\"addEditShareholders\"]",
            "button[onclick^=\"addBanks\"]",
            "button[onclick^=\"addStaffList\"]",
            "button[onclick^=\"addProjectRecord\"]",
            "button[onclick^=\"addCurrentProjectRecord\"]",
            "button[onclick^=\"addCreditFacilities\"]",
            "button[onclick^=\"toggleTradeEditSave\"]",
            "button[onclick^=\"editSpecialRow\"]"
        ].join(',');
        try {
            document.querySelectorAll(btnSelectors).forEach(b => b.style.display = 'none');
        } catch (e) { /* ignore */ }

        // Hide any table "Action" header and its corresponding column cells
        function hideActionColumns() {
            document.querySelectorAll('table').forEach(table => {
                const headers = Array.from(table.querySelectorAll('th'));
                headers.forEach((th, idx) => {
                    const txt = (th.textContent || '').trim().toLowerCase();
                    if (txt === 'action' || txt.includes('action')) {
                        th.style.display = 'none';
                        // hide all cells in this column (including rows without tbody)
                        table.querySelectorAll('tr').forEach(tr => {
                            const cells = tr.querySelectorAll('td, th');
                            if (cells[idx]) cells[idx].style.display = 'none';
                        });
                    }
                });
            });
        }

        // initial hide
        hideActionColumns();

        // observe DOM changes so dynamically-added rows/columns are also hidden
        try {
            const observer = new MutationObserver(m => { hideActionColumns(); });
            observer.observe(document.body, { childList: true, subtree: true });
        } catch (e) { /* ignore on old browsers */ }
    }
    const resubmitBtn = document.getElementById('resubmitBtn');
    if (resubmitBtn) {
        resubmitBtn.addEventListener('click', function() {
            if (!confirm('Resubmit will send the form to admin and lock further edits. Continue?')) return;
            fetch('VendorResubmit.php', {
                method: 'POST',
                headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
                body: new URLSearchParams({ registrationFormID: formID })
            }).then(r => r.json()).then(data => {
                if (data && data.success) {
                    alert('Form resubmitted to admin. Editing is now locked.');
                    window.location.reload();
                } else {
                    alert('Resubmit failed: ' + (data.error || 'Unknown'));
                }
            }).catch(() => alert('Resubmit request failed'));
        });
    }
});