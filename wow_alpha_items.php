<?php

/*
  Author: X'Genesis Qhulut <XGenesis-Qhulut@protonmail.com>
  Date:   August 2022

  See LICENSE for license details.
*/


// ITEMS

function lookupItemHelper ($id, $count)
  {
  global $items;
  $countStr = '';
  if ($count > 1)
    $countStr = "&nbsp;x$count";

  $link = "<a href='?action=show_item&id=$id'>$id</a>";
  if (!$id)
    return ('');

  if (!isset ($items [$id]))
    return ("$id (not found)$countStr");

  return ("$link: " . $items  [$id] . $countStr);

  } // end of lookupItem

function lookupItem ($id, $count)
  {
  tdh (lookupItemHelper ($id, $count));
  } // end of lookupItem

function lookupItems ($row, $items, $counts)
  {
  $s = '';
  foreach ($items as $n => $item)
    {
    if ($row [$item])
      {
      if ($s)
        $s .= '<br>';
      $s .= lookupItemHelper ($row [$item], $row [$counts [$n]]);
      }
    } // end of foreach

  return $s;
  } // end of lookupItems

function simulateItem ($id, $row)
  {
  global $game_objects, $creatures, $zones, $quests, $spells, $skills;
  global $documentRoot, $executionDir;

 // simulate item

  echo "<p><div class='item'>\n";
  $color = ITEM_QUALITY_COLOR [$row ['quality']];

  echo "<h3 style='color:$color;'>" . fixHTML ($row ['name']) . "</h3>\n";

  // image

  // fallback icon: INV_Misc_QuestionMark.png

  $imageRow = dbQueryOneParam ("SELECT * FROM ".ITEMDISPLAYINFO." WHERE ID = ?", array ('i', &$row ['display_id']));

  if ($imageRow)
    {
    $icon = $imageRow ['InventoryIcon'] . '.png';
    if (file_exists ("$documentRoot$executionDir/icons/$icon"))
      echo "<img src='icons/$icon' alt='Item icon' title='" . fixHTML ($imageRow ['InventoryIcon']) . "'>\n";
    else
      echo "<img src='icons/INV_Misc_QuestionMark.png' alt='Item icon' title='INV_Misc_QuestionMark'>\n";
    }
  else
    echo "<img src='icons/INV_Misc_QuestionMark.png' alt='Item icon' title='INV_Misc_QuestionMark'>\n";

  echo '<p><b>' . ITEM_CLASS [$row ['class']] . "</b><br>\n";
  if ($row ['subclass'])
    echo ITEM_SUBCLASSES  [$row ['class']]  [$row ['subclass']] . "<br>\n";
  echo INVENTORY_TYPE [$row ['inventory_type']] . "<br>\n";
  echo 'Level ' . $row ['item_level'];
  if ($row ['required_level'])
    echo ' (Min ' . $row ['required_level'] . ")\n";
  echo "<br>\n";

  if ($row ['required_skill'])
    echo 'Requires ' . expandSimple ($skills, $row ['required_skill'], false) .
         ' (' . $row ['required_skill_rank'] . ")\n";

  // damage

  echo "<div>\n";
  for ($i = 1; $i <= 5; $i++)
    if ($row ["dmg_min$i"])
      echo "<p class='item_lh'>" . $row ["dmg_min$i"] . ' - ' . $row ["dmg_max$i"]  . " Damage</p>\n";

  if ($row ['delay'])
    echo "<p class='item_rh'>Speed: " . $row ['delay'] / 1000 . "</p>\n";
  echo "</div>\n";
  // clear float
  echo "<div style='clear: both;'></div>\n";

  // stats
  echo "<p>\n";
  for ($i = 1; $i <= 10; $i++)
    if ($row ["stat_value$i"])
      echo addSign ($row ["stat_value$i"]) . ' ' . ITEM_STATS [$row ["stat_type$i"]] . "<br>\n";

  // resistances
  if ($row ['holy_res'])
    echo addSign ($row ['holy_res']) . " holy resistance<br>\n";
  if ($row ['frost_res'])
    echo addSign ($row ['frost_res']) . " frost resistance<br>\n";
  if ($row ['fire_res'])
    echo addSign ($row ['fire_res']) . " fire resistance<br>\n";
  if ($row ['nature_res'])
    echo addSign ($row ['nature_res']) . " nature resistance<br>\n";
  if ($row ['shadow_res'])
    echo addSign ($row ['shadow_res']) . " shadow resistance<br>\n";
  if ($row ['arcane_res'])
    echo addSign ($row ['arcane_res']) . " arcane resistance<br>\n";

  echo "<p>\n";
  if ($row ['armor'])
    echo $row ['armor'] . " Armor<br>\n";
  if ($row ['block'])
    echo $row ['block'] . " Block<br>\n";

  $count = 0;
  for ($i = 1; $i <= 5; $i++)
    if ($row ["spellid_$i"])
      $count++;

  if ($count)
    echo "<b>Casts:</b><br>\n";

  for ($i = 1; $i <= 5; $i++)
    if ($row ["spellid_$i"])
      echo lookupThing ($spells, $row ["spellid_$i"], 'show_spell') . "\n";


  echo "<div>\n";
  echo "<p class='item_lh'>Buy price: " . convertGold ($row ['buy_price'])  . "</p>\n";
  if ($row ['stackable'])
    echo "<p class='item_rh'>Stackable: " . $row ['stackable']  . "</p>\n";
  echo "</div>\n";

  // clear float
  echo "<div style='clear: both;'></div>\n";

  if ($row ['bonding'])
    echo BONDING [$row ['bonding']];

  // end of item box
  echo "</div>\n";


// =============================================================================================================

 // who sells it
  $results = dbQueryParam ("SELECT * FROM ".NPC_VENDOR." WHERE item = ? AND entry <= " . MAX_CREATURE, array ('i', &$id));
  if (count ($results) > 0)
    {
    echo "<h2>Sold by</h2><ul>\n";
    foreach ($results as $vendorRow)
      {
      listThing ('', $creatures, $vendorRow ['entry'], 'show_creature');
      $maxcount = $vendorRow ['maxcount'];
      if ($maxcount  > 0)
        echo (" (limit $maxcount)");
      } // for each quest finisher NPC
    echo "</ul>\n";
    }


  // who drops it

  $creature_loot_template = CREATURE_LOOT_TEMPLATE;
  $reference_loot_template = REFERENCE_LOOT_TEMPLATE;
  $creature_template = CREATURE_TEMPLATE;

  $results = dbQueryParam ("SELECT $creature_template.entry AS npc,
                            $creature_loot_template.ChanceOrQuestChance AS chance,
                            $creature_loot_template.item AS item
                            FROM $creature_loot_template INNER JOIN $creature_template ON
                            ($creature_loot_template.entry = IF($creature_template.loot_id > 0, $creature_template.loot_id, $creature_template.entry))
                            WHERE $creature_loot_template.item = ?
                            AND $creature_loot_template.mincountOrRef >= 0
                            AND $creature_template.entry <= " . MAX_CREATURE . "
                            ORDER BY name", array ('i', &$id));

  // count quest items - they have a negative drop chance
  $count = 0;
  foreach ($results as $creatureRow)
    if ($creatureRow ['chance'] < 0)
        $count++;

 // who drops it
  if ($count > 0)
    {
    echo "<h2>Quest item dropped by</h2><ul>\n";
    foreach ($results as $creatureRow)
      {
      if ($creatureRow ['chance'] < 0)
        {
        listThing ('', $creatures, $creatureRow ['npc'], 'show_creature');
        echo ' - ' . -$creatureRow ['chance'] . '%';
        }
      } // for each quest finisher NPC
    echo "</ul>\n";
    }

  // non-quest items
  $count = 0;
  foreach ($results as $creatureRow)
    if ($creatureRow ['chance'] > 0)
        $count++;

  $running_count = 0;
  if ($count > 0)
    {
    echo "<h2>Item dropped by</h2>";

    // make two columns if there are a lot of them
    if ($count > TWO_COLUMN_SPLIT)
      {
      echo "<div class='one_thing_container'>\n";
      echo "<div class='one_thing_section'>\n";
      }
    echo "<ul>\n";

    foreach ($results as $creatureRow)
      {
      if ($creatureRow ['chance'] > 0)
        {
        listThing ('', $creatures, $creatureRow ['npc'], 'show_creature');
        echo ' - ' . $creatureRow ['chance'] . "%\n";
        $running_count++;
        // start the second column half-way through
        if ($count > TWO_COLUMN_SPLIT &&  $running_count == intval ($count / 2))
          {
          echo "</ul></div>\n";
          echo "<div class='one_thing_section'><ul>\n";
          }
        }
      } // for each quest finisher NPC
    echo "</ul>\n";

    // end of two columns
    if ($count > TWO_COLUMN_SPLIT)
      echo "</div></div>\n";

    }


  // now the reference items <sigh>


  $lootResults = dbQueryParam (
      "SELECT $creature_template.entry AS npc,
              $reference_loot_template.item AS refItem,
              $creature_loot_template.ChanceOrQuestChance AS chance,
              $reference_loot_template.mincountOrRef as minCount
     FROM $creature_loot_template
          INNER JOIN $reference_loot_template
            ON $reference_loot_template.entry = $creature_loot_template.item
          INNER JOIN $creature_template ON
            ($creature_loot_template.entry = IF($creature_template.loot_id > 0, $creature_template.loot_id, $creature_template.entry))
          WHERE $reference_loot_template.item = ?
          AND $creature_loot_template.mincountOrRef < 0
          AND $creature_template.entry <= " . MAX_CREATURE . "
          ORDER BY $creature_template.name", array ('i', &$id));

  $running_count = 0;
  $count = count ($lootResults);
  if ($count > 0)
    {
    echo "<h2 title='Table: alpha_world.reference_loot_template'>NPCs that drop this as reference loot</h2>\n";

    // make two columns if there are a lot of them
    if ($count > TWO_COLUMN_SPLIT)
      {
      echo "<div class='one_thing_container'>\n";
      echo "<div class='one_thing_section'>\n";
      }

    echo "<ul>\n";

    foreach ($lootResults as $lootRow)
      {
      listThing ('', $creatures, $lootRow ['npc'], 'show_creature');
      echo  ' - ' .  $lootRow ['chance'] . "%\n";
      $running_count++;
      // start the second column half-way through
      if ($count > TWO_COLUMN_SPLIT && $running_count == intval ($count / 2))
        {
        echo "</ul></div>\n";
        echo "<div class='one_thing_section'><ul>\n";
        }
      } // for each loot item

    echo "</ul>\n";
    // end of two columns
    if ($count > TWO_COLUMN_SPLIT)
      echo "</div></div>\n";
    }

  } // end of simulateItem

function showOneItem ($id)
  {
  global $quests, $creatures;

 // we need the item info in this function
  $row = dbQueryOneParam ("SELECT * FROM ".ITEM_TEMPLATE." WHERE entry = ?", array ('i', &$id));

  $extras =  array (
        'required_skill' => 'skill',
        'spellid_1' => 'spell',
        'spellid_2' => 'spell',
        'spellid_3' => 'spell',
        'spellid_4' => 'spell',
        'spellid_5' => 'spell',
        'class'     => 'item_class',
        'subclass'     => 'item_subclass',
        'start_quest' => 'quest',
        'inventory_type' => 'inventory_type',
        'flags' => 'item_flags_mask',
        'buy_price' => 'gold',
        'sell_price' => 'gold',
        'min_money_loot' => 'gold',
        'max_money_loot' => 'gold',
        'bonding' => 'bonding',

    );

  for ($i = 1; $i <= 10; $i++)
    if ($row ["stat_value$i"])
      $extras ["stat_type$i"] = 'item_stats';

  for ($i = 1; $i <= 5; $i++)
    {
    if ($row ["spellcooldown_$i"])
      $extras ["spellcooldown_$i"] = 'time';
    if ($row ["spellcategorycooldown_$i"])
      $extras ["spellcategorycooldown_$i"] = 'time';
    }

  showOneThing (ITEM_TEMPLATE, 'alpha_world.item_template', 'entry', $id, "Item", "name", $extras, 'simulateItem');

  } // end of showOneItem


function showItems ()
  {
  global $where, $params, $sort_order;

  $sortFields = array (
    'entry',
    'class',
    'subclass',
    'name',
    'description',
  );

  if (!in_array ($sort_order, $sortFields))
    $sort_order = 'name';

  echo "<h2>Items</h2>\n";

  $td  = function ($s) use (&$row) { tdx ($row  [$s]); };
  $tdr = function ($s) use (&$row) { tdx ($row  [$s], 'tdr'); };

  setUpSearch ('entry', array ('name', 'description'));

  $offset = getQueryOffset(); // based on the requested page number

  $results = dbQueryParam ("SELECT * FROM ".ITEM_TEMPLATE." $where AND ignored = 0 ORDER BY $sort_order LIMIT $offset, " . QUERY_LIMIT,
                    $params);

  if (!showSearchForm ($sortFields, $results, ITEM_TEMPLATE, "$where AND ignored = 0"))
    return;

  echo "<table class='search_results'>\n";
  headings (array ('Entry', 'Class', 'Subclass', 'Name', 'Description'));
  foreach ($results as $row)
    {
    echo "<tr>\n";
    $id = $row ['entry'];
    tdhr ("<a href='?action=show_item&id=$id'>$id</a>");
    $item_class = $row ['class'];
    tdx ("$item_class: " . ITEM_CLASS [$item_class]);
    $item_subclass = $row ['subclass'];
    tdx ("$item_subclass : " . ITEM_SUBCLASSES [$item_class] [$item_subclass]);
    $td  ('name');
    $td  ('description');
    showFilterColumn ($row);
    echo "</tr>\n";
    }
  echo "</table>\n";

  showCount ($results);

  } // end of showItems
  ?>
