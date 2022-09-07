<?php

/*
  Author: X'Genesis Qhulut <XGenesis-Qhulut@protonmail.com>
  Date:   August 2022

  See LICENSE for license details.
*/

// PROXIMITY


function showProximityDetails ($info)
  {
  global $creatures;

  $prox_location    = $info ['prox_location'];
  $prox_distance    = $info ['prox_distance'];

  $ok = preg_match ('`^\s*' .
                    '(?P<x>[+\-]?(\d*\.)?\d+)\s+' .
                    '(?P<y>[+\-]?(\d*\.)?\d+)\s+' .
                    '(?P<z>[+\-]?(\d*\.)?\d+)\s+' .
                    '(?P<map>\d+)\s*$`', $prox_location, $matches);

  if (!$ok)
    {
    boxTitle ("Invalid location. Must be in form 'x y z map' (all numbers)");
    return;
    }

  if (!preg_match ('/^\d+$/', $prox_distance))
    {
    boxTitle ("Invalid distance. Must be a number.");
    return;
    }


  // extract fields from supplied string
  $x    = $matches ['x'];
  $y    = $matches ['y'];
  $z    = $matches ['z'];
  $map  = $matches ['map'];

  $results = dbQueryParam ("SELECT *,
              SQRT(POWER(? - position_x, 2) + POWER(? - position_y, 2) + POWER(? - position_z, 2))
               AS distance
            FROM ".SPAWNS_CREATURES."
            WHERE map = ? AND ignored = 0
            HAVING distance <= ?
            ORDER BY distance
            LIMIT " . QUERY_LIMIT * 2,  // more generous query limit for this
            array ('dddii', &$x, &$y, &$z, &$map, &$prox_distance));


  $count = count ($results);

  if ($count >= QUERY_LIMIT * 2)
    $notice = ' (Query limited to ' . QUERY_LIMIT * 2 . ' results)';
  else
    $notice = '';

  if ($count > 0)
    boxTitle ("NPCs within $prox_distance yards ($count)$notice");
  else
    {
    boxTitle ("No NPCs within $prox_distance yards");
    return;
    }

  echo "
  <table class='table-rows'>
      <thead>
        <tr>
        <th>Entry</th>
        <th>Name</th>
        <th>Distance (yards)</th>
        <th>Alternate</th>
        </tr>
      </thead>
      <tbody>
      ";

  foreach ($results as $row)
    {
    $distance = $row ['distance'] ;

    for ($i = 1; $i <= 4; $i++)
      if ($row ["spawn_entry$i"])
        {
        echo "<tr>\n";
        $id = $row ["spawn_entry$i"];
        tdh ("<a href='?action=show_creature&id=$id'>$id</a>");
        tdh ("<a href='?action=show_creature&id=$id'>" . fixHTML ($creatures [$id]) . "</a>");
        td (round ($distance, 1));
        if ($row ["spawn_entry2"])
          td ('Yes');
        else
          td ('');

        echo "</tr>\n";
        }


    } // end of foreach result

  echo "</tbody>
  </table>";


//  if (count ($results) >= QUERY_LIMIT * 2)
//    echo "<p>Query limited to " . (QUERY_LIMIT * 2) . " matches. There may be more.";

  } // end of showProximityDetails


define ('PROX_LOCATION_SIZE', 50);
define ('PROX_DISTANCE_SIZE', 50);
define ('PROX_DEFAULT_DISTANCE', 100);

function showProximity ()
{
  $prox_location    = getP ('prox_location', PROX_LOCATION_SIZE);
  $prox_distance    = getP ('prox_distance', PROX_DISTANCE_SIZE);

  // default to 100 yards
  if (!$prox_distance)
    $prox_distance = PROX_DEFAULT_DISTANCE;

  $PHP_SELF = $_SERVER['PHP_SELF'];

  setTitle ("Proximity search");

  searchContainerStart ('', 'Proximity search');
  echo "
   <form method='post' action='$PHP_SELF'>
   <input Type=hidden Name=action Value=proximity>
        <div class='search-bar'>
        <div class='search-bar__main'>
            <input
            class='custom-input'
            id='proximity-coord'
            type='text'
            size='" . PROX_LOCATION_SIZE . "'
            value='" . fixHTML ($prox_location) . "'
            placeholder='x y z map'
            autofocus='autofocus'
            title='Enter a number, text, or a regular expression'
            name='prox_location'
            />
        </div>
        <div class='search-bar__filters'>
            <label for='proximity-distance'>Within distance (yards)</label>
            <input
            class='custom-input'
            id='proximity-distance'
            type='text'
            name='prox_distance'
            size='" . PROX_DISTANCE_SIZE. "'
            value='" . fixHTML ($prox_distance)  . "'
            placeholder='" . PROX_DEFAULT_DISTANCE . "'
            />
        </div>
        </div>
        <button class='search-button' type='submit' name='SubmitFilter' title='Click to search'>
        <i class='fas fa-search'></i>
        </button>
    </form>
    ";

  searchContainerEnd ();

  $info = array ('prox_location' => $prox_location, 'prox_distance' => $prox_distance);

  echo "
    <!-- PAGE CONTENT -->
    <div class='creature-details page-content'>
      <!-- TABLE CONTAINER -->
      <div class='table-container table-container--full'>
    ";

  if ($prox_location && $prox_distance)
    {
    showProximityDetails ($info);
    }

  endDiv ('table-container table-container--full');
  endDiv ('creature-details page-content');
  echo "</section>\n";

} // end of showProximity

?>
