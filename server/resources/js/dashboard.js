import "flowbite";
import "dropzone/dist/dropzone.css";

import Dropzone from "dropzone";

// Init Dropzone
const dropzoneArea = document.querySelectorAll("#demo-upload");

if (dropzoneArea.length) {
  let myDropzone = new Dropzone("#demo-upload", { url: "/file/post" });
}

// Get the current year
const year = document.getElementById("year");
if (year) {
  year.textContent = new Date().getFullYear();
}

// For Copy//
document.addEventListener("DOMContentLoaded", () => {
  const copyInput = document.getElementById("copy-input");
  if (copyInput) {
    // Select the copy button and input field
    const copyButton = document.getElementById("copy-button");
    const copyText = document.getElementById("copy-text");
    const websiteInput = document.getElementById("website-input");

    // Event listener for the copy button
    copyButton.addEventListener("click", () => {
      // Copy the input value to the clipboard
      navigator.clipboard.writeText(websiteInput.value).then(() => {
        // Change the text to "Copied"
        copyText.textContent = "Copied";

        // Reset the text back to "Copy" after 2 seconds
        setTimeout(() => {
          copyText.textContent = "Copy";
        }, 2000);
      });
    });
  }
});

document.addEventListener("livewire:init", () => {
  Livewire.on("showModal", (event) => {
    showModal(event[0].id);
  });

  Livewire.on("hideModal", (event) => {
    hideModal(event[0].id);
  });
});

window.showModal = function (id) {
  document.querySelector(`#${id}`).classList.remove("hidden");
  document.querySelector(`#${id}`).classList.add("flex");
};

window.hideModal = function (id) {
  document.querySelector(`#${id}`).classList.remove("flex");
  document.querySelector(`#${id}`).classList.add("hidden");
};
