// MAGNIFIER - written by X'Genesis Qhulut in September 2022

// global variables

var startDragX = 0;     // mouse-down location (start of drag)
var startDragY = 0;     //   "
var dragging = false;   // are we dragging?
var magnification = 1   // initial magnification
const zoomFactor = 1.1  // amount to change when you zoom
const spawnHighlightMaxDistance = 38  // if spawns fall within this number of pixels, show the highlighter

function hideHelp ()
{
  // hide all these things
  [
  'spawn-map-help-box',
  'caroussel_arrows',
  'spawn-map-highlighter',
  ].forEach(id =>
    {
    var element = document.getElementById (id)
    if (element)
      element.style.display = 'none';
   })

} // end of hideHelp


// mouse down (start of drag) - remember starting point
function onMouseDownMapContainer (event)
  {
  event.preventDefault();
  var [ currentImage, imageLeft, imageTop, offsetX, offsetY ] = findImageInfo (event)

  if (!currentImage)
    return

  startDragX  = offsetX
  startDragY  = offsetY
  dragging = true
  event.target.cursor = "grabbing"
  hideHelp ()
  } // end of onMouseDownMapContainer

// mouse up (end of drag)
function onMouseUpMapContainer (event)
  {
  dragging = false
  event.target.cursor = "unset"
  } // end of onMouseUpMapContainer

// redraw spawn points based on their original position multiplied by the magnification factor
function redrawSpawnPoints (currentImage)
  {
  const spawnPoints = document.getElementsByClassName("spawn_point")
  const offsetX = getPosition (currentImage.style.left)
  const offsetY = getPosition (currentImage.style.top)

  for (var i = 0; i < spawnPoints.length; i++)
    {
    spawnPoints[i].style.left = ((spawnPoints[i].dataset.left * magnification) + offsetX) + "px";
    spawnPoints[i].style.top  = ((spawnPoints[i].dataset.top * magnification)  + offsetY) + "px";
    } // end of for

  } // end of redrawSpawnPoints

function findImageInfo (event)
  {
  var element = event.target;
  while (true)
    {
    if (!element)
      return [ false, false, false, false, false ]

    if (element.classList && element.classList.contains ("map-container"))
      break

    element = element.parentNode;
    } // end of while we haven't found the map container

  const currentImage = element.querySelector("img")

  // where does the image start? (it may be offscreen)
  var imageLeft = getPosition (currentImage.style.left)
  var imageTop  = getPosition (currentImage.style.top)

  var offsetX = event.offsetX;
  var offsetY = event.offsetY;

  // if mouse over a spawn point, find where the spawn point is
  if (event.target.nodeName == 'circle')
    {
    offsetX = getPosition (event.target.parentNode.style.left) - imageLeft
    offsetY = getPosition (event.target.parentNode.style.top)  - imageTop
    } // end of if over a spawn point
  else if (event.target.nodeName == 'svg')
    {
    offsetX = getPosition (event.target.style.left) - imageLeft
    offsetY = getPosition (event.target.style.top)  - imageTop
    } // end of if over a SVG point
  else if (event.target.nodeName == 'DIV')
    {
    return [ false, false, false, false, false ]
    } // end of if over a DIV point

  return [ currentImage, imageLeft, imageTop, offsetX, offsetY ]

  }   // end of findImage

function onMouseMoveMapContainer (event)
  {
  event.preventDefault();

  if (!dragging)
    return;

  // if button is now up they must have released it outside the container
  if (event.buttons == 0)
    {
    onMouseUpMapContainer (event)
    return
    }

  const [ currentImage, imageLeft, imageTop, offsetX, offsetY ] = findImageInfo (event)

  if (!currentImage)
    return

  // difference between where we started and where we are now
  const diffX = startDragX - offsetX;
  const diffY = startDragY - offsetY;

  // move it by the difference between where we started and where we are now
  currentImage.style.left = (getPosition (currentImage.style.left) - diffX) + "px"
  currentImage.style.top  = (getPosition (currentImage.style.top)  - diffY) + "px"

  redrawSpawnPoints (currentImage)

  } // end of onMouseMoveMapContainer

function onMouseWheelMapContainer (event)
{
  event.preventDefault();

  const [ currentImage, imageLeft, imageTop, offsetX, offsetY ] = findImageInfo (event)

  if (!currentImage)
    return

  // hide help box
  hideHelp ()

  // how far through image is mouse assuming no magnification
  // (image may start offscreen)
  const mouseX = offsetX / magnification
  const mouseY = offsetY / magnification

  // how far cursor is through container
  const cursorX = offsetX + imageLeft
  const cursorY = offsetY + imageTop

  magnification *= event.deltaY > 0 ? 1/zoomFactor : zoomFactor

  // constrain to 0.5 to 30 magnification
  magnification = Math.min (magnification, 30)
  magnification = Math.max (magnification, 0.5)

  // adjust image size
  currentImage.style.width  = (currentImage.dataset.width  * magnification) + "px"
  currentImage.style.height = (currentImage.dataset.height * magnification) + "px"

  // move image so that the place under the cursor is still under it
  currentImage.style.left = - mouseX * magnification +  cursorX + "px"
  currentImage.style.top  = - mouseY * magnification +  cursorY + "px"

  redrawSpawnPoints (currentImage)

} // end of onMouseWheelMapContainer

function getPosition (which)
  {
  return parseFloat (which.split ("px") [0])
  } // end of getPosition

// Apply highlight if there is only one point on maps
// or a lot of points close together (less than spawnHighlightMaxDistance from the first one checked)
function applyHighLightOnFirstPoint() {
  var spawnPoints = document.getElementsByClassName("spawn_point")

  // give up if no spawn points
  if (spawnPoints.length < 1)
    return;

  var totalLeft = 0
  var totalTop = 0

  // find average of the points
  for (var i = 0; i < spawnPoints.length; i++)
    {
    totalLeft += parseFloat (spawnPoints[i].dataset.left)
    totalTop  += parseFloat (spawnPoints[i].dataset.top)
    } // end of for

  const averageX = totalLeft / spawnPoints.length
  const averageY = totalTop / spawnPoints.length
  var maxDistance = 0

  // now find the distance from all points to the average, and remember the largest
  for (var i = 0; i < spawnPoints.length; i++)
    {
    const x = parseFloat (spawnPoints[i].dataset.left)
    const y  = parseFloat (spawnPoints[i].dataset.top)
    const distance = Math.sqrt (Math.pow (x - averageX, 2) + Math.pow (y - averageY, 2))
    maxDistance = Math.max (maxDistance, distance)
    } // end of for

  // if too big, draw no highlight
  if (maxDistance > spawnHighlightMaxDistance)
    return;

  const highlighter = document.querySelector("#spawn-map-highlighter");

  const firstPoint = spawnPoints[0];
  // we had 1px stroke
  const pointRadius = (firstPoint.width.animVal.value + 1) / 2;
  // we display it before taking his width
  highlighter.style.display = "block";
  const highlighterRadius = highlighter.clientWidth / 2;
  const totalRadius = highlighterRadius - pointRadius;
  highlighter.style.top = `${averageY - totalRadius}px`;
  highlighter.style.left = `${averageX - totalRadius}px`;
} // end of applyHighLightOnFirstPoint

applyHighLightOnFirstPoint();
