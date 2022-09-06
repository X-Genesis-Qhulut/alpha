<?php

/*
  Author: X'Genesis Qhulut <XGenesis-Qhulut@protonmail.com>
  Date:   August 2022

  See LICENSE for license details.
*/

// MAPS

// https://wowdev.wiki/DB/WorldMapArea



function mapDetails ($info)
  {
  bottomSection ($info, function ($info)
      {
      global $id;
      $extras = $info ['extras'];
      comment ('MAP DETAILS');
      showOneThing (MAP, 'alpha_dbc.map', 'ID', $id, "Map", "Directory",  $extras);
      });
  } // end of mapDetails


function showOneMap ()
  {
  global $id;

  $row = dbQueryOneParam ("SELECT * FROM ".MAP." WHERE ID = ?", array ('i', &$id));
  $name = $row ['Directory'];
  $extras = array ('MapName_Mask' => 'mask');
  // we pass this stuff around to the helper functions
  $info = array ('row' => $row, 'extras' => $extras, 'limit' => array ());
  // ready to go! show the page info and work our way down into the sub-functions
  pageContent ($info, 'Map', $name, 'maps', 'mapDetails', MAP);
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
