<?php

/*
  Author: X'Genesis Qhulut <XGenesis-Qhulut@protonmail.com>
  Date:   August 2022

  See LICENSE for license details.
*/

// GAME OBJECTS

// See: https://github.com/cmangos/issues/wiki/gameobject_template

function simulateGameObject ($info)
  {
  global $id;
  global $maps, $quests;

  $row = $info ['row'];

  comment ('SIMULATE GAME OBJECT');

  boxTitle ('Details');

  // SIMULATE GAME OBJECT

  echo "<div class='simulate_box gameobject'>\n";
  if ($row ['name'])
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

  endDiv ('simulate_box gameobject');

    } // end of simulateGameObject


function listGameObjectSpawnPoints ($id)
{

  $where = 'spawn_entry = ? AND ignored = 0';
  $param = array ('i', &$id);
  $count = 0;

  comment ('SPAWN POINTS - EASTERN KINGDOMS');

// show spawn points - Eastern Kingdoms'
  $results = dbQueryParam ("SELECT * FROM ".SPAWNS_GAMEOBJECTS."
            WHERE $where AND spawn_map = 0", $param);

  $count += listSpawnPoints ($results, 'Spawn points - Eastern Kingdoms', 'alpha_world.spawns_gameobjects',
                    'spawn_id', 'spawn_positionX', 'spawn_positionY', 'spawn_positionZ', 'spawn_map');

  comment ('SPAWN POINTS - KALIMDOR');

 // show spawn points - Kalimdor
  $results = dbQueryParam ("SELECT * FROM ".SPAWNS_GAMEOBJECTS."
            WHERE $where AND spawn_map = 1", $param);

  $count += listSpawnPoints ($results, 'Spawn points - Kalimdor', 'alpha_world.spawns_gameobjects',
                    'spawn_id', 'spawn_positionX', 'spawn_positionY', 'spawn_positionZ', 'spawn_map');

  comment ('SPAWN POINTS - OTHER');

  // show spawn points - everywhere else
  $results = dbQueryParam ("SELECT * FROM ".SPAWNS_GAMEOBJECTS."
            WHERE $where AND spawn_map > 1", $param);

  $count += listSpawnPoints ($results, 'Spawn points - Instances', 'alpha_world.spawns_gameobjects',
                    'spawn_id', 'spawn_positionX', 'spawn_positionY', 'spawn_positionZ', 'spawn_map');

  if (!$count)
    showNoSpawnPoints ();

  comment ('END SPAWN POINTS');
} // end of listGameObjectSpawnPoints


// ---------------- QUESTS -----------------

function listGameObjectQuests ($id)
{
  global $quests;

  comment ('QUESTS GIVEN');

  // what quests they give
  $results = dbQueryParam ("SELECT * FROM ".GAMEOBJECT_QUEST_STARTER." WHERE entry = ?", array ('i', &$id));

/*  I'm not so sure about this now ... The quest should exist, right?

  $results = dbQueryParam ("SELECT T1.* FROM ".GAMEOBJECT_QUEST_STARTER." AS T1
                            INNER JOIN ".QUEST_TEMPLATE." AS T2 ON (T1.quest = T2.entry)
                            WHERE T1.entry = ? AND T2.ignored = 0", array ('i', &$id));
*/


  listItems ('Game object starts these quests', GAMEOBJECT_QUEST_STARTER, count ($results), $results,
    function ($row) use ($quests)
      {
      listThing ($quests, $row ['quest'], 'show_quest');
      } // end listing function
      , true  // goes up top, slightly different CSS
      );

  comment ('QUESTS FINISHED');

 // what quests they finish
  $results = dbQueryParam ("SELECT * FROM ".GAMEOBJECT_QUEST_FINISHER." WHERE entry = ?", array ('i', &$id));

/*  I'm not so sure about this now ... The quest should exist, right?

  $results = dbQueryParam ("SELECT T1.* FROM ".GAMEOBJECT_QUEST_FINISHER." AS T1
                            INNER JOIN ".QUEST_TEMPLATE." AS T2 ON (T1.quest = T2.entry)
                            WHERE T1.entry = ? AND T2.ignored = 0", array ('i', &$id));
*/


  listItems ('Game object finishes these quests', GAMEOBJECT_QUEST_FINISHER, count ($results), $results,
    function ($row) use ($quests)
      {
      listThing ($quests, $row ['quest'], 'show_quest');
      } // end listing function
      , true  // goes up top, slightly different CSS
      );

} // end of listGameObjectQuests

function showGameObjectLoot ($info)
{
  global $items;

    $row = $info ['row'];

   // ---------------- CHEST LOOT -----------------

  // show chest loot, which includes mining and herb nodes


  if ($row ['type'] == GAMEOBJECT_TYPE_CHEST)
    {

    $lootResults = dbQueryParam (
          "SELECT * FROM ".GAMEOBJECT_LOOT_TEMPLATE." WHERE entry = ?
            ORDER BY ChanceOrQuestChance DESC", array ('i', &$row ['data1']));

    usort($lootResults, 'loot_item_compare');
    listItems ('Game object loot', GAMEOBJECT_LOOT_TEMPLATE, count ($lootResults), $lootResults,

    function ($row) use ($items)
      {
      $chance = $row ['ChanceOrQuestChance'];
      listThing ($items, $row ['item'], 'show_item', $chance . "%");
      } // end listing function
      );

    } // end of chest type
} // end of showGameObjectLoot

function showGameObjectModel ($row)
{
  global $documentRoot, $executionDir;

  // ---------------- IMAGE OF GAME OBJECT -----------------

  if ($row ["displayId"])
    {
    $display_id = $row ['displayId'];
    $model = $display_id . '.webp';
    if (!file_exists ("$documentRoot$executionDir/game_objects/$model"))
      {
      comment ("$documentRoot$executionDir/game_objects/$model   NOT ON FILE");
      $model = 'missing_game_object.webp';
      }

    echo "
      <!-- MODEL DISPLAY ID -->
      <img
        class='model-display'
        src='game_objects/$model'
        alt='Game object model for display ID $display_id'
      />
      <!-- END MODEL DISPLAY ID -->
      ";
    } // end of if non-zero display ID


} // end of showGameObjectModel

function gameobjectTopLeft ($info)
{
  global $id;

  $row = $info ['row'];
  $extras = $info ['extras'];
  $limit = $info ['limit'];

  showGameObjectModel ($row);

  comment ('SHORT LISTING OF FIELDS');
  showOneThing (GAMEOBJECT_TEMPLATE, 'entry',
              $id, "", "name", $extras, $limit);

} // end of gameobjectTopLeft

function gameobjectTopMiddle ($info)
{
  global $id;

  listGameObjectSpawnPoints ($id);
  listGameObjectQuests ($id);

} // end of gameobjectTopMiddle

function gameobjectTopRight ($info)
{
  global $id;

  comment ('SPAWN POINTS ON MAP');

  echo "<div class='caroussel__maps'>\n";

  $where = 'spawn_entry = ? AND ignored = 0';
  $param = array ('i', &$id);

 // doArrowsForMap (SPAWNS_GAMEOBJECTS, $where, $param, 'spawn_map');

  comment ('KALIMDOR');

  // show spawn points - Kalimdor
  $results = dbQueryParam ("SELECT * FROM ".SPAWNS_GAMEOBJECTS."
        WHERE $where AND spawn_map = 1", $param);

  showSpawnPoints ($results, 'Spawn points - Kalimdor', SPAWNS_GAMEOBJECTS,
                'spawn_id', 'spawn_positionX', 'spawn_positionY', 'spawn_positionZ', 'spawn_map');

  comment ('EASTERN KINGDOMS');

  // show spawn points - Eastern Kingdoms
  $results = dbQueryParam ("SELECT * FROM ".SPAWNS_GAMEOBJECTS."
        WHERE $where AND spawn_map = 0", $param) ;

  showSpawnPoints ($results, 'Spawn points - Eastern Kingdoms', SPAWNS_GAMEOBJECTS,
                'spawn_id', 'spawn_positionX', 'spawn_positionY', 'spawn_positionZ', 'spawn_map');


  comment ('END MAP SPAWN POINTS');

  endDiv ('caroussel__maps');

} // end of gameobjectTopRight

function gameobjectDetails ($info)
  {
  global $id;

  $row = $info ['row'];

  topSection    ($info, function ($info) use ($id)
      {
      topLeft   ($info, 'gameobjectTopLeft');
      topMiddle ($info, 'gameobjectTopMiddle');
      topRight  ($info , 'gameobjectTopRight');
      });

  middleSection ($info, function ($info) use ($id, $row)
      {
      middleDetails ($info, 'simulateGameObject');
      listGameObjectQuests ($info);
      showGameObjectLoot ($info);
      });

  bottomSection ($info, function ($info) use ($id)
      {
      $extras = $info ['extras'];
      showOneThing (GAMEOBJECT_TEMPLATE, 'entry', $id,
                  "Database entry for game object", "name", $extras);
      });

  } // end of gameobjectDetails

function showOneGameobject ()
  {
  global $id;

  if (($id === false && !repositionSearch()) || !checkID ())
    return;

 // we need the game object info in this function
  $row = dbQueryOneParam ("SELECT * FROM ".GAMEOBJECT_TEMPLATE." WHERE entry = ?", array ('i', &$id));

  if (!$row)
    {
    ShowWarning ("Game object $id is not on the database");
    return;
    } // end of not finding it

  // this is the short summary fields
  $limit = array (
    'entry',
    'type',
    'faction',
    'displayId',
    'size',
  );

  // stuff to be displayed differently
  $extras = array (
        'faction' => 'npc_faction',
        'type' => 'gameobject_type',
  );

  $name = $row ['name'];

  setTitle ("G/O $name");

  // we pass this stuff around to the helper functions
  $info = array ('row' => $row, 'extras' => $extras, 'limit' => $limit);

  // ready to go! show the page info and work our way down into the sub-functions
  pageContent ($info, 'Gameobject', $name, 'game_objects', 'gameobjectDetails', GAMEOBJECT_TEMPLATE);
  } // end of showOneGameobject


function showGameObjects ()
  {
  global $where, $params, $npc_factions, $sort_order, $matches;


  $sortFields = array (
    'entry',
    'name',
    'faction',
  );

  if (!in_array ($sort_order, $sortFields))
    $sort_order = 'name';

  setTitle ("Game objects listing");

  $td  = function ($s) use (&$row) { tdx ($row  [$s]); };

  $headings = array ('Entry', 'Name', 'Faction');

  $results = setUpSearch ('Game Objects', $sortFields, $headings);

  if (!$results)
    return;

  $searchURI = makeSearchURI (true);
  $pos = 0;

  foreach ($results as $row)
    {
    $pos++;
    echo "<tr>\n";
    $id = $row ['entry'];
    tdh ("<a href='?action=show_go&id=$id$searchURI&pos=$pos&max=$matches'>$id</a>");
    tdh ("<a href='?action=show_go&id=$id$searchURI&pos=$pos&max=$matches'>" . fixHTML ($row ['name']) . "</a>");
    td (expandSimple ($npc_factions, $row ["faction"]));;
    showFilterColumn ($row);
    echo "</tr>\n";
    }

  wrapUpSearch ();

  } // end of showGameObjects
  ?>
