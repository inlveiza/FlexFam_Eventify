document.addEventListener('DOMContentLoaded', function() {
  const section = document.querySelector("section"),
        overlay = document.querySelector(".overlay"),
        registerBtn = document.getElementById("registerBtn"),
        modal = document.getElementById("modal"),
        closeBtn = document.querySelector(".close-btn");
  
        console.log(closeBtn);

  const form1 = document.getElementById('form1');
  const form2 = document.getElementById('form2');
  const fullnameInput = document.getElementById('fullname');
  const programInput = document.getElementById('program');
  const nameEventInput = document.getElementById('nameEvent');
  const startTimeInput = document.getElementById('startTime');
  const emailInput = document.getElementById('email');
  const yearInput = document.getElementById('year');
  const dateEventInput = document.getElementById('dateEvent');
  const finishTimeInput = document.getElementById('finishTime');

  const fullnameError = document.getElementById('fullname-error');
  const programError = document.getElementById('program-error');
  const nameEventError = document.getElementById('nameEvent-error');
  const startTimeError = document.getElementById('startTime-error');
  const emailError = document.getElementById('email-error');
  const yearError = document.getElementById('year-error');
  const dateEventError = document.getElementById('dateEvent-error');
  const finishTimeError = document.getElementById('finishTime-error');

  form1.addEventListener('submit', validateForm);
  form2.addEventListener('submit', validateForm);

  function showModal(message) {
    const modalMessage = modal.querySelector("h3");
    modalMessage.textContent = message;
    section.classList.add("active");
    modal.classList.add("active");
    overlay.classList.add("active");
}

function hideModal() {
    section.classList.remove("active");
    modal.classList.remove("active");
    overlay.classList.remove("active");
}

  function validateForm() {
      event.preventDefault();
      fullnameError.textContent = '';
      programError.textContent = '';
      nameEventError.textContent = '';
      startTimeError.textContent = '';
      emailError.textContent = '';
      yearError.textContent = '';
      dateEventError.textContent = '';
      finishTimeError.textContent = '';

      let isValid = true;

      if (!fullnameInput.value) {
        fullnameInput.placeholder = 'Please enter your full name';
        isValid = false;
      } else {
        fullnameInput.placeholder = '';
      }
  
  
      if (!programInput.value) {
        programInput.placeholder = 'Please enter your program';
        isValid = false;
      } else {
        programInput.placeholder = '';
      }
  
  
      if (!nameEventInput.value) {
        nameEventError.textContent = 'Please select an event';
        isValid = false;
      }
  
  
      if (!startTimeInput.value) {
        startTimeError.textContent = 'Please enter the event start time';
        isValid = false;
      }
  
  
      if (!emailInput.value) {
        emailInput.placeholder = 'Please enter your email address';
        isValid = false;
      } else {
        emailInput.placeholder = '';
      }
  
  
      if (!yearInput.value) {
        yearInput.placeholder = 'Please enter your year level';
        isValid = false;
      } else {
        yearInput.placeholder = '';
      }
  
  
      if (!dateEventInput.value) {
        dateEventError.textContent = 'Please enter the event date';
        isValid = false;
      }
  
  
      if (!finishTimeInput.value) {
        finishTimeError.textContent = 'Please enter the event finish time';
        isValid = false;
      }

      if (isValid) {
        showModal();
      }
  }

  const errorMessages = [
    fullnameError,
    programError,
    nameEventError,
    startTimeError,
    emailError,
    yearError,
    dateEventError,
    finishTimeError
  ];
  errorMessages.forEach((error) => {
    error.classList.add('error-message');
  });

  registerBtn.addEventListener("click", async () => {
    console.log("Register button clicked");
    const isValid = validateForm();

    if (isValid) {
        const formData = {
            fullname: fnameInput.value,
            email: emailInput.value,
            program: ProgramInput.value,
            year: YearInput.value,
            nameEvent: nameEventInput.value,
            dateEvent: dateEventInput.value,
            startTime: startTimeInput.value,
            finishTime: finishTimeInput.value
        };

        console.log("Form Data:", formData);

        try {
            const response = await fetch('registration.php', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json'
                },
                body: JSON.stringify(formData)
            });

            const result = await response.text(); // Get the response text
            showModal(result);
        } catch (error) {
            console.error('Error:', error);
            showModal("An error occurred while processing your request.");
        }
    }
});

  closeBtn.addEventListener("click", (event) => {
      event.stopPropagation();
      hideModal();
      resetFormFields();
  });

  overlay.addEventListener("click", hideModal);
  function resetFormFields() {
    fnameInput.value = '';
    ProgramInput.value = '';
    nameEventInput.value = '';
    startTimeInput.value = '';
    emailInput.value = '';
    YearInput.value = '';
    dateEventInput.value = '';
    finishTimeInput.value = '';

    fnameError.textContent = '';
    ProgramError.textContent = '';
    nameEventError.textContent = '';
    startTimeError.textContent = '';
    emailError.textContent = '';
    YearError.textContent = '';
    dateEventError.textContent = '';
    finishTimeError.textContent = '';
}
});
