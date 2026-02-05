/* VendorUpdateScript.js */
const formID = document.getElementById("registrationFormID").value;
function showLoading() { document.getElementById('loadingOverlay').style.display = 'block'; }
function hideLoading() { document.getElementById('loadingOverlay').style.display = 'none'; }

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
    fetch("UpdateRegistration.php", {
        method: "POST",
        headers: { "Content-Type": "application/x-www-form-urlencoded" },
        body: new URLSearchParams({ "field": dbField, "value": value, "registrationFormID": formID, "Table": tableName })
    }).then(res => res.text()).then(data => hideLoading());
}

/** Radio Group Edit */
function editRadioGroup(button, groupId, tableName) {
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
    fetch("UpdateTableRow.php", {
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
    if(!confirm("Delete this record?")) return;
    const row = button.closest("tr");
    fetch("DeleteTableRow.php", {
        method: "POST",
        headers: { "Content-Type": "application/x-www-form-urlencoded" },
        body: new URLSearchParams({ "ID": row.dataset.id, "idName": idName, "registrationFormID": formID, "Table": tableName })
    }).then(res => res.text()).then(data => { if(data.trim()==="Deleted") row.remove(); });
}

/** Add Row Logic */
function addEditShareholders(tableName, tableId) {
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
        params.append("staffNo", 1); params.append("name", "New Staff"); params.append("designation", "-");
        params.append("qualification", "-"); params.append("yearsOfExperience", 0); params.append("employmentStatus", "Permanent");
        params.append("skills", "-"); params.append("relevantCertification", "-");
    }
    else if (tableName === 'ProjectTrackRecord') {
        params.append("projectRecordNo", 1); params.append("projectTitle", "New Project"); params.append("projectNature", "OSP");
        params.append("location", "-"); params.append("clientName", "-"); params.append("projectValue", 0);
        params.append("commencementDate", today); params.append("completionDate", today);
    }
    else if (tableName === 'CurrentProject') {
        params.append("currentProjectRecordNo", 1); params.append("projectTitle", "New Current Project"); params.append("projectNature", "OSP");
        params.append("location", "-"); params.append("clientName", "-"); params.append("projectValue", 0);
        params.append("commencementDate", today); params.append("completionDate", today); params.append("progressOfTheWork", 0);
    }
    else if (tableName === 'CreditFacilities') {
        params.append("typeOfCreditFacilities", "Loan"); params.append("financialInstitution", "-");
        params.append("totalAmount", 0); params.append("expiryDate", today);
        params.append("unutilisedAmountCurrentlyAvailable", 0); params.append("asAtDate", today);
    }

    fetch("insertUpdatedTableRow.php", { method: "POST", body: params })
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
                    <td><input type="number" data-field="staffNo" class="form-control" value="1" readonly></td>
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
            } else if(table && tableName === 'ProjectTrackRecord') {
                const tbody = table.querySelector('tbody') || table;
                tr = document.createElement('tr');
                tr.setAttribute('data-id', data.id);
                tr.innerHTML = `
                    <td><input type="number" data-field="projectRecordNo" class="form-control" value="1" readonly></td>
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
            } else if(table && tableName === 'CurrentProject') {
                const tbody = table.querySelector('tbody') || table;
                tr = document.createElement('tr');
                tr.setAttribute('data-id', data.id);
                tr.innerHTML = `
                    <td><input type="number" data-field="currentProjectRecordNo" class="form-control" value="1" readonly></td>
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