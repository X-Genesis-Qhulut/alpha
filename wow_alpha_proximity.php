<?php

/*
  Author: X'Genesis Qhulut <XGenesis-Qhulut@protonmail.com>
  Date:   August 2022

  See LICENSE for license details.
*/

// PROXIMITY


function showProximity ()
  {
  global $creatures;

  $PHP_SELF = $_SERVER['PHP_SELF'];

  $prox_location    = getP ('prox_location', 30);
  $prox_distance    = getP ('prox_distance', 5);

  // default to 100 yards
  if (!$prox_distance)
    $prox_distance = 100;

  echo "<h2>Proximity spawn point search</h2>\n";

  echo "<form METHOD=\"post\" ACTION=$PHP_SELF>\n";
  echo "<input Type=hidden Name=action Value=proximity>\n";
  echo "Location: ";

  echo " <input style='margin-right:1em;' placeholder='x y z map' type=text Name='prox_location' size=30 Value='" . fixHTML ($prox_location) . "'>\n";
  echo " Within distance: ";
  echo " <input type=text Name='prox_distance' size=5 placeholder='yards' Value='" . fixHTML ($prox_distance) . "'>\n";
  echo " yards.\n";
  echo "<input style='margin-left:1em;' Type=submit Name=SubmitFilter Value='Search' title='Click to search'>\n";
  echo "</form>\n";

  if (!$prox_location || !$prox_distance)
    return;   // no location? We are DONE HERE!

  $ok = preg_match ('`^\s*' .
                    '(?P<x>[+\-]?(\d*\.)?\d+)\s+' .
                    '(?P<y>[+\-]?(\d*\.)?\d+)\s+' .
                    '(?P<z>[+\-]?(\d*\.)?\d+)\s+' .
                    '(?P<map>\d+)\s*$`', $prox_location, $matches);

  if (!$ok)
    {
    ShowWarning ("Invalid location. Must be in form 'x y z map' (all numbers)");
    return;
    }

  if (!preg_match ('/^\d+$/', $prox_distance))
    {
    ShowWarning ("Invalid distance. Must be a number.");
    return;
    }


  // extract fields from supplied string
  $x    = $matches ['x'];
  $y    = $matches ['y'];
  $z    = $matches ['z'];
  $map  = $matches ['map'];

  $results = dbQueryParam ("SELECT *,
              SQRT(POWER(ABS (? - position_x), 2) + POWER(ABS (? - position_y), 2) + POWER(ABS(? - position_z), 2))
               AS distance
            FROM ".SPAWNS_CREATURES."
            WHERE map = ? AND ignored = 0
            HAVING distance <= ?
            ORDER BY distance",
            array ('dddii', &$x, &$y, &$z, &$map, &$prox_distance));


  if (count ($results) > 0)
    echo "<h3>NPCs within $prox_distance yards</h3>\n";
  else
    echo "<p>No NPCs within $prox_distance yards\n";

  echo "<ul>\n";
  foreach ($results as $row)
    {
    $distance = $row ['distance'] ;
    if (getCount ($row, 'spawn_entry', 4) > 1)
      {
      echo "<li>Distance: " . round ($distance, 1) . " yards:<ul>\n";
      for ($i = 1; $i <= 4; $i++)
        if ($row ["spawn_entry$i"])
          listThing ('', $creatures, $row ["spawn_entry$i"], 'show_creature');
      echo "</ul>\n";
      }
    else
      {
      // single item
      for ($i = 1; $i <= 4; $i++)
        if ($row ["spawn_entry$i"])
          listThing ('', $creatures, $row ["spawn_entry$i"], 'show_creature');
      echo ' - ' . round ($distance, 1) . " yards.\n";
      } // end of one NPC

    } // end of foreach result

  echo "</ul>\n";

  } // end of showProximity
?>
