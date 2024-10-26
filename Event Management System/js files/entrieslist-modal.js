
let section = document.querySelector("section");
  overlay = document.querySelector(".overlay"),
  showBtn = document.querySelector(".entries-button"),
  closeBtn = document.querySelector(".modal-button-cancel");
showBtn.addEventListener("click", () => section.classList.add("active"));
overlay.addEventListener("click", () =>
  section.classList.remove("active")
);
closeBtn.addEventListener("click", () =>
  section.classList.remove("active")
);

/* interaction of popup modal form */
const saveBtn = document.querySelector(".modal-button-save");
const nameInput = document.querySelector("#name");
const usernameInput = document.querySelector("#username");
const passwordInput = document.querySelector("#password");
const venueInput = document.querySelector("#venue");
const imageInput = document.querySelector("#image");
const textAreaInput = document.querySelector("#textarea");

const modal = document.querySelector(".modal-form"); // Assuming this is your popup modal container

saveBtn.addEventListener("click", () => {
  const name = nameInput.value.trim();
  const username = usernameInput.value.trim();
  const password = passwordInput.value.trim();

  if (name && username && password) {
    alert("Registered successfully");
    
    // Hide the modal after successful registration
    modal.classList.remove("active");

    // Optionally reset the form for reuse
    nameInput.value = "";
    usernameInput.value = "";
    passwordInput.value = "";
    venueInput.value = "";
    imageInput.value = "";
    textAreaInput.value = "";
    
    // You can include any additional actions here if needed
  } else if (username || password || name) {
    alert("Some fields are empty");
  } else {
    alert("Please fill all the fields");
  }
});


modal-form.classList.remove("active");
modal-form.classList.add("inactive");

