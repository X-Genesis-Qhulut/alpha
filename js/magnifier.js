// Global Variables
const magnifier = document.querySelector(".magnifier");
const kalimdor = document.querySelector("#Kalimdor_map");
const easternKingdom = document.querySelector("#Eastern_Kingdoms_map");
let actualMapInUse = null;
const spawnPoints = document.querySelectorAll(".spawn_point");
let modifiedSpawnPoints = [];
const magnifierHeight = 200;
const magnifierWidth = 200;
const magnifierZoomLevel = 3;
let magnifierX = 0;
let magnifierY = 0;

// When mouse hover on image, we initialise magnifier without display it
function onMouseEnterImg(e) {
  const map = e.target;
  actualMapInUse = e;
  const { width, height } = map;

  magnifier.style.height = `${magnifierHeight}px`;
  magnifier.style.width = `${magnifierWidth}px`;
  magnifier.style.backgroundImage = `url(${map.src})`;
  magnifier.style.backgroundSize = `${width * magnifierZoomLevel}px ${
    height * magnifierZoomLevel
  }px`;
}

// When mouse move on image, we verify if cursor is still in magnifier radius to hide it if not
function onMouseMoveImg(e) {
  // if magnifier is closed, we dont need to do calculation
  if (magnifier.style.display == "none") {
    return;
  }

  const img = e.currentTarget;
  const { top, left } = img.getBoundingClientRect();
  const widthToAdd = _calculateWidthToAdd();

  // calculate cursor position on the image
  let cursorX = e.pageX - left - window.pageXOffset + widthToAdd;
  let cursorY = e.pageY - top - window.pageYOffset;

  const distance = _calculateDistance(cursorX, cursorY, magnifierX, magnifierY);
  if (distance > magnifierWidth / 2) {
    magnifier.style.display = "none";
    _resetModifiedSpawnPoints();
  }
}

// When mouse hover a point, we display magnifier
function onMouseEnterPoint(e) {
  // if magnifier is already open and point is in magnfier radius, we dont do anything
  if (
    _checkIfPointIsInMagnifier(e.target) &&
    magnifier.style.display != "none"
  ) {
    return;
  }

  _resetModifiedSpawnPoints();
  magnifier.style.display = "block";
  e.target.style.zIndex = "1001";
  _alignMagnifierWithPoint(e);

  for (let point of spawnPoints) {
    if (!point.isEqualNode(e.target) && _checkIfPointIsInMagnifier(point)) {
      _correctMagnifiedPointPosition(e.target, point);
    }
  }
}

// When mouse leave a point
function onMouseLeavePoint(e) {
  return;
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

// When user hover a point, magnifier will align its center with it
function _alignMagnifierWithPoint(e) {
  const { width, height } = actualMapInUse.target;
  const widthToAdd = _calculateWidthToAdd();
  magnifierX = _domValueToInt(e.target.style.left) + widthToAdd;
  magnifierY = _domValueToInt(e.target.style.top);
  _updateMagnifierPosition();
}

// Determine if both map are present in caroussel to add the first map width
function _calculateWidthToAdd() {
  let widthToAdd = 0;
  if (
    kalimdor != null &&
    easternKingdom != null &&
    actualMapInUse.target.isEqualNode(kalimdor)
  ) {
    widthToAdd = easternKingdom.width;
  }

  return widthToAdd;
}

// Calculate distance between 2 points and return result
function _calculateDistanceBetweenPoints(point1, point2) {
  const point1Y = _domValueToInt(point1.style.top);
  const point1X = _domValueToInt(point1.style.left);
  const point2Y = _domValueToInt(point2.style.top) + magnifierHeight / 2;
  const point2X = _domValueToInt(point2.style.left) + magnifierHeight / 2;

  return _calculateDistance(point1X, point1Y, point2X, point2Y);
}

// Check if point is in magnifier radius
function _checkIfPointIsInMagnifier(point) {
  const widthToAdd = _calculateWidthToAdd();
  // we had point width/2 to be sure that point cant be in between magnifier border
  const radius = magnifierHeight / 2 + point.width.animVal.value / 2;
  const pointX = _domValueToInt(point.style.left) + widthToAdd;
  const pointY = _domValueToInt(point.style.top);
  const distance = _calculateDistance(magnifierX, magnifierY, pointX, pointY);
  return distance < radius;
}

// Update spawn point to fit zoom if it is in magnifier range
function _correctMagnifiedPointPosition(referencePoint, point) {
  // distance difference X between points
  let diffX =
    (_domValueToInt(referencePoint.style.left) -
      _domValueToInt(point.style.left)) *
    magnifierZoomLevel;

  // distance difference Y between points
  let diffY =
    (_domValueToInt(referencePoint.style.top) -
      _domValueToInt(point.style.top)) *
    magnifierZoomLevel;

  let updatedPoint = point.cloneNode();

  // we add/sub perimeter of spawn point to be more accurate
  const perimeter = point.width.animVal.value;
  diffY = diffY < 0 ? diffY + perimeter : diffY - perimeter;
  diffX = diffX < 0 ? diffX + perimeter : diffX - perimeter;
  updatedPoint.style.top = `${_domValueToInt(point.style.top) - diffY}px`;
  updatedPoint.style.left = `${_domValueToInt(point.style.left) - diffX}px`;

  // we push point into array to be able to reset its location later
  modifiedSpawnPoints.push({
    point,
    originalY: point.style.top,
    originalX: point.style.left,
  });

  // We verify if point is still in magnifier radius after updating coords
  if (_checkIfPointIsInMagnifier(updatedPoint)) {
    point.style.top = updatedPoint.style.top;
    point.style.left = updatedPoint.style.left;
  } else {
    // it is not in magnifier radius anymore, we hide it to prevent wrong location
    point.style.display = "none";
  }
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

// Return int value of DOM style property : 450px -> 450
function _domValueToInt(value) {
  return parseInt(value.split("px")[0]);
}

// Calculate distance between coords
function _calculateDistance(x1, y1, x2, y2) {
  return Math.sqrt(Math.pow(x1 - x2, 2) + Math.pow(y1 - y2, 2));
}
