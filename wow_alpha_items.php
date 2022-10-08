<?php

/*
  Author: X'Genesis Qhulut <XGenesis-Qhulut@protonmail.com>
  Date:   August 2022

  See LICENSE for license details.
*/


// ITEMS

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

  listItems ('Sold by', NPC_VENDOR, count ($results), $results,
    function ($row) use ($creatures)
      {
      $maxcount = $row ['maxcount'];
      listThing ($creatures, $row ['entry'], 'show_creature', $maxcount  > 0 ? "(limit $maxcount)" : '');
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
                            ORDER BY chance DESC, name", array ('i', &$id));

  // count quest items - they have a negative drop chance
  $count = 0;
  foreach ($results as $creatureRow)
    if ($creatureRow ['chance'] < 0)
        $count++;

  // TODO: fix this - counts will be wrong

  listItems ('Quest item dropped by', CREATURE_LOOT_TEMPLATE,
            $count, $results,
    function ($row) use ($creatures)
      {
      if ($row ['chance'] >= 0)
        return true;   // ignore non-quest items
      listThing ($creatures, $row ['npc'], 'show_creature', roundChance ($row ['chance']));
      } // end listing function
      );

  $count = count($results) - $count;

  listItems ('Item dropped by', CREATURE_LOOT_TEMPLATE,
              $count, $results,
    function ($row) use ($creatures)
      {
      if ($row ['chance'] < 0)
        return true;   // ignore quest items
      listThing ($creatures, $row ['npc'], 'show_creature', roundChance ($row ['chance']));
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

  listItems ('NPCs that drop this as reference loot', REFERENCE_LOOT_TEMPLATE,
             count($lootResults), $lootResults,
    function ($row) use ($creatures)
      {
      listThing ($creatures, $row ['npc'], 'show_creature',  roundChance ($row ['chance']));
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


   listItems ('Item can be skinned from', SKINNING_LOOT_TEMPLATE,
            count ($lootResults), $lootResults,
    function ($row) use ($creatures)
      {
      listThing ($creatures, $row ['npc'], 'show_creature', roundChance ($row ['chance']));
      } // end listing function
      );
} // end of showItemSkinningLoot

function showCreatureEquips ()
{
  global $id, $creatures;

  $creature_template = CREATURE_TEMPLATE;
  $creature_equip_template = CREATURE_EQUIP_TEMPLATE;

  comment ('WHAT CREATURE EQUIPS IT');

 // what they equip

  $fields = array ();
  $params = array ('');
  for ($i = 1; $i <= CREATURE_EQUIP_ITEMS; $i++)
    {
    $fields [] = "equipentry$i";
    $params [0] .= 'i';
    $params [] = &$id;
    }

  $fieldList = implode (' = ? OR ', $fields) . ' = ?';

  $results = dbQueryParam ("SELECT entry AS npc FROM ".CREATURE_EQUIP_TEMPLATE." WHERE ($fieldList) AND entry <= " . MAX_CREATURE, $params);

  listItems ('Item is equipped by', CREATURE_EQUIP_TEMPLATE,
          count ($results), $results,
  function ($row) use ($creatures)
    {
    listThing ($creatures, $row ['npc'], 'show_creature');
    } // end listing function
    );
} // end of showCreatureEquips

function showItemSpellReagents ()
{
  global $id, $spells;

  comment ('REAGENTS FOR');


  $fields = array ();
  $params = array ('');
  for ($i = 1; $i <= SPELL_REAGENTS; $i++)
    {
    $fields [] = "Reagent_$i";
    $params [0] .= 'i';
    $params [] = &$id;
    }

  $fieldList = implode (' = ? OR ', $fields) . ' = ?';

  $results = dbQueryParam ("SELECT ID AS spell FROM ".SPELL." WHERE ($fieldList)", $params);

 listItems ('Item is a reagent for spell', SPELL,
          count ($results), $results,
  function ($row) use ($spells)
    {
    listThing ($spells, $row ['spell'], 'show_spell');
    } // end listing function
    );

} // end of showItemSpellReagents

function showItemSpellProduced ()
{
  global $id, $spells;

  comment ('ITEM PRODUCED BY SPELLS');


  $fields = array ();
  $params = array ('');
  for ($i = 1; $i <= SPELL_EFFECT_ITEM_TYPES; $i++)
    {
    $fields [] = "EffectItemType_$i";
    $params [0] .= 'i';
    $params [] = &$id;
    }

  $fieldList = implode (' = ? OR ', $fields) . ' = ?';

  $results = dbQueryParam ("SELECT ID AS spell FROM ".SPELL." WHERE ($fieldList)", $params);

 listItems ('Item is produced by spell', SPELL,
          count ($results), $results,
  function ($row) use ($spells)
    {
    listThing ($spells, $row ['spell'], 'show_spell');
    } // end listing function
    );

} // end of showItemSpellProduced

function showItemQuestRequirement ()
{
  global $id, $quests;

  comment ('ITEM IS REQUIREMENT OF QUEST');


  $fields = array ('SrcItemId');
  $params = array ('i');
  $params [] = &$id;
  for ($i = 1; $i <= QUEST_REQUIRED_ITEMS; $i++)
    {
    $fields [] = "ReqItemId$i";
    $params [0] .= 'i';
    $params [] = &$id;
    }

  $fieldList = implode (' = ? OR ', $fields) . ' = ?';

  $results = dbQueryParam ("SELECT entry AS quest FROM ".QUEST_TEMPLATE." WHERE ($fieldList)", $params);

  listItems ('Item is a quest requirement', QUEST_TEMPLATE,
          count ($results), $results,
  function ($row) use ($quests)
    {
    listThing ($quests, $row ['quest'], 'show_quest');
    } // end listing function
    );

} // end of showItemQuestRequirement

function showItemQuestReward ()
{
  global $id, $quests;

  comment ('ITEM IS REWARD OF QUEST');


  $fields = array ();
  $params = array ('');
  for ($i = 1; $i <= QUEST_REWARD_ITEMS; $i++)
    {
    $fields [] = "RewItemCount$i";
    $params [0] .= 'i';
    $params [] = &$id;
    }

  for ($i = 1; $i <= QUEST_REWARD_ITEM_CHOICES; $i++)
    {
    $fields [] = "RewChoiceItemId$i";
    $params [0] .= 'i';
    $params [] = &$id;
    }

  $fieldList = implode (' = ? OR ', $fields) . ' = ?';

  $results = dbQueryParam ("SELECT entry AS quest FROM ".QUEST_TEMPLATE." WHERE ($fieldList)", $params);

 listItems ('Item is a quest reward', QUEST_TEMPLATE,
          count ($results), $results,
  function ($row) use ($quests)
    {
    listThing ($quests, $row ['quest'], 'show_quest');
    } // end listing function
    );

} // end of showItemQuestReward

function showItemQuestStart ()
{
  global $id, $quests;

  comment ('ITEM STARTS A QUEST');

  $results = dbQueryParam ("SELECT start_quest AS quest FROM ".ITEM_TEMPLATE." WHERE entry = ? AND start_quest > 0", array ('i', &$id));

 listItems ('Item starts a quest', ITEM_TEMPLATE,
          count ($results), $results,
  function ($row) use ($quests)
    {
    listThing ($quests, $row ['quest'], 'show_quest');
    } // end listing function
    );

} // end of showItemQuestStart

function showItemModel ($row)
{
  echo "
  <!-- MODEL DISPLAY ID -->
  <img
    class='model-display'
    src='missing_creature.webp'
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
  showOneThing (ITEM_TEMPLATE, 'entry',
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

  middleSection ($info, function ($info) use ($id)
      {
      global $listedItemsCount;

      $listedItemsCount = 0;

      showItemVendors ();
      showItemDrops ();
      showItemSkinningLoot ();
      if ($id > 0)
        {
        showCreatureEquips ();
        showItemSpellReagents ();
        showItemSpellProduced ();
        showItemQuestReward ();
        showItemQuestRequirement ();
        showItemQuestStart ();
        }

      if ($listedItemsCount == 0)
        middleDetails ($info, function ($info) use ($listedItemsCount)
          {
          showNoSpawnPoints ('Unused item', 'This item appears to be unused.');
          });

      });

  bottomSection ($info, function ($info) use ($id)
      {
      $extras = $info ['extras'];
      comment ('ITEM DETAILS');
      showOneThing (ITEM_TEMPLATE, 'entry',
                    $id, "Item details", "name", $extras);
      });

  } // end of itemDetails


function showOneItem ()
  {
  global $id;

  if (($id === false && !repositionSearch()) || !checkID ())
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
//        'allowable_class' => 'class_mask',
//        'allowable_race'  => 'race_mask',

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

  setTitle ("Item $name");


  // fields to show in short summary
  $limit = array (
    'entry',
    'class',
    'display_id',
    'sell_price',
    'inventory_type',
    'spellid_1',

  );


 // we pass this stuff around to the helper functions
  $info = array ('row' => $row, 'extras' => $extras, 'limit' => $limit);

  // ready to go! show the page info and work our way down into the sub-functions
  pageContent ($info, 'Item', $name, 'items', 'itemDetails', ITEM_TEMPLATE);


  } // end of showOneItem

function showItems ()
  {
  global $where, $params, $sort_order, $matches;

  setTitle ('Items listing');

  $sortFields = array (
    'entry',
    'name',
    'class',
    'subclass',
    'description',
  );

  if (!in_array ($sort_order, $sortFields))
    $sort_order = 'name';

  $td  = function ($s) use (&$row) { tdx ($row  [$s]); };

  $headings = array ('Entry', 'Name', 'Class', 'Subclass', 'Description');

  $results = setUpSearch ('Items', $sortFields, $headings);

  if (!$results)
    return;

  $searchURI = makeSearchURI (true);
  $pos = 0;

  foreach ($results as $row)
    {
    $pos++;
    echo "<tr>\n";
    $id = $row ['entry'];
    tdh ("<a href='?action=show_item&id=$id$searchURI&pos=$pos&max=$matches'>$id</a>");
    tdh ("<a href='?action=show_item&id=$id$searchURI&pos=$pos&max=$matches'>" . fixHTML ($row ['name']) . "</a>");
    $item_class = $row ['class'];
    td ("$item_class: " . ITEM_CLASS [$item_class]);
    $item_subclass = $row ['subclass'];
    tdx ("$item_subclass : " . ITEM_SUBCLASSES [$item_class] [$item_subclass]);
    $td  ('description');
    showFilterColumn ($row);
    echo "</tr>\n";
    } // end of foreach

  wrapUpSearch ();

  } // end of showItems


function og_item ()
{
  global $id, $documentRoot, $executionDir;

  if ($id === false)
    repositionSearch();

  if ($id)
    {
    $row = dbQueryOneParam ("SELECT * FROM ".ITEM_TEMPLATE." WHERE entry = ?", array ('i', &$id));
    if ($row)
      $imageRow = dbQueryOneParam ("SELECT * FROM ".ITEMDISPLAYINFO." WHERE ID = ?", array ('i', &$row ['display_id']));
    }
  else
    {
    $id = 0;
    $row = false;
    $imageRow = false;
    }

  if (!$row)
    {
    $title = 'Item not on file';
    $image = '/icons/INV_Misc_QuestionMark.png';
    $description = '';
    }
  else
    {
    $title = $row ['name'];
    if ($imageRow)
      {
      $icon = $imageRow ['InventoryIcon'] . '.png';
      $image = "/icons/$icon";
      if (!file_exists ("$documentRoot$executionDir/icons/$icon"))
        {
        $image = '/icons/INV_Misc_QuestionMark.png';
        }
      }
    else
      $image = '/icons/INV_Misc_QuestionMark.png';
    $description = "Entry: $id";
    }

  sendOgMeta ($title, $image, 'png', $description);

}   // end of og_item


?>
