function addShareholders(){
    const table = document.getElementById("shareholderTable");
    
    const newShareholderRow = table.insertRow(-1);
    
    const fields = [
        "shareholderName[]",
        "ShareholderNationality[]",
        "shareholderAddress[]",
    ];
    
    const Numbercell = newShareholderRow.insertCell();
    const Numberinput = document.createElement("input");
    Numberinput.type = "number";
    Numberinput.name = "shareholderID[]";
    Numberinput.step = "1";
    Numbercell.appendChild(Numberinput);
    
    fields.forEach(name => {
        const cell = newShareholderRow.insertCell();
        const input = document.createElement("input");
        input.type = "text";
        input.name = name;
        cell.appendChild(input);
    });
    
    const Percentcell = newShareholderRow.insertCell();
    const Percentinput = document.createElement("input");
    Percentinput.type = "number";
    Percentinput.name = "ShareholderPercent[]";
    Percentinput.step = "0.01";
    Percentinput.min = "0";
    Percentinput.max = "100";
    Percentcell.appendChild(Percentinput);
    
    const deleteCell = newShareholderRow.insertCell();
    const deleteButton = document.createElement("button");
    deleteButton.type = "button";
    deleteButton.textContent= "Delete";
    deleteButton.onclick = function() {
        deleteRow(this);
    };
    deleteCell.appendChild(deleteButton);
        
}

function deleteRow(button){
    const row = button.closest("tr");
    row.remove();
}

function addDirector(){
    const table = document.getElementById("DirectorTable");
    
    const newDirectorRow = table.insertRow(-1);
    
    const fields = [
        "DirectorName[]",
        "DirectorNationality[]",
        "DirectorPosition[]",
    ];
    
    const Datefields = [
        "DirectorAppointmentDate[]",
        "DirectorDOB[]",
    ];
    
    fields.forEach(name => {
        const cell = newDirectorRow.insertCell();
        const input = document.createElement("input");
        input.type = "text";
        input.name = name;
        cell.appendChild(input);
    });
    
    Datefields.forEach(name => {
        const cell = newDirectorRow.insertCell();
        const input = document.createElement("input");
        input.type = "date";
        input.name = name;
        cell.appendChild(input);
    });
    
    const deleteCell = newDirectorRow.insertCell();
    const deleteButton = document.createElement("button");
    deleteButton.type = "button";
    deleteButton.textContent= "Delete";
    deleteButton.onclick = function() {
        deleteRow(this);
    };
    deleteCell.appendChild(deleteButton);
}

function addManagement(){
    const table = document.getElementById("ManagementTable");
    
    const newManagementRow = table.insertRow(-1);
    
    const fields = [
        "ManagementName[]",
        "ManagementNationality[]",
        "ManagementPosition[]",
    ];
    
    const Datefields = [
        "ManagementYearInPosition[]",
        "ManagementYearsInIndustry[]",
    ];
    
    fields.forEach(name => {
        const cell = newManagementRow.insertCell();
        const input = document.createElement("input");
        input.type = "text";
        input.name = name;
        cell.appendChild(input);
    });
    
    Datefields.forEach(name => {
        const cell = newManagementRow.insertCell();
        const input = document.createElement("input");
        input.type = "date";
        input.name = name;
        cell.appendChild(input);
    });
    
    const deleteCell = newManagementRow.insertCell();
    const deleteButton = document.createElement("button");
    deleteButton.type = "button";
    deleteButton.textContent= "Delete";
    deleteButton.onclick = function() {
        deleteRow(this);
    };
    deleteCell.appendChild(deleteButton);
}

function bankruptYes(){
    const detailsDiv = document.getElementById("bankruptcy-details");
    detailsDiv.style.display = "block";  
}

function bankruptNO(){
    const detailsDiv = document.getElementById("bankruptcy-details");
    detailsDiv.style.display = "none";  
}

function addBanks(){
    const table = document.getElementById("bankTable");
    
    const newBankRow = table.insertRow(-1);
    
    const fields = [
        "NameOfBank[]",
        "AddressOfBank[]",
        "SwiftCodeOfBank[]",
    ];
    
    fields.forEach(name => {
        const cell = newBankRow.insertCell();
        const input = document.createElement("input");
        input.type = "text";
        input.name = name;
        cell.appendChild(input);
    });
    
    const deleteCell = newBankRow.insertCell();
    const deleteButton = document.createElement("button");
    deleteButton.type = "button";
    deleteButton.textContent= "Delete";
    deleteButton.onclick = function() {
        deleteRow(this);
    };
    deleteCell.appendChild(deleteButton);
        
}
function addCreditFacilities() {
    const table = document.getElementById("CreditTable");
    const newCreditRow = table.insertRow(-1);

    const fields = [
        "TypeOfCredit[]",
        "FinancialInstitution[]",
        "CreditTotalAmount[]",
        "CreditUnutilisedAmount[]",
    ];

    const dateFields = [
        "CreditExpiryDate[]",
        "CreditAsAtDate[]",
    ];

    // Text fields
    fields.forEach(name => {
        const cell = newCreditRow.insertCell();
        const input = document.createElement("input");
        input.type = "text";
        input.name = name;
        cell.appendChild(input);
    });

    // Date fields
    dateFields.forEach(name => {
        const cell = newCreditRow.insertCell();
        const input = document.createElement("input");
        input.type = "date";
        input.name = name;
        cell.appendChild(input);
    });

    // Delete button
    const deleteCell = newCreditRow.insertCell();
    const deleteButton = document.createElement("button");
    deleteButton.type = "button";
    deleteButton.textContent = "Delete";
    deleteButton.onclick = function () {
        deleteRow(this);
    };
    deleteCell.appendChild(deleteButton);
}


function turnOnCreditDetails(){
    const detailsDiv = document.getElementById("CreditFacilities-Details");
    detailsDiv.style.display = "block"; 
}
function turnOffCreditDetails(){
    const detailsDiv = document.getElementById("CreditFacilities-Details");
    detailsDiv.style.display = "none"; 
}

function OnOthersDetails(){
    const detailsDiv = document.getElementById("CIDBOthersDetails");
    detailsDiv.style.display = "block"; 
}
function OffOthersDetails(){
    const detailsDiv = document.getElementById("CIDBOthersDetails");
    detailsDiv.style.display = "none"; 
}

function addStaffList() {
    const table = document.getElementById("StaffTeamTable");
    const newStaffRow = table.insertRow(-1);

    // Staff No
    let cell = newStaffRow.insertCell();
    let input = document.createElement("input");
    input.type = "number";
    input.name = "StaffNo[]";
    input.min = "1";
    cell.appendChild(input);

    const fields = [
        "StaffName[]",
        "StaffDesignation[]",
        "StaffQualification[]",
        "StaffEmploymentStatus[]",
        "StaffSkills[]",
        "StaffCertification[]",
    ];

    // Text fields
    fields.forEach(name => {
        cell = newStaffRow.insertCell();
        input = document.createElement("input");
        input.type = "text";
        input.name = name;
        cell.appendChild(input);
    });

    // Experience
    cell = newStaffRow.insertCell();
    input = document.createElement("input");
    input.type = "number";
    input.name = "StaffExperience[]";
    input.min = "1";
    cell.appendChild(input);

    // Delete button
    cell = newStaffRow.insertCell();
    const deleteButton = document.createElement("button");
    deleteButton.type = "button";
    deleteButton.textContent = "Delete";
    deleteButton.onclick = function () {
        deleteRow(this);
    };
    cell.appendChild(deleteButton);
}

function addStaffList() {
    const table = document.getElementById("StaffTeamTable");
    const newStaffRow = table.insertRow(-1);

    // Staff No
    let cell = newStaffRow.insertCell();
    let input = document.createElement("input");
    input.type = "number";
    input.name = "StaffNo[]";
    input.min = "1";
    cell.appendChild(input);

    const fields = [
        "StaffName[]",
        "StaffDesignation[]",
        "StaffQualification[]",
        "StaffEmploymentStatus[]",
        "StaffSkills[]",
        "StaffCertification[]",
    ];

    // Text fields
    fields.forEach(name => {
        cell = newStaffRow.insertCell();
        input = document.createElement("input");
        input.type = "text";
        input.name = name;
        cell.appendChild(input);
    });

    // Experience
    cell = newStaffRow.insertCell();
    input = document.createElement("input");
    input.type = "number";
    input.name = "StaffExperience[]";
    input.min = "1";
    cell.appendChild(input);

    // Delete button
    cell = newStaffRow.insertCell();
    const deleteButton = document.createElement("button");
    deleteButton.type = "button";
    deleteButton.textContent = "Delete";
    deleteButton.onclick = function () {
        deleteRow(this);
    };
    cell.appendChild(deleteButton);
}

function addProjectRecord() {
    const table = document.getElementById("ProjectRecordTable");
    const newStaffRow = table.insertRow(-1);

    // Staff No
    let cell = newStaffRow.insertCell();
    let input = document.createElement("input");
    input.type = "number";
    input.name = "StaffNo[]";
    input.min = "1";
    cell.appendChild(input);

    const fields = [
        "ProjectTitle[]",
        "ProjectNature[]",
        "ProjectLocation[]",
        "ProjectClientName[]",
        "ProjectValue[]",
    ];
    
    const dateFields = [
        "ProjectCommencementDate[]",
        "ProjectCompletionDate[]",
    ]

    // Text fields
    fields.forEach(name => {
        cell = newStaffRow.insertCell();
        input = document.createElement("input");
        input.type = "text";
        input.name = name;
        cell.appendChild(input);
    });

    // date
    dateFields.forEach(name => {
        cell = newStaffRow.insertCell();
        input = document.createElement("input");
        input.type = "date";
        input.name = name;
        cell.appendChild(input);
    });

    // Delete button
    cell = newStaffRow.insertCell();
    const deleteButton = document.createElement("button");
    deleteButton.type = "button";
    deleteButton.textContent = "Delete";
    deleteButton.onclick = function () {
        deleteRow(this);
    };
    cell.appendChild(deleteButton);
}

function addCurrentProjectRecord() {
    const table = document.getElementById("CurrentProjTable");
    const newStaffRow = table.insertRow(-1);

    // Staff No
    let cell = newStaffRow.insertCell();
    let input = document.createElement("input");
    input.type = "number";
    input.name = "CurrentProjectNo[]";
    input.min = "1";
    cell.appendChild(input);

    const fields = [
        "CurrentProjTitle[]",
        "CurrentPorjNature[]",
        "CurrentProjLocation[]",
        "CurrentProjName[]",
        "CurrentProjValue[]",
    ];
    
    const dateFields = [
        "CurrentProjStartDate[]",
        "CurrentProjEndDate[]",
    ]

    // Text fields
    fields.forEach(name => {
        cell = newStaffRow.insertCell();
        input = document.createElement("input");
        input.type = "text";
        input.name = name;
        cell.appendChild(input);
    });

    // date
    dateFields.forEach(name => {
        cell = newStaffRow.insertCell();
        input = document.createElement("input");
        input.type = "date";
        input.name = name;
        cell.appendChild(input);
    });
    
    let progressCell = newStaffRow.insertCell();
    let Processinput = document.createElement("input");
    Processinput.type = "number";
    Processinput.name = "CurrentProjProgress[]";
    Processinput.min = "1";
    Processinput.max = "100";
    progressCell.appendChild(Processinput);

    // Delete button
    cell = newStaffRow.insertCell();
    const deleteButton = document.createElement("button");
    deleteButton.type = "button";
    deleteButton.textContent = "Delete";
    deleteButton.onclick = function () {
        deleteRow(this);
    };
    cell.appendChild(deleteButton);
}

function deleteRow(button) {
    const row = button.closest("tr");
    row.remove();
}

function validateShareholderTotal() {
    const rows = document.querySelectorAll("#shareholderTable tr");
    let totalShare = 0;
    let hasData = false;

    // Skip header row (row 0), start from row 1
    for (let i = 1; i < rows.length; i++) {
        const inputs = rows[i].querySelectorAll("input");
        // The shareholder percent input is the 5th input (index 4)
        if (inputs.length > 4) {
            const shareInput = inputs[4]; // ShareholderPercent
            if (shareInput && shareInput.value && shareInput.value.trim() !== "") {
                totalShare += parseFloat(shareInput.value) || 0;
                hasData = true;
            }
        }
    }

    // Only validate if shareholders have percentages entered
    if (hasData) {
        // Allow small floating point tolerance (0.01%)
        if (Math.abs(totalShare - 100) > 0.01) {
            alert(`Shareholder percentages must sum to exactly 100%. Current total: ${totalShare.toFixed(2)}%`);
            return false;
        }
    }
    // If no shareholder data, allow submission (shareholders are optional)
    return true;
}

function validateBobcatRating() {
    const bobcat = document.querySelector('input[name="BobcatRating"]');
    if (!bobcat) return true; // nothing to validate

    const val = bobcat.value;
    if (val === null || val === undefined || val === '') return true; // allow empty

    const num = parseFloat(val);
    const min = parseFloat(bobcat.getAttribute('min'));
    const max = parseFloat(bobcat.getAttribute('max'));

    if (isNaN(num)) {
        alert('Bobcat rating must be a number.');
        bobcat.focus();
        bobcat.scrollIntoView({ behavior: 'smooth', block: 'center' });
        return false;
    }

    if (num < min || num > max) {
        alert(`Bobcat rating must be between ${min} and ${max}. You entered ${num}.`);
        bobcat.focus();
        bobcat.scrollIntoView({ behavior: 'smooth', block: 'center' });
        return false;
    }

    return true;
}

function validateAndSubmit() {
    console.log("validateAndSubmit called");
    
    // First validate shareholders
    if (!validateShareholderTotal()) {
        console.log("Shareholder validation failed");
        return; // Stop if validation fails
    }

    // Validate Bobcat rating range
    if (!validateBobcatRating()) {
        console.log("Bobcat rating validation failed");
        return; // Stop if invalid
    }
    
    // Get the form element
    const form = document.getElementById('vendorForm') || document.querySelector("form");
    console.log("Form element:", form);

    const formErrorsDiv = document.getElementById('formErrors');
    if (formErrorsDiv) formErrorsDiv.innerHTML = '';

    // Ensure all accordion sections are expanded so hidden required fields are focusable
    document.querySelectorAll('.accordion-collapse').forEach(collapse => {
        if (!collapse.classList.contains('show')) {
            collapse.classList.add('show');
            const headerBtn = collapse.parentElement.querySelector('.accordion-button');
            if (headerBtn) {
                headerBtn.classList.remove('collapsed');
                headerBtn.setAttribute('aria-expanded', 'true');
            }
        }
    });

    // Check if form is valid using HTML5 validation
    if (!form.checkValidity()) {
        console.log("Form validation failed");

        // Collect invalid elements and group by accordion section
        const invalidEls = Array.from(form.querySelectorAll(':invalid'));
        const sections = {};

        invalidEls.forEach(el => {
            let sectionName = 'Other';
            const accordionItem = el.closest('.accordion-item');
            if (accordionItem) {
                const headerBtn = accordionItem.querySelector('.accordion-button');
                sectionName = headerBtn ? headerBtn.textContent.trim() : 'Section';

                // Expand the accordion section so user can see the invalid fields
                const collapse = accordionItem.querySelector('.accordion-collapse');
                if (collapse && !collapse.classList.contains('show')) {
                    collapse.classList.add('show');
                }
                if (headerBtn) {
                    headerBtn.classList.remove('collapsed');
                    headerBtn.setAttribute('aria-expanded', 'true');
                }
            }

            // Determine a friendly label for the field
            let labelText = '';
            if (el.id) {
                const lab = form.querySelector('label[for="' + el.id + '"]');
                if (lab) labelText = lab.textContent.trim();
            }
            if (!labelText) labelText = el.name || el.placeholder || el.type || 'field';

            if (!sections[sectionName]) sections[sectionName] = new Set();
            sections[sectionName].add(labelText);
        });

        // Build and show a summary message
        let html = '<div class="error-box"><strong>Please complete required fields:</strong><ul style="margin-top:8px;">';
        for (const [sec, fields] of Object.entries(sections)) {
            html += '<li><strong>' + sec + ':</strong> ' + Array.from(fields).join(', ') + '</li>';
        }
        html += '</ul></div>';

        if (formErrorsDiv) {
            formErrorsDiv.innerHTML = html;
        } else {
            alert('Please complete required fields.');
        }

        // Focus the first invalid field and scroll into view
        if (invalidEls.length) {
            invalidEls[0].focus();
            invalidEls[0].scrollIntoView({ behavior: 'smooth', block: 'center' });
        }

        form.reportValidity();
        return;
    }

    console.log("All validations passed, submitting form");
    // If all validations pass, submit the form
    form.submit();
}

function submitTable() {
    const rows = document.querySelectorAll("#shareholderTable tr");
    let outputHTML = "<h3>Submitted Data:</h3><ul>";

    // Skip header row (start at 1)
    for (let i = 1; i < rows.length; i++) {
        const inputs = rows[i].querySelectorAll("input");
        let rowData = [];
        inputs.forEach(input => rowData.push(input.value.trim()));

        outputHTML += `<li><strong>Row ${i}:</strong> ${rowData.join(" | ")}</li>`;
    }

    outputHTML += "</ul>";
    document.getElementById("output").innerHTML = outputHTML;
}

