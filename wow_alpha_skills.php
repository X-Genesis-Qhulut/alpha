<?php

/*
  Author: X'Genesis Qhulut <XGenesis-Qhulut@protonmail.com>
  Date:   August 2022

  See LICENSE for license details.
*/

// SKILLS

function showOneSkill ()
  {
  global $id;

// we need the item info in this function
  $row = dbQueryOneParam ("SELECT * FROM ".SKILLLINE." WHERE ID = ?", array ('i', &$id));

  $name = $row ['DisplayName_enUS'];

  startOfPageCSS ('Skill details', $name, 'skills');
  echo "<div class='object-container__items'>\n";

  showOneThing (SKILLLINE, 'alpha_dbc.skillline', 'ID', $id, "Skill", "DisplayName_enUS",
      array (
        'RaceMask'  => 'race_mask',
        'ClassMask' => 'class_mask',
        'SkillType' => 'skill_type',
        'DisplayName_Mask' => 'mask',
      ));

  echo "</div>\n";  // end of object-container__items
  endOfPageCSS ();

  } // end of showOneSkill

function showSkills ()
  {
  global $where, $params, $maps, $sort_order;

  $sortFields = array (
    'ID',
    'DisplayName_enUS',
    'RaceMask',
    'ClassMask',
    'MaxRank',
  );

  if (!in_array ($sort_order, $sortFields))
    $sort_order = 'DisplayName_enUS';


 // echo "<h2>Skills</h2>\n";

  $td  = function ($s) use (&$row) { tdx ($row  [$s]); };
  $tdr = function ($s) use (&$row) { tdx ($row  [$s], 'tdr'); };

  $results = setUpSearch ('Skills',
                          $sortFields,            // fields we can sort on
                          array ('ID', 'Name', 'Race', 'Class', 'Max Rank'),    // headings
                          'ID',                // key
                          array ('DisplayName_enUS'),  // searchable fields
                          SKILLLINE,          // table
                          '');     // extra conditions

  if (!$results)
    return;


  foreach ($results as $row)
    {
    echo "<tr>\n";
    $id = $row ['ID'];
    tdhr ("<a href='?action=show_skill&id=$id'>$id</a>");
    $td ('DisplayName_enUS');
    tdx (expandRaceMask ($row ['RaceMask']));
    tdx (expandClassMask ($row ['ClassMask']));
    $tdr ('MaxRank');
    showFilterColumn ($row);
    echo "</tr>\n";
    }

   wrapUpSearch ();


  } // end of showSkills
?>
