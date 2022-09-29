
// mouse-down location
var startDragX = 0;
var startDragY = 0;
var dragging = false;
var currentImage = null
var magnification = 1
const zoomFactor = 1.5

function onMouseDownMapContainer (event)
{
console.log ('onMouseDownMapContainer')
startDragX  = event.offsetX;
startDragY  = event.offsetY;
console.log (`mouse down at ${startDragX}, ${startDragY}`)
dragging = true
event.preventDefault();

}


function onMouseUpMapContainer (event)
{
console.log ('onMouseUpMapContainer')
dragging = false

} // end of onMouseUpMapContainer

function onMouseLeaveMapContainer (event)
{
console.log ('onMouseLeaveMapContainer')

} // end of onMouseLeaveMapContainer

function redrawSpawnPoints ()
  {
  var spawnPoints = document.getElementsByClassName("spawn_point")
  var offsetX = getPosition (currentImage.style.left)
  var offsetY = getPosition (currentImage.style.top)

  for (let i = 0; i < spawnPoints.length; i++)
    {
    spawnPoints[i].style.left = ((spawnPoints[i].dataset.left * magnification) + offsetX) + "px";
    spawnPoints[i].style.top  = ((spawnPoints[i].dataset.top * magnification) + offsetY) + "px";
    }

  } // end of redrawSpawnPoints


function onMouseMoveMapContainer (event)
  {
  event.preventDefault();


  console.log ('onMouseMoveMapContainer')
  if (!dragging)
    return;

  var offsetX = event.offsetX;
  var offsetY = event.offsetY;

  var diffX = startDragX - offsetX;
  var diffY = startDragY - offsetY;

  if (!currentImage)
    {
    console.log ('No image')
    return
    }

  console.log (`Moved X by ${diffX} and Y by ${diffY}`)
  currentImage.style.left = (getPosition (currentImage.style.left) - diffX) + "px"
  currentImage.style.top  = (getPosition (currentImage.style.top)  - diffY) + "px"

  console.log (`New position = ${currentImage.style.left}, ${currentImage.style.top}`)

  redrawSpawnPoints ()

  } // end of onMouseMoveMapContainer

function onMouseWheelMapContainer (event)
{
  event.preventDefault();

  if (!currentImage)
    {
    console.log ('No image')
    return
    }

  // where is mouse over?
  var offsetX = event.offsetX;
  var offsetY = event.offsetY;

  console.log (`X = ${offsetX}, Y = ${offsetY}`)

  // where does the image start? (it may be offscreen)
  var imageLeft = getPosition (currentImage.style.left)
  var imageTop  = getPosition (currentImage.style.top)

  // normalise as if it were not scrolled
  offsetX += imageLeft
  offsetY += imageTop

  // how far through image is mouse assuming no magnification
  var mouseX = (offsetX - imageLeft ) / magnification
  var mouseY = (offsetY - imageTop  ) / magnification

  console.log (`mouseX = ${mouseX}, mouseY = ${mouseY}`)

  var oldMagnification = magnification
  magnification *= event.deltaY > 0 ? 1/zoomFactor : zoomFactor

  console.log (`Magnification now ${magnification}`)

  // adjust image size
  currentImage.style.width  = (currentImage.dataset.width  * magnification) + "px"
  currentImage.style.height = (currentImage.dataset.height * magnification) + "px"

  // the new left and top should be the same as before, adjusting for the new magnification
 // currentImage.style.left = (imageLeft) -((mouseX) * (magnification / oldMagnification - 1)) + "px"
 // currentImage.style.top  = (imageTop ) -((mouseY) * (magnification / oldMagnification - 1)) + "px"

  currentImage.style.left = - ((offsetX * (magnification - 1)))  + "px"
  currentImage.style.top  = - ((offsetY * (magnification - 1)))  + "px"

  console.log (`New dimensions = ${currentImage.style.width}, ${currentImage.style.height}`)
  console.log (`New position = ${currentImage.style.left}, ${currentImage.style.top}`)

  redrawSpawnPoints ()

}

// caroussel ??
function onMouseMoveArea (event)
{
//console.log ('onMouseMoveArea')

} // end of onMouseMoveArea

function onMouseEnterImg (event)
{
console.log ('onMouseEnterImg')
currentImage = event.target;
} // end of onMouseEnterImg


function getPosition (which)
  {
  return parseFloat (which.split ("px") [0])
  } // end of getPosition
