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

function simulateItem ($row)
  {
  global $id, $game_objects, $creatures, $zones, $quests, $spells, $skills;
  global $documentRoot, $executionDir;

  comment ('SIMULATED ITEM CONTAINER');

  boxTitle ('In-game view');
  echo "<div class='simulate_box item'>";

 // simulate item

  $color = ITEM_QUALITY_COLOR [$row ['quality']];

  echo "<h3 style='color:$color;'>" . fixHTML ($row ['name']) . "</h3>\n";

  // image

  // fallback icon: INV_Misc_QuestionMark.png

  comment ('ICON');

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

  comment ('CLASS, SUBCLASS, LEVEL, TYPE');

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

  comment ('DAMAGE');

  // damage

  echo "<div>\n";
  for ($i = 1; $i <= ITEM_DAMAGES; $i++)
    if ($row ["dmg_min$i"])
      echo "<p class='item_lh'>" . $row ["dmg_min$i"] . ' – ' . $row ["dmg_max$i"]  . " Damage</p>\n";

  if ($row ['delay'])
    echo "<p class='item_rh'>Speed: " . $row ['delay'] / 1000 . "</p>\n";
  echo "</div>\n";
  // clear float
  echo "<div style='clear: both;'></div>\n";

  comment ('STATS');

  // stats
  echo "<p>\n";
  for ($i = 1; $i <= ITEM_STATS_COUNT; $i++)
    if ($row ["stat_value$i"])
      echo addSign ($row ["stat_value$i"]) . ' ' . ITEM_STATS [$row ["stat_type$i"]] . "<br>\n";

  comment ('RESISTANCES');

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


  comment ('ARMOR');

  echo "<p>\n";
  if ($row ['armor'])
    echo $row ['armor'] . " Armor<br>\n";
  if ($row ['block'])
    echo $row ['block'] . " Block<br>\n";


  comment ('SPELLS');

  $count = getCount ($row, 'spellid_', ITEM_SPELLS);

  if ($count)
    echo "<b>Casts:</b><br>\n";

  for ($i = 1; $i <= ITEM_SPELLS; $i++)
    if ($row ["spellid_$i"])
      echo lookupThing ($spells, $row ["spellid_$i"], 'show_spell') . "\n";

  comment ('BUY PRICE');


  echo "<div>\n";
  echo "<p class='item_lh'>Buy price: " . convertGold ($row ['buy_price'])  . "</p>\n";
  if ($row ['stackable'])
    echo "<p class='item_rh'>Stackable: " . $row ['stackable']  . "</p>\n";
  echo "</div>\n";

  // clear float
  echo "<div style='clear: both;'></div>\n";

  comment ('BONDING');

  if ($row ['bonding'])
    echo BONDING [$row ['bonding']];

  comment ('DESCRIPTION');

  $description = $row ['description'];

  if ($description)
    {
    echo "<hr>\n";
    echo fixHTML ('“' . $description .'”');
    }

  comment ('PAGE TEXT');

  // if there is page text (eg. a book) offer to show it
  $page_text = $row ['page_text'];
  if ($row ['page_text'])
    {
    echo "<p class='read_page'><a href='?action=read_text&id=$page_text&item=$id' >Click to read</a></p>\n";
    }

  endDiv ('simulate_box item');

  comment ('END SIMULATED ITEM CONTAINER');

  } // end of simulateItem

function showItemVendors ()
{
  global $id, $creatures;

  comment ('WHO SELLS IT');

 // who sells it
  $results = dbQueryParam ("SELECT * FROM ".NPC_VENDOR." WHERE item = ? AND entry <= " . MAX_CREATURE, array ('i', &$id));

  listItems ('Sold by', 'alpha_world.npc_vendor', count ($results), $results,
    function ($row) use ($creatures)
      {
      listThing ($creatures, $row ['entry'], 'show_creature');
      $maxcount = $row ['maxcount'];
      if ($maxcount  > 0)
        echo (" (limit $maxcount)");
      } // end listing function
      );
}  // end of showItemVendorsshowItemVendors

function showItemDrops ()
{
  global $id, $creatures;

  comment ('WHO DROPS IT');

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

  listItems ('Quest item dropped by', 'alpha_world.creature_loot_template',
            $count, $results,
    function ($row) use ($creatures)
      {
      if ($row ['chance'] >= 0)
        return true;   // ignore non-quest items
      listThing ($creatures, $row ['npc'], 'show_creature');
      echo ' — ' . -$row ['chance'] . '%';
      } // end listing function
      );

  $count = count($results) - $count;

  listItems ('Item dropped by', 'alpha_world.creature_loot_template',
              $count, $results,
    function ($row) use ($creatures)
      {
      if ($row ['chance'] < 0)
        return true;   // ignore quest items
      listThing ($creatures, $row ['npc'], 'show_creature');
      echo ' — ' . $row ['chance'] . '%';
      } // end listing function
      );

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

  listItems ('NPCs that drop this as reference loot', 'alpha_world.reference_loot_template',
             count($lootResults), $lootResults,
    function ($row) use ($creatures)
      {
      listThing ($creatures, $row ['npc'], 'show_creature');
      echo  ' — ' .  $row ['chance'] . "%\n";
      } // end listing function
      );

} // end of showItemDrops

function showItemSkinningLoot ()
{
  global $id, $creatures;

  $creature_template = CREATURE_TEMPLATE;

  comment ('WHO CAN WE SKIN FOR IT');

    // who can be skinned for it

  $skinning_loot_template = SKINNING_LOOT_TEMPLATE;

  $lootResults = dbQueryParam ("SELECT $creature_template.entry AS npc,
                            $skinning_loot_template.ChanceOrQuestChance AS chance,
                            $skinning_loot_template.item AS item
                            FROM $skinning_loot_template INNER JOIN $creature_template ON
                            ($skinning_loot_template.entry = IF($creature_template.loot_id > 0, $creature_template.loot_id, $creature_template.entry))
                            WHERE $skinning_loot_template.item = ?
                            AND $skinning_loot_template.mincountOrRef >= 0
                            AND $creature_template.entry <= " . MAX_CREATURE . "
                            ORDER BY name", array ('i', &$id));


   listItems ('Item can be skinned from', 'alpha_world.skinning_loot_template',
            count ($lootResults), $lootResults,
    function ($row) use ($creatures)
      {
      listThing ($creatures, $row ['npc'], 'show_creature');
      echo ' — ' . -$row ['chance'] . '%';
      } // end listing function
      );
} // end of showItemSkinningLoot

function showItemModel ($row)
{
  echo "
  <!-- MODEL DISPLAY ID -->
  <img
    class='model-display'
    src='missing_creature.png'
    alt='Item model'
  />
  <!-- END MODEL DISPLAY ID -->
  ";
} // end of showItemModel


function itemTopLeft ($info)
{
  global $id;
  global $documentRoot, $executionDir;

  $row = $info ['row'];
  $extras = $info ['extras'];
  $limit = $info ['limit'];

  boxTitle ('General');

  comment ('ICON');

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

  echo "<p></p>\n";

  comment ('SHORT LISTING OF FIELDS');
  showOneThing (ITEM_TEMPLATE, 'alpha_world.item_template', 'entry',
              $id, "", "name", $extras, $limit);

} // end of itemTopLeft

function itemTopMiddle ($info)
{
  $row = $info ['row'];
  simulateItem ($row);
}   // end of itemTopMiddle


function itemDetails ($info)
  {
  global $id;

  topSection    ($info, function ($info)       {
      topLeft   ($info, 'itemTopLeft');
      topMiddle ($info, 'itemTopMiddle');
      });

  middleSection ($info, function ($info)
      {
      showItemVendors ();
      showItemDrops ();
      showItemSkinningLoot ();
      });

  bottomSection ($info, function ($info) use ($id)
      {
      $extras = $info ['extras'];
      comment ('ITEM DETAILS');
      showOneThing (ITEM_TEMPLATE, 'alpha_world.item_template', 'entry',
                    $id, "Item details", "name", $extras);
      });

  } // end of itemDetails


function showOneItem ()
  {
  global $id;

  if (!checkID ())
    return;

 // we need the item info in this function
  $row = dbQueryOneParam ("SELECT * FROM ".ITEM_TEMPLATE." WHERE entry = ?", array ('i', &$id));

  if (!$row)
    {
    ShowWarning ("Item $id is not on the database");
    return;
    } // end of not finding it

  $extras =  array (
        'required_skill'  => 'skill',
        'class'           => 'item_class',
        'subclass'        => 'item_subclass',
        'start_quest'     => 'quest',
        'inventory_type'  => 'inventory_type',
        'flags'           => 'item_flags_mask',
        'buy_price'       => 'gold',
        'sell_price'      => 'gold',
        'min_money_loot'  => 'gold',
        'max_money_loot'  => 'gold',
        'bonding'         => 'bonding',
        'extra_flags'     => 'mask',

    );

  for ($i = 1; $i <= ITEM_STATS_COUNT; $i++)
    if ($row ["stat_value$i"])
      $extras ["stat_type$i"] = 'item_stats';

  for ($i = 1; $i <= ITEM_SPELLS; $i++)
    if ($row ["spellid_$i"])
      $extras ["spellid_$i"] = 'spell';

  for ($i = 1; $i <= ITEM_SPELL_COOLDOWNS; $i++)
    {
    if ($row ["spellcooldown_$i"])
      $extras ["spellcooldown_$i"] = 'time';
    if ($row ["spellcategorycooldown_$i"])
      $extras ["spellcategorycooldown_$i"] = 'time';
    }

  $name = $row ['name'];


  // fields to show in short summary
  $limit = array (
    'entry',
    'class',
    'display_id',
    'sell_price',
    'inventory_type',
    'disenchant_id',

  );


 // we pass this stuff around to the helper functions
  $info = array ('row' => $row, 'extras' => $extras, 'limit' => $limit);

  // ready to go! show the page info and work our way down into the sub-functions
  pageContent ($info, 'Item', $name, 'items', 'itemDetails', ITEM_TEMPLATE);


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

  $td  = function ($s) use (&$row) { tdx ($row  [$s]); };
  $tdr = function ($s) use (&$row) { tdx ($row  [$s], 'tdr'); };

  $results = setUpSearch ('Items',
                          $sortFields,            // fields we can sort on
                          array ('Entry', 'Class', 'Subclass', 'Name', 'Description'),    // headings
                          'entry',                // key
                          array ('name', 'description'),  // searchable fields
                          ITEM_TEMPLATE,          // table
                          'AND ignored = 0');     // extra conditions

  if (!$results)
    return;

  $searchURI = makeSearchURI (true);

  foreach ($results as $row)
    {
    echo "<tr>\n";
    $id = $row ['entry'];
    tdhr ("<a href='?action=show_item&id=$id$searchURI'>$id</a>");
    $item_class = $row ['class'];
    tdx ("$item_class: " . ITEM_CLASS [$item_class]);
    $item_subclass = $row ['subclass'];
    tdx ("$item_subclass : " . ITEM_SUBCLASSES [$item_class] [$item_subclass]);
    $td  ('name');
    $td  ('description');
    showFilterColumn ($row);
    echo "</tr>\n";
    } // end of foreach

  wrapUpSearch ();

  } // end of showItems
  ?>
