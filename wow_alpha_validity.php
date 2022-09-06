<?php

/*
  Author: X'Genesis Qhulut <XGenesis-Qhulut@protonmail.com>
  Date:   August 2022

  See LICENSE for license details.
*/

// VALIDITY OF DATABASE

// See: https://mangoszero-docs.readthedocs.io/en/latest/database/world/creature-loot-template.html

function showBadNPCs ($info)
  {
  global $creatures;

  $heading = $info ['heading'];
  $results = $info ['results'];
  $label = $info ['label'];
  $field = $info ['field'];

  $count = dbRows ($results);

  boxTitle ("$heading ($count)");

  echo "<ul>\n";
  while ($row = dbFetch ($results))
    echo "<li>" . lookupThing ($creatures,  $row ['npc_key'], 'show_creature') .
          " ($label " . $row [$field] . ")\n";
  dbFree ($results);
  echo "</ul>\n";

  } // end of showBadNPCs

/*
function showBadNPCs ($heading, $results, $label, $field)
  {
  global $action;
  $info = array ('results' => $results, 'label' => $label, 'field' => $field);
  pageContent ($info, 'Validation', $heading,  '',
    function ($info)
      {
      topSection ($info, function  ($info)
        {
        middleDetails ($info, 'showBadNPCsHelper');
        });
      }, $field);
  } // end of showBadNPCs
*/

function showBadGOs ($heading, $results, $label, $field)
  {
  global $game_objects;

  echo "<h2>" . fixHTML ($heading) . "</h2>\n";

  $count = dbRows ($results);

  echo "<ul>\n";
  while ($row = dbFetch ($results))
    {
    echo "<li>" . lookupThing ($game_objects,  $row ['go_key'], 'show_go');
    if ($label)
      echo " ($label " . $row [$field] . ")";
    echo "\n";
    }
  dbFree ($results);
  echo "</ul>\n";

  $s = 's';
  if ($count == 1)
    $s = '';

  echo "$count row$s returned.\n";

  } // end of showBadGOs

function showBadQuests ($heading, $results, $label, $field)
  {
  global $quests;

  echo "<h2>" . fixHTML ($heading) . "</h2>\n";

  $count = dbRows ($results);

  echo "<ul>\n";
  while ($row = dbFetch ($results))
    echo "<li>" . lookupThing ($quests,  $row ['quest_key'], 'show_quest') .
          " ($label " . $row [$field] . ")\n";
  dbFree ($results);
  echo "</ul>\n";

  $s = 's';
  if ($count == 1)
    $s = '';

  echo "$count row$s returned.\n";

  } // end of showBadQuests

function showBadItems ($heading, $results, $label, $field)
  {
  global $items;

  echo "<h2>" . fixHTML ($heading) . "</h2>\n";

  $count = dbRows ($results);

  echo "<ul>\n";
  while ($row = dbFetch ($results))
    echo "<li>" . lookupThing ($items,  $row ['item_key'], 'show_item') .
          " ($label " . $row [$field] . ")\n";
  dbFree ($results);
  echo "</ul>\n";

  $s = 's';
  if ($count == 1)
    $s = '';

  echo "$count row$s returned.\n";

  } // end of showBadItems

function showUnknownFaction ()
  {

  $factiontemplate = FACTIONTEMPLATE;
  $creature_template = CREATURE_TEMPLATE;

  $results = dbQuery ("SELECT T1.entry   AS npc_key,
                              T1.faction AS creature_faction
                      FROM $creature_template AS T1
                        LEFT JOIN $factiontemplate AS T2
                         ON T2.ID = T1.faction
                        WHERE T2.FactionGroup IS NULL
                        AND T1.faction <> 0
                        AND T1.entry <= " . MAX_CREATURE .
                        " ORDER BY name");

  showBadNPCs ('Creatures with unknown faction', $results, 'Faction', 'creature_faction');

  echo "<p>(Excludes faction: 0)\n";

  } // end of showUnknownFaction

function showMissingQuestItems ()
{
  global $quests;

  echo "<h2>Missing quest items</h2>\n";

  $quest_template = QUEST_TEMPLATE;
  $item_template = ITEM_TEMPLATE;
  $totalCount = 0;

  $fields = array ('SrcItemId');

  for ($i = 1; $i <= QUEST_REQUIRED_ITEMS; $i++)
    $fields [] = "ReqItemId$i";
  for ($i = 1; $i <= QUEST_REWARD_ITEMS; $i++)
    $fields [] = "RewItemId$i";
  for ($i = 1; $i <= QUEST_REWARD_ITEM_CHOICES; $i++)
    $fields [] = "RewChoiceItemId$i";

  foreach ($fields as $field)
    {
    $results = dbQuery ("SELECT T1.entry AS quest_key,
                                T1.$field AS quest_item
                        FROM $quest_template AS T1
                            LEFT JOIN $item_template AS T2
                            ON (T1.$field = T2.entry AND T2.ignored = 0)
                        WHERE T2.entry IS NULL
                        AND T1.$field <> 0
                        AND T1.ignored = 0
                        ORDER BY T1.entry");

    $count = dbRows ($results);
    $totalCount += $count;

    if ($count)
      showBadQuests ("Quests where <$field> item is not on file", $results, 'Item', 'quest_item');

    } // end of foreach

  if ($totalCount == 0)
    echo "<p>No problems found.\n";

} // end of showMissingQuestItems

function showMissingQuestSpells ()
{
  global $quests;

  echo "<h2>Missing quest spells</h2>\n";

  $quest_template = QUEST_TEMPLATE;
  $spell = SPELL;
  $totalCount = 0;

  $fields = array ();

  for ($i = 1; $i <= QUEST_REQUIRED_SPELLS; $i++)
    $fields [] = "ReqSpellCast$i";

  foreach ($fields as $field)
    {
    $results = dbQuery ("SELECT T1.entry AS quest_key,
                                T1.$field AS quest_spell
                        FROM $quest_template AS T1
                            LEFT JOIN $spell AS T2
                            ON T1.$field = T2.ID
                        WHERE T2.ID IS NULL
                        AND T1.$field <> 0
                        AND T1.ignored = 0
                        ORDER BY T1.entry");

    $count = dbRows ($results);
    $totalCount += $count;

    if ($count)
       showBadQuests ("Quests where <$field> spell is not on file", $results, 'Spell', 'quest_spell');

    } // end of foreach

  if ($totalCount == 0)
    echo "<p>No problems found.\n";

} // end of showMissingQuestSpells

// analyse quests to see if the previous, next or next in chain are not on file
function showMissingQuestQuests ()
{
  global $quests;
  $totalCount = 0;

  echo "<h2>Missing quest chains</h2>\n";

  $quest_template = QUEST_TEMPLATE;

  $fields = array (
    'PrevQuestId',
    'NextQuestId',
    'NextQuestInChain'
  );

  foreach ($fields as $field)
    {
    $results = dbQuery ("SELECT T1.entry AS quest_key,
                                T1.$field AS quest_quest
                        FROM $quest_template AS T1
                            LEFT JOIN $quest_template AS T2
                            ON (ABS(T1.$field) = T2.entry AND T2.ignored = 0)
                        WHERE T2.entry IS NULL
                        AND T1.$field <> 0
                        AND T1.ignored = 0
                        ORDER BY T1.entry");

    $count = dbRows ($results);
    $totalCount += $count;

    if ($count)
      showBadQuests ("Quests where <$field> quest is not on file", $results, 'Quest', 'quest_quest');

    } // end of foreach

  if ($totalCount == 0)
    echo "<p>No problems found.\n";

} // end of showMissingQuestQuests

function showNoProblems ()
{
    middleDetails ($info, 'showBadNPCs');

    echo "<p>No problems found.\n";


} // end of showNoProblems

// analyse creatures to see if they start a missing quest
function showMissingCreatureQuestsDetails ($info)
{
  $totalCount = 0;

  $quest_template = QUEST_TEMPLATE;
  $creature_template = CREATURE_TEMPLATE;

  $tables = array (
   CREATURE_QUEST_STARTER,
   CREATURE_QUEST_FINISHER
  );

  foreach ($tables as $table)
    {
    $results = dbQuery ("SELECT T1.entry AS npc_key,
                                T2.quest AS npc_quest
                        FROM $creature_template AS T1
                            INNER JOIN $table AS T2 USING (entry)
                            LEFT JOIN $quest_template AS T3 ON (T2.quest = T3.entry)
                        WHERE T3.entry IS NULL AND T1.entry <= " . MAX_CREATURE .
                        " ORDER BY T1.entry");

    $count = dbRows ($results);
    $totalCount += $count;

    if ($count)
      {

      if ($table == CREATURE_QUEST_STARTER)
        $label = 'start';
      else
        $label = 'finish';

      $info = array ('heading' => "NPCs which $label a missing quest",
                     'results' => $results,
                     'label' => 'Quest',
                     'field' => 'npc_quest');

      middleDetails ($info, 'showBadNPCs');

      } // end of if any rows

    } // end of foreach

  if ($totalCount == 0)
    showNoProblems ();

} // end of showMissingCreatureQuestsDetails

function showMissingCreatureQuests ()
  {
  pageContent (false, 'Validation', 'NPC/Quest validation',  '', function ($info)
    {
    middleSection ($info, 'showMissingCreatureQuestsDetails');
    } , QUEST_TEMPLATE);
  }


// analyse game objects to see if they start a missing quest
function showMissingGameobjectQuests ()
{
  $totalCount = 0;

  $quest_template = QUEST_TEMPLATE;
  $gameobject_template = GAMEOBJECT_TEMPLATE;

  $tables = array (
   GAMEOBJECT_QUESTRELATION,
   GAMEOBJECT_INVOLVEDRELATION
  );

  foreach ($tables as $table)
    {
    $results = dbQuery ("SELECT T1.entry AS go_key,
                                T2.quest AS go_quest
                        FROM $gameobject_template AS T1
                            INNER JOIN $table AS T2 USING (entry)
                            LEFT JOIN $quest_template AS T3 ON (T2.quest = T3.entry AND T3.ignored = 0)
                        WHERE T3.entry IS NULL
                        ORDER BY T1.entry");

    $count = dbRows ($results);
    $totalCount += $count;

    if ($count)
      {

      if ($table == GAMEOBJECT_QUESTRELATION)
        $label = 'start';
      else
        $label = 'finish';

      showBadGOs ("Game objects which $label a quest which is not on file", $results, 'Quest', 'go_quest');

      } // end of if any rows

    } // end of foreach

  if ($totalCount == 0)
    echo "<p>No problems found.\n";

} // end of showMissingGameobjectQuests

function showGameObjectsNotSpawned ()
  {
  $gameobject_template = GAMEOBJECT_TEMPLATE;
  $spawns_gameobjects = SPAWNS_GAMEOBJECTS;

  $results = dbQuery ("SELECT T1.entry AS go_key
                      FROM $gameobject_template AS T1
                          LEFT JOIN $spawns_gameobjects AS T2
                      ON (T1.entry = T2.spawn_entry)
                      WHERE T2.spawn_entry IS NULL
                      ORDER BY T1.name");

  showBadGOs ("Game objects which are not spawned", $results, '', '');

  } // end of showGameObjectsNotSpawned

function showNoItemText ()
  {
  $item_template = ITEM_TEMPLATE;
  $page_text = PAGE_TEXT;

  $results = dbQuery ("SELECT T1.entry AS item_key,
                              T1.page_text AS page_text
                      FROM $item_template AS T1
                          LEFT JOIN $page_text AS T2
                      ON (T1.page_text = T2.entry)
                      WHERE T2.entry IS NULL AND T1.page_text > 0
                      ORDER BY T1.name");

  showBadItems ("Items which should have text to be read", $results, 'Item', 'page_text');
  } // end of showNoItemText

?>

