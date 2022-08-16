<?php

/*
  Author: X'Genesis Qhulut <XGenesis-Qhulut@protonmail.com>
  Date:   August 2022

  See LICENSE for license details.
*/

// ZONES

function showOneZone ($id)
  {
  showOneThing (WORLDMAPAREA, 'alpha_dbc.worldmaparea', 'ID', $id, "Zone", "ID",
      array (
        'MapID' => 'map',
      ));
  } // end of showOneZone

function showZones ()
  {
  global $where, $params;

  echo "<h2>Zones</h2>\n";

  $td  = function ($s) use (&$row) { tdx ($row  [$s]); };
  $tdr = function ($s) use (&$row) { tdx ($row  [$s], 'tdr'); };

  setUpSearch ('id', array ('directory'));

  $results = dbQueryParam ("SELECT * FROM ".WORLDMAPAREA." $where ORDER BY AreaName LIMIT " . QUERY_LIMIT,
                    $params);

  if (!showSearchForm ($results))
    return;

  echo "<table>\n";
  headings (array ('ID', 'Name', 'Area ID'));
  foreach ($results as $row)
    {
    echo "<tr>\n";
    $id = $row ['ID'];
    tdhr ("<a href='?action=show_zone&id=$id'>$id</a>");
    $td ('AreaName');
    $tdr ('AreaID');
    echo "</tr>\n";
    }
  echo "</table>\n";

  showCount ($results);

  } // end of showZones
?>
