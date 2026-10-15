// Validate the form in the browser before allowing submission.
const form = document.getElementById("registrationForm");
const get = (id) => document.getElementById(id);
const clearErrors = () => {
  document.querySelectorAll(".error").forEach((el) => { el.textContent = ""; });
  get("successMessage").textContent = "";
};

form.addEventListener("submit", (event) => {
  event.preventDefault();
  clearErrors();
  let valid = true;

  if (get("name").value.trim().length < 2) {
    get("nameError").textContent = "Enter a name with at least 2 characters.";
    valid = false;
  }
  if (!get("roll").value.trim()) {
    get("rollError").textContent = "Roll number is required.";
    valid = false;
  }
  const email = get("email").value.trim();
  if (!/^[^\s@]+@[^\s@]+\.[^\s@]+$/.test(email)) {
    get("emailError").textContent = "Enter a valid email address.";
    valid = false;
  }
  if (!get("course").value) {
    get("courseError").textContent = "Choose a course.";
    valid = false;
  }
  const password = get("password").value;
  if (!/^(?=.*[a-z])(?=.*[A-Z])(?=.*\d).{8,}$/.test(password)) {
    get("passwordError").textContent =
      "Use 8+ characters with uppercase, lowercase and a number.";
    valid = false;
  }
  const file = get("document").files[0];
  if (!file) {
    get("documentError").textContent = "Please select a document.";
    valid = false;
  } else {
    const allowed = ["application/pdf", "image/jpeg", "image/png"];
    if (!allowed.includes(file.type)) {
      get("documentError").textContent = "Only PDF, JPG or PNG files are allowed.";
      valid = false;
    } else if (file.size > 2 * 1024 * 1024) {
      get("documentError").textContent = "File size must not exceed 2 MB.";
      valid = false;
    }
  }

  if (valid) {
    get("successMessage").textContent =
      "Validation successful. This front-end demonstration does not save data.";
    form.reset();
  }
});
