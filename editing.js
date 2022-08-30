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
