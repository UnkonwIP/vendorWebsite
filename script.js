/* VendorRegistration.js */

// Helper to prevent deleting the last row
function deleteRow(button) {
    const row = button.closest("tr");
    const table = row.closest("table");
    // Check if it's the last data row (assuming row 0 is header)
    if (table.rows.length <= 2) {
        alert("You cannot delete the only remaining row.");
        return;
    }
    row.remove();
}

function addShareholders(){
    const table = document.getElementById("shareholderTable");
    const newRow = table.insertRow(-1);
    
    // FIX: Capitalized names to match the HTML form and PHP $_POST expectations
    const colums = [
        { type: "text", name: "CompanyShareholderID[]" },
        { type: "text", name: "ShareholderName[]" },
        { type: "text", name: "ShareholderNationality[]" },
        { type: "text", name: "ShareholderAddress[]" },
        { type: "number", name: "ShareholderPercent[]" }
    ];

    // Text Columns
    colums.forEach(col => {
        let cell = newRow.insertCell();
        let input = document.createElement("input");
        input.type = col.type; 
        input.name = col.name;
        cell.appendChild(input);
    });
    
    // Delete Button
    let cell = newRow.insertCell();
    const btn = document.createElement("button");
    btn.type = "button"; btn.textContent = "Delete";
    btn.onclick = function() { deleteRow(this); };
    cell.appendChild(btn);
}

function addDirector(){
    const table = document.getElementById("DirectorTable");
    const newRow = table.insertRow(-1);
    
    // FIX: Capitalized names
    const colums = [
        { type: "text", name: "DirectorName[]" },
        { type: "text", name: "DirectorNationality[]" },
        { type: "text", name: "DirectorPosition[]" },
        { type: "date", name: "DirectorAppointmentDate[]" },
        { type: "date", name: "DirectorDOB[]" }
    ];

    colums.forEach(col => {
        let cell = newRow.insertCell();
        let input = document.createElement("input");
        input.type = col.type; input.name = col.name;
        cell.appendChild(input);
    });

    // Delete Button
    let cell = newRow.insertCell();
    const btn = document.createElement("button");
    btn.type = "button"; btn.textContent = "Delete";
    btn.onclick = function() { deleteRow(this); };
    cell.appendChild(btn);
}

function addManagement(){
    const table = document.getElementById("ManagementTable");
    const newRow = table.insertRow(-1);
    
    const columns = [
        { type: "text", name: "ManagementName[]" },
        { type: "text", name: "ManagementNationality[]" },
        { type: "text", name: "ManagementPosition[]" },
        { type: "number", name: "ManagementYearInPosition[]" },
        { type: "number", name: "ManagementYearsInIndustry[]" }
    ];
    columns.forEach(col => {
        let cell = newRow.insertCell();
        let input = document.createElement("input");
        input.type = col.type; input.name = col.name;
        cell.appendChild(input);
    });
    
    let cell = newRow.insertCell();
    const btn = document.createElement("button");
    btn.type = "button"; btn.textContent = "Delete";
    btn.onclick = function() { deleteRow(this); };
    cell.appendChild(btn);
}

function addBanks(){
    const table = document.getElementById("bankTable");
    const newRow = table.insertRow(-1);
    const columns = [
        { type: "text", name: "NameOfBank[]" },
        { type: "text", name: "AddressOfBank[]" },
        { type: "text", name: "SwiftCodeOfBank[]" }
    ];
    
    columns.forEach(col => {
        let cell = newRow.insertCell();
        let input = document.createElement("input");
        input.type = col.type; input.name = col.name;
        cell.appendChild(input);
    });
    
    let cell = newRow.insertCell();
    const btn = document.createElement("button");
    btn.type = "button"; btn.textContent = "Delete";
    btn.onclick = function() { deleteRow(this); };
    cell.appendChild(btn);
}

function addCreditFacilities() {
    const table = document.getElementById("CreditTable");
    const newRow = table.insertRow(-1);

    const columns = [
        { type: "text", name: "TypeOfCredit[]" },
        { type: "text", name: "FinancialInstitution[]" },
        { type: "number", name: "CreditTotalAmount[]" },
        { type: "date", name: "CreditExpiryDate[]" },
        { type: "number", name: "CreditUnutilisedAmount[]" },
        { type: "date", name: "CreditAsAtDate[]" }
    ];

    columns.forEach(col => {
        let cell = newRow.insertCell();
        let input = document.createElement("input");
        input.type = col.type;
        input.name = col.name;
        cell.appendChild(input);
    });

    let cell = newRow.insertCell();
    const btn = document.createElement("button");
    btn.type = "button"; btn.textContent = "Delete";
    btn.onclick = function() { deleteRow(this); };
    cell.appendChild(btn);
}

function addStaffList() {
    const table = document.getElementById("StaffTeamTable");
    const newRow = table.insertRow(-1);
    
    // Insert empty cell for row number
    let cell = newRow.insertCell();
    cell.textContent = ""; // This will be updated by the row numbering function

    const columns = [

        { type: "text", name: "StaffName[]" },
        { type: "text", name: "StaffDesignation[]" },
        { type: "text", name: "StaffQualification[]" },
        { type: "number", name: "StaffExperience[]" , min : 1 },
        { type: "text", name: "StaffEmploymentStatus[]" },
        { type: "text", name: "StaffSkills[]" },
        { type: "text", name: "StaffCertification[]" }
    ];
    
    columns.forEach(col => {
        cell = newRow.insertCell();
        input = document.createElement("input");
        input.type = col.type; input.name = col.name;
        if (col.type === "number") {
            if (col.min !== undefined) input.min = col.min;
            // You can add max or step if needed
            //
        }
        cell.appendChild(input);
    });

    cell = newRow.insertCell();
    const btn = document.createElement("button");
    btn.type = "button"; btn.textContent = "Delete";
    btn.onclick = function() { deleteRow(this); };
    cell.appendChild(btn);
    updateRowNumbers('StaffTeamTable');
}

function addProjectRecord() {
    const table = document.getElementById("ProjectRecordTable");
    const newRow = table.insertRow(-1);

    // Insert empty cell for row number
    let cell = newRow.insertCell();
    cell.textContent = ""; // This will be updated by the row numbering function
    
    const colums = [
        { type : "text" , name : "ProjectTitle[]"},
        {type : "text" , name : "ProjectNature[]"},
        {type : "text" , name : "ProjectLocation[]"},
        {type : "text" , name : "ProjectClientName[]"},
        {type : "number" , name : "ProjectValue[]"},
        {type : "date" , name : "ProjectCommencementDate[]"},
        {type : "date" , name : "ProjectCompletionDate[]"}
    ];
    colums.forEach(col => {
        cell = newRow.insertCell();
        input = document.createElement("input");
        input.type = col.type; input.name = col.name;
        if (col.type === "number" && col.min !== undefined) {
            input.min = col.min;
            // You can add max or step if needed temporarily use this format
        }
        cell.appendChild(input);
    });

    cell = newRow.insertCell();
    const btn = document.createElement("button");
    btn.type = "button"; btn.textContent = "Delete";
    btn.onclick = function() { deleteRow(this); };
    cell.appendChild(btn);
    updateRowNumbers('ProjectRecordTable');
}

function addCurrentProjectRecord() {
    const table = document.getElementById("CurrentProjTable");
    const newRow = table.insertRow(-1);
    
    // Insert empty cell for row number
    let cell = newRow.insertCell();
    cell.textContent = ""; // This will be updated by the row numbering function

    const columns = [
        { type: "text", name: "CurrentProjTitle[]" },
        { type: "text", name: "CurrentProjNature[]" },
        { type: "text", name: "CurrentProjLocation[]" },
        { type: "text", name: "CurrentProjClientName[]" },
        { type: "number", name: "CurrentProjValue[]" },
        { type: "date", name: "CurrentProjStartDate[]" },
        { type: "date", name: "CurrentProjEndDate[]" },
        { type: "number", name: "CurrentProjProgress[]" , min : 1 , max : 100 }
    ];

    columns.forEach(col => {
        cell = newRow.insertCell();
        input = document.createElement("input");
        input.type = col.type; input.name = col.name;
        if (col.type === "number" && col.min !== undefined) {
            input.min = col.min;
            if (col.max !== undefined) input.max = col.max;
            // You can add max or step if needed temporarily use this format
        }
        cell.appendChild(input);
    });

    cell = newRow.insertCell();
    const btn = document.createElement("button");
    btn.type = "button"; btn.textContent = "Delete";
    btn.onclick = function() { deleteRow(this); };
    cell.appendChild(btn);
    updateRowNumbers('CurrentProjTable');
}

console.log("VendorRegistration.js loaded. and updated 2.0");

// --- Auto Row Numbering for Dynamic Tables ---
function updateRowNumbers(tableId) {
    const table = document.getElementById(tableId);
    if (!table) return;
    const rows = table.getElementsByTagName('tr');
    let rowIndex = 1;
    for (let i = 1; i < rows.length; i++) { // skip header
        const firstCell = rows[i].getElementsByTagName('td')[0];
        if (firstCell) firstCell.textContent = rowIndex++;
    }
}

// Form Validation and Submission
function validateAndSubmit() {
    const form = document.getElementById('vendorForm');
    const formErrorsDiv = document.getElementById('formErrors');
    
    // Clear previous errors
    if (formErrorsDiv) formErrorsDiv.innerHTML = '';
    
    // Find all required fields
    const requiredFields = form.querySelectorAll('[required]');
    const sections = {}; // Group errors by accordion section
    let isValid = true;
    let firstInvalidEl = null;

    requiredFields.forEach(field => {
        if (!field.value.trim()) {
            isValid = false;
            if (!firstInvalidEl) firstInvalidEl = field;

            // Find the name of the accordion section this field belongs to
            const accordionItem = field.closest('.accordion-item');
            const sectionName = accordionItem 
                ? accordionItem.querySelector('.accordion-header').innerText.trim() 
                : "General Information";

            // Find the label text for the field
            const label = field.closest('.mb-3')?.querySelector('label')?.innerText.replace(':', '') || field.name;

            if (!sections[sectionName]) {
                sections[sectionName] = new Set();
            }
            sections[sectionName].add(label);
            
            // Highlight the field
            field.classList.add('is-invalid');
        } else {
            field.classList.remove('is-invalid');
        }
    });


    if (!isValid) {
        // Build the error box HTML
        let html = '<div class="error-box"><strong>Please complete required fields:</strong><ul style="margin-top:8px;">';
        for (const [sec, fields] of Object.entries(sections)) {
            html += `<li><strong>${sec}:</strong> ${Array.from(fields).join(', ')}</li>`;
        }
        html += '</ul></div>';

        if (formErrorsDiv) {
            formErrorsDiv.innerHTML = html;
        } else {
            alert('Please complete all required fields.');
        }

        // Scroll to the first error and focus it
        if (firstInvalidEl) {
            firstInvalidEl.scrollIntoView({ behavior: 'smooth', block: 'center' });
            firstInvalidEl.focus();
        }
        
        form.reportValidity();
        return false;
    }
    // Additional custom validation for Shareholder Percentages
    const shareholderPercentResult = validateShareholderPercent();
    if (shareholderPercentResult !== true) {
        const percentMsg = `Shareholder percentages must total 100%.\nCurrent total: ${shareholderPercentResult.toFixed(2)}%`;
        if (formErrorsDiv) {
            formErrorsDiv.innerHTML += `<div class="error-box"><strong>${percentMsg.replace(/\n/g, '<br>')}</strong></div>`;
        } else {
            alert(percentMsg);
        }
        return false;
    }
    // If everything is valid, submit the form
    console.log("Validation passed. Submitting...");
    return true; // Allow normal form submission
}

function validateShareholderPercent() {
    const percentInputs = document.querySelectorAll('input[name="ShareholderPercent[]"]');
    let totalPercent = 0;

    percentInputs.forEach(input => {
        let val = parseFloat(input.value);
        input.classList.remove('is-invalid');
        if (isNaN(val) || val < 0 || val > 100) {
            input.classList.add('is-invalid');
            val = 0;
        }
        totalPercent += val;
    });

    if (percentInputs.length === 0 || Math.abs(totalPercent - 100) <= 0.01) {
        return true;
    } else {
        return totalPercent;
    }
}


// UI Toggles
function bankruptYes() { document.getElementById("bankruptcy-details").style.display = "block"; }
function bankruptNO() { document.getElementById("bankruptcy-details").style.display = "none"; }
function turnOnCreditDetails() { document.getElementById("CreditFacilities-Details").style.display = "block"; }
function turnOffCreditDetails() { document.getElementById("CreditFacilities-Details").style.display = "none"; }
function OnOthersDetails() { document.getElementById("CIDBOthersDetails").style.display = "block"; }
function OffOthersDetails() { document.getElementById("CIDBOthersDetails").style.display = "none"; }

// checkbox toggles
document.addEventListener("DOMContentLoaded", function() {
    restoreSectionVisibility();
});


document.addEventListener("DOMContentLoaded", function() {
    var form = document.getElementById("vendorForm");
    if (form) {
        form.addEventListener("submit", function (e) {
            if (!validateAndSubmit()) {
                e.preventDefault(); // stop submit
            }
        });
    }
});


// Also run this when the user navigates back to the page
window.addEventListener("pageshow", function(event) {
    if (event.persisted) {
        restoreSectionVisibility();
    }
});

function restoreSectionVisibility() {
    // 1. Check Bankruptcy History
    const bankruptYes = document.getElementById("bankrupt-yes"); // Ensure ID matches your HTML    
    // You might need to use querySelector if you don't have IDs on the radios
    // Example: document.querySelector('input[name="bankruptHistory"][value="Yes"]')
        if (bankruptYes.checked) {
        document.getElementById("bankruptcy-details").style.display = "block";
    }

    // 2. Check Credit Facilities
    const creditRadioYes = document.getElementById("CreditFacilities-Yes");
    // Note: Verify the name attribute from your HTML. I see 'creditFacilities' implies logic.
    // Based on your script.js, you have turnOnCreditDetails()
    
    // Let's look for the radio that triggers turnOnCreditDetails()
    // It is likely name="CreditFacilities" or similar based on context.
    // Assuming name="CreditFacilities" and value="Yes"
    const creditYes = document.getElementById("CreditFacilities-Yes");
    if ( creditYes.checked) {
        document.getElementById("CreditFacilities-Details").style.display = "block";
    }

    // 3. Check CIDB Others
    // This is for the "Others" checkbox or radio in CIDB section
    const cidbOthers = document.getElementById("CIDBOthersRadio");
    if (cidbOthers && cidbOthers.checked) {
        document.getElementById("CIDBOthersDetails").style.display = "block";
    }
}