// ============================================================================
// GLOBAL VARIABLES
// ============================================================================

let fields = [];
let editingIndex = -1;

// ============================================================================
// INITIALIZATION
// ============================================================================

document.addEventListener("DOMContentLoaded", function () {
  // Initialize form builder features
  initializeFormBuilder();

  // Initialize form validation features
  initializeFormValidation();
});

// ============================================================================
// FORM BUILDER INITIALIZATION
// ============================================================================

function initializeFormBuilder() {
  // Show/hide options field based on type
  const fieldTypeSelect = document.getElementById("fieldType");
  if (fieldTypeSelect) {
    fieldTypeSelect.addEventListener("change", function () {
      const optionsGroup = document.getElementById("optionsGroup");
      if (this.value === "dropdown" || this.value === "checkbox") {
        optionsGroup.style.display = "block";
      } else {
        optionsGroup.style.display = "none";
      }
    });
  }

  // Form submission validation
  const formBuilderForm = document.getElementById("formBuilderForm");
  if (formBuilderForm) {
    formBuilderForm.addEventListener("submit", function (e) {
      if (fields.length === 0) {
        e.preventDefault();
        alert("Please add at least one field to the form");
      }
    });
  }

  // Display existing fields if any (for edit page)
  if (fields.length > 0) {
    displayFields();
  }
}

// ============================================================================
// FORM BUILDER FUNCTIONS
// ============================================================================

/**
 * Add new field to the form or update existing
 */
function addField() {
  const label = document.getElementById("fieldLabel").value.trim();
  const type = document.getElementById("fieldType").value;
  const required = document.getElementById("fieldRequired").checked;
  const optionsInput = document.getElementById("fieldOptions").value.trim();

  // Validation
  if (!label) {
    alert("Please enter field label");
    return;
  }

  if ((type === "dropdown" || type === "checkbox") && !optionsInput) {
    alert("Please enter options for " + type);
    return;
  }

  // Create field object
  const field = {
    label: label,
    type: type,
    required: required,
  };

  // Add options if dropdown or checkbox
  if (type === "dropdown" || type === "checkbox") {
    field.options = optionsInput
      .split(",")
      .map((opt) => opt.trim())
      .filter((opt) => opt);

    if (field.options.length === 0) {
      alert("Please provide at least one valid option");
      return;
    }
  }

  // Check if editing or adding new
  if (editingIndex >= 0) {
    // Update existing field
    fields[editingIndex] = field;
    editingIndex = -1;

    // Change button text back
    const addButton = document.querySelector(
      '.field-builder button[onclick="addField()"]',
    );
    if (addButton) {
      addButton.innerHTML = "+ Add Field";
      addButton.className = "btn btn-primary";
    }
  } else {
    // Add new field
    fields.push(field);
  }

  // Update display
  displayFields();

  // Clear inputs
  clearFieldInputs();
}

/**
 * Display all added fields
 */
function displayFields() {
  const preview = document.getElementById("fieldsPreview");

  if (!preview) return;

  // Determine header text based on page
  const headerText = document.getElementById("formBuilderForm")
    ? window.location.href.includes("edit-form")
      ? "Current Fields:"
      : "Added Fields:"
    : "Added Fields:";

  preview.innerHTML = `<h4>${headerText}</h4>`;

  if (fields.length === 0) {
    preview.innerHTML += '<p class="no-fields">No fields added yet</p>';
    return;
  }

  fields.forEach((field, index) => {
    const fieldDiv = document.createElement("div");
    fieldDiv.className = "field-item";

    let optionsText = "";
    if (field.options) {
      optionsText = `<br><small>Options: ${field.options.join(", ")}</small>`;
    }

    fieldDiv.innerHTML = `
            <div class="field-info">
                <strong>${index + 1}. ${escapeHtml(field.label)}</strong>
                <span class="badge">${field.type}</span>
                ${field.required ? '<span class="badge badge-required">Required</span>' : ""}
                ${optionsText}
            </div>
            <div class="field-actions">
                <button type="button" onclick="editField(${index})" class="btn-small">✏️ Edit</button>
                <button type="button" onclick="deleteField(${index})" class="btn-small btn-danger">🗑️ Delete</button>
                ${index > 0 ? `<button type="button" onclick="moveFieldUp(${index})" class="btn-small">⬆️</button>` : ""}
                ${index < fields.length - 1 ? `<button type="button" onclick="moveFieldDown(${index})" class="btn-small">⬇️</button>` : ""}
            </div>
        `;
    preview.appendChild(fieldDiv);
  });

  // Update hidden input with JSON
  const structureJson = document.getElementById("structureJson");
  if (structureJson) {
    structureJson.value = JSON.stringify(fields);
  }
}

/**
 * Delete field by index
 */
function deleteField(index) {
  if (confirm("Delete this field?")) {
    fields.splice(index, 1);
    displayFields();

    // If we were editing this field, cancel editing
    if (editingIndex === index) {
      cancelEdit();
    } else if (editingIndex > index) {
      editingIndex--;
    }
  }
}

/**
 * Edit field by index
 */
function editField(index) {
  const field = fields[index];

  // Populate form with field data
  document.getElementById("fieldLabel").value = field.label;
  document.getElementById("fieldType").value = field.type;
  document.getElementById("fieldRequired").checked = field.required;

  // Show options if dropdown or checkbox
  if (field.options) {
    document.getElementById("fieldOptions").value = field.options.join(", ");
    document.getElementById("optionsGroup").style.display = "block";
  } else {
    document.getElementById("optionsGroup").style.display = "none";
  }

  // Set editing index
  editingIndex = index;

  // Change button text to indicate editing
  const addButton = document.querySelector(
    '.field-builder button[onclick="addField()"]',
  );
  if (addButton) {
    addButton.innerHTML = "✅ Update Field";
    addButton.className = "btn btn-success";
  }

  // Scroll to form builder
  const fieldBuilder = document.querySelector(".field-builder");
  if (fieldBuilder) {
    fieldBuilder.scrollIntoView({ behavior: "smooth", block: "start" });
  }
}

/**
 * Cancel editing
 */
function cancelEdit() {
  editingIndex = -1;
  clearFieldInputs();

  const addButton = document.querySelector(
    '.field-builder button[onclick="addField()"]',
  );
  if (addButton) {
    addButton.innerHTML = "+ Add Field";
    addButton.className = "btn btn-primary";
  }
}

/**
 * Move field up
 */
function moveFieldUp(index) {
  if (index > 0) {
    [fields[index - 1], fields[index]] = [fields[index], fields[index - 1]];

    // Update editing index if needed
    if (editingIndex === index) {
      editingIndex = index - 1;
    } else if (editingIndex === index - 1) {
      editingIndex = index;
    }

    displayFields();
  }
}

/**
 * Move field down
 */
function moveFieldDown(index) {
  if (index < fields.length - 1) {
    [fields[index], fields[index + 1]] = [fields[index + 1], fields[index]];

    // Update editing index if needed
    if (editingIndex === index) {
      editingIndex = index + 1;
    } else if (editingIndex === index + 1) {
      editingIndex = index;
    }

    displayFields();
  }
}

/**
 * Clear field input fields
 */
function clearFieldInputs() {
  document.getElementById("fieldLabel").value = "";
  document.getElementById("fieldType").value = "text";
  document.getElementById("fieldOptions").value = "";
  document.getElementById("fieldRequired").checked = false;
  document.getElementById("optionsGroup").style.display = "none";
}

/**
 * Reset entire form
 */
function resetForm() {
  if (confirm("Reset all fields? This will clear all added fields.")) {
    fields = [];
    editingIndex = -1;
    displayFields();

    const formTitle = document.getElementById("formTitle");
    const formDescription = document.getElementById("formDescription");

    if (formTitle) formTitle.value = "";
    if (formDescription) formDescription.value = "";

    clearFieldInputs();
  }
}

/**
 * Escape HTML to prevent XSS
 */
function escapeHtml(text) {
  const div = document.createElement("div");
  div.textContent = text;
  return div.innerHTML;
}

// ============================================================================
// PUBLIC FORM VALIDATION
// ============================================================================

/**
 * Initialize form validation for public forms
 */
function initializeFormValidation() {
  const publicForm = document.getElementById("publicForm");

  if (publicForm) {
    // Add real-time validation
    addRealTimeValidation(publicForm);

    // Form submission validation
    publicForm.addEventListener("submit", function (e) {
      if (!validateForm(publicForm)) {
        e.preventDefault();
      }
    });
  }

  // Reset button confirmation
  const resetButtons = document.querySelectorAll('button[type="reset"]');
  resetButtons.forEach((button) => {
    button.addEventListener("click", function (e) {
      if (
        !confirm(
          "Are you sure you want to reset the form? All entered data will be lost.",
        )
      ) {
        e.preventDefault();
      }
    });
  });
}

/**
 * Add real-time validation to form fields
 */
function addRealTimeValidation(form) {
  const inputs = form.querySelectorAll("input[required], select[required]");

  inputs.forEach((input) => {
    // Remove error styling on focus
    input.addEventListener("focus", function () {
      this.style.borderColor = "";
      removeErrorMessage(this);
    });

    // Validate on blur
    input.addEventListener("blur", function () {
      validateField(this);
    });
  });

  // Number inputs - allow only numbers
  const numberInputs = form.querySelectorAll('input[type="number"]');
  numberInputs.forEach((input) => {
    input.addEventListener("input", function () {
      this.value = this.value.replace(/[^0-9.-]/g, "");
    });
  });
}

/**
 * Validate entire form
 */
function validateForm(form) {
  let isValid = true;
  const requiredFields = form.querySelectorAll("[required]");

  requiredFields.forEach((field) => {
    if (!validateField(field)) {
      isValid = false;
    }
  });

  if (!isValid) {
    alert("Please fill all required fields correctly");
    const firstError = form.querySelector(".error-border");
    if (firstError) {
      firstError.scrollIntoView({ behavior: "smooth", block: "center" });
      firstError.focus();
    }
  }

  return isValid;
}

/**
 * Validate individual field
 */
function validateField(field) {
  const value = field.value.trim();
  const fieldType = field.type;
  const isRequired = field.hasAttribute("required");

  // Remove previous error
  removeErrorMessage(field);
  field.style.borderColor = "";

  // Check if required and empty
  if (isRequired && !value) {
    showError(field, "This field is required");
    return false;
  }

  // If not empty, validate by type
  if (value) {
    // Number validation
    if (fieldType === "number") {
      if (isNaN(value) || value === "") {
        showError(field, "Please enter a valid number");
        return false;
      }
    }

    // Email validation
    if (field.name.toLowerCase().includes("email")) {
      if (!isValidEmail(value)) {
        showError(field, "Please enter a valid email address");
        return false;
      }
    }

    // Phone validation
    if (field.name.toLowerCase().includes("phone")) {
      if (!isValidPhone(value)) {
        showError(field, "Please enter a valid phone number");
        return false;
      }
    }
  }

  // Check checkbox groups
  if (field.type === "checkbox" && isRequired) {
    const checkboxGroup = document.querySelectorAll(
      `input[name="${field.name}"]`,
    );
    const isAnyChecked = Array.from(checkboxGroup).some((cb) => cb.checked);

    if (!isAnyChecked) {
      showError(field, "Please select at least one option");
      return false;
    }
  }

  return true;
}

/**
 * Show error message
 */
function showError(field, message) {
  field.style.borderColor = "red";
  field.classList.add("error-border");

  const errorDiv = document.createElement("div");
  errorDiv.className = "error-message";
  errorDiv.style.color = "red";
  errorDiv.style.fontSize = "12px";
  errorDiv.style.marginTop = "5px";
  errorDiv.textContent = message;

  const formGroup = field.closest(".form-group");
  if (formGroup) {
    formGroup.appendChild(errorDiv);
  }
}

/**
 * Remove error message
 */
function removeErrorMessage(field) {
  field.classList.remove("error-border");
  const formGroup = field.closest(".form-group");
  if (formGroup) {
    const errorMsg = formGroup.querySelector(".error-message");
    if (errorMsg) {
      errorMsg.remove();
    }
  }
}

/**
 * Validate email format
 */
function isValidEmail(email) {
  const emailRegex = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
  return emailRegex.test(email);
}

/**
 * Validate phone number
 */
function isValidPhone(phone) {
  const cleaned = phone.replace(/\D/g, "");
  return cleaned.length >= 10;
}

