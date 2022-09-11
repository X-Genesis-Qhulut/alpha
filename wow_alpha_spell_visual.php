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

  if (($id === false && !repositionSearch()) || !checkID ())
    return;

  $row = dbQueryOneParam ("SELECT * FROM ".SPELLVISUAL." WHERE ID = ?", array ('i', &$id));

  if (!$row)
    {
    ShowWarning ("Spell visual $id is not on the database");
    return;
    } // end of not finding it

  // page content
   pageContent ($row, 'Spell Visual', "Spell Visual ID $id", 'spell_visuals',
       function ($row) use ($id)
        {
        setTitle ("Spell Visual ID $id ");
        // put it down the bottom in the table area
        bottomSection (false, function ($row)
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
  global $where, $params, $sort_order, $matches;

  $sortFields = array (
    'ID', 'PrecastKit', 'CastKit', 'ImpactKit', 'StateKit', 'ChannelKit', 'AreaKit',
  );

  if (!in_array ($sort_order, $sortFields))
    $sort_order = 'ID';

  setTitle ("Spell Visual IDs");

  $td  = function ($s) use (&$row) { tdx ($row  [$s]); };

  $headings = array ('ID', 'PrecastKit', 'CastKit', 'ImpactKit', 'StateKit', 'ChannelKit', 'AreaKit');

  $results = setUpSearch ('Spell Visuals', $sortFields, $headings);


  if (!$results)
    return;

  $searchURI = makeSearchURI (true);
  $pos = 0;

  foreach ($results as $row)
    {
    $pos++;
    echo "<tr>\n";
    $id = $row ['ID'];
    tdhr ("<a href='?action=show_spell_visual&id=$id'$searchURI&pos=$pos&max=$matches>$id</a>");
    $td ('PrecastKit');
    $td ('CastKit');
    $td ('ImpactKit');
    $td ('StateKit');
    $td ('ChannelKit');
    $td ('AreaKit');
    showFilterColumn ($row);
    echo "</tr>\n";
    }
  wrapUpSearch ();


  } // end of showSpellVisuals


function showOneSpellVisualAnimName ()
  {
  global $id;

  if (($id === false && !repositionSearch()) || !checkID ())
    return;

  $row = dbQueryOneParam ("SELECT * FROM ".SPELLVISUALANIMNAME." WHERE ID = ?", array ('i', &$id));

  if (!$row)
    {
    ShowWarning ("Spell visual anim name $id is not on the database");
    return;
    } // end of not finding it

  // page content
  pageContent (false, 'Spell Visual Anim Name', $row ['Name'], 'spell_visual_anim_names',
       function ($row)
        {
        setTitle ("Spell Visual Anim Name " . $row ['Name']);

        // put it down the bottom in the table area
        bottomSection (false, function ($row)
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
  global $where, $params, $sort_order, $matches;

  $sortFields = array (
    'ID', 'AnimID', 'Name',
  );

  if (!in_array ($sort_order, $sortFields))
    $sort_order = 'AnimID';

  setTitle ("Spell Visual anim names");

  $td  = function ($s) use (&$row) { tdx ($row  [$s]); };

  $headings = array ('ID', 'AnimID', 'Name');

  $results = setUpSearch ('Spell Visual Animation names', $sortFields, $headings);

  if (!$results)
    return;

  $searchURI = makeSearchURI (true);
  $pos = 0;

  foreach ($results as $row)
    {
    $pos++;
    echo "<tr>\n";
    $id = $row ['ID'];
    tdhr ("<a href='?action=show_spell_visual_anim&id=$id$searchURI&pos=$pos&max=$matches'>$id</a>");
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

  if (($id === false && !repositionSearch()) || !checkID ())
    return;

  $row = dbQueryOneParam ("SELECT * FROM ".SPELLVISUALEFFECTNAME." WHERE ID = ?", array ('i', &$id));

  if (!$row)
    {
    ShowWarning ("Spell visual effect name $id is not on the database");
    return;
    } // end of not finding it

  // page content
  pageContent (false, 'Spell Visual Effect Name', $row ['FileName'], 'spell_visual_effect_names',
       function ($row)
        {
        setTitle ("Spell Visual Effect Name " . $row ['FileName']);

        // put it down the bottom in the table area
        bottomSection (false, function ($row)
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
  global $where, $params, $sort_order, $matches;

  $sortFields = array (
    'ID', 'FileName', 'Name',
  );

  if (!in_array ($sort_order, $sortFields))
    $sort_order = 'FileName';

  setTitle ("Spell Visual effect names");

  $td  = function ($s) use (&$row) { tdx ($row  [$s]); };

  $headings = array ('ID', 'File Name');

  $results = setUpSearch ('Spell Visual Effect names', $sortFields, $headings);

  if (!$results)
    return;

  $searchURI = makeSearchURI (true);
  $pos = 0;

  foreach ($results as $row)
    {
    $pos++;
    echo "<tr>\n";
    $id = $row ['ID'];
    tdh ("<a href='?action=show_spell_visual_effect&id=$id$searchURI&pos=$pos&max=$matches'>$id</a>");
    $td ('FileName');
    showFilterColumn ($row);
    echo "</tr>\n";
    }
  wrapUpSearch ();


  } // end of showSpellVisualEffectNames


?>
