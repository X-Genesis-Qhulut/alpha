<?php

/*
  Author: X'Genesis Qhulut <XGenesis-Qhulut@protonmail.com>
  Date:   August 2022

  See LICENSE for license details.
*/

// ZONES

function zoneDetails ($info)
  {
  bottomSection ($info, function ($info)
      {
      global $id;
      $extras = $info ['extras'];
      comment ('ZONE DETAILS');
      showOneThing (WORLDMAPAREA, 'alpha_dbc.worldzonearea', 'ID', $id, "Zone", "AreaName", $extras);
      });
  } // end of zoneDetails


function showOneZone ()
  {
  global $id;

  if (($id === false && !repositionSearch()) || !checkID ())
    return;

// we need the item info in this function
  $row = dbQueryOneParam ("SELECT * FROM ".WORLDMAPAREA." WHERE ID = ?", array ('i', &$id));

  if (!$row)
    {
    ShowWarning ("Zone $id is not on the database");
    return;
    } // end of not finding it

  $name = $row ['AreaName'];
  setTitle ("Zone $name");

 $extras = array ('MapID' => 'map');
  // we pass this stuff around to the helper functions
  $info = array ('row' => $row, 'extras' => $extras, 'limit' => array ());
  // ready to go! show the page info and work our way down into the sub-functions
  pageContent ($info, 'Map', $name, 'zones', 'zoneDetails', WORLDMAPAREA);
  } // end of showOneZone

function showZones ()
  {
  global $where, $params, $sort_order, $matches;

  $sortFields = array (
    'ID',
    'AreaName',
    'AreaID',
  );

  if (!in_array ($sort_order, $sortFields))
    $sort_order = 'AreaName';

  setTitle ("Zones listing");

  $td  = function ($s) use (&$row) { tdx ($row  [$s]); };

  $headings = array ('ID', 'Name', 'Area ID');

  $results = setUpSearch ('Zones', $sortFields, $headings);

  if (!$results)
    return;

  $searchURI = makeSearchURI (true);
  $pos = 0;

  foreach ($results as $row)
    {
    $pos++;
    echo "<tr>\n";
    $id = $row ['ID'];
    tdh ("<a href='?action=show_zone&id=$id$searchURI&pos=$pos&max=$matches'>$id</a>");
    tdh ("<a href='?action=show_zone&id=$id$searchURI&pos=$pos&max=$matches'>" . fixHTML ($row ['AreaName']) . "</a>");
    $td ('AreaID');
    showFilterColumn ($row);
    echo "</tr>\n";
    }
   wrapUpSearch ();

  } // end of showZones
?>
