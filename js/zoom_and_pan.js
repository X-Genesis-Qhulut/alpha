20// MAGNIFIER - written by X'Genesis Qhulut in September 2022

// global variables

// Note: 10% webp quality on small map, 91% on big map

var startDragX = 0;     // mouse-down location (start of drag)
var startDragY = 0;     //   "
var dragging = false;   // are we dragging?
var magnification = 1   // initial magnification
const zoomFactor = 1.1  // amount to change when you zoom
const maxZoom = 40      // maximum magnification
const minZoom = 0.5     // minimum magnification
const spawnHighlightMaxDistance = 38  // if spawns fall within this number of pixels, show the highlighter
const mediumZoom = 8;
const largeZoom = 16;

function hideHelp ()
{

  // hide all these things
 ;[
  'spawn-map-help-box',
  'spawn-map-highlighter',
  'map-arrow-right',
  'map-arrow-left',
  ].forEach(id =>
    {
    var element = document.getElementById (id)
    if (element)
      {
      element.style.display = 'none';
      }
   }) // end of foreach


  // lower opacity of all these things
  ;[
  'shortcut-icon',
  ].forEach(id =>
    {
    var element = document.getElementById (id)
    if (element)
      {
      element.style.opacity = '0.4';
      }
   }) // end of foreach


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
  event.target.style.cursor = "grabbing"
  } // end of onMouseDownMapContainer

// mouse up (end of drag)
function onMouseUpMapContainer (event)
  {
  dragging = false
  event.target.style.cursor = "unset"

  // check for Alt key - to give the location

  if (!event.altKey)
    return

  var [ currentImage, imageLeft, imageTop, offsetX, offsetY ] = findImageInfo (event)

  if (!currentImage)
    return

  // map coordinates in yards
  const mapWidth = currentImage.dataset.mapwidth
  const mapHeight = currentImage.dataset.mapheight
  const mapLeft = currentImage.dataset.mapleft
  const mapTop = currentImage.dataset.maptop
  const mapNumber = currentImage.dataset.mapnumber

  const imageWidth = currentImage.dataset.width
  const imageHeight = currentImage.dataset.height

  // how far through image is mouse assuming no magnification
  // (image may start offscreen)
  const mouseX = offsetX / magnification
  const mouseY = offsetY / magnification

  // distance through map we are (from 0 to 1)
  const xOffset = mouseX / imageWidth
  const yOffset = mouseY / imageHeight

  const x = mapLeft - mapWidth  * xOffset
  const y = mapTop  - mapHeight * yOffset

  copyToClipboard (`.port ${Math.round (y * 1000) / 1000} ${Math.round (x * 1000) / 1000} 300 ${mapNumber}`)

  } // end of onMouseUpMapContainer

// redraw spawn points based on their original position multiplied by the magnification factor
function redrawSpawnPoints (currentImage)
  {
  const spawnPoints = document.getElementsByClassName("spawn_point")
  const offsetX = currentImage.offsetLeft
  const offsetY = currentImage.offsetTop

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
//  var imageLeft = getPosition (currentImage.style.left)
  var imageLeft = currentImage.offsetLeft;
  var imageTop  = currentImage.offsetTop;

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

  hideHelp ()

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
  currentImage.style.left = (imageLeft - diffX) + "px"
  currentImage.style.top  = (imageTop  - diffY) + "px"

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
  magnification = Math.min (magnification, maxZoom)
  magnification = Math.max (magnification, minZoom)

  // adjust image size
  currentImage.style.width  = (currentImage.dataset.width  * magnification) + "px"
  currentImage.style.height = (currentImage.dataset.height * magnification) + "px"

  // move image so that the place under the cursor is still under it
  currentImage.style.left = - mouseX * magnification +  cursorX + "px"
  currentImage.style.top  = - mouseY * magnification +  cursorY + "px"

 // load a medium def map if needed

  const baseName = currentImage.dataset.basename
  const extension = currentImage.dataset.extension
  const currentName = currentImage.src

  // adjust map image file name depending on amount of magnification
  // we downgrade when zooming out because the browser struggles to resize very large files down very small
  if (magnification < mediumZoom && currentName != (baseName + extension))
    currentImage.src = baseName + extension
  else if (magnification >= mediumZoom && magnification < largeZoom && currentName != (baseName + '_big' + extension))
    currentImage.src = baseName + '_big' + extension
  else if (magnification >= largeZoom && currentName != (baseName + '_bigger' + extension))
    currentImage.src = baseName + '_bigger' + extension

//  console.log (`magnification = ${magnification}, file name = ${currentImage.src}`)

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

  const highlighter = document.querySelector("#spawn-map-highlighter");

  // give up if no highlighter or spawn points
  if (!highlighter || !spawnPoints)
    return;

  // give up if no spawn points
  if (spawnPoints.length < 1)
    {
    highlighter.style.display = "none";
    return;
    }

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
    {
    highlighter.style.display = "none";
    return;
    }

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
