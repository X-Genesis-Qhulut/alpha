<?php

/*
  Author: X'Genesis Qhulut <XGenesis-Qhulut@protonmail.com>
  Date:   August 2022

  See LICENSE for license details.
*/


function showOneSpell ($id)
  {
  showOneThing (SPELL, 'ID', $id, "Spell", "Name_enUS",
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

                ));
  } // end of showOneSpell

function showSpells ()
  {
  global $where, $params;

  echo "<h2>Spells</h2>\n";

  $td  = function ($s) use (&$row) { tdx ($row  [$s]); };
  $tdr = function ($s) use (&$row) { tdx ($row  [$s], 'tdr'); };

  setUpSearch ('ID', array ('Name_enUS', 'Description_enUS'));

  $results = dbQueryParam ("SELECT * FROM ".SPELL." $where ORDER BY Name_enUS, NameSubtext_enUS LIMIT " . QUERY_LIMIT,
            $params);

  showSearchForm ();

  if (count ($results) == 0)
    {
    echo "No matches.";
    return;
    } // end of nothing

  echo "<table>\n";
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
    lookupItems ($row,
                 array ('Reagent_1', 'Reagent_2', 'Reagent_3', 'Reagent_4', 'Reagent_5', 'Reagent_6', 'Reagent_7', 'Reagent_8'),
                 array ('ReagentCount_1', 'ReagentCount_2', 'ReagentCount_3', 'ReagentCount_4', 'ReagentCount_5', 'ReagentCount_6', 'ReagentCount_7', 'ReagentCount_8'));
    lookupItems ($row,
                 array ('EffectItemType_1',   'EffectItemType_2',   'EffectItemType_3'),
                 array ('EffectMiscValue_1',  'EffectMiscValue_2',  'EffectMiscValue_3'));
    $td ('Description_enUS');
    echo "</tr>\n";
    }
  echo "</table>\n";

  showCount ($results);

  } // end of showSpells

?>
