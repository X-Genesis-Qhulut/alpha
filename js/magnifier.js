// MAGNIFIER

// global variables

var startDragX = 0;     // mouse-down location (start of drag)
var startDragY = 0;     //   "
var dragging = false;   // are we dragging?
var magnification = 1   // initial magnification
const zoomFactor = 1.1  // amount to change when you zoom

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
  } // end of onMouseDownMapContainer

// mouse up (end of drag)
function onMouseUpMapContainer (event)
  {
  dragging = false
  event.target.cursor = "unset"
  } // end of onMouseUpMapContainer

function onMouseLeaveMapContainer (event)
  {
  } // end of onMouseLeaveMapContainer

// redraw spawn points based on their original position multiplied by the magnification factor
function redrawSpawnPoints (currentImage)
  {
  var spawnPoints = document.getElementsByClassName("spawn_point")
  var offsetX = getPosition (currentImage.style.left)
  var offsetY = getPosition (currentImage.style.top)

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
    }

  var currentImage = element.querySelector("img")

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

  var [ currentImage, imageLeft, imageTop, offsetX, offsetY ] = findImageInfo (event)

  if (!currentImage)
    return


  console.log (`node = ${event.target.nodeName}, ID = ${event.target.id}, offsetX = ${offsetX}, offsetY = ${offsetY}`)

  // difference between where we started and where we are now
  var diffX = startDragX - offsetX;
  var diffY = startDragY - offsetY;

  // move it by the difference between where we started and where we are now
  currentImage.style.left = (getPosition (currentImage.style.left) - diffX) + "px"
  currentImage.style.top  = (getPosition (currentImage.style.top)  - diffY) + "px"

  redrawSpawnPoints (currentImage)

  } // end of onMouseMoveMapContainer

function onMouseWheelMapContainer (event)
{
  event.preventDefault();

  var [ currentImage, imageLeft, imageTop, offsetX, offsetY ] = findImageInfo (event)

  if (!currentImage)
    return

 // where does the image start? (it may be offscreen)
  var imageLeft = getPosition (currentImage.style.left)
  var imageTop  = getPosition (currentImage.style.top)

  // where is mouse over? - relative to the IMAGE not the container
  var offsetX = event.offsetX;
  var offsetY = event.offsetY;



  // if mouse over a spawn point, find where the spawn point is
  if (event.target.nodeName == 'circle')
    {
    offsetX = getPosition (event.target.parentNode.style.left) - imageLeft
    offsetY = getPosition (event.target.parentNode.style.top)  - imageTop
    } // end of if over a spawn point


  // how far through image is mouse assuming no magnification
  // (image may start offscreen)
  var mouseX = offsetX / magnification
  var mouseY = offsetY / magnification

  // how far cursor is through container
  var cursorX = offsetX + imageLeft
  var cursorY = offsetY + imageTop

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

// caroussel ??
function onMouseMoveArea (event)
  {
  } // end of onMouseMoveArea

function onMouseEnterImg (event)
  {
  } // end of onMouseEnterImg


function getPosition (which)
  {
  return parseFloat (which.split ("px") [0])
  } // end of getPosition
