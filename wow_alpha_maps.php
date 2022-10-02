<?php

/*
  Author: X'Genesis Qhulut <XGenesis-Qhulut@protonmail.com>
  Date:   August 2022

  See LICENSE for license details.
*/

// MAPS

// https://wowdev.wiki/DB/WorldMapArea


function mapshowMap ()
  {
  global $id;

  //echo "<div class='object-container page-content'>\n";

  comment ('MAP');

  echo "
    <!-- CAROUSSEL -->
    <aside
      class='caroussel caroussel--independant'
      id='spawn-map-caroussel'
    >
    ";

  echo "<div class='caroussel__maps'>\n";

  showMapHelp ();

  if ($id == 1)
    {

    comment ('KALIMDOR');

    $results = array ();

    showSpawnPoints ($results, 'Spawn points — Kalimdor', SPAWNS_CREATURES,
                  'spawn_id', 'position_x', 'position_y', 'position_z', 'map', 'movement_type', true);
    } // end if Kalimdor

  if ($id == 0)
    {
    comment ('EASTERN KINGDOMS');

    $results = array ();

    showSpawnPoints ($results, 'Spawn points — Eastern Kingdoms', SPAWNS_CREATURES,
                  'spawn_id', 'position_x', 'position_y', 'position_z', 'map', 'movement_type', true);

    } // end if Eastern Kingdoms

  comment ('END MAP');

  endDiv ('caroussel__maps');

  echo "</aside>\n";

 // endDiv ('object-container page-content');

  } // end of mapshowMap


function mapDetails ($info)
  {
  global $id;

  if ($id == 0 || $id == 1)
    {
    mapshowMap ();
    }

  bottomSection ($info, function ($info)
      {
      global $id;
      $extras = $info ['extras'];
      comment ('MAP DETAILS');
      showOneThing (MAP, 'ID', $id, "Map", "Directory",  $extras);
      });
  } // end of mapDetails


function showOneMap ()
  {
  global $id;

  if (($id === false && !repositionSearch()) || !checkID ())
    return;

  $row = dbQueryOneParam ("SELECT * FROM ".MAP." WHERE ID = ?", array ('i', &$id));

  if (!$row)
    {
    ShowWarning ("Map $id is not on the database");
    return;
    } // end of not finding it

  $name = $row ['Directory'];

  setTitle ("Map $name");

  $extras = array ('MapName_Mask' => 'mask');
  // we pass this stuff around to the helper functions
  $info = array ('row' => $row, 'extras' => $extras, 'limit' => array ());
  // ready to go! show the page info and work our way down into the sub-functions
  pageContent ($info, 'Map', $name, 'maps', 'mapDetails', MAP);
  } // end of showOneMap

function showMaps ()
  {
  global $where, $params, $sort_order, $matches;

  $sortFields = array (
    'ID',
    'Directory',
  );

  setTitle ("Maps listing");

  if (!in_array ($sort_order, $sortFields))
    $sort_order = 'Directory';

  $td  = function ($s) use (&$row) { tdx ($row  [$s]); };

  $headings = array ('ID', 'Name');

  $results = setUpSearch ('Maps', $sortFields, $headings);

  if (!$results)
    return;

  $searchURI = makeSearchURI (true);
  $pos = 0;

  foreach ($results as $row)
    {
    $pos++;
    echo "<tr>\n";
    $id = $row ['ID'];
    tdh ("<a href='?action=show_map&id=$id$searchURI&pos=$pos&max=$matches'>$id</a>");
    tdh ("<a href='?action=show_map&id=$id$searchURI&pos=$pos&max=$matches'>" . fixHTML ($row ['Directory']) . "</a>");
    showFilterColumn ($row);
    echo "</tr>\n";
    }

  wrapUpSearch ();


  } // end of showMaps
?>
