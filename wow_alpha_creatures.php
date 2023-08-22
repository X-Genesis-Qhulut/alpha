<?php

/*
  Author: X'Genesis Qhulut <XGenesis-Qhulut@protonmail.com>
  Date:   August 2022

  See LICENSE for license details.
*/

// CREATURES (NPCs)

// See: https://mangoszero-docs.readthedocs.io/en/latest/database/world/creature-loot-template.html

function creatureTopLeft ($info)
{
  global $id;
  global $documentRoot, $executionDir;

  $row = $info ['row'];
  $extras = $info ['extras'];
  $limit = $info ['limit'];

  // ---------------- IMAGE OF CREATURE -----------------

  $navDotSize = 13;
  $halfNavDotSize = $navDotSize / 2;

  $count = getCount ($row, 'display_id', 4);

  echo "
    <!-- CAROUSSEL DISPLAY ID -->
    <div class='caroussel-model' id='caroussel-model' data-modelcount='$count'>
  ";

  for ($i = 1; $i <= 4; $i++)
    {
    $display_id = $row ["display_id$i"];
    if ($display_id)
      {
      $model = $display_id . '.webp';
      if (!file_exists ("$documentRoot$executionDir/creatures/$model"))
        {
        comment ("$documentRoot$executionDir/creatures/$model   NOT ON FILE");
        $model = 'missing_creature.webp';
        }

      $display = ($i == 1) ? 'block' : 'none';

      echo "<!-- MODEL DISPLAY ID $display_id -->
        <img
          class='model-display'
          src='creatures/$model'
          alt='Creature model for display ID $display_id'
          title='Model for display ID $display_id'
          id='model$i'
          style='display:$display;'
        >
      ";
      } // end of having a display ID

    } // end of for all 4 possible display IDs

$display3 = $count > 2 ? '' : 'none';
$display4 = $count > 3 ? '' : 'none';

if ($count > 1)
  {
  echo "
  <div class='dot-container'>
    <i
      id='model_navigate1'
      class='fas fa-circle'
      onclick='modelPage(event)'
      data-page='1'
      style='color:whitesmoke'
    ></i>
    <i
      id='model_navigate2'
      class='fas fa-circle'
      onclick='modelPage(event)'
      data-page='2'
    ></i>
    <i
      id='model_navigate3'
      class='fas fa-circle'
      onclick='modelPage(event)'
      data-page='3'
      style='display:$display3'
    ></i>
    <i
      id='model_navigate4'
      class='fas fa-circle'
      onclick='modelPage(event)'
      data-page='4'
      style='display:$display4'
    ></i>
  </div>
  ";
  } // if we have more than one model

  endDiv ('caroussel-model');

  comment ('SHORT LISTING OF FIELDS');
  showOneThing (CREATURE_TEMPLATE, 'entry',
              $id, "", "name", $extras, $limit);

} // end of creatureTopLeft

function creatureTopMiddle ($info)
  {
  global $id;
  global $quests, $spells, $items;
  global $countOfSpawnPoints;

  comment ('SPAWN POINTS - EASTERN KINGDOMS');

  $countOfSpawnPoints = 0;

  $where = '(spawn_entry1 = ? OR spawn_entry2 = ? OR spawn_entry3 = ? OR spawn_entry4 = ?)' .
           ' AND ignored = 0 ';
  $param = array ('iiii', &$id, &$id, &$id, &$id);

  // show spawn points - Eastern Kingdoms
  $results = dbQueryParam ("SELECT * FROM ".SPAWNS_CREATURES."
        WHERE $where AND map = 0", $param) ;

  $countOfSpawnPoints += listSpawnPoints ($results, 'Spawn points — Eastern Kingdoms', SPAWNS_CREATURES,
                'spawn_id', 'position_x', 'position_y', 'position_z', 'map', 'movement_type');

  comment ('SPAWN POINTS - KALIMDOR');

  // show spawn points - Kalimdor
  $results = dbQueryParam ("SELECT * FROM ".SPAWNS_CREATURES."
        WHERE $where AND map = 1", $param);

  $countOfSpawnPoints += listSpawnPoints ($results, 'Spawn points — Kalimdor', SPAWNS_CREATURES,
                'spawn_id', 'position_x', 'position_y', 'position_z', 'map', 'movement_type');


  comment ('SPAWN POINTS - OTHER');

  // show spawn points - other
  $results = dbQueryParam ("SELECT * FROM ".SPAWNS_CREATURES."
        WHERE $where AND map > 1", $param);

  $countOfSpawnPoints += listSpawnPoints ($results, 'Spawn points — Instances', SPAWNS_CREATURES,
                'spawn_id', 'position_x', 'position_y', 'position_z', 'map', 'movement_type');


  if (!$countOfSpawnPoints)
    showNoSpawnPoints ();

  comment ('END SPAWN POINTS');

 // ---------------- QUESTS -----------------

  // show quests they start

  comment ('QUESTS GIVEN');

  // what quests they give
  $results = dbQueryParam ("SELECT * FROM ".CREATURE_QUEST_STARTER." WHERE entry = ?", array ('i', &$id));

  listItems ('NPC starts these quests', CREATURE_QUEST_STARTER, count ($results), $results,
    function ($row) use ($quests)
      {
      listThing ($quests, $row ['quest'], 'show_quest');
      } // end listing function
      , true  // goes up top, slightly different CSS
      );
  comment ('QUESTS FINISHED');

  // what quests they finish
  $results = dbQueryParam ("SELECT * FROM ".CREATURE_QUEST_FINISHER." WHERE entry = ?", array ('i', &$id));

  listItems ('NPC finishes these quests', CREATURE_QUEST_FINISHER, count ($results), $results,
    function ($row) use ($quests)
      {
      listThing ($quests, $row ['quest'], 'show_quest');
      } // end listing function
      , true  // goes up top, slightly different CSS
      );

  return $countOfSpawnPoints;

  } // end of creatureTopMiddle

function creatureTopRight ($info)
  {
  global $id, $creatures;

  comment ('SPAWN POINTS ON MAP');

  echo "<div class='caroussel__maps'>\n";

  $where = '(spawn_entry1 = ? OR spawn_entry2 = ? OR spawn_entry3 = ? OR spawn_entry4 = ?)' .
           ' AND ignored = 0 ';
  $param = array ('iiii', &$id, &$id, &$id, &$id);

  comment ('KALIMDOR');

  // show spawn points - Kalimdor
  $results = dbQueryParam ("SELECT * FROM ".SPAWNS_CREATURES."
        WHERE $where AND map = 1", $param);

  // add in creature name
  foreach ($results as &$row)
    $row ['name'] = $creatures [$id];

  showSpawnPoints ($results, 'Spawn points — Kalimdor', SPAWNS_CREATURES,
                'spawn_id', 'position_x', 'position_y', 'position_z', 'map', 'movement_type');

  comment ('EASTERN KINGDOMS');

  // show spawn points - Eastern Kingdoms
  $results = dbQueryParam ("SELECT * FROM ".SPAWNS_CREATURES."
        WHERE $where AND map = 0", $param) ;

  // add in creature name
  foreach ($results as &$row)
    $row ['name'] = $creatures [$id];

  showSpawnPoints ($results, 'Spawn points — Eastern Kingdoms', SPAWNS_CREATURES,
                'spawn_id', 'position_x', 'position_y', 'position_z', 'map', 'movement_type');

  comment ('END MAP SPAWN POINTS');

  endDiv ('caroussel__maps');

  } // end of creatureTopRight

function creatureSpells ($info)
{
  global $id;
  global $documentRoot, $executionDir;
  global $spells;

  $row = $info ['row'];

  comment ('SPELLS THEY CAST');

  if ($row ['spell_list_id'])
    {
    $spellListRow = dbQueryOneParam ("SELECT * FROM ".CREATURE_SPELLS." WHERE entry = ?", array ('i', &$row ['spell_list_id']));
    if ($spellListRow)
      {

      $results = array ();

      for ($i = 1; $i <= 8; $i++)
        {
        if ($spellListRow ["spellId_$i"])
          {
          $details =
          "Probability: " . $spellListRow ["probability_$i"] . "%\n" .
          "Target type: " . expandSimple (TARGET_TYPE, $spellListRow ["castTarget_$i"]) . "\n";
          if ($spellListRow ["targetParam1_$i"] || $spellListRow ["targetParam2_$i"])
            {
            $details .= " (param1: " . $spellListRow ["targetParam1_$i"] .
            ", param2: " . $spellListRow ["targetParam2_$i"] . ")\n";
            }
          if ($spellListRow ["castFlags_$i"])
            $details .= "Flags: "  . expandShiftedMask (SPELL_CAST_FLAGS, $spellListRow ["castFlags_$i"], false) . "\n";
          $details .= "Initial delay: "  . $spellListRow ["delayInitialMin_$i"];
          if ($spellListRow ["delayInitialMin_$i"] != $spellListRow ["delayInitialMax_$i"])
            $details .= ' to ' . $spellListRow ["delayInitialMax_$i"];
          $details .=  "\n";
          $details .= "Repeat delay: "  . $spellListRow ["delayRepeatMin_$i"];
          if ($spellListRow ["delayRepeatMin_$i"] != $spellListRow ["delayRepeatMax_$i"])
            $details .= ' to ' . $spellListRow ["delayRepeatMax_$i"];
          $details .=  "\n";
          if ($spellListRow ["scriptId_$i"])
            $details .= "Script ID: "  . $spellListRow ["scriptId_$i"] . "\n";

          $results [] = array ( 'ID' => $spellListRow ["spellId_$i"], 'details' => $details );
          }   // end of if this spell entry is there (non-zero)
        } // end of for each of the 8 possible spells

      listItems ('Spells they cast', CREATURE_SPELLS, count ($results), $results,
        function ($row) use ($spells)
          {
          listThing ($spells, $row ['ID'], 'show_spell', '', '', $row ['details']);
          } // end listing function
          );


      } // if we found the spell list


    } // end of if they had a spell_list_id


} // end of creatureSpells

function creatureVendorItems ($info)
{
  global $id;
  global $items;

  comment ('VENDOR ITEMS');

 // what they sell
  $results = dbQueryParam ("SELECT * FROM ".NPC_VENDOR." WHERE entry = ?", array ('i', &$id));
  usort($results, 'item_compare');

  listItems ('NPC sells', NPC_VENDOR, count ($results), $results,
    function ($row) use ($items)
      {
      $maxcount = $row ['maxcount'];
      listThing ($items, $row ['item'], 'show_item', $maxcount  > 0 ? "(limit $maxcount)" : 0);
      } // end listing function
      );
} // end of creatureVendorItems

function creatureEquippedItems ($info)
{
  global $id;
  global $items;

  comment ('EQUIPPED ITEMS');

 // what they equip

  $totalResults = array ();
  for ($i = 1; $i <= CREATURE_EQUIP_ITEMS; $i++)
    {
    $results = dbQueryParam ("SELECT equipentry$i AS item FROM ".CREATURE_EQUIP_TEMPLATE." WHERE entry = ? AND equipentry$i <> 0", array ('i', &$id));
    if ($results)
      $totalResults [] = $results [0];
    } // for each entry

  usort($totalResults, 'item_compare');

  listItems ('NPC equips', CREATURE_EQUIP_TEMPLATE, count ($totalResults), $totalResults,
    function ($row) use ($items)
      {
      listThing ($items, $row ['item'], 'show_item');
      } // end listing function
      );
} // end of creatureEquippedItems

function creatureTrainer ($info)
{
  global $id, $spells;

  $row = $info ['row'];

  comment ('WHAT THEY TRAIN');

  // what they train

  $results = dbQueryParam ("SELECT * FROM ".TRAINER_TEMPLATE." WHERE template_entry = ?",
                            array ('i', &$row ['trainer_id']));

  usort($results, 'trainer_spell_compare');
  listItems ('NPC trains', TRAINER_TEMPLATE, count ($results), $results,
    function ($row) use ($spells)
      {
      listThing ($spells, $row ['playerspell'], 'show_spell');
      } // end listing function
      );
} // end of creatureTrainer

function creatureQuestLoot ($info)
{
  global $id, $items;

  $row = $info ['row'];

  comment ('QUEST LOOT');

  // show loot

  $loot_id = $row ['loot_id'];
  if (!$loot_id)
    $loot_id = $id;

  $creature_loot_template = CREATURE_LOOT_TEMPLATE;

  $results = dbQueryParam ("SELECT * FROM $creature_loot_template
                            WHERE entry = ? AND mincountOrRef > 0 AND ChanceOrQuestChance < 0",
                            array ('i', &$loot_id));

  $count = count ($results);
  usort($results, 'loot_item_compare');

  listItems ('Quest item loot', CREATURE_LOOT_TEMPLATE, $count, $results,
    function ($row) use ($items)
      {
      $chance = $row ['ChanceOrQuestChance'];
      listThing ($items, $row ['item'], 'show_item', -$chance . "%");
      } // end listing function
      );

} // end of creatureQuestLoot

function creatureLoot ($info)
{
 global $id, $items;

  $row = $info ['row'];

  comment ('NON-QUEST LOOT');

  // show loot

  $loot_id = $row ['loot_id'];
  if (!$loot_id)
    $loot_id = $id;

  $creature_loot_template = CREATURE_LOOT_TEMPLATE;
  $reference_loot_template = REFERENCE_LOOT_TEMPLATE;

  $results = dbQueryParam ("SELECT * FROM $creature_loot_template
                            WHERE entry = ? AND mincountOrRef > 0 AND ChanceOrQuestChance >= 0
                            ORDER BY ChanceOrQuestChance DESC",
                            array ('i', &$loot_id));

  $count = count ($results);
  usort($results, 'loot_item_compare');

  listItems ('Loot', CREATURE_LOOT_TEMPLATE, $count, $results,
    function ($row) use ($items)
      {
      listThing ($items, $row ['item'], 'show_item', roundChance ($row ['ChanceOrQuestChance']));
      } // end listing function
      );
} // end of creatureLoot

function creatureReferenceLoot ($info)
{
 global $id, $items;

  $row = $info ['row'];

  comment ('NON-QUEST LOOT');

  // show loot

  $loot_id = $row ['loot_id'];
  if (!$loot_id)
    $loot_id = $id;

  $creature_loot_template = CREATURE_LOOT_TEMPLATE;
  $reference_loot_template = REFERENCE_LOOT_TEMPLATE;

  // reference loot - the creature_loot_template table points to the reference_loot_template table
  // if the mincountOrRef field is negative, which may lead to multiple loot items for one reference
  // item - I presume to allow batches of loot to be attached to one creature_loot_template entry

  $lootResults = dbQueryParam ("SELECT $reference_loot_template.item AS refItem,
                            $creature_loot_template.ChanceOrQuestChance AS chance,
                            $reference_loot_template.mincountOrRef as minCount
                           FROM $creature_loot_template
                                INNER JOIN $reference_loot_template
                                  ON ($reference_loot_template.entry = $creature_loot_template.item)
                           WHERE $creature_loot_template.entry = ?
                                AND $creature_loot_template.mincountOrRef < 0
                           ORDER BY chance DESC",
                           array ('i', &$loot_id));


  usort ($lootResults, 'reference_item_compare');  // ordering by loot chance now
  listItems ('Reference loot', REFERENCE_LOOT_TEMPLATE, count ($lootResults), $lootResults,
    function ($row) use ($items)
      {
      $chance = $row ['chance'];
      $info2 = '';
      if ($chance < 0)
        {
        $chance = -$chance;
        $info2 = 'Quest';
        }
      listThing ($items, $row ['refItem'], 'show_item', roundChance ($chance), $info2);
      } // end listing function
      );

} // end of creatureReferenceLoot

function creaturePickpocketingLoot ($info)
{
 global $id, $items;

  $row = $info ['row'];

    // show pickpocketing loot

  comment ('PICKPOCKETING LOOT');

  $loot_id = $row ['pickpocket_loot_id'];
  if (!$loot_id)
    $loot_id = $id;

  $lootResults = dbQueryParam ("SELECT * FROM ".PICKPOCKETING_LOOT_TEMPLATE."
                               WHERE entry = ?
                               ORDER BY ChanceOrQuestChance DESC", array ('i', &$loot_id));
  usort($lootResults, 'loot_item_compare');
  listItems ('Pickpocketing loot', PICKPOCKETING_LOOT_TEMPLATE, count ($lootResults), $lootResults,
    function ($row) use ($items)
      {
      $chance = $row ['ChanceOrQuestChance'];
      listThing ($items, $row ['item'], 'show_item', roundChance ($chance));
      } // end listing function
      );

} // end of creaturePickpocketingLoot

function creatureSkinningLoot ($info)
{
 global $id, $items;

  $row = $info ['row'];

    // show skinning loot

  comment ('SKINNING LOOT');

  $loot_id = $row ['skinning_loot_id'];
  if (!$loot_id)
    $loot_id = $id;

  $lootResults = dbQueryParam ("SELECT * FROM ".SKINNING_LOOT_TEMPLATE." WHERE entry = ?", array ('i', &$loot_id));
  usort($lootResults, 'loot_item_compare');
  listItems ('Skinning loot', SKINNING_LOOT_TEMPLATE, count ($lootResults), $lootResults,
    function ($row) use ($items)
      {
      $chance = $row ['ChanceOrQuestChance'];
      listThing ($items, $row ['item'], 'show_item', roundChance ($chance));
      } // end listing function
      );

} // end of creatureSkinningLoot

function creatureDetails ($info)
  {
  global $id;

  $row = $info ['row'];

  topSection    ($info, function ($info)
      {
      global $countOfSpawnPoints;
      topLeft   ($info, 'creatureTopLeft');
      topMiddle ($info, 'creatureTopMiddle');
      if ($countOfSpawnPoints > 0)
        topRight  ($info , 'creatureTopRight');
      });

  middleSection ($info, function ($info) use ($row)
      {
      global $listedItemsCount;

      $listedItemsCount = 0;

      if ($row ['spell_list_id'])
        creatureSpells ($info);
      creatureVendorItems ($info);
      creatureTrainer ($info);
      creatureQuestLoot ($info);
      creatureLoot ($info);
      creatureReferenceLoot ($info);
      creaturePickpocketingLoot ($info);
      creatureSkinningLoot ($info);
      creatureEquippedItems ($info);
      if ($listedItemsCount == 0)
        middleDetails ($info, function ($info)
          {
          showNoSpawnPoints ('Extra information', 'No further information about this NPC.');
          });
      });

  bottomSection ($info, function ($info) use ($id)
      {
      $extras = $info ['extras'];
      showOneThing (CREATURE_TEMPLATE, 'entry', $id,
                  "Database entry for NPC", "name", $extras);
      });

  } // end of creatureDetails

function showOneCreature ()
  {
  global $id;

  if (($id === false && !repositionSearch()) || !checkID ())
    return;

  // we need the item info in this function
  $row = dbQueryOneParam ("SELECT * FROM ".CREATURE_TEMPLATE." WHERE entry = ?", array ('i', &$id));

  if (!$row)
    {
    ShowWarning ("Creature $id is not on the database");
    return;
    } // end of not finding it

  $extras = array (
        'spell_id1' => 'spell',
        'spell_id2' => 'spell',
        'spell_id3' => 'spell',
        'spell_id4' => 'spell',
        'faction'   => 'npc_faction',
        'mechanic_immune_mask' => 'mechanic_mask',
        'school_immune_mask' => 'school_mask',    // I think this might be out by one
        'inhabit_type'  => 'inhabit_type_mask',
        'movement_type' => 'movement_type',
        'flags_extra'   => 'flags_extra_mask',
        'npc_flags'     => 'npc_flags_mask',
        'rank'          => 'rank',
        'gold_min'      => 'gold',
        'gold_max'      => 'gold',
        'damage_school'    => 'spell_school',
        'base_attack_time'    => 'time',
        'ranged_attack_time'    => 'time',
        'type'    => 'creature_type',

         'static_flags'   => 'creature_static_flags',
         'school_immune_mask' => 'mask',

 //       'trainer_spell' => 'spell',   // Hmmm, must have the wrong end of the stick here

    );


  if ($row ['npc_flags'] & TRAINER_FLAG)
    {
    $extras  ['trainer_type'] = 'trainer_type';
    $extras  ['trainer_class'] = 'class';
    $extras  ['trainer_race'] = 'race';
    }

  $name = $row ['name'];
  if ($row ['subname'])
    $name .= ' <' . $row ['subname'] . '>';

  setTitle ("NPC $name");

  $limit = array (
    'entry',
    'display_id1',
    'level_min',
    'health_multiplier',
    'mana_multiplier',
    'armor_multiplier',
    'faction',
    'npc_flags',

  );

  // we pass this stuff around to the helper functions
  $info = array ('row' => $row, 'extras' => $extras, 'limit' => $limit);

  // ready to go! show the page info and work our way down into the sub-functions
  pageContent ($info, 'Creature', $name, 'creatures', 'creatureDetails', CREATURE_TEMPLATE);
  } // end of showOneCreature


function showCreatures ()
  {
  global $where, $params, $sort_order, $matches;

  $sortFields = array (
    'entry',
    'name',
    'subname',
    'level_min',
  );

  setTitle ("NPCs listing");

  if (!in_array ($sort_order, $sortFields))
    $sort_order = 'name';

  $td  = function ($s) use (&$row) { td ($row  [$s]); };

  $headings = array ('Entry', 'Name', 'Subname', 'Level');

  $results = setUpSearch ('Creatures', $sortFields, $headings);

  if (!$results)
    return;

  $searchURI = makeSearchURI (true);
  $pos = 0;

  foreach ($results as $row)
    {
    $pos++;
    echo "<tr>\n";
    $id = $row ['entry'];
    tdh ("<a href='?action=show_creature&id=$id$searchURI&pos=$pos&max=$matches'>$id</a>");
    tdh ("<a href='?action=show_creature&id=$id$searchURI&pos=$pos&max=$matches'>" . fixHTML ($row ['name']) . "</a>");
    $td ('subname');
    if ($row ['level_min'] != $row ['level_max'])
      td  ($row ['level_min'] . '-' . $row ['level_max'] );
    else
      $td ('level_min');
    showFilterColumn ($row);

    echo "</tr>\n";
    } // end of foreach

  wrapUpSearch ();

  } // end of showCreatures

function og_creature ()
  {
  global $id, $documentRoot, $executionDir;

  if ($id === false)
    repositionSearch();

  if ($id)
    $row = dbQueryOneParam ("SELECT * FROM ".CREATURE_TEMPLATE." WHERE entry = ?", array ('i', &$id));
  else
    {
    comment ("No ID for NPC");
    $id = 0;
    $row = false;
    }

  if (!$row)
    {
    comment ("NPC not on file");

    $title = 'NPC not on file';
    $image = '/creatures/missing_creature.webp';
    $description = '';
    }
  else
    {
    $title = $row ['name'];
    if ($row ['subname'])
      $title .= ' ‹' . $row ['subname'] . '›';
    $display_id = $row ['display_id1'];
    if ($display_id)
      {
      $model = $display_id . '.webp';
      $image = "/creatures/$model";
      if (!file_exists ("$documentRoot$executionDir/creatures/$model"))
        {
        comment ("$documentRoot$executionDir/creatures/$model   NOT ON FILE");
        $image = '/creatures/missing_creature.webp';
        } // end if not on file
      }
    else
      {
      comment ("No display ID for NPC");
      $image = '/creatures/missing_creature.webp';
      }
    $level_min = $row ['level_min'];
    $level_max = $row ['level_max'];
    $description = "Entry: $id, Display ID: $display_id, Level: $level_min";
    if ($level_min != $level_max)
      $description .= "–$level_max";

    $npc_flags = $row ['npc_flags'];
    if ($npc_flags)
      $description .= ', ' . expandNpcFlagsMask ($npc_flags, false);

    }

  sendOgMeta ($title, $image, 'webp', $description);

  }   // end of og_creature

?>
