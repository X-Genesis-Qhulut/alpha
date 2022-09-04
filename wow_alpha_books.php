<?php

/*
  Author: X'Genesis Qhulut <XGenesis-Qhulut@protonmail.com>
  Date:   August 2022

  See LICENSE for license details.
*/

// BOOKS (page_text)

function simulateBookPage ($row)
  {
  echo "<p><div class='simulate_box page_text'>\n";
  echo nl2br_http (fixQuestText ($row ['text']));
  echo "</div>\n";
  }

function showText ()
{
  global $id, $item, $items;

  $page = 0;

  // to avoid loops
  $shown = array ();

  echo "<h2>" . fixHTML ($items [$item]) . "</h2>\n";
  // for each page ...

  while ($id)
    {
    $row = dbQueryOneParam ("SELECT * FROM ".PAGE_TEXT." WHERE entry = ?", array ('i', &$id));
    if (!$row)
      break;
    $page++;
    echo "<h3 style='margin-bottom:0px;'>Page $page</h3>\n";
    simulateBookPage ($id, $row);
    $shown [$id] = true;
    $id = $row ['next_page'];
    // avoid endless loops
    if (array_key_exists ($id, $shown))
      break;
    } // end of while each page

  echo "<p>Item containing this text: ";
  echo lookupThing ($items, $item, 'show_item');
} // end of showText


function showOneBook ()
  {
  global $id, $items;

  $item_template = ITEM_TEMPLATE;
  $page_text = PAGE_TEXT;

  // we need the item info in this function
  $row = dbQueryOneParam ("SELECT * FROM ".PAGE_TEXT." WHERE entry = ?", array ('i', &$id));


  startOfPageCSS ('Page', "Page $id", 'books');
  echo "<div class='object-container__informations__details1'>\n";
  boxTitle ('General');

  showOneThing (PAGE_TEXT, 'alpha_world.page_text', 'entry', $id, "", "", array (),
                array ('entry', 'next_page'));


 // find the page chains

  $pages = array ();
  $results = dbQuery ("SELECT entry, next_page FROM $page_text");
  while ($chainRow = dbFetch ($results))
    $pages [$chainRow ['next_page']] = $chainRow ['entry'];
  dbFree ($results);

  // now we can work back until we find the start of the chain
  $doneThis = array ();
  do
    {
    if (!array_key_exists ($id,  $pages))
      break;  // no previous, this must be the start of the chain
    $prevPage = $pages [$id];
    if (array_key_exists ($id,  $doneThis))
      break;    // doing the same one again, give up
    $doneThis [$id] = true;
    $id = $prevPage;
    } while ($prevPage);

  $itemRow = dbQueryOneParam (
        "SELECT T2.entry AS item_key
          FROM $page_text AS T1
              INNER JOIN $item_template AS T2
          ON (T1.entry = T2.page_text)
          WHERE T1.entry = ?",
          array ('i', &$id));

  if (!$itemRow)
    {
    echo "<p>Cannot find an item linked to this page";
    return;
    }

  echo "<p>Item with this text: ";
  echo lookupThing ($items, $itemRow ['item_key'], 'show_item');

  echo "</div>\n";  // end of details__informations__details1

  // find what item refers to this

  echo "<div class='object-container__items'>\n";

  simulateBookPage ($row);

  echo "</div>\n";  // end of object-container__informations__details1  (spawn points, quests, etc.)


  echo "<div class='object-container__items'>\n";

  showOneThing (PAGE_TEXT, 'alpha_world.page_text', 'entry', $id, "", "", array ());
  echo "</div>\n";  // end of object-container__items


  endOfPageCSS ();

  } // end of showOneBook

function showBooks ()
  {
  global $where, $params, $maps, $sort_order;

  $sortFields = array (
    'entry',
    'text',
    'next_page',
  );

  if (!in_array ($sort_order, $sortFields))
    $sort_order = 'entry';

  $td  = function ($s) use (&$row) { tdx ($row  [$s]); };
  $tdr = function ($s) use (&$row) { tdx ($row  [$s], 'tdr'); };

  $results = setUpSearch ('Pages',
                          $sortFields,            // fields we can sort on
                          array ('Entry', 'Text', 'Next Page'),    // headings
                          'entry',                // key
                          array ('text'),  // searchable fields
                          PAGE_TEXT,          // table
                          '');     // extra conditions

  if (!$results)
    return;

  foreach ($results as $row)
    {
    echo "<tr>\n";
    $id = $row ['entry'];
    tdhr ("<a href='?action=show_book&id=$id'>$id</a>");
    echo "<td>" . nl2br_http (fixQuestText ($row ['text'])) . "</td>\n";

    $tdr ('next_page');
    showFilterColumn ($row);
    echo "</tr>\n";
    }

  wrapUpSearch ();


  } // end of showBooks
?>
