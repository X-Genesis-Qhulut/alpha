// Reduce/Increase map area size
function resizeCaroussel(event) {
  const caroussel = document.querySelector("#spawn-map-caroussel");
  const detailsContainer = document.querySelector(
    ".object-container__informations__details2"
  );

  let expandButton = event.target;
  while (expandButton.tagName != "BUTTON") {
    expandButton = expandButton.parentNode;
  }

  if (detailsContainer.style.display == "none") {
    detailsContainer.style.display = "flex";
    caroussel.style.flexGrow = "0";
    expandButton.style.transform = "rotate(0deg)";
  } else {
    detailsContainer.style.display = "none";
    caroussel.style.flexGrow = "1";
    expandButton.style.transform = "rotate(180deg)";
    hideHelp();
  }
}

// change current map with the other
function changeMapInCaroussel(event) {
  const kalimdorContainer = document.querySelector("#map-container-Kalimdor");
  const easterKingdomsContainer = document.querySelector(
    "#map-container-Eastern_Kingdoms"
  );

  if (kalimdorContainer.style.display == "none") {
    kalimdorContainer.style.display = "block";
    easterKingdomsContainer.style.display = "none";
  } else {
    kalimdorContainer.style.display = "none";
    easterKingdomsContainer.style.display = "block";
  }
}
