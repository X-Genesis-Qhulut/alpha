<?php

/*
  Author: X'Genesis Qhulut <XGenesis-Qhulut@protonmail.com>
  Date:   August 2022

  See LICENSE for license details.
*/

// ZONES

function showOneZone ($id)
  {
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

  echo "<h2>Zones</h2>\n";

  $td  = function ($s) use (&$row) { tdx ($row  [$s]); };
  $tdr = function ($s) use (&$row) { tdx ($row  [$s], 'tdr'); };

  setUpSearch ('id', array ('directory'));

  $offset = getQueryOffset(); // based on the requested page number

  $results = dbQueryParam ("SELECT * FROM ".WORLDMAPAREA." $where ORDER BY $sort_order, ID LIMIT $offset, " . QUERY_LIMIT,
                    $params);

  if (!showSearchForm ($sortFields, $results, WORLDMAPAREA, $where))
    return;

  echo "<table class='search_results'>\n";
  headings (array ('ID', 'Name', 'Area ID'));
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
  echo "</table>\n";

  showCount ($results);

  } // end of showZones
?>
