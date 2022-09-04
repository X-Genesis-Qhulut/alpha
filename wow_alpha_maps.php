<?php

/*
  Author: X'Genesis Qhulut <XGenesis-Qhulut@protonmail.com>
  Date:   August 2022

  See LICENSE for license details.
*/

// MAPS

// https://wowdev.wiki/DB/WorldMapArea


function showOneMap ()
  {
  global $id;

// we need the item info in this function
  $row = dbQueryOneParam ("SELECT * FROM ".MAP." WHERE ID = ?", array ('i', &$id));

  $name = $row ['Directory'];

  startOfPageCSS ('Map', $name, 'maps');
  echo "<div class='object-container__items'>\n";

  showOneThing (MAP, 'alpha_dbc.map', 'ID', $id, "Map", "Directory",  array (
                'MapName_Mask' => 'mask',
                ));

  echo "</div>\n";  // end of object-container__items
  endOfPageCSS ();

  } // end of showOneMap

function showMaps ()
  {
  global $where, $params, $sort_order;


  $sortFields = array (
    'ID',
    'Directory',
  );

  if (!in_array ($sort_order, $sortFields))
    $sort_order = 'Directory';

  $td  = function ($s) use (&$row) { tdx ($row  [$s]); };
  $tdr = function ($s) use (&$row) { tdx ($row  [$s], 'tdr'); };

  $results = setUpSearch ('Maps',
                          $sortFields,            // fields we can sort on
                          array ('ID', 'Name'),    // headings
                          'id',                // key
                          array ('directory'),  // searchable fields
                          MAP,          // table
                          '');     // extra conditions

  if (!$results)
    return;

  $searchURI = makeSearchURI (true);

  foreach ($results as $row)
    {
    echo "<tr>\n";
    $id = $row ['ID'];
    tdhr ("<a href='?action=show_map&id=$id$searchURI'>$id</a>");
    $td ('Directory');
    showFilterColumn ($row);
    echo "</tr>\n";
    }

  wrapUpSearch ();


  } // end of showMaps
?>
