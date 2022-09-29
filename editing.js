/*

  Author: X'Genesis Qhulut <XGenesis-Qhulut@protonmail.com>
  Date:   August 2022

  See LICENSE for license details.


*/


// See: https://stackoverflow.com/a/22480938/

function isScrolledIntoView(el) {
  var rect = el.getBoundingClientRect();
  var elemTop = rect.top;
  var elemBottom = rect.bottom;

  // Only completely visible elements return true:
  var isVisible = (elemTop >= 0) && (elemBottom <= window.innerHeight);
  // Partially visible elements return true:
  //isVisible = elemTop < window.innerHeight && elemBottom >= 0;
  return isVisible;
} // end of isScrolledIntoView

function onRowClick(event, id)
{
  // Alt+Click to show an editing box
  if (!event.altKey)
    return

  event.preventDefault()

  // extract out the field name, which will start with "field_"
  var field = id.match (/^field_(.+)$/)
  if (!field)
    return

  // find the editing SQL box
  var editingDiv = document.getElementById('editing_sql')
  if (!editingDiv)
    return

  // alter the field name
  document.getElementById('sql_field_name').innerHTML = field [1]

  // make the editing box visible
  editingDiv.style.display = 'block'

  // scroll it into view if necessary
  if (!isScrolledIntoView (editingDiv))
    editingDiv.scrollIntoView()
} // end of onRowClick

// see: https://stackoverflow.com/questions/51805395/navigator-clipboard-is-undefined

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
            document.execCommand('copy') ? res() : rej();
            textArea.remove();
        });
    }
} // end of copyToClipboard

// for copying a spawn point coordinates to the clipboard
function copyContents (event)
  {
  clickOnSpawnPoint (event, event.currentTarget.dataset.location)
  return false
  } // end of copyContents

// for switching model pages (out of a possible 4 variations)
function modelPage (event)
  {
  var page = event.currentTarget.dataset.page
  var model_count = document.getElementById('caroussel-model').dataset.modelcount

  for (i = 1; i <= model_count; i++)
    {
    document.getElementById('model' + i).style.display = 'none';
    document.getElementById('model_navigate' + i).style.color = 'darkgray';
    }

  document.getElementById('model' + page).style.display = 'block';
  document.getElementById('model_navigate' + page).style.color = 'whitesmoke';

  event.preventDefault()

  return false
  } // end of modelPage

function goBack (pageType)
  {
  if (window.history.length > 1)
    window.history.go(-1)
  else
    window.location.href = pageType
  return false;
  }

function clickOnSpawnPoint (event, location)
  {
  if (event.ctrlKey)
    location = '.port ' + location;
  copyToClipboard (location);
  } // end of clickOnSpawnPoint
