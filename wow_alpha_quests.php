<?php

/*
  Author: X'Genesis Qhulut <XGenesis-Qhulut@protonmail.com>
  Date:   August 2022

  See LICENSE for license details.
*/

// QUESTS

function fixQuestText ($s)
  {
  $s = str_ireplace ('$n', "<name>", $s);
  $s = str_ireplace ('$r', "<race>", $s);
  $s = str_ireplace ('$c', "<class>", $s);

  $s = fixHTML ($s);
  return str_ireplace ('$b', "<br>", $s);
  } // end of fixQuestText

function simulateQuest ($id, $row)
  {
  global $game_objects, $creatures, $zones, $quests, $spells;

 // simulate quest

  echo "<p><div class='quest'>\n";
  echo "<h2>" . fixHTML ($row ['Title']) . "</h2>\n";
  echo "<p>" . fixQuestText ($row ['Details'] ). "</h2>\n";

  echo "<h3>Objectives</h3>\n";
  echo "<p>" . fixQuestText ($row ['Objectives']) . "</h2>\n";

  echo "<h3>Requirements</h3>\n";

  for ($n = 1; $n <= 4; $n++)
    if ($row ["ReqItemId$n"])
      echo (lookupItemHelper ($row ["ReqItemId$n"], $row ["ReqItemCount$n"]) . "<br>");

  // creatures of game objects

  for ($n = 1; $n <= 4; $n++)
    {
    $value = $row ["ReqCreatureOrGOId$n"];
    if ($value)
      {
      if ($value < 0)
        echo (lookupThing ($game_objects, -$value, 'show_go'));
      else
        echo (lookupThing ($creatures, $value, 'show_creature'));
      echo (showItemCount ($row ['ReqCreatureOrGOCount1']));
      echo "<br>";
      }
    } // end of for each creature or game object objective

  echo "<p>\n";

  // spells

  for ($n = 1; $n <= 4; $n++)
    if ($row ["ReqSpellCast$n"])
      echo ('Cast: ' . lookupThing ($spells, $row ["ReqSpellCast$n"], 'show_spell'). "<br>");


  echo "<h3>Progress</h3>\n";
  echo "<p>" . fixQuestText ($row ['RequestItemsText']) . "</h2>\n";

  echo "<hr><h3>Completion</h3>\n";
  echo "<p>" . fixQuestText ($row ['OfferRewardText']) . "</h2>\n";

  echo "<h3>Reward</h3>\n";

  // non-choice item rewards

  for ($n = 1; $n <= 4; $n++)
    if ($row ["RewItemId$n"])
      echo (lookupItemHelper ($row ["RewItemId$n"], $row ["RewItemCount$n"]) . "<br>");

  // choose an item from these six

  $count = ($row ["RewChoiceItemId1"] ? 1 : 0) +
           ($row ["RewChoiceItemId2"] ? 1 : 0) +
           ($row ["RewChoiceItemId3"] ? 1 : 0) +
           ($row ["RewChoiceItemId4"] ? 1 : 0) +
           ($row ["RewChoiceItemId5"] ? 1 : 0) +
           ($row ["RewChoiceItemId6"] ? 1 : 0);

  if ($count > 1)
    echo "<h4>Choice of:</h4><ul>\n";

  for ($n = 1; $n <= 6; $n++)
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

  // reputation

 $count = ($row ["RewRepValue1"] ? 1 : 0) +
          ($row ["RewRepValue2"] ? 1 : 0) +
          ($row ["RewRepValue3"] ? 1 : 0) +
          ($row ["RewRepValue4"] ? 1 : 0) +
          ($row ["RewRepValue5"] ? 1 : 0);

  if ($count > 0)
    {
    echo "<h4>Reputation adjustment</h4><ul>\n";
    for ($n = 1; $n <= 5; $n++)
      if ($row ["RewRepValue$n"])
        echo ('<li>' . addSign ($row ["RewRepValue$n"]) . ' with ' . getFaction ($row ["RewRepFaction$n"], false));
    echo "</ul>\n";
    } // end of any reputation adjustment

  echo "<hr>\n";

  $zone = $row ['ZoneOrSort'];
  echo "<br><b>Zone</b>: " . (array_key_exists ($zone, $zones) ? $zones [$zone] : ($zone . ' (unknown)'));
  echo "<br><b>Minimum level</b>: " . $row ['MinLevel'];
  echo "<br><b>Quest level</b>: " . $row ['QuestLevel'];
  if ($row ['LimitTime'])
    echo "<br><b>Time limit</b>: " . convertTimeMinutes ($row ['LimitTime'] . 'm', true);
  if ($row ['PrevQuestId'])
   echo "<br><b>Previous quest</b>: " . lookupThing ($quests, $row ['PrevQuestId'], 'show_quest');
  if ($row ['NextQuestId'])
   echo "<br><b>Next quest</b>: " . lookupThing ($quests, $row ['NextQuestId'], 'show_quest');
  if ($row ['NextQuestInChain'])
   echo "<br><b>Next quest in chain</b>: " . lookupThing ($quests, $row ['NextQuestInChain'], 'show_quest');


  echo "</div>\n";


  // find previous quests in the chain

  $foundQuests = array ($id);    // stop looping

  while ($row ['PrevQuestId'])
    {
    // get the previous one
    $row = dbQueryOneParam ("SELECT entry, PrevQuestId FROM ".QUEST_TEMPLATE." WHERE entry = ? AND ignored = 0",
                            array ('i', &$row ['PrevQuestId']));
    if (!$row)  // not on file?
      break;
    if (in_array ($row ['entry'], $foundQuests))
      break;  // avoid going into a loop
    $foundQuests [] = $row ['entry']; // add this one to the chain
    } // while we still have previous quest IDs

  $foundQuests = array_reverse ($foundQuests);  // get into ascending order

  // now get the next quests in the chain

  // get this quest back
  $row = dbQueryOneParam ("SELECT entry, NextQuestInChain FROM ".QUEST_TEMPLATE." WHERE entry = ?",
                          array ('i', &$id));

  while ($row ['NextQuestInChain'])
    {
    // get the next one
    $row = dbQueryOneParam ("SELECT entry, NextQuestInChain FROM ".QUEST_TEMPLATE." WHERE entry = ? AND ignored = 0",
                            array ('i', &$row ['NextQuestInChain']));
    if (!$row)  // not on file?
      break;
    if (in_array ($row ['entry'], $foundQuests))
      break;  // avoid going into a loop
    $foundQuests [] = $row ['entry']; // add this one to the chain
    } // while we still have previous quest IDs

  if (count ($foundQuests) > 1)
    {
    echo "<h2>Quest chain</h2><ul>\n";
    foreach ($foundQuests as $quest)
      {
      echo ("<li>" . lookupThing ($quests,     $quest, 'show_quest'));
      if ($quest == $id)
        echo fixHTML (' <-- this quest');
      } // end of foreach
    echo "</ul>\n";
    } // end of previous quests in the chain

  } // end of simulateQuest

function showOneQuest ($id)
  {
  global $quests, $creatures, $items, $game_objects, $spells;

  showOneThing (QUEST_TEMPLATE, 'alpha_world.quest_template', 'entry', $id, "Quest", "Title",
    array (
        'ReqItemId1' => 'item',
        'ReqItemId2' => 'item',
        'ReqItemId3' => 'item',
        'ReqItemId4' => 'item',
        'RewItemId1' => 'item',
        'RewItemId2' => 'item',
        'RewItemId3' => 'item',
        'RewItemId4' => 'item',
        'ReqCreatureOrGOId1' => 'creature_or_go',
        'ReqCreatureOrGOId2' => 'creature_or_go',
        'ReqCreatureOrGOId3' => 'creature_or_go',
        'ReqCreatureOrGOId4' => 'creature_or_go',
        'RewChoiceItemId1' => 'item',
        'RewChoiceItemId2' => 'item',
        'RewChoiceItemId3' => 'item',
        'RewChoiceItemId4' => 'item',
        'RewChoiceItemId5' => 'item',
        'RewChoiceItemId6' => 'item',
        'SrcItemId' => 'item',
        'PrevQuestId' => 'quest',
        'NextQuestId' => 'quest',
        'NextQuestInChain' => 'quest',
        'ExclusiveGroup' => 'quest',
        'ReqSpellCast1' => 'spell',
        'ReqSpellCast2' => 'spell',
        'ReqSpellCast3' => 'spell',
        'ReqSpellCast4' => 'spell',
        'RewSpellCast' => 'spell',
        'RewSpell' => 'spell',
        'RewRepFaction1' => 'faction',
        'RewRepFaction2' => 'faction',
        'RewRepFaction3' => 'faction',
        'RewRepFaction4' => 'faction',
        'RewRepFaction5' => 'faction',
        'RequiredRaces' => 'race_mask',
        'RequiredClasses' => 'class_mask',
        'RequiredSkill' => 'skill',
        'RewOrReqMoney' => 'gold',
        'LimitTime' => 'time_secs',
        'QuestFlags' => 'quest_flags',
        'SpecialFlags' => 'quest_special_flags',


    ),
    'simulateQuest');

  // who gives this quest
  echo "<h2  title='Table: alpha_world.creature_quest_starter'>Quest givers</h2><ul>\n";
  $results = dbQueryParam ("SELECT * FROM ".CREATURE_QUEST_STARTER." WHERE quest = ?", array ('i', &$id));
  foreach ($results as $row)
    {
    listThing ('NPC', $creatures, $row ['entry'], 'show_creature');
    } // for each quest starter NPC

  $results = dbQueryParam ("SELECT * FROM ".ITEM_TEMPLATE." WHERE start_quest = ?", array ('i', &$id));
  foreach ($results as $row)
    {
    listThing ('Item', $items, $row ['entry'], 'show_item');
    } // for each quest starter item


  $results = dbQueryParam ("SELECT * FROM ".GAMEOBJECT_QUESTRELATION." WHERE quest = ?", array ('i', &$id));
  foreach ($results as $row)
    {
    listThing ('Game object', $game_objects, $row ['entry'], 'show_go');
    } // for each quest starter game object

  echo "</ul>\n";

  // who finishes this quest
  echo "<h2  title='Table: alpha_world.creature_quest_finisher'>Quest finishers</h2><ul>\n";
  $results = dbQueryParam ("SELECT * FROM ".CREATURE_QUEST_FINISHER." WHERE quest = ?", array ('i', &$id));
  foreach ($results as $row)
    {
    listThing ('NPC', $creatures, $row ['entry'], 'show_creature');
    } // for each quest finisher


  $results = dbQueryParam ("SELECT * FROM ".GAMEOBJECT_INVOLVEDRELATION." WHERE quest = ?", array ('i', &$id));
  if (count ($results) > 0)
    {
    foreach ($results as $questRow)
      {
      listThing ('Game object', $game_objects, $questRow ['entry'], 'show_go');
      } // for each quest finisher GO
    }

  echo "</ul>\n";

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


  echo "<h2>Quests</h2>\n";

  $td  = function ($s) use (&$row) { tdx ($row  [$s]); };
  $tdr = function ($s) use (&$row) { tdx ($row  [$s], 'tdr'); };

  setUpSearch ('entry', array ('Title', 'Details', 'Objectives', 'OfferRewardText',
                                'RequestItemsText', 'EndText', 'ObjectiveText1', 'ObjectiveText2',
                                'ObjectiveText3', 'ObjectiveText4' ));

  $offset = getQueryOffset(); // based on the requested page number

  $results = dbQueryParam ("SELECT * FROM ".QUEST_TEMPLATE." $where AND ignored = 0 ORDER BY $sort_order LIMIT $offset, " .
                            QUERY_LIMIT,  $params);

  if (!showSearchForm ($sortFields, $results, QUEST_TEMPLATE, "$where AND ignored = 0"))
    return;

  echo "<table class='search_results'>\n";
  headings (array ('Entry', 'Title' ,'Level'));
  foreach ($results as $row)
    {
    echo "<tr>\n";
    $id = $row ['entry'];
    tdhr ("<a href='?action=show_quest&id=$id'>$id</a>");
    $td ('Title');
    if ($row ['MinLevel'] != $row ['MaxLevel'] && $row ['MaxLevel']  > 0)
      tdxr  ($row ['MinLevel'] . '-' . $row ['MaxLevel'] );
    else
      $tdr ('MinLevel');
    showFilterColumn ($row);
    echo "</tr>\n";
    }
  echo "</table>\n";

  showCount ($results);

  } // end of showQuests
?>
