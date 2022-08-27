<?php

/*
  Author: X'Genesis Qhulut <XGenesis-Qhulut@protonmail.com>
  Date:   August 2022

  See LICENSE for license details.
*/

// SPELLS

function creature_compare ($a, $b)
  {
  global $creatures;
  return $creatures [$a ['npc']] <=> $creatures [$b ['npc']];
  } // end of item_compare

function fixSpellText ($s, $duration)
  {
  // numbered alternatives, eg. loaf/loaves
  // Example: "$lloaf:loaves;"    becomes "<loaf/loaves>"

  $s = preg_replace ('/\$l ?([^:]+):([^;]+);/i', '<\1/\2>', $s);
  $s = str_ireplace ('$d',  convertTimeGeneral ($duration), $s);

  $s = fixHTML ($s);
  return str_ireplace ('$b', "<br>", $s);
  } // end of fixQuestText

function simulateSpell ($id, $row)
  {
  global $spells, $items, $creatures;
  global $documentRoot, $executionDir;

  echo "<p><div class='simulate_box spell'>\n";
  echo "<h3 style='color:yellow;'>" . fixHTML ($row ['Name_enUS'] );
  if ($row ['NameSubtext_enUS'])
    echo " - " . fixHTML ($row ['NameSubtext_enUS']);
  echo "</h3>\n";

  // spell icon

  // fallback icon: INV_Misc_QuestionMark.png

  $imageRow = dbQueryOneParam ("SELECT * FROM ".SPELLICON." WHERE ID = ?", array ('i', &$row ['SpellIconID']));

  if ($imageRow)
    {
    $TextureFilename = $imageRow ['TextureFilename'] ;

    if (preg_match ("|([^\\\\]+)$|i", $TextureFilename, $matches))
      $TextureFilename = $matches [1];
    $TextureFilename  .= '.png';

    if (file_exists ("$documentRoot$executionDir/icons/$TextureFilename"))
      echo "<img src='icons/$TextureFilename' alt='Spell icon' title='" . fixHTML ($imageRow ['TextureFilename']) . "'>\n";
    else
      echo "<img src='icons/INV_Misc_QuestionMark.png' alt='Item icon' title='INV_Misc_QuestionMark'>\n";
    }
  else
    echo "<img src='icons/INV_Misc_QuestionMark.png' alt='Item icon' title='INV_Misc_QuestionMark'>\n";

  // spell type (left) and mana cost (right)
  echo "<div>\n";
  echo "<p class='item_lh'>" . expandSimple (SPELL_SCHOOLS,   $row ['School'], false)  . " Magic </p>\n";
  if ($row ['ManaCost'] )
    echo "<p class='item_rh'>" . $row ['ManaCost'] . ' ' . expandSimple (POWER_TYPES, $row ['PowerType'], false) . " </p>\n";
  echo "</div>\n";
  // clear float
  echo "<div style='clear: both;'></div>\n";

  // look up the cast time in another table
  $spellCastTimeRow = dbQueryOneParam ("SELECT * FROM ".SPELLCASTTIMES." WHERE ID = ?", array ('i', &$row ['CastingTimeIndex']));

  // cast time (left) and cooldown time (right)
  echo "<div>\n";
  echo "<span class='item_lh'>";
  if ($spellCastTimeRow ['Base'] == 0)
    echo "Instant cast\n";
  else
    echo convertTimeSeconds ($spellCastTimeRow ['Base']) . " sec cast\n";
  echo "</span><span class='item_rh'>";
  if ($row ['CategoryRecoveryTime'])
    if ($row ['CategoryRecoveryTime'] >= 60000)
      echo convertTimeMinutes($row ['CategoryRecoveryTime']) . " min cooldown\n";
    else
      echo convertTimeSeconds($row ['CategoryRecoveryTime']) . " sec cooldown\n";
  echo "</span></div>\n";
  // clear float
  echo "<div style='clear: both;'></div>\n";

  // look up the range in yet another table
  $spellRangeRow = dbQueryOneParam ("SELECT * FROM ".SPELLRANGE." WHERE ID = ?", array ('i', &$row ['RangeIndex']));

  if ($spellRangeRow ['RangeMax'] > 0)
    echo '<br>' . $spellRangeRow ['RangeMax'] . ' yd range';

  // look up the duration in yet another table again
  $spellDurationRow = dbQueryOneParam ("SELECT * FROM ".SPELLDURATION." WHERE ID = ?", array ('i', &$row ['DurationIndex']));
  $duration = $spellDurationRow ['Duration'];

  // show what it casts

  if (getCount ($row, 'EffectTriggerSpell_', 3))
    {
    echo "<p><b>Effect trigger spells:</b><br>\n";
    for ($i = 1; $i <= 3; $i++)
      if ($row ["EffectTriggerSpell_$i"])
         echo '<br>' . lookupThing ($spells, $row ["EffectTriggerSpell_$i"], 'show_spell');
    }

  // show effects

  if (getCount ($row, 'Effect_', 3))
    {
    echo "<p><b>Effects:</b>\n";
    for ($i = 1; $i <= 3; $i++)
      if ($row ["Effect_$i"])
         echo '<br>' . expandSimple (SPELL_EFFECTS, $row ["Effect_$i"], false);
    }

  // show effect auras
  if (getCount ($row, 'EffectAura_', 3))
    {
    echo "<p><b>Auras:</b>\n";
    for ($i = 1; $i <= 3; $i++)
      if ($row ["EffectAura_$i"])
         echo '<br>' . expandSimple (SPELL_AURAS, $row ["EffectAura_$i"]);
    } // end if any auras


  // reagents

  if (getCount ($row, 'Reagent_', 8))
    {
    echo "<p><b>Reagents:</b><br>\n";
    echo (lookupItems ($row,
                 array ('Reagent_1', 'Reagent_2', 'Reagent_3', 'Reagent_4', 'Reagent_5', 'Reagent_6', 'Reagent_7', 'Reagent_8'),
                 array ('ReagentCount_1', 'ReagentCount_2', 'ReagentCount_3', 'ReagentCount_4', 'ReagentCount_5', 'ReagentCount_6', 'ReagentCount_7', 'ReagentCount_8')));
    }

  // show effect items

  if (getCount ($row, 'EffectItemType_', 3))
    {
    echo "<p><b>Effect items:</b><br>\n";
    tdh (lookupItems ($row,
                 array ('EffectItemType_1',   'EffectItemType_2',   'EffectItemType_3'),
                 array ('EffectMiscValue_1',  'EffectMiscValue_2',  'EffectMiscValue_3')));
    }


  echo "<hr>\n";

  $description = $row ['Description_enUS'];

  // calculate spell roll ranges for all three effect die
  for ($i = 1; $i <= 3; $i++)
    $description = str_replace ('$s' . $i,
                  spellRoll ($row ["EffectDieSides_$i"], $row ["EffectBaseDice_$i"], $row ["EffectDicePerLevel_$i"],
                             $row ["EffectBasePoints_$i"]),
                    $description);

  // calculate aura amounts for all three effect auras
  for ($i = 1; $i <= 3; $i++)
    if ($row ["EffectAura_$i"] > 0 && $row ["EffectAuraPeriod_$i"] > 0)
      $description = str_replace ('$o' . $i,
                    spellRoll ($row ["EffectDieSides_$i"], $row ["EffectBaseDice_$i"], $row ["EffectDicePerLevel_$i"],
                               $row ["EffectBasePoints_$i"]) * $duration / $row ["EffectAuraPeriod_$i"],
                      $description);

  echo "<span style='color:yellow;'>" . fixSpellText ($description, $duration) . "</span>\n";

  echo "</div>\n";    // end of simulation box

  // ==========================================================================================================

  // ---------------- WHO TRAINS THIS -----------------

  // what they train

  $trainer_template = TRAINER_TEMPLATE;
  $creature_template = CREATURE_TEMPLATE;
  $results = dbQueryParam ("SELECT * FROM $trainer_template
                          LEFT JOIN $creature_template ON $trainer_template.template_entry = $creature_template.trainer_id
                          WHERE $trainer_template.playerspell = ? AND entry <= ".MAX_CREATURE." ORDER BY name, subname",
                            array ('i', &$id));


  listItems ('NPCs that train this spell', 'alpha_world.trainer_id', count ($results), $results,
    function ($row) use ($creatures)
      {
      listThing ($creatures, $row ['entry'], 'show_creature');
      });

  // ---------------- WHAT ITEMS CAST THIS -----------------

  $results = dbQueryParam ("SELECT * FROM ".ITEM_TEMPLATE.
                           " WHERE (spellid_1 = ? OR spellid_2 = ? OR spellid_3 = ? OR spellid_4 = ? OR spellid_5 = ?)
                           AND ignored = 0 ORDER BY name",
                            array ('iiiii', &$id, &$id, &$id, &$id, &$id));


  listItems ('Items that cast this spell', 'alpha_world.item_template', count ($results), $results,
    function ($row) use ($items)
      {
      listThing ($items, $row ['entry'], 'show_item');
      });


  // ---------------- WHAT CREATURES CAST THIS -----------------

  // build a list of all 8 possible spell slots in CREATURE_SPELLS
  $where = '';
  $params = array ('');
  for ($i = 1; $i <= 8; $i++)
    {
    $where .= " spellId_$i = ?";
    if ($i < 8)
      $where .= ' OR ';
    $params [0] .= 'i';
    $params [] = &$id;
    }

  // find entries in CREATURE_SPELLS which refer to this spell (in one of the 8 spots)
  $creature_spells = CREATURE_SPELLS;
  $results = dbQueryParam ("SELECT $creature_template.entry AS npc FROM $creature_spells
          INNER JOIN $creature_template ON ($creature_template.spell_list_id = $creature_spells.entry)
          WHERE ($where) AND $creature_template.entry <= " . MAX_CREATURE, $params);


  listItems ('NPCs that cast this spell', 'alpha_world.creature_spells', count ($results), $results,
    function ($row) use ($creatures)
      {
      listThing ($creatures, $row ['npc'], 'show_creature');
      });

  } // end of simulateSpell

function showOneSpell ($id)
  {
  showOneThing (SPELL, 'alpha_dbc.spell', 'ID', $id, "Spell", "Name_enUS",
                array (
                  'Reagent_1' => 'item',
                  'Reagent_2' => 'item',
                  'Reagent_3' => 'item',
                  'Reagent_4' => 'item',
                  'Reagent_5' => 'item',
                  'Reagent_6' => 'item',
                  'Reagent_7' => 'item',
                  'Reagent_8' => 'item',
                  'EffectItemType_1' => 'item',
                  'EffectItemType_2' => 'item',
                  'EffectItemType_3' => 'item',
                  'PowerType' => 'power_type',
                  'School' => 'spell_school',
                  'EffectTriggerSpell_1' => 'spell',
                  'EffectTriggerSpell_2' => 'spell',
                  'EffectTriggerSpell_3' => 'spell',
                  'Effect_1' => 'spell_effect',
                  'Effect_2' => 'spell_effect',
                  'Effect_3' => 'spell_effect',
                  'Targets' => 'spell_target_type_mask',
                  'Attributes' => 'spell_attributes_mask',
                  'AttributesEx' => 'spell_attributes_ex_mask',
                  'EquippedItemClass' => 'item_class',
                  'EquippedItemSubclass' => 'item_subclass_mask',
                  'EffectAura_1' => 'spell_aura',
                  'EffectAura_2' => 'spell_aura',
                  'EffectAura_3' => 'spell_aura',
                  'ImplicitTargetA_1' => 'spell_implicit_target',
                  'ImplicitTargetA_2' => 'spell_implicit_target',
                  'ImplicitTargetA_3' => 'spell_implicit_target',
                  'ImplicitTargetB_1' => 'spell_implicit_target',
                  'ImplicitTargetB_2' => 'spell_implicit_target',
                  'ImplicitTargetB_3' => 'spell_implicit_target',
                  'InterruptFlags' => 'spell_interrupt_flags',

                ), 'simulateSpell');
  } // end of showOneSpell

function showSpells ()
  {
  global $where, $params, $sort_order;

  $sortFields = array (
    'ID',
    'Name_enUS',
    'NameSubtext_enUS',
    'School',
    'Category',
    'PowerType',
    'Description_enUS',
  );

  if (!in_array ($sort_order, $sortFields))
    $sort_order = 'Name_enUS';



  echo "<h2>Spells</h2>\n";

  $td  = function ($s) use (&$row) { tdx ($row  [$s]); };
  $tdr = function ($s) use (&$row) { tdx ($row  [$s], 'tdr'); };

  setUpSearch ('ID', array ('Name_enUS', 'Description_enUS'));

  $offset = getQueryOffset(); // based on the requested page number

  $results = dbQueryParam ("SELECT * FROM ".SPELL." $where ORDER BY $sort_order, ID LIMIT $offset, " . QUERY_LIMIT,
            $params);

  if (!showSearchForm ($sortFields, $results, SPELL, $where))
    return;

  echo "<table class='search_results'>\n";
  headings (array ('ID', 'Name', 'Subtext', 'School', 'Category',
                   'Power Type', 'Reagents', 'Effect Item', 'Description'));
  foreach ($results as $row)
    {
    echo "<tr>\n";
    $id = $row ['ID'];
    tdhr ("<a href='?action=show_spell&id=$id'>$id</a>");
    $td ('Name_enUS');
    $td ('NameSubtext_enUS');
    $school = $row ['School'];
    tdx ("$school: " . SPELL_SCHOOLS [$school]);
    $tdr ('Category');
    $powerType = $row ['PowerType'];
    tdx ("$powerType: " . POWER_TYPES [$powerType]);
    tdh (lookupItems ($row,
                 array ('Reagent_1', 'Reagent_2', 'Reagent_3', 'Reagent_4', 'Reagent_5', 'Reagent_6', 'Reagent_7', 'Reagent_8'),
                 array ('ReagentCount_1', 'ReagentCount_2', 'ReagentCount_3', 'ReagentCount_4', 'ReagentCount_5', 'ReagentCount_6', 'ReagentCount_7', 'ReagentCount_8')));
    tdh (lookupItems ($row,
                 array ('EffectItemType_1',   'EffectItemType_2',   'EffectItemType_3'),
                 array ('EffectMiscValue_1',  'EffectMiscValue_2',  'EffectMiscValue_3')));
    $td ('Description_enUS');
    showFilterColumn ($row);
    echo "</tr>\n";
    }
  echo "</table>\n";

  showCount ($results);

  } // end of showSpells

?>
