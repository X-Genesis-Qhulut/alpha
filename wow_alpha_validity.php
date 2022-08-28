<?php

/*
  Author: X'Genesis Qhulut <XGenesis-Qhulut@protonmail.com>
  Date:   August 2022

  See LICENSE for license details.
*/

// VALIDITY OF DATABASE

// See: https://mangoszero-docs.readthedocs.io/en/latest/database/world/creature-loot-template.html

function showUnknownFaction ()
  {
  global $creatures;

  $factiontemplate = FACTIONTEMPLATE;
  $creature_template = CREATURE_TEMPLATE;

  $results = dbQuery ("SELECT $creature_template.entry AS creature_key,
                              $creature_template.faction AS creature_faction
                      FROM $creature_template
                        LEFT JOIN $factiontemplate ON $factiontemplate.ID =  $creature_template.faction
                        WHERE $factiontemplate.FactionGroup IS NULL
                        AND $creature_template.faction <> 0
                        AND $creature_template.entry <= " . MAX_CREATURE .
                        " ORDER BY name LIMIT  " . QUERY_LIMIT);

  echo "<h2>Creatures with unknown faction</h2>\n";

  echo "<p>(Excludes faction: 0)\n";

  echo "<ul>\n";
  while ($row = dbFetch ($results))
    echo "<li>" . lookupThing ($creatures,  $row ['creature_key'], 'show_creature') .
          " - Faction: " . $row ['creature_faction'] . "\n";
  dbFree ($results);
  echo "</ul>\n";


  } // end of showUnknownFaction
?>
