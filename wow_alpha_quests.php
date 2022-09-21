<?php

/*
  Author: X'Genesis Qhulut <XGenesis-Qhulut@protonmail.com>
  Date:   August 2022

  See LICENSE for license details.
*/

// QUESTS

// See: https://mangoszero-docs.readthedocs.io/en/latest/database/world/quest-template.html

function simulateQuest ($row)
  {
  global $id, $game_objects, $creatures, $zones, $quests, $spells, $items;

 // simulate quest

  echo "<div class='simulate_box quest'>\n";
  echo "<h2>" . fixHTML ($row ['Title']) . "</h2>\n";
  echo "<p>" . fixQuestText ($row ['Details'] ). "</h2>\n";

  comment ('OBJECTIVES');

  echo "<h3>Objectives</h3>\n";
  echo "<p>" . fixQuestText ($row ['Objectives']) . "</h2>\n";

  comment ('REQUIREMENTS');

  echo "<h3>Requirements</h3>\n";

  for ($n = 1; $n <= QUEST_REQUIRED_ITEMS; $n++)
    if ($row ["ReqItemId$n"])
      echo (lookupItemHelper ($row ["ReqItemId$n"], $row ["ReqItemCount$n"]) . "<br>");

  comment ('REQUIRED CREATURES OR GAME OBJECTS');

  // creatures or game objects

  for ($n = 1; $n <= QUEST_REQUIRED_CREATURES; $n++)
    {
    $value = $row ["ReqCreatureOrGOId$n"];
    if ($value)
      {
      if ($value < 0)
        echo (lookupThing ($game_objects, -$value, 'show_go'));
      else
        echo (lookupThing ($creatures, $value, 'show_creature'));
      echo (showItemCount ($row ["ReqCreatureOrGOCount$n"]));
      echo "<br>";
      }
    } // end of for each creature or game object objective

  echo "<p>\n";

  comment ('REQUIRED SPELLS');

  // spells

  for ($n = 1; $n <= QUEST_REQUIRED_SPELLS; $n++)
    if ($row ["ReqSpellCast$n"])
      echo ('Cast: ' . lookupThing ($spells, $row ["ReqSpellCast$n"], 'show_spell'). "<br>");

  comment ('PROGRESS');

  echo "<h3>Progress</h3>\n";
  echo "<p>" . fixQuestText ($row ['RequestItemsText']) . "\n";

  comment ('COMPLETION');

  echo "<hr><h3>Completion</h3>\n";
  echo "<p>" . fixQuestText ($row ['OfferRewardText']) . "\n";

  comment ('REWARDS');

  echo "<h3>Reward</h3>\n";

  // non-choice item rewards

  for ($n = 1; $n <= QUEST_REWARD_ITEMS; $n++)
    if ($row ["RewItemId$n"])
      echo (lookupItemHelper ($row ["RewItemId$n"], $row ["RewItemCount$n"]) . "<br>");

  // choose an item from these six

  $count = getCount ($row, 'RewChoiceItemId', QUEST_REWARD_ITEM_CHOICES);

  if ($count > 1)
    echo "<h4>Choice of:</h4><ul>\n";

  for ($n = 1; $n <= QUEST_REWARD_ITEM_CHOICES; $n++)
    if ($row ["RewChoiceItemId$n"])
      echo ('<li>' . lookupItemHelper ($row ["RewChoiceItemId$n"], $row ["RewChoiceItemCount$n"]) . "<br>");

  if ($count > 1)
    echo "</ul>\n";

  if ($row ['RewXP'])
    echo "<p>" . $row ['RewXP'] . " XP<br>";

  if ($row ['RewOrReqMoney'])
    echo "<p>You will receive: " . convertGold ($row ['RewOrReqMoney']) . " <br>";

  if ($row ['RewSpellCast'])
    echo "<p>" . lookupThing ($spells, $row ["RewSpellCast"], 'show_spell') . " will be cast on you<br>";

  if ($row ['RewSpell'])
    echo "<p>" . lookupThing ($spells, $row ["RewSpell"], 'show_spell') . " will be cast on you<br>";

  comment ('REPUTATION');

  // reputation

  $count = getCount ($row, 'RewRepValue', QUEST_REWARD_REPUTATION);

  if ($count > 0)
    {
    echo "<h4>Reputation adjustment</h4><ul>\n";
    for ($n = 1; $n <= QUEST_REWARD_REPUTATION; $n++)
      if ($row ["RewRepValue$n"])
        echo ('<li>' . addSign ($row ["RewRepValue$n"]) . ' with ' . getFaction ($row ["RewRepFaction$n"], false));
    echo "</ul>\n";
    } // end of any reputation adjustment

  comment ('OTHER INFO');

  echo "<hr>\n";

  $quest_type = $row ['Type'];
  if ($quest_type > 0)  // quest type
    {
    echo "<br><b>Type</b>: " . expandSimple (QUEST_TYPE, $quest_type, false);
    }

  $zone = $row ['ZoneOrSort'];
  if ($zone > 0)  // quest zone
    {
    echo "<br><b>Zone</b>: " . expandSimple ($zones, $zone, false);
    }
  echo "<br><b>Minimum level</b>: " . $row ['MinLevel'];
  echo "<br><b>Quest level</b>: " . $row ['QuestLevel'];
  if ($row ['LimitTime'])
    echo "<br><b>Time limit</b>: " . convertTimeGeneral ($row ['LimitTime'] * 1000);

  $PrevQuestId = $row ['PrevQuestId'];
  if ($PrevQuestId > 0)
    echo "<br><b>Requires completion of</b>: " . lookupThing ($quests, $row ['PrevQuestId'], 'show_quest');
  if ($PrevQuestId < 0)
    echo "<br><b>This quest must be active</b>: " . lookupThing ($quests, abs ($row ['PrevQuestId']), 'show_quest');

  if ($row ['NextQuestId'])
   echo "<br><b>Next quest</b>: " . lookupThing ($quests, abs ($row ['NextQuestId']), 'show_quest');
  if ($row ['NextQuestInChain'])
   echo "<br><b>Next quest in chain</b>: " . lookupThing ($quests, $row ['NextQuestInChain'], 'show_quest');


  endDiv ('simulate_box quest');

// ===============================================================================================================


  } // end of simulateQuest

function showQuestGivers ()
{
  global $id, $creatures, $items, $game_objects;

 // who gives this quest

  $results = dbQueryParam ("SELECT * FROM ".CREATURE_QUEST_STARTER." WHERE quest = ? AND entry <= " . MAX_CREATURE, array ('i', &$id));
  listItems ('NPCs that start this quest', CREATURE_QUEST_STARTER, count ($results) , $results,
    function ($row) use ($creatures)
      {
      listThing ($creatures, $row ['entry'], 'show_creature');
      });

  $results = dbQueryParam ("SELECT * FROM ".ITEM_TEMPLATE." WHERE start_quest = ? AND ignored = 0", array ('i', &$id));
  listItems ('Items that start this quest', ITEM_TEMPLATE, count ($results) , $results,
    function ($row) use ($items)
      {
      listThing ($items, $row ['entry'], 'show_item');
      });

  $results = dbQueryParam ("SELECT * FROM ".GAMEOBJECT_QUEST_STARTER." WHERE quest = ?", array ('i', &$id));

  listItems ('Game objects that start this quest', GAMEOBJECT_QUEST_STARTER, count ($results) , $results,
    function ($row) use ($game_objects)
      {
      listThing ($game_objects, $row ['entry'], 'show_go');
      });

} // end of showQuestGivers

function showQuestFinishers ()
{
  global $id, $creatures, $items, $game_objects;

  // who finishes this quest
  $results = dbQueryParam ("SELECT * FROM ".CREATURE_QUEST_FINISHER." WHERE quest = ? AND entry <= " . MAX_CREATURE, array ('i', &$id));

  listItems ('NPCs that finish this quest', CREATURE_QUEST_FINISHER, count ($results) , $results,
    function ($row) use ($creatures)
      {
      listThing ($creatures, $row ['entry'], 'show_creature');
      });

  $results = dbQueryParam ("SELECT * FROM ".GAMEOBJECT_QUEST_FINISHER." WHERE quest = ?", array ('i', &$id));

  listItems ('Game objects that finish this quest', GAMEOBJECT_QUEST_FINISHER, count ($results) , $results,
    function ($row) use ($game_objects)
      {
      listThing ($game_objects, $row ['entry'], 'show_go');
      });


} // end of showQuestFinishers


function showQuestAreaTriggers ()
{
  global $id, $area_triggers;

  // who finishes this quest
  $results = dbQueryParam ("SELECT * FROM ".AREATRIGGER_QUEST_RELATION." WHERE quest = ?", array ('i', &$id));

  listItems ('Area triggers used by this quest', AREATRIGGER_QUEST_RELATION, count ($results) , $results,
    function ($row)  use ($area_triggers)
      {
      listThing ($area_triggers, $row ['id'], 'show_area_trigger');
      });

} // end of showQuestAreaTriggers

function showQuestChain ()
{
  global $id, $quests;

 // we need the item info in this function
  $row = dbQueryOneParam ("SELECT * FROM ".QUEST_TEMPLATE." WHERE entry = ?", array ('i', &$id));

  // find previous quests in the chain

  $foundQuests = array ($id);    // stop looping

  while ($row ['PrevQuestId'])
    {
    $PrevQuestId = abs ($row ['PrevQuestId']);

    if (in_array ($PrevQuestId, $foundQuests))
      break;  // avoid going into a loop
    $foundQuests [] = $PrevQuestId; // add this one to the chain
    // get the previous one
    $row = dbQueryOneParam ("SELECT entry, PrevQuestId FROM ".QUEST_TEMPLATE." WHERE entry = ? AND ignored = 0",
                            array ('i', &$PrevQuestId));
    if (!$row)  // not on file?
      break;
    } // while we still have previous quest IDs

  $foundQuests = array_reverse ($foundQuests);  // get into ascending order

  // now get the next quests in the chain

  // get this quest back
  $row = dbQueryOneParam ("SELECT entry, NextQuestId, NextQuestInChain FROM ".QUEST_TEMPLATE." WHERE entry = ?",
                          array ('i', &$id));

  while ($row ['NextQuestInChain'] || $row ['NextQuestId'] )
    {
    $NextQuest = $row ['NextQuestInChain'];
    if (!$NextQuest)
      $NextQuest = abs ($row ['NextQuestId']);

    if (in_array ($NextQuest, $foundQuests))
      break;  // avoid going into a loop
    $foundQuests [] = $NextQuest; // add this one to the chain
    // get the next one
    $row = dbQueryOneParam ("SELECT entry, NextQuestId, NextQuestInChain FROM ".QUEST_TEMPLATE." WHERE entry = ? AND ignored = 0",
                            array ('i', &$NextQuest));
    if (!$row)  // not on file?
      break;
    } // while we still have previous quest IDs

  if (count ($foundQuests) > 1)
    {
    listItems ('Quest chain', QUEST_TEMPLATE, count ($foundQuests) , $foundQuests,
      function ($quest) use ($quests, $id)
        {
        listThing ($quests, $quest, 'show_quest', $quest == $id ? '<- This' : '');
        });
    } // end of other quests in the chain

} // end of showQuestChain

function questTopMiddle ($info)
  {
  $row = $info ['row'];

  boxTitle ("Quest simulation");
  simulateQuest ($row);
  } // end of questTopMiddle

function addCreatureToMapPoints ($which, $color)
  {
  global $questMapPoints;
  global $creatures;

  $where = '(spawn_entry1 = ? OR spawn_entry2 = ? OR spawn_entry3 = ? OR spawn_entry4 = ?)' .
           ' AND ignored = 0 ';
  $param = array ('iiii', &$which, &$which, &$which, &$which);

  $spawnPoints = dbQueryParam ("SELECT * FROM ".SPAWNS_CREATURES." WHERE $where", $param) ;

  foreach ($spawnPoints as $spawnRow)
    $questMapPoints [] = array (
        'id'    => $spawnRow ['spawn_id'],
        'X'     => $spawnRow ['position_x'],
        'Y'     => $spawnRow ['position_y'],
        'Z'     => $spawnRow ['position_z'],
        'map'   => $spawnRow ['map'],
        'color' => $color,
        'name'  => $creatures [$which],
        );
  } // end of addCreatureToMapPoints

function gameObjectToMapPoints ($which, $color)
  {
  global $questMapPoints;
  global $game_objects;

  $where = 'spawn_entry = ? AND ignored = 0 ';
  $param = array ('i', &$which);

  // show spawn points - Eastern Kingdoms
  $spawnPoints = dbQueryParam ("SELECT * FROM ".SPAWNS_GAMEOBJECTS." WHERE $where", $param) ;

  foreach ($spawnPoints as $spawnRow)
    $questMapPoints [] = array (
        'id'    => $spawnRow ['spawn_id'],
        'X'     => $spawnRow ['spawn_positionX'],
        'Y'     => $spawnRow ['spawn_positionY'],
        'Z'     => $spawnRow ['spawn_positionZ'],
        'map'   => $spawnRow ['spawn_map'],
        'color' => $color,
        'name'  => $game_objects [$which],
        );

  } // end of gameObjectToMapPoints

function questTopRight ($info)
  {
  global $id;
  global $questMapPoints;

  // find where the quest giver NPC is spawned
  $results = dbQueryParam ("SELECT * FROM ".CREATURE_QUEST_STARTER." WHERE quest = ? AND entry <= " . MAX_CREATURE, array ('i', &$id));
  foreach ($results as $row)
    {
    addCreatureToMapPoints ($row ['entry'], 'lightgreen');
    } // end of quest givers

  // find where the quest giver game object is spawned
  $results = dbQueryParam ("SELECT * FROM ".GAMEOBJECT_QUEST_STARTER." WHERE quest = ?", array ('i', &$id));
  foreach ($results as $row)
    {
    gameObjectToMapPoints ($row ['entry'], 'lightgreen');
    } // end of quest giver game objects

  // who finishes this quest
  $results = dbQueryParam ("SELECT * FROM ".CREATURE_QUEST_FINISHER." WHERE quest = ? AND entry <= " . MAX_CREATURE, array ('i', &$id));
  foreach ($results as $row)
    {
    addCreatureToMapPoints ($row ['entry'], 'cyan');
    } // end of quest finishers

  // game objects that finish this quest
  $results = dbQueryParam ("SELECT * FROM ".GAMEOBJECT_QUEST_FINISHER." WHERE quest = ?", array ('i', &$id));
  foreach ($results as $row)
    {
    gameObjectToMapPoints ($row ['entry'], 'cyan');
    } // end of quest finisher game objects

  // creatures or game objects

  $row = $info ['row'];

  // creatures we have to kill
  for ($n = 1; $n <= QUEST_REQUIRED_CREATURES; $n++)
    {
    $value = $row ["ReqCreatureOrGOId$n"];
    if ($value)
      {
      if ($value < 0)
        gameObjectToMapPoints (-$value, 'red');
      else
        addCreatureToMapPoints ($value, 'red');
      }
    } // end of for each creature or game object objective


  comment ('QUEST INFORMATION ON MAP');

  $mapPoints_0 = array ();
  $mapPoints_1 = array ();

  foreach ($questMapPoints as $mapPoint)
    {
    if ($mapPoint ['map'] == 0)
      $mapPoints_0 [] = $mapPoint;
    elseif ($mapPoint ['map'] == 1)
      $mapPoints_1 [] = $mapPoint;
    }

  showSpawnPoints ($mapPoints_0, 'Quest information - Eastern Kingdoms', 'Multiple tables',
                  'id', 'X', 'Y', 'Z', 'map');

  comment ('KALIMDOR');

  showSpawnPoints ($mapPoints_1, 'Quest information- Kalimdor', 'Multiple tables',
                  'id', 'X', 'Y', 'Z', 'map');

  comment ('QUEST INFORMATION ON MAP');


  } // end of questTopRight

function questDetails ($info)
  {
  global $id;

  global $questMapPoints;

  // for the quest starter, finisher, mobs, game objects and area triggers
  $questMapPoints = array ();

  $row = $info ['row'];

  topSection    ($info, function ($info) use ($id)
      {
      topMiddle ($info, 'questTopMiddle');
      topRight ($info,  'questTopRight');
      });

  middleSection ($info, function ($info) use ($id, $row)
      {
      showQuestGivers ();
      showQuestFinishers ();
      showQuestChain ();
      showQuestAreaTriggers ();

      });

  bottomSection ($info, function ($info) use ($id)
      {
      $extras = $info ['extras'];
      showOneThing (QUEST_TEMPLATE, 'entry', $id,
                  "Database entry for quest", "Title", $extras);
      });

  } // end of questDetails


function showOneQuest ()
  {
  global $id;
  global $quests, $creatures, $items, $game_objects, $spells;

  if (($id === false && !repositionSearch()) || !checkID ())
    return;

 // we need the item info in this function
  $row = dbQueryOneParam ("SELECT * FROM ".QUEST_TEMPLATE." WHERE entry = ?", array ('i', &$id));

  if (!$row)
    {
    ShowWarning ("Quest $id is not on the database");
    return;
    } // end of not finding it

  $name = $row ['Title'];

  setTitle ("Quest $name");

  $extras = array (
        'SrcItemId' => 'item',
        'PrevQuestId' => 'quest',
        'NextQuestId' => 'quest',
        'NextQuestInChain' => 'quest',
        'ExclusiveGroup' => 'quest',
        'RewSpellCast' => 'spell',
        'RewSpell' => 'spell',
        'RequiredRaces' => 'race_mask',
        'RequiredClasses' => 'class_mask',
        'RequiredSkill' => 'skill',
        'RewOrReqMoney' => 'gold',
        'LimitTime' => 'time_secs',
        'QuestFlags' => 'quest_flags',
        'SpecialFlags' => 'quest_special_flags',
        'Type' => 'quest_type',
    );

  for ($i = 1; $i <= QUEST_REQUIRED_ITEMS; $i++)
    $extras ["ReqItemId$i"] = 'item';
  for ($i = 1; $i <= QUEST_REWARD_ITEMS; $i++)
    $extras ["RewItemId$i"] = 'item';
  for ($i = 1; $i <= QUEST_REWARD_ITEM_CHOICES; $i++)
    $extras ["RewChoiceItemId$i"] = 'item';
  for ($i = 1; $i <= QUEST_REQUIRED_CREATURES; $i++)
    $extras ["ReqCreatureOrGOId$i"] = 'creature_or_go';
  for ($i = 1; $i <= QUEST_REQUIRED_SPELLS; $i++)
    $extras ["ReqSpellCast$i"] = 'spell';
  for ($i = 1; $i <= QUEST_REWARD_REPUTATION; $i++)
    $extras ["RewRepFaction$i"] = 'faction';

  // we pass this stuff around to the helper functions
  $info = array ('row' => $row, 'extras' => $extras, 'limit' => array ());

  // ready to go! show the page info and work our way down into the sub-functions
  pageContent ($info, 'Quest', $name, 'quests', 'questDetails', QUEST_TEMPLATE);

  } // end of showOneQuest


function showQuests ()
  {
  global $where, $params, $sort_order, $matches;


  $sortFields = array (
    'entry',
    'Title',
    'MinLevel',
  );

  if (!in_array ($sort_order, $sortFields))
    $sort_order = 'Title';

  setTitle ("Quests listing");

  $td  = function ($s) use (&$row) { tdx ($row  [$s]); };

  $headings = array ('Entry', 'Title' ,'Level');

  $results = setUpSearch ('Quests', $sortFields,  $headings);

  if (!$results)
    return;

  $searchURI = makeSearchURI (true);
  $pos = 0;

  foreach ($results as $row)
    {
    $pos++;
    echo "<tr>\n";
    $id = $row ['entry'];
    tdh ("<a href='?action=show_quest&id=$id$searchURI&pos=$pos&max=$matches'>$id</a>");
    tdh ("<a href='?action=show_quest&id=$id$searchURI&pos=$pos&max=$matches'>" . fixHTML ($row ['Title']) . "</a>");
    if ($row ['MinLevel'] != $row ['MaxLevel'] && $row ['MaxLevel']  > 0)
      td  ($row ['MinLevel'] . '-' . $row ['MaxLevel'] );
    else
      $td ('MinLevel');
    showFilterColumn ($row);
    echo "</tr>\n";
    }

  wrapUpSearch ();


  } // end of showQuests
?>
