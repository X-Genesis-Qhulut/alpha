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

// we need the item info in this function
  $row = dbQueryOneParam ("SELECT * FROM ".WORLDPORTS." WHERE entry = ?", array ('i', &$id));

  $name = $row ['name'];

  startOfPageCSS ('World ports', $name, 'ports');
  echo "<div class='object-container__items'>\n";

  showOneThing (WORLDPORTS, 'alpha_world.worldports', 'entry', $id, "World Port", "name",
      array (
        'map' => 'map',
      ));

  echo "</div>\n";  // end of object-container__items
  endOfPageCSS ();


  } // end of showOnePort

function showPorts ()
  {
  global $where, $params, $maps, $sort_order;

  $sortFields = array (
    'entry',
    'name',
    'x',
    'y',
    'z',
    'map',
  );

  if (!in_array ($sort_order, $sortFields))
    $sort_order = 'name';

//  echo "<p>For use with <b>.tel</b> command. Only map 0 and 1 shown.\n";

  $td  = function ($s) use (&$row) { tdx ($row  [$s]); };
  $tdr = function ($s) use (&$row) { tdx ($row  [$s], 'tdr'); };

  $results = setUpSearch ('World Ports',
                          $sortFields,            // fields we can sort on
                          array ('Entry', 'Name', 'x', 'y', 'z', 'Map'),    // headings
                          'entry',                // key
                          array ('name'),  // searchable fields
                          WORLDPORTS,          // table
                          '');     // extra conditions

  if (!$results)
    return;

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

  wrapUpSearch ();


  } // end of showPorts
?>
