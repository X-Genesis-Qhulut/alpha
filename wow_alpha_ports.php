<?php

/*
  Author: X'Genesis Qhulut <XGenesis-Qhulut@protonmail.com>
  Date:   August 2022

  See LICENSE for license details.
*/

// WORLD PORTS (for use with .tel command)

function findNearbyPorts (&$info)
{
  global $id;

  $row = $info ['row'];
  $x   = $row ['x'];
  $y   = $row ['y'];
  $z   = $row ['z'];
  $map = $row ['map'];
  $nearbyResults = dbQueryParam ("SELECT *,
              SQRT(POWER(? - x, 2) + POWER(? - y, 2) + POWER(? - z, 2))
               AS distance
            FROM ".WORLDPORTS."
            WHERE map = ? AND entry <> ?
            HAVING distance <= ".NEARBY_PORTS_DISTANCE."
            ORDER BY distance
            LIMIT " . QUERY_LIMIT * 2,  // more generous query limit for this
            array ('dddii', &$x, &$y, &$z, &$map, &$id));

  $info ['nearbyResults'] = $nearbyResults;

} // end of findNearbyPorts

function portTopLeft ($info)
  {
  global $id;
  $extras = $info ['extras'];

  comment ('PORT DETAILS');
  showOneThing (WORLDPORTS, 'entry', $id, "Teleport: .tel <name>", "name", $extras);  //  —>

  } // end of portTopLeft

function portTopRight ($info)
  {
  global $id;
  $row = $info ['row'];

  $nearbyResults = $info ['nearbyResults'];
  foreach ($nearbyResults as &$portRow)
    $portRow ['color'] = 'cyan';

  $nearbyResults [] = $row;

  echo "<div class='caroussel__maps'>\n";

  showSpawnPoints ($nearbyResults, 'Teleport location', WORLDPORTS,
                    'entry', 'x', 'y', 'z', 'map');

  endDiv ('caroussel__maps');

  } // end of portTopRight

function showNearbyPorts ($info)
{

  $nearbyResults = $info ['nearbyResults'];

  boxTitle ('Nearby teleports');

  echo "<table class='table-rows'>\n";
  echo "<thead>\n";
  echo "<tr>\n";
  th ('Name');
  th ('X');
  th ('Y');
  th ('Z');
  th ('Map');
  th ('Distance (yards)');
  echo "</tr>\n";
  echo "</thead>\n";
  echo "<tbody>\n";

  $td  = function ($s) use (&$portRow) { td ($portRow  [$s]); };

  $searchURI = makeSearchURI (true);
  $pos = 0;

  foreach ($nearbyResults as $portRow)
    {
    $pos++;
    $entry = $portRow ['entry'];

    $distance = $portRow ['distance'];
    $name = $portRow ['name'];
    echo "<tr>\n";
    tdh ("<a href='?action=show_port&id=$entry'>$name</a>");
    $td ('x');
    $td ('y');
    $td ('z');
    $td ('map');
    td (round ($distance, 0));
    echo "</tr>\n";
    } // end of foreach


  echo "</tbody>\n";
  echo "</table>\n";

} // end of showNearbyPorts

function portDetails ($info)
  {
  global $id;

  findNearbyPorts ($info);

  topSection    ($info, function ($info)
      {
      topMiddle ($info, 'portTopLeft');
      topRight ($info, 'portTopRight');
      });

  if ($info ['row'] ['map'] <= 1)
    {
    bottomSection ($info, function ($info)
        {
        showNearbyPorts ($info);
        });
    }

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
  pageContent ($info, 'Teleport', $name, 'ports', 'portDetails', WORLDPORTS);

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

  $results = setUpSearch ('Teleports', $sortFields, $headings);

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
