<?php

/*
  Author: X'Genesis Qhulut <XGenesis-Qhulut@protonmail.com>
  Date:   August 2022

  See LICENSE for license details.
*/

// SKILLS

function skillDetails ($info)
  {
  bottomSection ($info, function ($info)
      {
      global $id;
      $extras = $info ['extras'];
      comment ('MAP DETAILS');
      showOneThing (SKILLLINE, 'ID', $id, "Skill", "DisplayName_enUS",  $extras);
      });
  } // end of skillDetails


function showOneSkill ()
  {
  global $id;

  if (($id === false && !repositionSearch()) || !checkID ())
    return;

// we need the item info in this function
  $row = dbQueryOneParam ("SELECT * FROM ".SKILLLINE." WHERE ID = ?", array ('i', &$id));

  if (!$row)
    {
    ShowWarning ("Skill $id is not on the database");
    return;
    } // end of not finding it

  $name = $row ['DisplayName_enUS'];

  setTitle ("Skill $name");

  $extras = array (
        'RaceMask'  => 'race_mask',
        'ClassMask' => 'class_mask',
        'SkillType' => 'skill_type',
        'DisplayName_Mask' => 'mask',
      );

  // we pass this stuff around to the helper functions
  $info = array ('row' => $row, 'extras' => $extras, 'limit' => array ());
  // ready to go! show the page info and work our way down into the sub-functions
  pageContent ($info, 'Skill', $name, 'skills', 'skillDetails', SKILLLINE);

  } // end of showOneSkill

function showSkills ()
  {
  global $where, $params, $maps, $sort_order, $matches;

  $sortFields = array (
    'ID',
    'DisplayName_enUS',
    'RaceMask',
    'ClassMask',
    'MaxRank',
  );

  if (!in_array ($sort_order, $sortFields))
    $sort_order = 'DisplayName_enUS';

  setTitle ("Skills listing");

  $td  = function ($s) use (&$row) { tdx ($row  [$s]); };

  $headings = array ('ID', 'Name', 'Race', 'Class', 'Max Rank');

  $results = setUpSearch ('Skills', $sortFields, $headings);

  if (!$results)
    return;

  $searchURI = makeSearchURI (true);
  $pos = 0;

  foreach ($results as $row)
    {
    $pos++;
    echo "<tr>\n";
    $id = $row ['ID'];
    tdh ("<a href='?action=show_skill&id=$id$searchURI&pos=$pos&max=$matches'>$id</a>");
    tdh ("<a href='?action=show_skill&id=$id$searchURI&pos=$pos&max=$matches'>" . fixHTML ($row ['DisplayName_enUS']) . "</a>");
    tdx (expandRaceMask ($row ['RaceMask']));
    tdx (expandClassMask ($row ['ClassMask']));
    $td ('MaxRank');
    showFilterColumn ($row);
    echo "</tr>\n";
    }

   wrapUpSearch ();


  } // end of showSkills
?>
