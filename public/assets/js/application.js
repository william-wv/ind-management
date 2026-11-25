document.addEventListener("DOMContentLoaded", function () {
  const imagePreviewInput = document.getElementById("image_preview_input");
  const preview = document.getElementById("image_preview");
  const imagePreviewSubmit = document.getElementById("image_preview_submit");

  if (!(imagePreviewInput && preview)) return;

  imagePreviewInput.style.display = "none";
  imagePreviewSubmit.style.display = "none";

  preview.addEventListener("click", function () {
    imagePreviewInput.click();
  });

  imagePreviewInput.addEventListener("change", function (event) {
    const file = event.target.files[0];
    if (file) {
      const reader = new FileReader();
      reader.onload = function (e) {
        document.getElementById("image_preview").src = e.target.result;
        imagePreviewSubmit.style.display = "block";
      };
      reader.readAsDataURL(file);
    }
  });
});



document.addEventListener("DOMContentLoaded", function () {
  const loginForm = document.getElementById("login-form");

  if (!loginForm) return;

  const loginError = document.getElementById("login-error");

  loginForm.addEventListener("submit", async (event) => {
    event.preventDefault();

    try {
      const response = await fetch(loginForm.action, {
        method: "POST",
        headers: {
          Accept: "application/json",
        },
        body: new URLSearchParams(new FormData(loginForm)),
      });

      const data = await response.json();

      if (data.success) {
        window.location.href = data.redirect;
        return;
      }

      if (loginError) {
        loginError.textContent = data.message;
        loginError.hidden = false;
      }
    } catch (error) {
      if (loginError) {
        loginError.textContent = "Não foi possível conectar ao servidor.";
        loginError.hidden = false;
      }
    }
  });
});