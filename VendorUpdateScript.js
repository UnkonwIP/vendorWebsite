/* AdminVendorEdit.js */
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
        if(tableName === 'NetWorth' || tableName === 'Equipment') button.textContent = "✎"; 
        button.classList.remove("btn-success");
        button.classList.add("btn-outline-primary");
        inputs.forEach(input => {
            updateTableField(tableName, rowId, input.dataset.field, input.value, idName, extraYear, extraTypeId, container);        
        });
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

    if(!id || id === "0") { row.remove(); return; }

    // CHANGED: File name
    fetch("APIDeleteTableRow.php", {
        method: "POST",
        headers: { "Content-Type": "application/x-www-form-urlencoded" },
        body: new URLSearchParams({ "ID": id, "idName": idName, "registrationFormID": formID, "Table": tableName })
    }).then(res => res.text()).then(data => { 
        if(data.trim()==="Deleted") row.remove(); 
        else alert("Error deleting: " + data);
    });
}

/** Add Row Logic */
function addEditShareholders(tableName, tableId) {
    // CHANGED: File name
    // Logic remains same as previous provided version, just updated API endpoint name
    if(!confirm("Create new blank row?")) return;
    const params = new URLSearchParams();
    params.append("Table", tableName);
    params.append("registrationFormID", formID);
    const today = new Date().toISOString().split('T')[0];

    // Build Default Params based on Table (Same as before)
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
        params.append("staffNo", 1); params.append("name", "New Staff"); params.append("designation", "-");
        params.append("qualification", "-"); params.append("yearsOfExperience", 0); params.append("employmentStatus", "Permanent");
        params.append("skills", "-"); params.append("relevantCertification", "-");
    } else if (tableName === 'ProjectTrackRecord') {
        params.append("projectRecordNo", 1); params.append("projectTitle", "New Project"); params.append("projectNature", "OSP");
        params.append("location", "-"); params.append("clientName", "-"); params.append("projectValue", 0);
        params.append("commencementDate", today); params.append("completionDate", today);
    } else if (tableName === 'CurrentProject') {
        params.append("currentProjectRecordNo", 1); params.append("projectTitle", "New Current Project"); params.append("projectNature", "OSP");
        params.append("location", "-"); params.append("clientName", "-"); params.append("projectValue", 0);
        params.append("commencementDate", today); params.append("completionDate", today); params.append("progressOfTheWork", 0);
    } else if (tableName === 'CreditFacilities') {
        params.append("typeOfCreditFacilities", "Loan"); params.append("financialInstitution", "-");
        params.append("totalAmount", 0); params.append("expiryDate", today);
        params.append("unutilisedAmountCurrentlyAvailable", 0); params.append("asAtDate", today);
    }

    // CHANGED: File Name
    fetch("APIAddTableRow.php", { method: "POST", body: params })
    .then(res => res.json())
    .then(data => {
        if(data.success) {
            // Reload page to reflect changes simply
             window.location.reload();
        } else {
            alert("Error adding row: " + (data.error || "Unknown error"));
        }
    })
    .catch(err => {
        console.error(err);
        alert("Error: Check console. Often caused by PHP warnings breaking JSON.");
    });
}
function editSpecialRow(btn, table, idName) { editTableRow(btn, table, idName); }