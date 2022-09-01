<?php

/*
  Author: X'Genesis Qhulut <XGenesis-Qhulut@protonmail.com>
  Date:   August 2022

  See LICENSE for license details.
*/

// SPELL VISUALS

function showOneSpellVisual ()
  {
  global $id;

  showOneThing (SPELLVISUAL, 'alpha_dbc.SpellVisual', 'ID', $id, "Spell Visual", '',
      array (
      ));
  } // end of showOneSpellVisual

function showSpellVisuals ()
  {
  global $where, $params, $sort_order;

  $sortFields = array (
    'ID', 'PrecastKit', 'CastKit', 'ImpactKit', 'StateKit', 'ChannelKit', 'AreaKit',
  );

  if (!in_array ($sort_order, $sortFields))
    $sort_order = 'ID';

  echo "<h2>Spell Visuals</h2>\n";

  $td  = function ($s) use (&$row) { tdx ($row  [$s]); };
  $tdr = function ($s) use (&$row) { tdx ($row  [$s], 'tdr'); };

  setUpSearch ('ID', array ());

  $offset = getQueryOffset(); // based on the requested page number

  $results = dbQueryParam ("SELECT * FROM ".SPELLVISUAL." $where ORDER BY $sort_order, ID LIMIT $offset, " . QUERY_LIMIT,
                    $params);

  if (!showSearchForm ($sortFields, $results, SPELLVISUAL, $where))
    return;

  echo "<table class='search_results'>\n";
  headings (array ('ID', 'PrecastKit', 'CastKit', 'ImpactKit', 'StateKit', 'ChannelKit', 'AreaKit'));
  foreach ($results as $row)
    {
    echo "<tr>\n";
    $id = $row ['ID'];
    tdhr ("<a href='?action=show_spell_visual&id=$id'>$id</a>");
    $tdr ('PrecastKit');
    $tdr ('CastKit');
    $tdr ('ImpactKit');
    $tdr ('StateKit');
    $tdr ('ChannelKit');
    $tdr ('AreaKit');
    showFilterColumn ($row);
    echo "</tr>\n";
    }
  echo "</table>\n";

  showCount ($results);

  } // end of showSpellVisuals


function showOneSpellVisualAnimName ()
  {
  global $id;

  showOneThing (SPELLVISUALANIMNAME, 'alpha_dbc.SpellVisualAnimName', 'ID', $id, "Spell Visual Animation", '',
      array (
      ));
  } // end of showOneSpellVisualAnimName

function showSpellVisualAnimNames ()
  {
  global $where, $params, $sort_order;

  $sortFields = array (
    'ID', 'AnimID', 'Name',
  );

  if (!in_array ($sort_order, $sortFields))
    $sort_order = 'AnimID';

  echo "<h2>Spell Visual Animation names</h2>\n";

  $td  = function ($s) use (&$row) { tdx ($row  [$s]); };
  $tdr = function ($s) use (&$row) { tdx ($row  [$s], 'tdr'); };

  setUpSearch ('ID', array ());

  $offset = getQueryOffset(); // based on the requested page number

  $results = dbQueryParam ("SELECT * FROM ".SPELLVISUALANIMNAME." $where ORDER BY $sort_order, ID LIMIT $offset, " . QUERY_LIMIT,
                    $params);

  if (!showSearchForm ($sortFields, $results, SPELLVISUALANIMNAME, $where))
    return;

  echo "<table class='search_results'>\n";
  headings (array ('ID', 'AnimID', 'Name'));
  foreach ($results as $row)
    {
    echo "<tr>\n";
    $id = $row ['ID'];
    tdhr ("<a href='?action=show_spell_visual_anim&id=$id'>$id</a>");
    $tdr ('AnimID');
    $td ('Name');
    showFilterColumn ($row);
    echo "</tr>\n";
    }
  echo "</table>\n";

  showCount ($results);

  } // end of showSpellVisualAnimNames


function showOneSpellVisualEffectName ()
  {
  global $id;

  showOneThing (SPELLVISUALEFFECTNAME, 'alpha_dbc.SpellVisualEffectName', 'ID', $id, "Spell Visual Effect", '',
      array (
      ));
  } // end of showOneSpellVisualEffectName

function showSpellVisualEffectNames ()
  {
  global $where, $params, $sort_order;

  $sortFields = array (
    'ID', 'FileName', 'Name',
  );

  if (!in_array ($sort_order, $sortFields))
    $sort_order = 'FileName';

  echo "<h2>Spell Visual Effect names</h2>\n";

  $td  = function ($s) use (&$row) { tdx ($row  [$s]); };
  $tdr = function ($s) use (&$row) { tdx ($row  [$s], 'tdr'); };

  setUpSearch ('ID', array ());

  $offset = getQueryOffset(); // based on the requested page number

  $results = dbQueryParam ("SELECT * FROM ".SPELLVISUALEFFECTNAME." $where ORDER BY $sort_order, ID LIMIT $offset, " . QUERY_LIMIT,
                    $params);

  if (!showSearchForm ($sortFields, $results, SPELLVISUALEFFECTNAME, $where))
    return;

  echo "<table class='search_results'>\n";
  headings (array ('ID', 'File Name'));
  foreach ($results as $row)
    {
    echo "<tr>\n";
    $id = $row ['ID'];
    tdhr ("<a href='?action=show_spell_visual_effect&id=$id'>$id</a>");
    $td ('FileName');
    showFilterColumn ($row);
    echo "</tr>\n";
    }
  echo "</table>\n";

  showCount ($results);

  } // end of showSpellVisualEffectNames


?>
