/*

  Author: X'Genesis Qhulut <XGenesis-Qhulut@protonmail.com>
  Date:   August 2022

  See LICENSE for license details.


*/


function onClick(event, id)
{
  // Ctrl+Click to show an editing box
  if (!event.ctrlKey)
    return

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
  // scroll it into view
  editingDiv.scrollIntoView()
}
