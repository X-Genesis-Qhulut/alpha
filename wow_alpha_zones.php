<?php

/*
  Author: X'Genesis Qhulut <XGenesis-Qhulut@protonmail.com>
  Date:   August 2022

  See LICENSE for license details.
*/

// ZONES

function showOneZone ()
  {
  global $id;
  showOneThing (WORLDMAPAREA, 'alpha_dbc.worldmaparea', 'ID', $id, "Zone", "AreaName",
      array (
        'MapID' => 'map',
      ));
  } // end of showOneZone

function showZones ()
  {
  global $where, $params, $sort_order;

  $sortFields = array (
    'ID',
    'AreaName',
    'AreaID',
  );

  if (!in_array ($sort_order, $sortFields))
    $sort_order = 'AreaName';

  $td  = function ($s) use (&$row) { tdx ($row  [$s]); };
  $tdr = function ($s) use (&$row) { tdx ($row  [$s], 'tdr'); };

  $results = setUpSearch ('Zones',
                          $sortFields,            // fields we can sort on
                          array ('ID', 'Name', 'Area ID'),    // headings
                          'id',                // key
                          array ('directory'),  // searchable fields
                          WORLDMAPAREA,          // table
                          '');     // extra conditions

  if (!$results)
    return;


  foreach ($results as $row)
    {
    echo "<tr>\n";
    $id = $row ['ID'];
    tdhr ("<a href='?action=show_zone&id=$id'>$id</a>");
    $td ('AreaName');
    $tdr ('AreaID');
    showFilterColumn ($row);
    echo "</tr>\n";
    }
   wrapUpSearch ();

  } // end of showZones
?>
