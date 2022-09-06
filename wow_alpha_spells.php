<?php

/*
  Author: X'Genesis Qhulut <XGenesis-Qhulut@protonmail.com>
  Date:   August 2022

  See LICENSE for license details.
*/

// SPELLS

// Interesting info: https://tswow.github.io/tswow-wiki/introduction/05-custom-spell/

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

function showSpellIcon ($row)
{
  global $documentRoot, $executionDir;

  comment ('SPELL ICON');

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


} // end of showSpellIcon

function simulateSpell ($row)
  {
  global $id, $spells, $items, $creatures;

  comment ('SPELL SIMULATION');

  echo "<div class='simulate_box spell'>\n";
  echo "<h3 style='color:yellow;'>" . fixHTML ($row ['Name_enUS'] );
  if ($row ['NameSubtext_enUS'])
    echo " — " . fixHTML ($row ['NameSubtext_enUS']);
  echo "</h3>\n";

  // spell icon
  showSpellIcon ($row);

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

  if (getCount ($row, 'EffectTriggerSpell_', SPELL_EFFECT_TRIGGER_SPELLS))
    {
    echo "<p><b>Effect trigger spells:</b><br>\n";
    for ($i = 1; $i <= SPELL_EFFECT_TRIGGER_SPELLS; $i++)
      if ($row ["EffectTriggerSpell_$i"])
         echo '<br>' . lookupThing ($spells, $row ["EffectTriggerSpell_$i"], 'show_spell');
    }

  // show effects

  if (getCount ($row, 'Effect_', SPELL_EFFECTS_COUNT))
    {
    echo "<p><b>Effects:</b>\n";
    for ($i = 1; $i <= SPELL_EFFECTS_COUNT; $i++)
      if ($row ["Effect_$i"])
         echo '<br>' . expandSimple (SPELL_EFFECTS, $row ["Effect_$i"], false);
    }

  // show effect auras
  if (getCount ($row, 'EffectAura_', SPELL_EFFECT_AURAS))
    {
    echo "<p><b>Auras:</b>\n";
    for ($i = 1; $i <= SPELL_EFFECT_AURAS; $i++)
      if ($row ["EffectAura_$i"])
         echo '<br>' . expandSimple (SPELL_AURAS, $row ["EffectAura_$i"]);
    } // end if any auras


  // reagents

  if (getCount ($row, 'Reagent_', SPELL_REAGENTS))
    {
    echo "<p><b>Reagents:</b><br>\n";
    for ($i = 1; $i <= SPELL_REAGENTS; $i++)
      {
      $reagents [] = "Reagent_$i";
      $reagentCounts [] = "ReagentCount_$i";
      }
    echo (lookupItems ($row,$reagents, $reagentCounts));
    }

  // show effect items

  if (getCount ($row, 'EffectItemType_', SPELL_EFFECT_ITEM_TYPES))
    {
    echo "<p><b>Effect items:</b><br>\n";

    $effectTypes = array ();
    $effectMiscValues = array ();
    for ($i = 1; $i <= SPELL_EFFECT_ITEM_TYPES; $i++)
      {
      $effectTypes [] = "EffectItemType_$i";
      $effectMiscValues [] = "EffectMiscValue_$i";
      }

    tdh (lookupItems ($row, $effectTypes, $effectMiscValues));
    }

  // description

  $description = $row ['Description_enUS'];

  if ($description)
    {
    echo "<hr>\n";

    // calculate spell roll ranges for all three effect die
    for ($i = 1; $i <= SPELL_EFFECT_DICE; $i++)
      $description = str_replace ('$s' . $i,
                    spellRoll ($row ["EffectDieSides_$i"], $row ["EffectBaseDice_$i"], $row ["EffectDicePerLevel_$i"],
                               $row ["EffectBasePoints_$i"]),
                      $description);

    // calculate aura amounts for all three effect auras
    for ($i = 1; $i <= SPELL_EFFECT_AURAS; $i++)
      if ($row ["EffectAura_$i"] > 0 && $row ["EffectAuraPeriod_$i"] > 0)
        $description = str_replace ('$o' . $i,
                      spellRoll ($row ["EffectDieSides_$i"], $row ["EffectBaseDice_$i"], $row ["EffectDicePerLevel_$i"],
                                 $row ["EffectBasePoints_$i"]) * $duration / $row ["EffectAuraPeriod_$i"],
                        $description);

    echo "<span style='color:yellow;'>" . fixSpellText ($description, $duration) . "</span>\n";
    } // end of having a description

  // spell visuals

  $SpellVisualID = $row ['SpellVisualID'];
  if ($SpellVisualID)
    {
    echo "<hr><b style='color:deepskyblue;'>Visual effects</b>\n";
    $visualRow = dbQueryOneParam ("SELECT * FROM ".SPELLVISUAL." WHERE ID = ?", array ('i', &$SpellVisualID ));
    if (!$visualRow)
      echo "Visual ID $SpellVisualID not on file\n";
    else
      {
      $kits = array ('PrecastKit', 'CastKit', 'ImpactKit', 'StateKit', 'ChannelKit', 'AreaKit');
      foreach ($kits as $kit)
        {
        $kitID = $visualRow [$kit];
        if ($kitID)
          {
          echo "<br><b>" . fixHTML (str_replace ('Kit', ' Kit', $kit)) . "</b>: $kitID\n";
          // now look up the animation for this kit
          $animRow = dbQueryOneParam ("SELECT T1.Anim, T1.KitType, T2.Name FROM ".SPELLVISUALKIT." AS T1
                                      LEFT JOIN ".SPELLVISUALANIMNAME." AS T2
                                      ON (T2.AnimID = T1.Anim)
                                      WHERE T1.ID = ?", array ('i', &$kitID ));
          $animID = $animRow ['Anim'] . " (" . $animRow ['KitType'] . ") " . $animRow ['Name'];
          echo " - animation: $animID";
          }  // end of this kit in use
        } // end of each possible kit

      $AreaModel = $visualRow ['AreaModel'];

      if ($AreaModel)
        {
        echo "<br><b>Area model:</b>\n";
        $areaRow = dbQueryOneParam ("SELECT * FROM ".SPELLVISUALEFFECTNAME." WHERE ID = ?", array ('i', &$AreaModel ));
        if ($areaRow)
          echo fixHTML ($areaRow ['FileName']);
        else
          echo "Not found.";
        }

      } // end of spell visual on file

    } // end of having a visual ID


  echo "</div>\n";    // end of simulation box


  } // end of simulateSpell

function spellTrainers ()
{
  global $id, $creatures;

  comment ('WHO TRAINS THIS');

  // what they train

  $trainer_template = TRAINER_TEMPLATE;
  $creature_template = CREATURE_TEMPLATE;
  $results = dbQueryParam ("SELECT * FROM $trainer_template
                          LEFT JOIN $creature_template ON $trainer_template.template_entry = $creature_template.trainer_id
                          WHERE $trainer_template.playerspell = ? AND entry <= ".MAX_CREATURE." ORDER BY name, subname",
                            array ('i', &$id));


  listItems ('NPCs that train this spell', 'alpha_world.trainer_template', count ($results), $results,
    function ($row) use ($creatures)
      {
      listThing ($creatures, $row ['entry'], 'show_creature');
      });
} // end of spellTrainers

function spellItemCasters ()
{
  global $id, $items;

  comment ('WHAT ITEMS CAST THIS');

  $results = dbQueryParam ("SELECT * FROM ".ITEM_TEMPLATE.
                           " WHERE (spellid_1 = ? OR spellid_2 = ? OR spellid_3 = ? OR spellid_4 = ? OR spellid_5 = ?)
                           AND ignored = 0 ORDER BY name",
                            array ('iiiii', &$id, &$id, &$id, &$id, &$id));


  listItems ('Items that cast this spell', 'alpha_world.item_template', count ($results), $results,
    function ($row) use ($items)
      {
      listThing ($items, $row ['entry'], 'show_item');
      });


} // end of spellItemCasters

function spellCasters ()
{
    global $id, $creatures;

    $creature_template = CREATURE_TEMPLATE;

  comment ('WHAT CREATURES CAST THIS');

  // build a list of all 8 possible spell slots in CREATURE_SPELLS
  $where = '';
  $params = array ('');
  for ($i = 1; $i <= CREATURE_SPELLS_COUNT; $i++)
    {
    $where .= " spellId_$i = ?";
    if ($i < CREATURE_SPELLS_COUNT)
      $where .= ' OR ';
    $params [0] .= 'i';
    $params [] = &$id;
    }

  // find entries in CREATURE_SPELLS which refer to this spell (in one of the CREATURE_SPELLS_COUNT (8) spots)
  $creature_spells = CREATURE_SPELLS;
  $results = dbQueryParam ("SELECT $creature_template.entry AS npc FROM $creature_spells
          INNER JOIN $creature_template ON ($creature_template.spell_list_id = $creature_spells.entry)
          WHERE ($where) AND $creature_template.entry <= " . MAX_CREATURE, $params);

  listItems ('NPCs that cast this spell', 'alpha_world.creature_spells', count ($results), $results,
    function ($row) use ($creatures)
      {
      listThing ($creatures, $row ['npc'], 'show_creature');
      });

} // end of spellCasters

function spellTopLeft ($info)
{
  global $id;
  global $documentRoot, $executionDir;

  $row = $info ['row'];
  $extras = $info ['extras'];
  $limit = $info ['limit'];

  boxTitle ('General');

  comment ('ICON');
  showSpellIcon ($row);

  echo "<p></p>\n";

  comment ('SHORT LISTING OF FIELDS');
  showOneThing (SPELL, 'alpha_dbc.spell', 'ID', $id, "", "Name_enUS", $extras, $limit);

} // end of spellTopLeft

function spellTopMiddle ($info)
{
  global $id;
  global $documentRoot, $executionDir;

  $row = $info ['row'];
  $extras = $info ['extras'];
  $limit = $info ['limit'];

  simulateSpell ($row);


}   // end of spellTopMiddle


function spellDetails ($info)
  {
  global $id;

  $row = $info ['row'];

  topSection    ($info, function ($info) use ($id, $row)
      {
      topLeft   ($info, 'spellTopLeft');
      topMiddle ($info, 'spellTopMiddle');
      });

  middleSection ($info, function ($info) use ($id, $row)
      {
      spellTrainers ();
      spellItemCasters ();
      spellCasters ();
      });

  bottomSection ($info, function ($info) use ($id)
      {
      $extras = $info ['extras'];
      comment ('SPELL DETAILS');
      showOneThing (SPELL, 'alpha_dbc.spell', 'ID', $id, "Spell details", "Name_enUS", $extras);
      });

  } // end of spellDetails


function showOneSpell ()
  {
  global $id;

  comment ('SHOWING ONE SPELL');

 // we need the item info in this function
  $row = dbQueryOneParam ("SELECT * FROM ".SPELL." WHERE ID = ?", array ('i', &$id));


  $extras = array (
                  'PowerType'             => 'power_type',
                  'School'                => 'spell_school',
                  'Targets'               => 'spell_target_type_mask',
                  'Attributes'            => 'spell_attributes_mask',
                  'AttributesEx'          => 'spell_attributes_ex_mask',
                  'EquippedItemClass'     => 'item_class',
                  'EquippedItemSubclass'  => 'item_subclass_mask',
                  'InterruptFlags'        => 'spell_interrupt_flags',
                  'Name_Mask'             => 'mask',
                  'NameSubtext_Mask'      => 'mask',
                  'Description_Mask'      => 'mask',
                  'SpellVisualID'         => 'spell_visual',
                );

  for ($i = 1; $i <= SPELL_REAGENTS; $i++)
    $extras ["Reagent_$i"] = 'item';
  for ($i = 1; $i <= SPELL_EFFECT_ITEM_TYPES; $i++)
    $extras ["EffectItemType_$i"] = 'item';
  for ($i = 1; $i <= SPELL_EFFECT_TRIGGER_SPELLS; $i++)
    $extras ["EffectTriggerSpell_$i"] = 'spell';
  for ($i = 1; $i <= SPELL_EFFECTS_COUNT; $i++)
    $extras ["Effect_$i"] = 'spell_effect';
  for ($i = 1; $i <= SPELL_EFFECT_AURAS; $i++)
    $extras ["EffectAura_$i"] = 'spell_aura';
  for ($i = 1; $i <= SPELL_IMPLICIT_TARGETS; $i++)
    {
    $extras ["ImplicitTargetA_$i"] = 'spell_implicit_target';
    $extras ["ImplicitTargetB_$i"] = 'spell_implicit_target';
    }

  $name = $row ['Name_enUS'];

  if ($row ['NameSubtext_enUS'])
    $name .= ' <' . $row ['NameSubtext_enUS'] . '>';




  $limit = array (
    'ID',
    'School',
    'ImplicitTargetA_1',
    'StartRecoveryTime',
    'SpellPriority',
    'SpellIconId',
    );

 // we pass this stuff around to the helper functions
  $info = array ('row' => $row, 'extras' => $extras, 'limit' => $limit);

  // ready to go! show the page info and work our way down into the sub-functions
  pageContent ($info, 'Spell', $name, 'spells', 'spellDetails', SPELL);

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

  $td  = function ($s) use (&$row) { tdx ($row  [$s]); };
  $tdr = function ($s) use (&$row) { tdx ($row  [$s], 'tdr'); };

  $results = setUpSearch ('Spells',
                          $sortFields,            // fields we can sort on
                          array ('ID', 'Name', 'Subtext', 'School',
                                 'Power Type', 'Reagents', 'Effect Item', 'Description'),    // headings
                          'ID',                // key
                          array ('Name_enUS', 'Description_enUS'),  // searchable fields
                          SPELL,          // table
                          '');     // extra conditions

  if (!$results)
    return;

  $searchURI = makeSearchURI (true);

  foreach ($results as $row)
    {
    echo "<tr>\n";
    $id = $row ['ID'];
    tdhr ("<a href='?action=show_spell&id=$id$searchURI'>$id</a>");
    $td ('Name_enUS');
    $td ('NameSubtext_enUS');
    tdx (expandSimple (SPELL_SCHOOLS, $row ['School'], false));
//    $tdr ('Category');
    tdx (expandSimple (POWER_TYPES, $row ['PowerType'], false));
    $reagents = array ();
    $reagentCounts = array ();
    for ($i = 1; $i <= SPELL_REAGENTS; $i++)
      {
      $reagents [] = "Reagent_$i";
      $reagentCounts [] = "ReagentCount_$i";
      }
    tdh (lookupItems ($row, $reagents, $reagentCounts));
    $effectTypes = array ();
    $effectMiscValues = array ();
    for ($i = 1; $i <= SPELL_EFFECT_ITEM_TYPES; $i++)
      {
      $effectTypes [] = "EffectItemType_$i";
      $effectMiscValues [] = "EffectMiscValue_$i";
      }
    tdh (lookupItems ($row, $effectTypes, $effectMiscValues));
    $td ('Description_enUS');
    showFilterColumn ($row);
    echo "</tr>\n";
    }

  wrapUpSearch ();

  } // end of showSpells

?>
