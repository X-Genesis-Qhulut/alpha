<?php

/*
  Author: X'Genesis Qhulut <XGenesis-Qhulut@protonmail.com>
  Date:   August 2022

  See LICENSE for license details.
*/

// CREATURES (NPCs)

// See: https://mangoszero-docs.readthedocs.io/en/latest/database/world/creature-loot-template.html


function trainer_spell_compare ($a, $b)
  {
  global $spells;
  if ($spells [$a ['playerspell']] == $spells [$b ['playerspell']])
    return $a ['playerspell'] <=> $b ['playerspell'];
  return $spells [$a ['playerspell']] <=> $spells [$b ['playerspell']];
  } // end of trainer_spell_compare

function item_compare ($a, $b)
  {
  global $items;
  return $items [$a ['item']] <=> $items [$b ['item']];
  } // end of item_compare

function reference_item_compare ($a, $b)
  {
  global $items;
  return $items [$a ['refItem']] <=> $items [$b ['refItem']];
  } // end of reference_item_compare

function extraCreatureInformation ($id, $row)
  {
  global $quests, $items, $maps, $spells;
  global $documentRoot, $executionDir;

  // ---------------- SPAWN POINTS -----------------

  // show spawn points - Eastern Kingdoms
  $results = dbQueryParam ("SELECT * FROM ".SPAWNS_CREATURES."
            WHERE (spawn_entry1 = ?
            OR spawn_entry2 = ?
            OR spawn_entry3 = ?
            OR spawn_entry4 = ?)
            AND ignored = 0
            AND map = 0", array ('iiii', &$id, &$id, &$id, &$id,));

  if (count ($results) > 0)
    showSpawnPoints ($results, 'Spawn points - Eastern Kingdoms', 'alpha_world.spawns_creatures',
                    'position_x', 'position_y', 'position_z', 'map');

  // show spawn points - Kalimdor
  $results = dbQueryParam ("SELECT * FROM ".SPAWNS_CREATURES."
            WHERE (spawn_entry1 = ?
            OR spawn_entry2 = ?
            OR spawn_entry3 = ?
            OR spawn_entry4 = ?)
            AND ignored = 0
            AND map = 1", array ('iiii', &$id, &$id, &$id, &$id,));

  if (count ($results) > 0)
    showSpawnPoints ($results, 'Spawn points - Kalimdor', 'alpha_world.spawns_creatures',
                    'position_x', 'position_y', 'position_z', 'map');

  // show spawn points - other
  $results = dbQueryParam ("SELECT * FROM ".SPAWNS_CREATURES."
            WHERE (spawn_entry1 = ?
            OR spawn_entry2 = ?
            OR spawn_entry3 = ?
            OR spawn_entry4 = ?)
            AND ignored = 0
            AND map > 1", array ('iiii', &$id, &$id, &$id, &$id,));

  if (count ($results) > 0)
    showSpawnPoints ($results, 'Spawn points - Instances', 'alpha_world.spawns_creatures',
                    'position_x', 'position_y', 'position_z', 'map');


  // ---------------- IMAGE OF CREATURE -----------------

  $icon = $row ['display_id1'] . '.webp';
  if (file_exists ("$documentRoot$executionDir/creatures/$icon"))
    echo "<img src='creatures/$icon' alt='Creature image'>\n";


  // ---------------- SPELL LISTS --------------


  if ($row ['spell_list_id'])
    {
    $spellListRow = dbQueryOneParam ("SELECT * FROM ".CREATURE_SPELLS." WHERE entry = ?", array ('i', &$row ['spell_list_id']));
    if ($spellListRow)
      {
      echo "<h2 title='Table: alpha_world.creature_spells'>Spells this NPC casts</h2><ul>\n";
      for ($i = 1; $i <= 8; $i++)
        {
        if ($spellListRow ["spellId_$i"])
          {
          echo "<li>";
          echo (lookupThing ($spells,   $spellListRow ["spellId_$i"], 'show_spell'));
          echo "<ul>\n";
          echo "<li>Probability: " . $spellListRow ["probability_$i"] . "%";
          echo "<li>Target type: " . expandSimple (TARGET_TYPE, $spellListRow ["castTarget_$i"]);
          if ($spellListRow ["targetParam1_$i"] || $spellListRow ["targetParam2_$i"])
            {
            echo " (param1: " . $spellListRow ["targetParam1_$i"];
            echo ", param2: " . $spellListRow ["targetParam2_$i"] . ")";
            }
          if ($spellListRow ["castFlags_$i"])
            echo "<li>Flags: "  . expandShiftedMask (SPELL_CAST_FLAGS, $spellListRow ["castFlags_$i"], false);

          echo "<li>Initial delay: "  . $spellListRow ["delayInitialMin_$i"] . ' to ' . $spellListRow ["delayInitialMax_$i"];
          echo "<li>Repeat delay: "  . $spellListRow ["delayRepeatMin_$i"] . ' to ' . $spellListRow ["delayRepeatMax_$i"];
          if ($spellListRow ["scriptId_$i"])
            echo "<li>Script ID: "  . $spellListRow ["scriptId_$i"];
          echo "</ul>\n";
          }   // end of if this spell entry is there (non-zero)
        } // end of for each of the 8 possible spells
      echo "</ul>\n";
      } // if we found the spell list

    } // end of if they had a spell_list_id

  // ---------------- QUESTS -----------------

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

  // ---------------- VENDOR ITEMS -----------------

 // what they sell
  $results = dbQueryParam ("SELECT * FROM ".NPC_VENDOR." WHERE entry = ?", array ('i', &$id));
  if (count ($results) > 0)
    {
    usort($results, 'item_compare');
    echo "<h2 title='Table: alpha_world.npc_vendor'>NPC sells</h2><ul>\n";
    foreach ($results as $vendorRow)
      {
      listThing ('', $items, $vendorRow ['item'], 'show_item');
      $maxcount = $vendorRow ['maxcount'];
      if ($maxcount  > 0)
        echo (" (limit $maxcount)");
      } // for each vendor NPC
    echo "</ul>\n";
    }

  // ---------------- TRAINER ITEMS -----------------

  // what they train

  $results = dbQueryParam ("SELECT * FROM ".TRAINER_TEMPLATE." WHERE template_entry = ?",
                            array ('i', &$row ['trainer_id']));
  if (count ($results) > 0)
    {
    echo "<h2 title='Table: alpha_world.trainer_id'>NPC trains</h2><ul>\n";
    usort($results, 'trainer_spell_compare');
    foreach ($results as $trainerRow)
      {
      listThing ('', $spells, $trainerRow ['playerspell'], 'show_spell');
      } // for each trainer NPC
    echo "</ul>\n";
    }

  // ---------------- LOOT-----------------

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
  usort($results, 'item_compare');
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

  usort ($lootResults, 'reference_item_compare');
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

  // ---------------- PICK POCKETING LOOT -----------------

  // show pickpocketing loot

  $loot_id = $row ['pickpocket_loot_id'];
  if (!$loot_id)
    $loot_id = $id;

  $results = dbQueryParam ("SELECT * FROM ".PICKPOCKETING_LOOT_TEMPLATE." WHERE entry = ?", array ('i', &$loot_id));
  echo "<h2 title='Table: alpha_world.pickpocketing_loot_template'>Pickpocketing loot</h2>\n<ul>\n";
  usort($results, 'item_compare');

  foreach ($results as $lootRow)
    {
    echo "<li>" . lookupItemHelper ($lootRow ['item'], $lootRow ['mincountOrRef']) . ' — ' .
         $lootRow ['ChanceOrQuestChance'] . '%';
    } // for each pickpocket item

  echo "</ul>\n";

  if (count($results) == 0)
    echo "<p>None.\n";


  } // end of extraCreatureInformation

function showOneCreature ($id)
  {


  $extras = array (
        'spell_id1' => 'spell',
        'spell_id2' => 'spell',
        'spell_id3' => 'spell',
        'spell_id4' => 'spell',
        'spell_id5' => 'spell',
        'faction'   => 'faction',
        'mechanic_immune_mask' => 'mechanic_mask',
        'school_immune_mask' => 'school_mask',    // I think this might be out by one
        'inhabit_type'  => 'inhabit_type_mask',
        'movement_type' => 'movement_type',
        'flags_extra'   => 'flags_extra_mask',
        'npc_flags'     => 'npc_flags_mask',
        'rank'          => 'rank',
        'gold_min'      => 'gold',
        'gold_max'      => 'gold',
        'dmg_school'    => 'spell_school',
        'base_attack_time'    => 'time',
        'ranged_attack_time'    => 'time',
        'type'    => 'creature_type',

         'static_flags'   => 'creature_static_flags',

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


  showOneThing (CREATURE_TEMPLATE, 'alpha_world.creature_template', 'entry',
              $id, "Creature", "name", $extras, 'extraCreatureInformation');


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

  $offset = getQueryOffset(); // based on the requested page number

  $results = dbQueryParam ("SELECT * FROM ".CREATURE_TEMPLATE." $where AND entry <= " . MAX_CREATURE .
                            " ORDER BY $sort_order LIMIT $offset, " . QUERY_LIMIT,
                            $params);

  if (!showSearchForm ($sortFields, $results, CREATURE_TEMPLATE, "$where AND entry <= " . MAX_CREATURE))
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
    showFilterColumn ($row);

    echo "</tr>\n";
    }
  echo "</table>\n";

  showCount ($results);

  } // end of showCreatures
?>
