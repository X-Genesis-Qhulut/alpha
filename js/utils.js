// return a promise
function copyToClipboard(textToCopy) {
  // navigator clipboard api needs a secure context (https)
  if (navigator.clipboard && window.isSecureContext) {
    // navigator clipboard api method'
    return navigator.clipboard.writeText(textToCopy);
  } else {
    // text area method
    let textArea = document.createElement("textarea");
    textArea.value = textToCopy;
    // make the textarea out of viewport
    textArea.style.position = "fixed";
    textArea.style.left = "-999999px";
    textArea.style.top = "-999999px";
    document.body.appendChild(textArea);
    textArea.focus();
    textArea.select();
    return new Promise((res, rej) => {
      // here the magic happens
      document.execCommand("copy") ? res() : rej();
      textArea.remove();
    });
  }
} // end of copyToClipboard

// Update map width and height when Zoom occurs
function _updateMapSize() {
  if (!actualMapInUse) {
    return;
  }
  actualMapInUse.setAttribute(
    "style",
    `width:${defaultMapWidth * actualZoomLevel}px; height:${
      defaultMapHeight * actualZoomLevel
    }px`
  );
}
// Hide element to help spot arrow if there is only one
function _hideHighlight() {
  spawnMapHighlighter.style.display = "none";
}

function clickOnSpawnPoint(event, location) {
  if (event.ctrlKey) location = ".port " + location;
  copyToClipboard(location);
} // end of clickOnSpawnPoint
