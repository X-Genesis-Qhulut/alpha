<?php

/*
  Author: X'Genesis Qhulut <XGenesis-Qhulut@protonmail.com>
  Date:   August 2022

  See LICENSE for license details.
*/

// GAME OBJECTS

function showOneGameObject ($id)
  {
  global $maps, $quests;

  showOneThing (GAMEOBJECT_TEMPLATE, 'alpha_world.gameobject_template', 'entry', $id, "Game Object", "name",
    array (
        'faction' => 'faction',
        'type' => 'gameobject_type',

    ));

 // show spawn points
  $results = dbQueryParam ("SELECT * FROM ".SPAWNS_GAMEOBJECTS."
            WHERE spawn_entry = ? AND ignored = 0", array ('i', &$id));

  if (count ($results) > 0)
    {
    echo "<h2 title='Table: alpha_world.spawns_gameobjects'>Spawn points</h2>\n<ul>\n";
    foreach ($results as $spawnRow)
      {
      $x = $spawnRow ['spawn_positionX'];
      $y = $spawnRow ['spawn_positionY'];
      $z = $spawnRow ['spawn_positionZ'];
      $map = $spawnRow ['spawn_map'];
      echo "<li>$x $y $z $map (" . htmlspecialchars ($maps [$map]) . ")";
      } // for each spawn point
    } // if any spawn points
    echo "</ul>\n";


  // what quests they give
  $results = dbQueryParam ("SELECT * FROM ".GAMEOBJECT_QUESTRELATION." WHERE entry = ?", array ('i', &$id));
  if (count ($results) > 0)
    {
    echo "<h2 title='Table: alpha_world.gameobject_questrelation'>Game object starts these quests</h2><ul>\n";
    foreach ($results as $questRow)
      {
      listThing ('', $quests, $questRow ['quest'], 'show_quest');
      } // for each quest starter GO
    echo "</ul>\n";
    }

 // what quests they finish
  $results = dbQueryParam ("SELECT * FROM ".GAMEOBJECT_INVOLVEDRELATION." WHERE entry = ?", array ('i', &$id));
  if (count ($results) > 0)
    {
    echo "<h2 title='Table: alpha_world.gameobject_involvedrelation'>Game object finishes these quests</h2><ul>\n";
    foreach ($results as $questRow)
      {
      listThing ('', $quests, $questRow ['quest'], 'show_quest');
      } // for each quest starter GO
    echo "</ul>\n";
    }


  } // end of showOneGameObject


function showGameObjects ()
  {
  global $where, $params, $factions, $sort_order;


  $sortFields = array (
    'entry',
    'name',
    'faction',
  );

  if (!in_array ($sort_order, $sortFields))
    $sort_order = 'name';

  echo "<h2>Game Objects</h2>\n";

  $td  = function ($s) use (&$row) { tdx ($row  [$s]); };
  $tdr = function ($s) use (&$row) { tdx ($row  [$s], 'tdr'); };

  setUpSearch ('entry', array ('name'));

  $results = dbQueryParam ("SELECT * FROM ".GAMEOBJECT_TEMPLATE." $where ORDER BY $sort_order, entry LIMIT " . QUERY_LIMIT,
                    $params);

  if (!showSearchForm ($sortFields, $results))
    return;


  echo "<table class='search_results'>\n";
  headings (array ('Entry', 'Name', 'Faction'));
  foreach ($results as $row)
    {
    echo "<tr>\n";
    $id = $row ['entry'];
    tdhr ("<a href='?action=show_go&id=$id'>$id</a>");
    $tdr ('name');
    $tdr ('faction');
 //   $faction = $row ['faction'];
 //   tdxr ($faction ? "$faction: " .
 //                                 (isset ($factions [$faction]) ? $factions [$faction] : '(not found)' ): $faction);

    echo "</tr>\n";
    }
  echo "</table>\n";

  showCount ($results);

  } // end of showGameObjects
  ?>
