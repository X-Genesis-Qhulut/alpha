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

  if (!checkID ())
    return;

  $row = dbQueryOneParam ("SELECT * FROM ".SPELLVISUAL." WHERE ID = ?", array ('i', &$id));

  if (!$row)
    {
    ShowWarning ("Spell visual $id is not on the database");
    return;
    } // end of not finding it

  // page content
  pageContent (false, 'Spell Visual', "Spell Visual ID $id", 'spell_visuals',
       function ($info)
        {
        setTitle ("Spell Visual ID $id ");
        // put it down the bottom in the table area
        bottomSection ($info, function ($info)
          {
          global $id;
          showOneThing (SPELLVISUAL, 'alpha_dbc.SpellVisual', 'ID',
                        $id, "Spell Visual", '',  array ());
          });
        },
        SPELLVISUAL);
  } // end of showOneSpellVisual

function showSpellVisuals ()
  {
  global $where, $params, $sort_order;

  $sortFields = array (
    'ID', 'PrecastKit', 'CastKit', 'ImpactKit', 'StateKit', 'ChannelKit', 'AreaKit',
  );

  if (!in_array ($sort_order, $sortFields))
    $sort_order = 'ID';

  setTitle ("Spell Visual IDs");

  $td  = function ($s) use (&$row) { tdx ($row  [$s]); };
  $tdr = function ($s) use (&$row) { tdx ($row  [$s], 'tdr'); };

  $results = setUpSearch ('Spell Visuals',
                          $sortFields,            // fields we can sort on
                          array ('ID', 'PrecastKit', 'CastKit', 'ImpactKit', 'StateKit', 'ChannelKit', 'AreaKit'),    // headings
                          'ID',                // key
                          array ('Name'),  // searchable fields
                          SPELLVISUAL,          // table
                          '');     // extra conditions

  if (!$results)
    return;

  $searchURI = makeSearchURI (true);
  foreach ($results as $row)
    {
    echo "<tr>\n";
    $id = $row ['ID'];
    tdhr ("<a href='?action=show_spell_visual&id=$id'$searchURI>$id</a>");
    $tdr ('PrecastKit');
    $tdr ('CastKit');
    $tdr ('ImpactKit');
    $tdr ('StateKit');
    $tdr ('ChannelKit');
    $tdr ('AreaKit');
    showFilterColumn ($row);
    echo "</tr>\n";
    }
  wrapUpSearch ();


  } // end of showSpellVisuals


function showOneSpellVisualAnimName ()
  {
  global $id;

  if (!checkID ())
    return;

  $row = dbQueryOneParam ("SELECT * FROM ".SPELLVISUALANIMNAME." WHERE ID = ?", array ('i', &$id));

  if (!$row)
    {
    ShowWarning ("Spell visual anim name $id is not on the database");
    return;
    } // end of not finding it

  // page content
  pageContent (false, 'Spell Visual Anim Name', $row ['Name'], 'spell_visual_anim_names',
       function ($info)
        {
        setTitle ("Spell Visual Anim Name " . $row ['Name']);

        // put it down the bottom in the table area
        bottomSection ($info, function ($info)
          {
          global $id;
          showOneThing (SPELLVISUALANIMNAME, 'alpha_dbc.SpellVisualAnimName', 'ID',
                        $id, "Spell Visual Animation", '',  array ());
          });
        },
        SPELLVISUALANIMNAME);
  } // end of showOneSpellVisualAnimName


function showSpellVisualAnimNames ()
  {
  global $where, $params, $sort_order;

  $sortFields = array (
    'ID', 'AnimID', 'Name',
  );

  if (!in_array ($sort_order, $sortFields))
    $sort_order = 'AnimID';

  setTitle ("Spell Visual anim names");

  $td  = function ($s) use (&$row) { tdx ($row  [$s]); };

  $results = setUpSearch ('Spell Visual Animation names',
                          $sortFields,            // fields we can sort on
                          array ('ID', 'AnimID', 'Name'),    // headings
                          'ID',                // key
                          array ('Name'),  // searchable fields
                          SPELLVISUALANIMNAME,          // table
                          '');     // extra conditions

  if (!$results)
    return;

  $searchURI = makeSearchURI (true);

  foreach ($results as $row)
    {
    echo "<tr>\n";
    $id = $row ['ID'];
    tdhr ("<a href='?action=show_spell_visual_anim&id=$id$searchURI'>$id</a>");
    $td ('AnimID');
    $td ('Name');
    showFilterColumn ($row);
    echo "</tr>\n";
    }
  wrapUpSearch ();
  } // end of showSpellVisualAnimNames


function showOneSpellVisualEffectName ()
  {
  global $id;


  if (!checkID ())
    return;

  $row = dbQueryOneParam ("SELECT * FROM ".SPELLVISUALEFFECTNAME." WHERE ID = ?", array ('i', &$id));

  if (!$row)
    {
    ShowWarning ("Spell visual effect name $id is not on the database");
    return;
    } // end of not finding it

  // page content
  pageContent (false, 'Spell Visual Effect Name', $row ['FileName'], 'spell_visual_effect_names',
       function ($info)
        {
        setTitle ("Spell Visual Effect Name " . $row ['FileName']);

        // put it down the bottom in the table area
        bottomSection ($info, function ($info)
          {
          global $id;
          showOneThing (SPELLVISUALEFFECTNAME, 'alpha_dbc.SpellVisualEffectName', 'ID',
                        $id, "Spell Visual Effect", '',  array ());
          });
        },
        SPELLVISUALEFFECTNAME);
  } // end of showOneSpellVisualEffectName

function showSpellVisualEffectNames ()
  {
  global $where, $params, $sort_order;

  $sortFields = array (
    'ID', 'FileName', 'Name',
  );

  if (!in_array ($sort_order, $sortFields))
    $sort_order = 'FileName';

  setTitle ("Spell Visual effect names");

  $td  = function ($s) use (&$row) { tdx ($row  [$s]); };
  $tdr = function ($s) use (&$row) { tdx ($row  [$s], 'tdr'); };

  $results = setUpSearch ('Spell Visual Effect names',
                          $sortFields,            // fields we can sort on
                          array ('ID', 'File Name'),    // headings
                          'ID',                // key
                          array ('FileName'),  // searchable fields
                          SPELLVISUALEFFECTNAME,          // table
                          '');     // extra conditions

  if (!$results)
    return;

  $searchURI = makeSearchURI (true);

  foreach ($results as $row)
    {
    echo "<tr>\n";
    $id = $row ['ID'];
    tdhr ("<a href='?action=show_spell_visual_effect&id=$id$searchURI'>$id</a>");
    $td ('FileName');
    showFilterColumn ($row);
    echo "</tr>\n";
    }
  wrapUpSearch ();


  } // end of showSpellVisualEffectNames


?>
