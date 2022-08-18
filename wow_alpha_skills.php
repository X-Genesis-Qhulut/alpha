<?php

/*
  Author: X'Genesis Qhulut <XGenesis-Qhulut@protonmail.com>
  Date:   August 2022

  See LICENSE for license details.
*/

// SKILLS

function showOneSkill ($id)
  {
  showOneThing (SKILLLINE, 'alpha_dbc.skillline', 'ID', $id, "Skill", "ID",
      array (
        'RaceMask'  => 'race_mask',
        'ClassMask' => 'class_mask',
        'SkillType' => 'skill_type',
      ));
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


  echo "<h2>Skills</h2>\n";

  $td  = function ($s) use (&$row) { tdx ($row  [$s]); };
  $tdr = function ($s) use (&$row) { tdx ($row  [$s], 'tdr'); };

  setUpSearch ('ID', array ('DisplayName_enUS'));

  $results = dbQueryParam ("SELECT * FROM ".SKILLLINE." $where ORDER BY $sort_order, ID LIMIT " . QUERY_LIMIT,
                    $params);

  if (!showSearchForm ($sortFields, $results))
    return;

  echo "<table class='search_results'>\n";
  headings (array ('ID', 'Name', 'Race', 'Class', 'Max Rank'));
  foreach ($results as $row)
    {
    echo "<tr>\n";
    $id = $row ['ID'];
    tdhr ("<a href='?action=show_skill&id=$id'>$id</a>");
    $td ('DisplayName_enUS');
    tdx (expandRaceMask ($row ['RaceMask']));
    tdx (expandClassMask ($row ['ClassMask']));
    $tdr ('MaxRank');
    echo "</tr>\n";
    }
  echo "</table>\n";

  showCount ($results);

  } // end of showSkills
?>
