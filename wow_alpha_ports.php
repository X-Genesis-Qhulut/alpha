<?php

/*
  Author: X'Genesis Qhulut <XGenesis-Qhulut@protonmail.com>
  Date:   August 2022

  See LICENSE for license details.
*/

// WORLD PORTS (for use with .tel command)

function portTopLeft ($info)
  {
  global $id;
      $extras = $info ['extras'];
      comment ('PORT DETAILS');
        showOneThing (WORLDPORTS, 'alpha_world.worldports', 'entry', $id, "World Port", "name", $extras);

  } // end of portTopLeft

function portTopRight ($info)
  {
  global $id;
  $row = $info ['row'];

  showSpawnPoints (array ($row), 'Teleport location', 'alpha_world.worldports',
                    'x', 'y', 'z', 'map');

  } // end of portTopRight

function portDetails ($info)
  {
  global $id;

  topSection    ($info, function ($info)
      {
      topMiddle ($info, 'portTopLeft');
      topRight ($info, 'portTopRight');
      });
  } // end of portDetails


function showOnePort ()
  {
  global $id;

// we need the item info in this function
  $row = dbQueryOneParam ("SELECT * FROM ".WORLDPORTS." WHERE entry = ?", array ('i', &$id));

  $name = $row ['name'];
  $extras = array ('map' => 'map');
  // we pass this stuff around to the helper functions
  $info = array ('row' => $row, 'extras' => $extras, 'limit' => array ());
  // ready to go! show the page info and work our way down into the sub-functions
  pageContent ($info, 'Port', $name, 'ports', 'portDetails', WORLDPORTS);

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

  echo "<ul><li>For use with <b>.tel</b> command. Only map 0 and 1 shown.</ul>\n";

  $searchURI = makeSearchURI (true);

  foreach ($results as $row)
    {
    echo "<tr>\n";
    $id = $row ['entry'];
    tdhr ("<a href='?action=show_port&id=$id$searchURI'>$id</a>");
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
