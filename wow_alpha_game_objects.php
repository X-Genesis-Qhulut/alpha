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



 // ==========================================================================================================






  } // end of extraGameObjectInformation

function showOneGameObject ()
  {
  global $id, $quests;

  // we need the game object info in this function
  $row = dbQueryOneParam ("SELECT * FROM ".GAMEOBJECT_TEMPLATE." WHERE entry = ?", array ('i', &$id));


  $extras = array (
        'faction' => 'npc_faction',
        'type' => 'gameobject_type',
  );

  $name = fixHTML ($row ['name']);

  startOfPageCSS ('Game object', $name, 'game_objects');

  // ---------------- IMAGE OF GAME OBJECT -----------------

/*
  if ($row ["display_id$i"])
    {
    $display_id = $row ["display_id$i"];
    $icon = $display_id . '.webp';
    if (!file_exists ("$documentRoot$executionDir/creatures/$icon"))
      {
      comment ("$documentRoot$executionDir/creatures/$icon   NOT ON FILE");
      $icon = 'missing_creature.png';
      }

    echo "
      <!-- MODEL DISPLAY ID -->
      <img
        class='model-display'
        src='creatures/$icon'
        alt='Creature model for display ID $display_id'
      />
      <!-- END MODEL DISPLAY ID -->
      ";
    } // end of if non-zero display ID

*/

  $icon = 'missing_creature.png';
   echo "
      <!-- MODEL DISPLAY ID -->
      <img
        class='model-display'
        src='creatures/$icon'
        alt='Creature model'
      />
      <!-- END MODEL DISPLAY ID -->
      ";


  $limit = array (
    'entry',
    'display_id1',
    'level_min',
    'health_min',
    'mana_min',
    'armor',
    'faction',
    'npc_flags',

  );

  comment ('SHORT LISTING OF FIELDS');

  showOneThing (GAMEOBJECT_TEMPLATE, 'alpha_world.gameobject_template', 'entry',
              $id, "", "name", $extras, $limit);

  echo "</div>\n";  // end of details__informations__details1
  echo "<div class='object-container__informations__details2'>\n";


  $where = 'spawn_entry = ? AND ignored = 0';
  $param = array ('i', &$id);
  $count = 0;

  comment ('SPAWN POINTS - EASTERN KINGDOMS');

// show spawn points - Eastern Kingdoms'
  $results = dbQueryParam ("SELECT * FROM ".SPAWNS_GAMEOBJECTS."
            WHERE $where AND spawn_map = 0", $param);

  $count += listSpawnPoints ($results, 'Spawn points - Eastern Kingdoms', 'alpha_world.spawns_gameobjects',
                    'spawn_positionX', 'spawn_positionY', 'spawn_positionZ', 'spawn_map');

  comment ('SPAWN POINTS - KALIMDOR');

 // show spawn points - Kalimdor
  $results = dbQueryParam ("SELECT * FROM ".SPAWNS_GAMEOBJECTS."
            WHERE $where AND spawn_map = 1", $param);

  $count += listSpawnPoints ($results, 'Spawn points - Kalimdor', 'alpha_world.spawns_gameobjects',
                    'spawn_positionX', 'spawn_positionY', 'spawn_positionZ', 'spawn_map');

  comment ('SPAWN POINTS - OTHER');

  // show spawn points - everywhere else
  $results = dbQueryParam ("SELECT * FROM ".SPAWNS_GAMEOBJECTS."
            WHERE $where AND spawn_map > 1", $param);

  $count += listSpawnPoints ($results, 'Spawn points - Instances', 'alpha_world.spawns_gameobjects',
                    'spawn_positionX', 'spawn_positionY', 'spawn_positionZ', 'spawn_map');

  if (!$count)
    echo "
   <div class='element-information'>
   <h2  class='element-information__title'>Spawn points</h2>
   <div class='element-information__bar'></div>
    <div class='element-information__content'>
        <ul>
          <li>None</li>
        </ul>
      </div>
    </div>
    ";

  comment ('END SPAWN POINTS');

 // ---------------- QUESTS -----------------

  comment ('QUESTS GIVEN');

  // what quests they give
  $results = dbQueryParam ("SELECT * FROM ".GAMEOBJECT_QUESTRELATION." WHERE entry = ?", array ('i', &$id));

/*  I'm not so sure about this now ... The quest should exist, right?

  $results = dbQueryParam ("SELECT T1.* FROM ".GAMEOBJECT_QUESTRELATION." AS T1
                            INNER JOIN ".QUEST_TEMPLATE." AS T2 ON (T1.quest = T2.entry)
                            WHERE T1.entry = ? AND T2.ignored = 0", array ('i', &$id));
*/


  listItems ('Game object starts these quests', 'alpha_world.gameobject_questrelation', count ($results), $results,
    function ($row) use ($quests)
      {
      listThing ($quests, $row ['quest'], 'show_quest');
      } // end listing function
      , true  // goes up top, slightly different CSS
      );


  comment ('QUESTS FINISHED');

 // what quests they finish
  $results = dbQueryParam ("SELECT * FROM ".GAMEOBJECT_INVOLVEDRELATION." WHERE entry = ?", array ('i', &$id));

/*  I'm not so sure about this now ... The quest should exist, right?

  $results = dbQueryParam ("SELECT T1.* FROM ".GAMEOBJECT_INVOLVEDRELATION." AS T1
                            INNER JOIN ".QUEST_TEMPLATE." AS T2 ON (T1.quest = T2.entry)
                            WHERE T1.entry = ? AND T2.ignored = 0", array ('i', &$id));
*/


  listItems ('Game object finishes these quests', 'alpha_world.gameobject_involvedrelation', count ($results), $results,
    function ($row) use ($quests)
      {
      listThing ($quests, $row ['quest'], 'show_quest');
      } // end listing function
      , true  // goes up top, slightly different CSS
      );

  echo "</div>\n";  // end of object-container__informations__details1  (spawn points, quests, etc.)


  comment ('SPAWN POINTS ON MAP');

  echo "<aside class='creatures-details__map'>\n";

  $where = 'spawn_entry = ? AND ignored = 0';
  $param = array ('i', &$id);

  comment ('EASTERN KINGDOMS');

  // show spawn points - Eastern Kingdoms
  $results = dbQueryParam ("SELECT * FROM ".SPAWNS_GAMEOBJECTS."
        WHERE $where AND spawn_map = 0", $param) ;

  showSpawnPoints ($results, 'Spawn points - Eastern Kingdoms', 'alpha_world.spawns_creatures',
                'spawn_positionX', 'spawn_positionY', 'spawn_positionZ', 'spawn_map');

  comment ('KALIMDOR');

  // show spawn points - Kalimdor
  $results = dbQueryParam ("SELECT * FROM ".SPAWNS_GAMEOBJECTS."
        WHERE $where AND spawn_map = 1", $param);

  showSpawnPoints ($results, 'Spawn points - Kalimdor', 'alpha_world.spawns_creatures',
                'spawn_positionX', 'spawn_positionY', 'spawn_positionZ', 'spawn_map');


  comment ('END MAP SPAWN POINTS');

  echo "  </aside>\n";

  echo "</div>\n";  // end of object-container__informations (stuff at top)

  echo "<div class='details-container' style='display:flex;'>\n";

  comment ('SIMULATE GAME OBJECT');

  startElementInformation ('Info', GAMEOBJECT_TEMPLATE);

  // SIMULATE GAME OBJECT

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


   endElementInformation ();

  // ---------------- CHEST LOOT -----------------

  // show chest loot, which includes mining and herb nodes


  if ($row ['type'] == GAMEOBJECT_TYPE_CHEST)
    {
    startElementInformation ('Loot', GAMEOBJECT_LOOT_TEMPLATE);

    $lootResults = dbQueryParam ("SELECT * FROM ".GAMEOBJECT_LOOT_TEMPLATE." WHERE entry = ?", array ('i', &$row ['data1']));
    usort($lootResults, 'item_compare');
    listItems ('Gameobject loot', 'alpha_world.gameobject_loot_template', count ($lootResults), $lootResults,
      function ($row)
        {
        echo "<li>" . lookupItemHelper ($row ['item'], $row ['mincountOrRef']) . ' — ' .
             $row ['ChanceOrQuestChance'] . '%';
        } // end listing function
        );
   endElementInformation ();

    } // end of chest type




  endOfPageCSS ();


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

  $td  = function ($s) use (&$row) { tdx ($row  [$s]); };
  $tdr = function ($s) use (&$row) { tdx ($row  [$s], 'tdr'); };

  $results = setUpSearch ('Game Objects',
                          $sortFields,          // fields we can sort on
                          array ('Entry', 'Name', 'Faction'),    // headings
                          'entry',              // key
                          array ('name'),       // searchable fields
                          GAMEOBJECT_TEMPLATE,  // table
                          '');                  // extra conditions

  if (!$results)
    return;

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

  wrapUpSearch ();

  } // end of showGameObjects
  ?>
