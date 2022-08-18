<?php

/*
  Author: X'Genesis Qhulut <XGenesis-Qhulut@protonmail.com>
  Date:   August 2022

  See LICENSE for license details.
*/

// CREATURES (NPCs)

// See: https://mangoszero-docs.readthedocs.io/en/latest/database/world/creature-loot-template.html

function showOneCreature ($id)
  {
  global $quests, $items, $maps;
  global $documentRoot, $executionDir;

  $extras = array (
        'spell_id1' => 'spell',
        'spell_id2' => 'spell',
        'spell_id3' => 'spell',
        'spell_id4' => 'spell',
        'spell_id5' => 'spell',
        'faction'   => 'faction',
        'mechanic_immune_mask' => 'mechanic_immune_mask',
        'inhabit_type'  => 'inhabit_type_mask',
        'movement_type' => 'movement_type',
        'flags_extra'   => 'flags_extra_mask',
        'npc_flags'     => 'npc_flags_mask',
        'rank'          => 'rank',
        'gold_min'          => 'gold',
        'gold_max'          => 'gold',
 //       'trainer_spell' => 'spell',   // Hmmm, must have the wrong end of the stick here

    );

  // we need the creature info in this function
  $row = dbQueryOneParam ("SELECT * FROM ".CREATURE_TEMPLATE." WHERE entry = ?", array ('i', &$id));

  if ($row ['npc_flags'] & TRAINER_FLAG)
    {
    $extras  ['trainer_type'] = 'trainer_type';
    $extras  ['trainer_class'] = 'class';
    $extras  ['trainer_race'] = 'race';
    }

/*
 // fallback icon: INV_Misc_QuestionMark.png


  $icon = $row ['display_id1'] . '.png';
  if (file_exists ("$documentRoot$executionDir/creatures/$icon"))
    echo "<img src='creatures/$icon' alt='Creature image'>\n";
  else
    echo "<img src='icons/INV_Misc_QuestionMark.png' alt='Creature image'>\n";

*/

  showOneThing (CREATURE_TEMPLATE, 'alpha_world.creature_template', 'entry', $id, "Creature", "name", $extras);

  // show spawn points
  $results = dbQueryParam ("SELECT * FROM ".SPAWNS_CREATURES."
            WHERE (spawn_entry1 = ?
            OR spawn_entry2 = ?
            OR spawn_entry3 = ?
            OR spawn_entry4 = ?)
            AND ignored = 0", array ('iiii', &$id, &$id, &$id, &$id,));

  if (count ($results) > 0)
    {
    echo "<h2 title='Table: alpha_world.spawns_creatures'>Spawn points</h2>\n<ul>\n";
    foreach ($results as $spawnRow)
      {
      $x = $spawnRow ['position_x'];
      $y = $spawnRow ['position_y'];
      $z = $spawnRow ['position_z'];
      $map = $spawnRow ['map'];
      echo "<li>$x $y $z $map (" . htmlspecialchars ($maps [$map]) . ")";
      } // for each spawn point
    } // if any spawn points
    echo "</ul>\n";

  // show quests they start

  // what quests they give
  $results = dbQueryParam ("SELECT * FROM ".CREATURE_QUEST_STARTER." WHERE entry = ?", array ('i', &$id));
  if (count ($results) > 0)
    {
    echo "<h2 title='Table: alpha_world.creature_quest_starter'>NPC starts these quests</h2><ul>\n";
    foreach ($results as $questRow)
      {
      listThing ('', $quests, $questRow ['quest'], 'show_quest');
      } // for each quest starter NPC
    echo "</ul>\n";
    }

  // what quests they finish
  $results = dbQueryParam ("SELECT * FROM ".CREATURE_QUEST_FINISHER." WHERE entry = ?", array ('i', &$id));
  if (count ($results) > 0)
    {
    echo "<h2 title='Table: alpha_world.creature_quest_finisher'>NPC finishes these quests</h2><ul>\n";
    foreach ($results as $questRow)
      {
      listThing ('', $quests, $questRow ['quest'], 'show_quest');
      } // for each quest finisher NPC
    echo "</ul>\n";
    }

 // what they sell
  $results = dbQueryParam ("SELECT * FROM ".NPC_VENDOR." WHERE entry = ?", array ('i', &$id));
  if (count ($results) > 0)
    {
    echo "<h2 title='Table: alpha_world.npc_vendor'>NPC sells</h2><ul>\n";
    foreach ($results as $vendorRow)
      {
      listThing ('', $items, $vendorRow ['item'], 'show_item');
      $maxcount = $vendorRow ['maxcount'];
      if ($maxcount  > 0)
        echo (" (limit $maxcount)");
      } // for each quest finisher NPC
    echo "</ul>\n";
    }



  // show loot

  $loot_id = $row ['loot_id'];
  if (!$loot_id)
    $loot_id = $id;


  $creature_loot_template = CREATURE_LOOT_TEMPLATE;
  $reference_loot_template = REFERENCE_LOOT_TEMPLATE;

  $results = dbQueryParam ("SELECT * FROM $creature_loot_template
                            WHERE entry = ? AND mincountOrRef > 0
                            ORDER BY item", array ('i', &$loot_id));

  // count quest items - they have a negative drop chance
  $count = 0;
  foreach ($results as $lootRow)
    if ($lootRow ['ChanceOrQuestChance'] < 0)
        $count++;

  if ($count)
    {
    echo "<h2 title='Table: alpha_world.creature_loot_template'>Quest item loot</h2>\n<ul>\n";
    foreach ($results as $lootRow)
      {
      $chance = $lootRow ['ChanceOrQuestChance'];
      if ($chance < 0)
        echo "<li>" . lookupItemHelper ($lootRow ['item'], $lootRow ['mincountOrRef']) . ' — ' .
             -$chance . '%';
      } // for each loot item
    echo "</ul>\n";
    } // if any quest drops

  // now do the other drops

  echo "<h2 title='Table: alpha_world.creature_loot_template'>Loot</h2>\n<ul>\n";

  foreach ($results as $lootRow)
    {
    $count++;
    $chance = $lootRow ['ChanceOrQuestChance'];
    if ($chance >= 0)
      echo "<li>" . lookupItemHelper ($lootRow ['item'], $lootRow ['mincountOrRef']) . ' — ' .
           $chance . '%';
    } // for each loot item


  // reference loot - the creature_loot_template table points to the reference_loot_template table
  // if the mincountOrRef field is negative, which may lead to multiple loot items for one reference
  // item - I presume to allow batches of loot to be attached to one creature_loot_template entry

  $lootResults = dbQueryParam ("SELECT $reference_loot_template.item AS refItem,
                            $creature_loot_template.ChanceOrQuestChance AS chance,
                            $reference_loot_template.mincountOrRef as minCount
                           FROM $creature_loot_template
                           INNER JOIN $reference_loot_template
                              ON $reference_loot_template.entry = $creature_loot_template.item
                           WHERE $creature_loot_template.entry = ?
                            AND $creature_loot_template.mincountOrRef < 0
                            ORDER BY $reference_loot_template.item", array ('i', &$loot_id));

  if (count ($lootResults) > 0)
    {
    echo "</ul><h2 title='Table: alpha_world.creature_loot_template'>Reference loot</h2>\n<ul>\n";
    }

  foreach ($lootResults as $lootRow)
    {
    $count++;
    $chance = $lootRow ['chance'];
    if ($chance >= 0)
      $chance .= '%';
    else
      $chance = -$chance . "% (quest)";
    echo "<li>" . lookupItemHelper ($lootRow ['refItem'], $lootRow ['minCount']) . ' — ' .
         $chance;
    } // for each loot item

  echo "</ul>\n";

  if ($count == 0)
    echo "<p>None.\n";

  // show pickpocketing loot

  $loot_id = $row ['pickpocket_loot_id'];
  if (!$loot_id)
    $loot_id = $id;

  $results = dbQueryParam ("SELECT * FROM ".PICKPOCKETING_LOOT_TEMPLATE." WHERE entry = ?", array ('i', &$loot_id));
  echo "<h2 title='Table: alpha_world.pickpocketing_loot_template'>Pickpocketing loot</h2>\n<ul>\n";

  foreach ($results as $lootRow)
    {
    echo "<li>" . lookupItemHelper ($lootRow ['item'], $lootRow ['mincountOrRef']) . ' — ' .
         $lootRow ['ChanceOrQuestChance'] . '%';
    } // for each pickpocket item

  echo "</ul>\n";

  if (count($results) == 0)
    echo "<p>None.\n";

  } // end of showOneCreature


function showCreatures ()
  {
  global $where, $params, $sort_order;

  $sortFields = array (
    'entry',
    'name',
    'subname',
    'name',
    'level_min',
  );


  if (!in_array ($sort_order, $sortFields))
    $sort_order = 'name';

  echo "<h2>Creatures (NPCs)</h2>\n";

  $td  = function ($s) use (&$row) { tdx ($row  [$s]); };
  $tdr = function ($s) use (&$row) { tdx ($row  [$s], 'tdr'); };

  setUpSearch ('entry', array ('name', 'subname'));

  $results = dbQueryParam ("SELECT * FROM ".CREATURE_TEMPLATE." $where AND entry <= " . MAX_CREATURE . " ORDER BY $sort_order, entry LIMIT " . QUERY_LIMIT,
                    $params);

  if (!showSearchForm ($sortFields, $results))
    return;

  echo "<table class='search_results'>\n";
  headings (array ('Entry', 'Name', 'Subname', 'Level'));
  foreach ($results as $row)
    {
    echo "<tr>\n";
    $id = $row ['entry'];
    tdhr ("<a href='?action=show_creature&id=$id'>$id</a>");
    $tdr ('name');
    $tdr ('subname');
    if ($row ['level_min'] != $row ['level_max'])
      tdxr  ($row ['level_min'] . '-' . $row ['level_max'] );
    else
      $tdr ('level_min');
    echo "</tr>\n";
    }
  echo "</table>\n";

  showCount ($results);

  } // end of showCreatures
?>
