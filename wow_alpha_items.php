<?php

/*
  Author: X'Genesis Qhulut <XGenesis-Qhulut@protonmail.com>
  Date:   August 2022

  See LICENSE for license details.
*/



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

  tdh ($s);
  } // end of lookupItems


function showOneItem ($id)
  {
  global $quests, $creatures;

  showOneThing (ITEM_TEMPLATE, 'entry', $id, "Item", "name",
    array (
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

    ));

 // who sells it
  $results = dbQueryParam ("SELECT * FROM ".NPC_VENDOR." WHERE item = ?", array ('i', &$id));
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


  } // end of showOneItem


function showItems ()
  {
  global $where, $params;

  echo "<h2>Items</h2>\n";

  $td  = function ($s) use (&$row) { tdx ($row  [$s]); };
  $tdr = function ($s) use (&$row) { tdx ($row  [$s], 'tdr'); };

  setUpSearch ('entry', array ('name', 'description'));

  $results = dbQueryParam ("SELECT * FROM ".ITEM_TEMPLATE." $where AND ignored = 0 ORDER BY name LIMIT " . QUERY_LIMIT,
                    $params);

  showSearchForm ();

  if (count ($results) == 0)
    {
    echo "No matches.";
    return;
    } // end of nothing

  echo "<table>\n";
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
    echo "</tr>\n";
    }
  echo "</table>\n";

  showCount ($results);

  } // end of showItems
  ?>
