document.addEventListener("DOMContentLoaded", function () {
  const div = document.querySelector(".js-data-images");
  const imagesElement = div.getElementsByClassName("js-images");
  const removeVideoButtons = document.getElementsByClassName("js-videos");
  const slug = div.dataset.slug;

  const init = { method: "GET", mode: "cors", cache: "default" };

  // delete image or set as main image
  for (let i = 0; i < imagesElement.length; i++) {
    let image = imagesElement[i].dataset.image;
    let deleteButton = document.getElementById(`delete-${image}`);
    let mainImageButton = document.getElementById(`main-${image}`);

    if (deleteButton) {
      deleteButton.addEventListener("click", function () {
        let confirm = window.confirm("Confirm you want to delete this image.");

        if (confirm) {
          let request = new Request(
            `https://127.0.0.1:8000/trick/edit/${slug}/remove-image/${image}`,
            init
          );

          fetch(request).then(function (response) {
            if (response.ok && response.status === 200) {
              document.getElementById(image).style.display = "none";
            }
          });
        }
      });
    }

    if (mainImageButton) {
      mainImageButton.addEventListener("click", function () {
        let confirm = window.confirm(
          "Confirm you want to set this image as main image."
        );

        if (confirm) {
          let request = new Request(
            `https://127.0.0.1:8000/trick/edit/${slug}/main-image/${image}`,
            init
          );

          fetch(request).then(function (response) {
            if (response.ok && response.status === 200) {
              document.getElementById(`main-${image}`).innerHTML =
                "new main image";
            }
          });
        }
      });
    }
  }

  // delete video
  for (let i = 0; i < removeVideoButtons.length; i++) {
    removeVideoButtons[i].addEventListener("click", function () {
      let confirm = window.confirm("Confirm you want to delete this video.");

      if (confirm) {
        let request = new Request(
          `https://127.0.0.1:8000/trick/edit/${slug}/remove-video/${i}`,
          init
        );

        fetch(request).then(function (response) {
          if (response.ok && response.status === 200) {
            document.getElementById(`video-${i}`).style.display = "none";
          }
        });
      }
    });
  }

  const seeMedia = document.getElementById("see-media");

  seeMedia.addEventListener("click", function () {
    let hiddenMedia = document.getElementsByClassName("mobile-hide");

    for (media of hiddenMedia) {
      media.classList.remove("mobile-hide");
    }

    seeMedia.classList.add("hide");
  });
});
