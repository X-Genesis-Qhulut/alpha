<?php

/*
  Author: X'Genesis Qhulut <XGenesis-Qhulut@protonmail.com>
  Date:   August 2022

  See LICENSE for license details.
*/

// WORLD PORTS (for use with .tel command)

function extraPortInformation ($id, $row)
  {
    showSpawnPoints (array ($row), 'Teleport location', 'alpha_world.worldports',
                    'x', 'y', 'z', 'map');

  } // end of extraPortInformation

function showOnePort ()
  {
  global $id;

  showOneThing (WORLDPORTS, 'alpha_world.worldports', 'entry', $id, "World Port", "name",
      array (
        'map' => 'map',
      ),
      'extraPortInformation');
  } // end of showOnePort

function showPorts ()
  {
  global $where, $params, $maps, $sort_order;

  $sortFields = array (
    'entry',
    'Name_enUS',
    'x',
    'y',
    'z',
    'map',
  );

  if (!in_array ($sort_order, $sortFields))
    $sort_order = 'name';

//  echo "<h2>World Ports</h2>\n";

//  echo "<p>For use with <b>.tel</b> command. Only map 0 and 1 shown.\n";

  $td  = function ($s) use (&$row) { tdx ($row  [$s]); };
  $tdr = function ($s) use (&$row) { tdx ($row  [$s], 'tdr'); };

  setUpSearch ('World Ports', 'id', array ('name'));

  $offset = getQueryOffset(); // based on the requested page number

  $results = dbQueryParam ("SELECT * FROM ".WORLDPORTS." $where AND (map = 0 or map = 1) ORDER BY $sort_order
                          LIMIT $offset, " . QUERY_LIMIT,
                    $params);

  if (!showSearchForm ($sortFields, $results, WORLDPORTS, $where))
    return;

  echo "<table class='search_results'>\n";
  headings (array ('Entry', 'Name', 'x', 'y', 'z', 'Map'));
  foreach ($results as $row)
    {
    echo "<tr>\n";
    $id = $row ['entry'];
    tdhr ("<a href='?action=show_port&id=$id'>$id</a>");
    $td ('name');
    $tdr ('x');
    $tdr ('y');
    $tdr ('z');
    $map = $row ['map'];
    tdh (expandSimple ($maps, $map));
    showFilterColumn ($row);
    echo "</tr>\n";
    }
  echo "</table>\n";

  showCount ($results);

  } // end of showPorts
?>
