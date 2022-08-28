<?php

/*
  Author: X'Genesis Qhulut <XGenesis-Qhulut@protonmail.com>
  Date:   August 2022

  See LICENSE for license details.
*/

// GAME OBJECTS

// See: https://github.com/cmangos/issues/wiki/gameobject_template

function extraGameObjectInformation ($id, $row)
  {
  global $maps, $quests;


  echo "<p><div class='simulate_box gameobject'>\n";
  echo "<h3 style='color:yellow;'>" . fixHTML ($row ['name'] ) . "</h3>\n";
  echo expandSimple (GAMEOBJECT_TYPE, $row ['type'], false);
  if ($row ['mingold'])
    echo "<p>Money: " . convertGold ($row ['mingold']) . ' to ' . convertGold ($row ['maxgold']);

  if ($row ['type'] == GAMEOBJECT_TYPE_CHEST)
    {
    if ($row ['data2'])
      echo "<p>Restock time: " . convertTimeGeneral ($row ['data2'] * 1000);
    if ($row ['data3'])
      echo "<br>Consumable\n";
    if ($row ['data4'])
      {
      echo "<br>Loot attempts allowed: " . $row ['data4'];
      if ($row ['data5'] != $row ['data4'])
        echo ' to ' . $row ['data5']. "\n";
      }
    } // end of chest

  echo "</div>\n";    // end of simulation box

 // ==========================================================================================================

 // show spawn points - Eastern Kingdoms'
  $results = dbQueryParam ("SELECT * FROM ".SPAWNS_GAMEOBJECTS."
            WHERE spawn_entry = ? AND ignored = 0 AND spawn_map = 0", array ('i', &$id));

  if (count ($results) > 0)
    showSpawnPoints ($results, 'Spawn points - Eastern Kingdoms', 'alpha_world.spawns_gameobjects',
                    'spawn_positionX', 'spawn_positionY', 'spawn_positionZ', 'spawn_map');

 // show spawn points - Kalimdor
  $results = dbQueryParam ("SELECT * FROM ".SPAWNS_GAMEOBJECTS."
            WHERE spawn_entry = ? AND ignored = 0 AND spawn_map = 1", array ('i', &$id));

  if (count ($results) > 0)
    showSpawnPoints ($results, 'Spawn points - Kalimdor', 'alpha_world.spawns_gameobjects',
                    'spawn_positionX', 'spawn_positionY', 'spawn_positionZ', 'spawn_map');


  // show spawn points - everywhere else
  $results = dbQueryParam ("SELECT * FROM ".SPAWNS_GAMEOBJECTS."
            WHERE spawn_entry = ? AND ignored = 0 AND spawn_map > 1", array ('i', &$id));

  if (count ($results) > 0)
    showSpawnPoints ($results, 'Spawn points - Instances', 'alpha_world.spawns_gameobjects',
                    'spawn_positionX', 'spawn_positionY', 'spawn_positionZ', 'spawn_map');

  // what quests they give
  $results = dbQueryParam ("SELECT * FROM ".GAMEOBJECT_QUESTRELATION." WHERE entry = ?", array ('i', &$id));
  if (count ($results) > 0)
    {
    echo "<h2 title='Table: alpha_world.gameobject_questrelation'>Game object starts these quests</h2><ul>\n";
    foreach ($results as $questRow)
      {
      listThing ($quests, $questRow ['quest'], 'show_quest');
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
      listThing ($quests, $questRow ['quest'], 'show_quest');
      } // for each quest starter GO
    echo "</ul>\n";
    }

  // ---------------- CHEST LOOT -----------------

  // show chest loot, which includes mining and herb nodes


  if ($row ['type'] == GAMEOBJECT_TYPE_CHEST)
    {
    $lootResults = dbQueryParam ("SELECT * FROM ".GAMEOBJECT_LOOT_TEMPLATE." WHERE entry = ?", array ('i', &$row ['data1']));
    usort($lootResults, 'item_compare');
    listItems ('Gameobject loot', 'alpha_world.gameobject_loot_template', count ($lootResults), $lootResults,
      function ($row)
        {
        echo "<li>" . lookupItemHelper ($row ['item'], $row ['mincountOrRef']) . ' — ' .
             $row ['ChanceOrQuestChance'] . '%';
        } // end listing function
        );
    } // end of chest type


  } // end of extraGameObjectInformation

function showOneGameObject ($id)
  {

  showOneThing (GAMEOBJECT_TEMPLATE, 'alpha_world.gameobject_template', 'entry', $id, "Game Object", "name",
    array (
        'faction' => 'npc_faction',
        'type' => 'gameobject_type',

    ), 'extraGameObjectInformation');


  } // end of showOneGameObject


function showGameObjects ()
  {
  global $where, $params, $npc_factions, $sort_order;


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

  $offset = getQueryOffset(); // based on the requested page number

  $results = dbQueryParam ("SELECT * FROM ".GAMEOBJECT_TEMPLATE." $where ORDER BY $sort_order LIMIT $offset , " . QUERY_LIMIT,
                    $params);

  if (!showSearchForm ($sortFields, $results, GAMEOBJECT_TEMPLATE, $where))
    return;


  echo "<table class='search_results'>\n";
  headings (array ('Entry', 'Name', 'Faction'));
  foreach ($results as $row)
    {
    echo "<tr>\n";
    $id = $row ['entry'];
    tdhr ("<a href='?action=show_go&id=$id'>$id</a>");
    $tdr ('name');
    tdxr (expandSimple ($npc_factions, $row ["faction"]));;
    showFilterColumn ($row);

    echo "</tr>\n";
    }
  echo "</table>\n";

  showCount ($results);

  } // end of showGameObjects
  ?>
