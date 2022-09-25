// ############### GLOBAL VARIABLES ##################

const magnifier = document.querySelector(".magnifier");
const kalimdor = document.querySelector("#Kalimdor_map");
const easternKingdom = document.querySelector("#Eastern_Kingdoms_map");
let actualMapInUse = null;
const spawnPoints = document.querySelectorAll(".spawn_point");
let modifiedSpawnPoints = [];
const magnifierHeight = 1300;
const magnifierWidth = 1300;
const magnifierZoomLevel = 5;
let magnifierX = 0;
let magnifierY = 0;

// ############### EVENT FUNCTIONS ##################

// When mouse leave caroussel, we close magnifier
function onMouseLeaveArea(e) {
  e;
  _closeMagnifier();
}

// When mouse hover on image, we initialise magnifier without display it
function onMouseEnterImg(e) {
  const map = e.target;
  actualMapInUse = e;
  const { width, height } = map;

  // Prevent display to be a empty string
  if (magnifier.style.display == "") {
    magnifier.style.display = "none";
  }

  magnifier.style.height = `${magnifierHeight}px`;
  magnifier.style.width = `${magnifierWidth}px`;
  magnifier.style.backgroundImage = `url(${map.src})`;
  magnifier.style.backgroundSize = `${width * magnifierZoomLevel}px ${
    height * magnifierZoomLevel
  }px`;
}

// When mouse move on image (UNUSED)
function onMouseMoveImg(e) {
  e;
  return;
}

// When mouse hover a point and magnifier is not open, we display magnifier
function onMouseEnterPoint(e) {
  const svg = e.target.childNodes[1];

  // if magnifier is already open, we do nothing
  if (magnifier.style.display != "none") {
    return;
  }
  _resetModifiedSpawnPoints();
  magnifier.style.display = "block";
  e.target.style.zIndex = "1001";
  _alignMagnifierWithPoint(svg);

  for (let point of spawnPoints) {
    if (!point.isEqualNode(svg) && _checkIfPointIsInActualMap(point)) {
      _correctMagnifiedPointPosition(svg, point);
    }
  }
}

// When mouse leave a point (UNUSED)
function onMouseLeavePoint(e) {
  e;
  return;
}

// ############### PRIVATE FUNCTIONS ##################

// Set display to none and reset points to their original location
function _closeMagnifier() {
  magnifier.style.display = "none";
  _resetModifiedSpawnPoints();
}

// Update magnifier top/left and its background img position to new coords
function _updateMagnifierPosition() {
  const widthToAdd = _calculateWidthToAdd();
  magnifier.style.top = `${magnifierY - magnifierHeight / 2}px`;
  magnifier.style.left = `${magnifierX - magnifierHeight / 2}px`;

  magnifier.style.backgroundPositionX = `${
    -magnifierX * magnifierZoomLevel +
    magnifierWidth / 2 +
    widthToAdd * magnifierZoomLevel
  }px`;
  magnifier.style.backgroundPositionY = `${
    -magnifierY * magnifierZoomLevel + magnifierHeight / 2
  }px`;
}

// Magnifier will align its center with point
function _alignMagnifierWithPoint(point) {
  const widthToAdd = _calculateWidthToAdd();
  magnifierX = _domValueToFloat(point.style.left) + widthToAdd;
  magnifierY = _domValueToFloat(point.style.top);

  _updateMagnifierPosition();
}

// Determine if both map are present in caroussel to add the first map width
function _calculateWidthToAdd() {
  let widthToAdd = 0;
  if (
    kalimdor != null &&
    easternKingdom != null &&
    actualMapInUse.target.isEqualNode(easternKingdom)
  ) {
    widthToAdd = easternKingdom.width;
  }

  return widthToAdd;
}

// Check if point is in actual map hovered by mouse
function _checkIfPointIsInActualMap(point) {
  if (!actualMapInUse) {
    return;
  }

  const mapContainer = actualMapInUse.target.parentNode;
  const mapChildren = mapContainer.children;
  for (let element of mapChildren) {
    let mapPoint = element.firstElementChild;
    if (mapPoint && mapPoint.isEqualNode(point)) {
      return true;
    }
  }

  return false;
}

// Update spawn point location to fit zoom
function _correctMagnifiedPointPosition(referencePoint, point) {
  // distance difference X between points
  let diffX =
    (_domValueToFloat(referencePoint.style.left) -
      _domValueToFloat(point.style.left)) *
    magnifierZoomLevel;

  // distance difference Y between points
  let diffY =
    (_domValueToFloat(referencePoint.style.top) -
      _domValueToFloat(point.style.top)) *
    magnifierZoomLevel;

  let updatedPoint = point.cloneNode();
  updatedPoint.style.top = `${
    _domValueToFloat(referencePoint.style.top) - diffY
  }px`;
  updatedPoint.style.left = `${
    _domValueToFloat(referencePoint.style.left) - diffX
  }px`;

  // we push point into array to be able to reset its location later
  modifiedSpawnPoints.push({
    point,
    originalY: point.style.top,
    originalX: point.style.left,
  });

  point.style.top = updatedPoint.style.top;
  point.style.left = updatedPoint.style.left;
}

// We set original point location when magnifier is closed
function _resetModifiedSpawnPoints() {
  for (let elem of modifiedSpawnPoints) {
    elem.point.style.top = elem.originalY;
    elem.point.style.left = elem.originalX;
    elem.point.style.display = "block";
  }
  modifiedSpawnPoints = [];
}

// Return float value of DOM style property : 450.13px -> 450.13
function _domValueToFloat(value) {
  return parseFloat(value.split("px")[0]);
}

// Calculate distance between coords
function _calculateDistance(x1, y1, x2, y2) {
  return Math.sqrt(Math.pow(x1 - x2, 2) + Math.pow(y1 - y2, 2));
}

// Calculate distance between 2 points and return result
// function _calculateDistanceBetweenPoints(point1, point2) {
//   const point1Y = _domValueToFloat(point1.style.top);
//   const point1X = _domValueToFloat(point1.style.left);
//   const point2Y = _domValueToFloat(point2.style.top) + magnifierHeight / 2;
//   const point2X = _domValueToFloat(point2.style.left) + magnifierHeight / 2;

//   return _calculateDistance(point1X, point1Y, point2X, point2Y);
// }

// Check if point is in magnifier radius
// function _checkIfPointIsInMagnifier(point) {
//   const widthToAdd = _calculateWidthToAdd();
//   // we had point width/2 to be sure that point cant be in between magnifier border
//   const radius = magnifierHeight / 2 + point.width.animVal.value / 2;
//   const pointX = _domValueToFloat(point.style.left) + widthToAdd;
//   const pointY = _domValueToFloat(point.style.top);
//   const distance = _calculateDistance(magnifierX, magnifierY, pointX, pointY);
//   return distance < radius;
// }
