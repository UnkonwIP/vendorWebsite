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
    const fields = [
        "ShareholderName[]",
        "ShareholderNationality[]",
        "ShareholderAddress[]",
    ];
    
    // ID Column
    let cell = newRow.insertCell();
    let input = document.createElement("input");
    input.type = "number"; input.name = "CompanyShareholderID[]"; input.step = "1";
    cell.appendChild(input);
    
    // Text Columns
    fields.forEach(name => {
        cell = newRow.insertCell();
        input = document.createElement("input");
        input.type = "text"; input.name = name;
        cell.appendChild(input);
    });
    
    // Percent Column
    cell = newRow.insertCell();
    input = document.createElement("input");
    input.type = "number"; input.name = "ShareholderPercent[]"; input.step = "0.01";
    cell.appendChild(input);
    
    // Delete Button
    cell = newRow.insertCell();
    const btn = document.createElement("button");
    btn.type = "button"; btn.textContent = "Delete";
    btn.onclick = function() { deleteRow(this); };
    cell.appendChild(btn);
}

function addDirector(){
    const table = document.getElementById("DirectorTable");
    const newRow = table.insertRow(-1);
    
    // FIX: Capitalized names
    const fields = ["DirectorName[]", "DirectorNationality[]", "DirectorPosition[]"];
    const dateFields = ["DirectorAppointmentDate[]", "DirectorDOB[]"];
    
    fields.forEach(name => {
        let cell = newRow.insertCell();
        let input = document.createElement("input");
        input.type = "text"; input.name = name;
        cell.appendChild(input);
    });
    
    dateFields.forEach(name => {
        let cell = newRow.insertCell();
        let input = document.createElement("input");
        input.type = "date"; input.name = name;
        cell.appendChild(input);
    });
    
    let cell = newRow.insertCell();
    const btn = document.createElement("button");
    btn.type = "button"; btn.textContent = "Delete";
    btn.onclick = function() { deleteRow(this); };
    cell.appendChild(btn);
}

function addManagement(){
    const table = document.getElementById("ManagementTable");
    const newRow = table.insertRow(-1);
    
    const fields = ["ManagementName[]", "ManagementNationality[]", "ManagementPosition[]"];
    const numFields = ["ManagementYearInPosition[]", "ManagementYearsInIndustry[]"];
    
    fields.forEach(name => {
        let cell = newRow.insertCell();
        let input = document.createElement("input");
        input.type = "text"; input.name = name;
        cell.appendChild(input);
    });
    
    numFields.forEach(name => {
        let cell = newRow.insertCell();
        let input = document.createElement("input");
        input.type = "number"; input.name = name;
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
    const fields = ["NameOfBank[]", "AddressOfBank[]", "SwiftCodeOfBank[]"];
    
    fields.forEach(name => {
        let cell = newRow.insertCell();
        let input = document.createElement("input");
        input.type = "text"; input.name = name;
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
    
    // No
    let cell = newRow.insertCell();
    let input = document.createElement("input");
    input.type = "number"; input.name = "StaffNo[]";
    cell.appendChild(input);

    const fields = ["StaffName[]", "StaffDesignation[]", "StaffQualification[]"];
    
    fields.forEach(name => {
        cell = newRow.insertCell();
        input = document.createElement("input");
        input.type = "text"; input.name = name;
        cell.appendChild(input);
    });
    
    // Exp
    cell = newRow.insertCell();
    input = document.createElement("input");
    input.type = "number"; input.name = "StaffExperience[]";
    cell.appendChild(input);
    
    const fields2 = ["StaffEmploymentStatus[]", "StaffSkills[]", "StaffCertification[]"];
    fields2.forEach(name => {
        cell = newRow.insertCell();
        input = document.createElement("input");
        input.type = "text"; input.name = name;
        cell.appendChild(input);
    });

    cell = newRow.insertCell();
    const btn = document.createElement("button");
    btn.type = "button"; btn.textContent = "Delete";
    btn.onclick = function() { deleteRow(this); };
    cell.appendChild(btn);
}

function addProjectRecord() {
    const table = document.getElementById("ProjectRecordTable");
    const newRow = table.insertRow(-1);
    
    let cell = newRow.insertCell();
    let input = document.createElement("input");
    input.type = "number"; input.name = "ProjectRecordNo[]";
    cell.appendChild(input);

    const fields = ["ProjectTitle[]", "ProjectNature[]", "ProjectLocation[]", "ProjectClientName[]", "ProjectValue[]"];
    const dateFields = ["ProjectCommencementDate[]", "ProjectCompletionDate[]"];

    fields.forEach(name => {
        cell = newRow.insertCell();
        input = document.createElement("input");
        input.type = "text"; input.name = name;
        cell.appendChild(input);
    });

    dateFields.forEach(name => {
        cell = newRow.insertCell();
        input = document.createElement("input");
        input.type = "date"; input.name = name;
        cell.appendChild(input);
    });

    cell = newRow.insertCell();
    const btn = document.createElement("button");
    btn.type = "button"; btn.textContent = "Delete";
    btn.onclick = function() { deleteRow(this); };
    cell.appendChild(btn);
}

function addCurrentProjectRecord() {
    const table = document.getElementById("CurrentProjTable");
    const newRow = table.insertRow(-1);
    
    let cell = newRow.insertCell();
    let input = document.createElement("input");
    input.type = "number"; input.name = "CurrentProjectRecordNo[]";
    cell.appendChild(input);

    const fields = ["CurrentProjTitle[]", "CurrentProjNature[]", "CurrentProjLocation[]", "CurrentProjClientName[]", "CurrentProjValue[]"];
    const dateFields = ["CurrentProjStartDate[]", "CurrentProjEndDate[]"];

    fields.forEach(name => {
        cell = newRow.insertCell();
        input = document.createElement("input");
        input.type = "text"; input.name = name;
        cell.appendChild(input);
    });

    dateFields.forEach(name => {
        cell = newRow.insertCell();
        input = document.createElement("input");
        input.type = "date"; input.name = name;
        cell.appendChild(input);
    });

    cell = newRow.insertCell();
    input = document.createElement("input");
    input.type = "number"; input.name = "CurrentProjProgress[]";
    cell.appendChild(input);

    cell = newRow.insertCell();
    const btn = document.createElement("button");
    btn.type = "button"; btn.textContent = "Delete";
    btn.onclick = function() { deleteRow(this); };
    cell.appendChild(btn);
}

console.log("VendorRegistration.js loaded. and updated vs");

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

    // If everything is valid, submit the form
    console.log("Validation passed. Submitting...");
    form.submit();
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