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

  if (($id === false && !repositionSearch()) || !checkID ())
    return;

// we need the item info in this function
  $row = dbQueryOneParam ("SELECT * FROM ".WORLDPORTS." WHERE entry = ?", array ('i', &$id));

  if (!$row)
    {
    ShowWarning ("Port $id is not on the database");
    return;
    } // end of not finding it

  $name = $row ['name'];
  setTitle ("Port $name");

  $extras = array ('map' => 'map');
  // we pass this stuff around to the helper functions
  $info = array ('row' => $row, 'extras' => $extras, 'limit' => array ());
  // ready to go! show the page info and work our way down into the sub-functions
  pageContent ($info, 'Port', $name, 'ports', 'portDetails', WORLDPORTS);

  } // end of showOnePort

function showPorts ()
  {
  global $where, $params, $maps, $sort_order, $matches;

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

  setTitle ("Ports listing");


  $td  = function ($s) use (&$row) { tdx ($row  [$s]); };
  $headings = array ('Entry', 'Name', 'x', 'y', 'z', 'Map');

  $results = setUpSearch ('World Ports', $sortFields, $headings);

  if (!$results)
    return;

  echo "<ul><li>For use with <b>.tel</b> command. Only map 0 and 1 shown.</ul>\n";

  $searchURI = makeSearchURI (true);
  $pos = 0;

  foreach ($results as $row)
    {
    $pos++;
    echo "<tr>\n";
    $id = $row ['entry'];
    tdhr ("<a href='?action=show_port&id=$id$searchURI&pos=$pos&max=$matches'>$id</a>");
    tdhr ("<a href='?action=show_port&id=$id$searchURI&pos=$pos&max=$matches'>" . fixHTML ($row ['name']) . "</a>");
    $td ('x');
    $td ('y');
    $td ('z');
    $map = $row ['map'];
    tdh (expandSimple ($maps, $map));
    showFilterColumn ($row);
    echo "</tr>\n";
    }

  wrapUpSearch ();


  } // end of showPorts
?>
