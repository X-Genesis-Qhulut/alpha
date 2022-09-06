<?php

/*
  Author: X'Genesis Qhulut <XGenesis-Qhulut@protonmail.com>
  Date:   August 2022

  See LICENSE for license details.
*/

// QUESTS

// See: https://mangoszero-docs.readthedocs.io/en/latest/database/world/quest-template.html


function fixQuestText ($s)
  {
  $s = str_ireplace ('$n', "<name>", $s);
  $s = str_ireplace ('$r', "<race>", $s);
  $s = str_ireplace ('$c', "<class>", $s);

  // gendered alternatives, eg. lad/lass, brother/sister, good sir/my lady etc.
  // Example: "$gmister:lady;"    becomes "<mister/lady>"

  $s = preg_replace ('/\$g ?([^:]+):([^;]+);/i', '<\1/\2>', $s);

  $s = fixHTML ($s);
  return str_ireplace ('$b', "<br>", $s);
  } // end of fixQuestText

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


  echo "</div>\n";    // end of simulated quest box

// ===============================================================================================================


  } // end of simulateQuest

function showQuestGivers ()
{
  global $id, $creatures, $items, $game_objects;

 // who gives this quest

  $results = dbQueryParam ("SELECT * FROM ".CREATURE_QUEST_STARTER." WHERE quest = ? AND entry <= " . MAX_CREATURE, array ('i', &$id));
  listItems ('NPCs that start this quest', 'alpha_world.creature_quest_starter', count ($results) , $results,
    function ($row) use ($creatures)
      {
      listThing ($creatures, $row ['entry'], 'show_creature');
      });

  $results = dbQueryParam ("SELECT * FROM ".ITEM_TEMPLATE." WHERE start_quest = ? AND ignored = 0", array ('i', &$id));
  listItems ('Items that start this quest', 'alpha_world.item_template', count ($results) , $results,
    function ($row) use ($items)
      {
      listThing ($items, $row ['entry'], 'show_item');
      });

  $results = dbQueryParam ("SELECT * FROM ".GAMEOBJECT_QUESTRELATION." WHERE quest = ?", array ('i', &$id));

  listItems ('Game objects that start this quest', 'alpha_world.gameobject_questrelation', count ($results) , $results,
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

  listItems ('NPCs that finish this quest', 'alpha_world.creature_quest_finisher', count ($results) , $results,
    function ($row) use ($creatures)
      {
      listThing ($creatures, $row ['entry'], 'show_creature');
      });

  $results = dbQueryParam ("SELECT * FROM ".GAMEOBJECT_INVOLVEDRELATION." WHERE quest = ?", array ('i', &$id));

  listItems ('Game objects that finish this quest', 'alpha_world.gameobject_involvedrelation', count ($results) , $results,
    function ($row) use ($game_objects)
      {
      listThing ($game_objects, $row ['entry'], 'show_go');
      });


} // end of showQuestFinishers

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
    listItems ('Quest chain', 'alpha_world.quest_template', count ($foundQuests) , $foundQuests,
      function ($quest) use ($quests, $id)
        {
        echo ("<li>" . lookupThing ($quests, $quest, 'show_quest'));
        if ($quest == $id)
          echo ' <i>(this quest)</i>';
        });
    } // end of other quests in the chain

} // end of showQuestChain

function questTopMiddle ($info)
  {
  $row = $info ['row'];

  boxTitle ("Quest simulation");
  simulateQuest ($row);
  } // end of questTopMiddle

function questDetails ($info)
  {
  global $id;

  $row = $info ['row'];

  topSection    ($info, function ($info) use ($id)
      {
      topMiddle ($info, 'questTopMiddle');
      });

  middleSection ($info, function ($info) use ($id, $row)
      {
      showQuestGivers ();
      showQuestFinishers ();
      showQuestChain ();

      });

  bottomSection ($info, function ($info) use ($id)
      {
      $extras = $info ['extras'];
      showOneThing (QUEST_TEMPLATE, 'alpha_world.quest_template', 'entry', $id,
                  "Database entry for quest", "Title", $extras);
      });

  } // end of questDetails


function showOneQuest ()
  {
  global $id;
  global $quests, $creatures, $items, $game_objects, $spells;

  if (!checkID ())
    return;

 // we need the item info in this function
  $row = dbQueryOneParam ("SELECT * FROM ".QUEST_TEMPLATE." WHERE entry = ?", array ('i', &$id));

  if (!$row)
    {
    ShowWarning ("Quest $id is not on the database");
    return;
    } // end of not finding it

  $name = $row ['Title'];

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
  global $where, $params, $sort_order;


  $sortFields = array (
    'entry',
    'Title',
    'MinLevel',
  );

  if (!in_array ($sort_order, $sortFields))
    $sort_order = 'Title';


  $td  = function ($s) use (&$row) { tdx ($row  [$s]); };

  $results = setUpSearch ('Quests',
                          $sortFields,            // fields we can sort on
                          array ('Entry', 'Title' ,'Level'),    // headings
                          'entry',                // key
                          array ('Title', 'Details', 'Objectives', 'OfferRewardText',
                                'RequestItemsText', 'EndText', 'ObjectiveText1', 'ObjectiveText2',
                                'ObjectiveText3', 'ObjectiveText4'),  // searchable fields
                          QUEST_TEMPLATE,          // table
                          'AND ignored = 0');     // extra conditions

  if (!$results)
    return;

  $searchURI = makeSearchURI (true);

  foreach ($results as $row)
    {
    echo "<tr>\n";
    $id = $row ['entry'];
    tdh ("<a href='?action=show_quest&id=$id$searchURI'>$id</a>");
    tdh ("<a href='?action=show_quest&id=$id$searchURI'>" . fixHTML ($row ['Title']) . "</a>");
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
