// Global Variables
const magnifier = document.querySelector(".magnifier");
const kalimdor = document.querySelector("#Kalimdor_map");
const easternKingdom = document.querySelector("#Eastern_Kingdoms_map");

const magnifierHeight = 200;
const magnifierWidth = 200;
const magnifierZoomLevel = 3;
let magnifierX = 0;
let magnifierY = 0;

// When mouse hover on image, we initialise magnifier without display it
function onMouseEnterImg(e) {
  const elem = e.target;
  magnifier.style.height = `${magnifierHeight}px`;
  magnifier.style.width = `${magnifierWidth}px`;
  magnifier.style.backgroundImage = `url(${elem.src})`;
}

// When mouse move on image, we update magnifier position to be centered around cursor without display it
function onMouseMoveImg(e) {
  // Used when both map are in caroussel
  // we need to add 272px (width of Eastern Kingdom)
  let widthToAdd = 0;
  if (
    kalimdor != null &&
    easternKingdom != null &&
    e.target.isEqualNode(kalimdor)
  ) {
    widthToAdd = easternKingdom.width;
  }

  const elem = e.currentTarget;
  const { top, left, width, height } = elem.getBoundingClientRect();

  magnifierX = e.pageX - left - window.pageXOffset + widthToAdd;
  magnifierY = e.pageY - top - window.pageYOffset;

  magnifier.style.top = `${magnifierY - magnifierHeight / 2}px`;
  magnifier.style.left = `${magnifierX - magnifierHeight / 2}px`;
  magnifier.style.backgroundSize = `${width * magnifierZoomLevel}px ${
    height * magnifierZoomLevel
  }px`;
  magnifier.style.backgroundPositionX = `${
    -magnifierX * magnifierZoomLevel +
    magnifierWidth / 2 +
    widthToAdd * magnifierZoomLevel
  }px`;
  magnifier.style.backgroundPositionY = `${
    -magnifierY * magnifierZoomLevel + magnifierHeight / 2
  }px`;
}

// When mouse hover a point, we display magnifier
function onMouseEnterPoint(e) {
  magnifier.style.display = "block";
  e.target.style.zIndex = "1001";
}

// When mouse leave a point, we hide magnifier
function onMouseLeavePoint(e) {
  magnifier.style.display = "none";
  e.target.style.zIndex = "1000";
}
