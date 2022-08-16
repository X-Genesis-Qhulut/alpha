<?php

/*
  Author: X'Genesis Qhulut <XGenesis-Qhulut@protonmail.com>
  Date:   August 2022

  See LICENSE for license details.
*/

function fixQuestText ($s)
  {
  return str_ireplace ('$b', "<br>", htmlspecialchars ($s));
  } // end of fixQuestText

function showOneQuest ($id)
  {
  global $quests, $creatures, $items, $game_objects, $spells;

  showOneThing (QUEST_TEMPLATE, 'entry', $id, "Quest", "Title",
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


    ));

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
    listThing ('', $creatures, $row ['entry'], 'show_creature');
    } // for each quest finisher
  echo "</ul>\n";

  // simulate quest

  // we need the creature info in this function
  $row = dbQueryOneParam ("SELECT * FROM ".QUEST_TEMPLATE." WHERE entry = ?", array ('i', &$id));

  echo "<div class='quest'>\n";
  echo "<h2>" . htmlspecialchars ($row ['Title']) . "</h2>\n";
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
    echo "<h4>Choice of:</h4>\n";

  for ($n = 1; $n <= 6; $n++)
    if ($row ["RewChoiceItemId$n"])
      echo (lookupItemHelper ($row ["RewChoiceItemId$n"], $row ["RewChoiceItemCount$n"]) . "<br>");

  if ($row ['RewXP'])
    echo "<p>" . $row ['RewXP'] . " XP<br>";

  if ($row ['RewOrReqMoney'])
    echo "<p>You will receive: " . convertGold ($row ['RewOrReqMoney']) . " <br>";

  if ($row ['RewSpellCast'])
    echo "<p>" . lookupThing ($spells, $row ["RewSpellCast"], 'show_spell') . " will be cast on you<br>";

  if ($row ['RewSpell'])
    echo "<p>" . lookupThing ($spells, $row ["RewSpell"], 'show_spell') . " will be cast on you<br>";


 $count = ($row ["RewRepValue1"] ? 1 : 0) +
          ($row ["RewRepValue2"] ? 1 : 0) +
          ($row ["RewRepValue3"] ? 1 : 0) +
          ($row ["RewRepValue4"] ? 1 : 0) +
          ($row ["RewRepValue5"] ? 1 : 0);

  if ($count > 0)
    {
    echo "<h4>Reputation adjustment</h4>\n";
    for ($n = 1; $n <= 5; $n++)
      if ($row ["RewRepValue$n"])
        echo (($row ["RewRepValue$n"] > 0 ? '+' : '' ). $row ["RewRepValue$n"] . ' with ' . getFaction ($row ["RewRepFaction$n"]));



    }

  echo "</div>\n";

  } // end of showOneQuest



function showQuests ()
  {
  global $where, $params;


  echo "<h2>Quests</h2>\n";

  $td  = function ($s) use (&$row) { tdx ($row  [$s]); };
  $tdr = function ($s) use (&$row) { tdx ($row  [$s], 'tdr'); };

  setUpSearch ('entry', array ('Title', 'Details', 'Objectives', 'OfferRewardText',
                                'RequestItemsText', 'EndText', 'ObjectiveText1', 'ObjectiveText2',
                                'ObjectiveText3', 'ObjectiveText4' ));

  $results = dbQueryParam ("SELECT * FROM ".QUEST_TEMPLATE." $where AND ignored = 0 ORDER BY Title LIMIT " . QUERY_LIMIT,
                    $params);

  showSearchForm ();

  if (count ($results) == 0)
    {
    echo "No matches.";
    return;
    } // end of nothing

  echo "<table>\n";
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
    echo "</tr>\n";
    }
  echo "</table>\n";

  showCount ($results);

  } // end of showQuests
?>
