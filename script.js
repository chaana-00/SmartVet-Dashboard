const sidebar = document.getElementById("sidebar");
const toggleBtn = document.getElementById("toggle-btn");

if (toggleBtn) {
  toggleBtn.addEventListener("click", () => {
    sidebar.classList.toggle("collapsed");
  });

  document.querySelectorAll(".sidebar ul li a").forEach(link => {
    link.addEventListener("click", () => {
      if (sidebar.classList.contains("collapsed")) {
        sidebar.classList.remove("collapsed");
      }
    });
  });
}


// USER'S FORM
if (userForm) {
  userForm.addEventListener("submit", async (e) => {
    e.preventDefault();

    const fullname = document.getElementById("fullname").value.trim();
    const designation = document.getElementById("designation").value.trim();
    const company = document.getElementById("company").value.trim();
    const telephone = document.getElementById("telephone").value.trim();
    const username = document.getElementById("username").value.trim();
    const password = document.getElementById("password").value;

    const confirmPassword = document.getElementById("confirmPassword").value;
    if (password !== confirmPassword) {
      alert("Passwords do not match!");
      return;
    }

    async function encryptPassword(pwd) {
      const encoder = new TextEncoder();
      const data = encoder.encode(pwd);
      const hash = await crypto.subtle.digest("SHA-256", data);
      return Array.from(new Uint8Array(hash)).map(b => b.toString(16).padStart(2, "0")).join("");
    }

    const encryptedPassword = await encryptPassword(password);

    // Save user into localStorage
    const users = JSON.parse(localStorage.getItem("users")) || [];
    users.push({
      fullname, designation, company, telephone, username, password: encryptedPassword
    });
    localStorage.setItem("users", JSON.stringify(users));

    document.getElementById("output").innerText = "User saved successfully!";
    userForm.reset();
  });
}

// FORM VALIDATION + ENCRYPTION
const userForm = document.getElementById("userForm");
if (userForm) {
  userForm.addEventListener("submit", async (e) => {
    e.preventDefault();

    const fullname = document.getElementById("fullname").value.trim();
    const designation = document.getElementById("designation").value.trim();
    const company = document.getElementById("company").value.trim();
    const telephone = document.getElementById("telephone").value.trim();
    const username = document.getElementById("username").value.trim();
    const password = document.getElementById("password").value;
    const confirmPassword = document.getElementById("confirmPassword").value;

    // Check password match
    if (password !== confirmPassword) {
      alert("Passwords do not match!");
      return;
    }

    // Encrypt password (SHA-256)
    async function encryptPassword(pwd) {
      const encoder = new TextEncoder();
      const data = encoder.encode(pwd);
      const hash = await crypto.subtle.digest("SHA-256", data);
      return Array.from(new Uint8Array(hash)).map(b => b.toString(16).padStart(2, "0")).join("");
    }

    const encryptedPassword = await encryptPassword(password);

    // Show output (simulate saving)
    document.getElementById("output").innerText =
      `User Saved!\nFull Name: ${fullname}\nUsername: ${username}\nEncrypted Password: ${encryptedPassword}`;
  });
}


// Toggle password visibility
document.querySelector(".toggle-password").addEventListener("click", function () {
  const passwordField = document.querySelector("input[type='password']");
  const icon = this.querySelector("i");

  if (passwordField.type === "password") {
    passwordField.type = "text";
    icon.classList.replace("bx-show", "bx-hide");
  } else {
    passwordField.type = "password";
    icon.classList.replace("bx-hide", "bx-show");
  }
});

// Handle login form submission
document.getElementById("loginForm").addEventListener("submit", function (e) {
  e.preventDefault();

  const username = document.querySelector("input[type='text']").value.trim();
  const password = document.querySelector("input[type='password']").value.trim();
  const role = document.querySelector("select").value;

  if (!username || !password || !role) {
    alert("Please fill all fields.");
    return;
  }

  // 🔑 Dummy redirection based on role
  if (role === "admin") {
    window.location.href = "index.html"; // Admin dashboard
  } else {
    window.location.href = "users-dashboard.html"; // User dashboard
  }
});
