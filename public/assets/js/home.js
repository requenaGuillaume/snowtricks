const hiddenCards = document.getElementsByClassName("hidden");
const cards = Array.from(hiddenCards);

if (cards.length > 0) {
  const loadMore = document.getElementById("loader");

  loadMore.addEventListener("click", function () {
    for (card of cards) {
      card.classList.remove("hidden");
    }

    loadMore.classList.add("hidden");
    document.getElementById("arrowUp").style.display = "inherit";
  });
}
