const mediaButton = document.getElementById("see-media");

mediaButton.addEventListener("click", () => {
  medias.classList.remove("hide");
  mediaButton.style.display = "none";
});
