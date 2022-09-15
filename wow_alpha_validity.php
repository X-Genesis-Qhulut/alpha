<?php

/*
  Author: X'Genesis Qhulut <XGenesis-Qhulut@protonmail.com>
  Date:   August 2022

  See LICENSE for license details.
*/

// VALIDITY OF DATABASE

// See: https://mangoszero-docs.readthedocs.io/en/latest/database/world/creature-loot-template.html

// HELPER FUNCTIONS

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
    {
    echo "<li>" . lookupThing ($creatures,  $row ['npc_key'], 'show_creature');
    if ($label)
      echo " ($label " . $row [$field] . ")";
    echo "\n";
    } // end of each row
  dbFree ($results);
  echo "</ul>\n";
  } // end of showBadNPCs

function listBadNPCs ($info)
  {
  global $creatures;

  $heading = $info ['heading'];
  $rows    = $info ['rows'];
  $label = $info ['label'];
  $field = $info ['field'];

  $count = count ($rows);

  boxTitle ("$heading ($count)");

  echo "<ul>\n";
  foreach ($rows as $row)
    {
    echo "<li>" . lookupThing ($creatures,  $row ['npc_key'], 'show_creature');
    if ($label)
      echo " ($label " . $row [$field] . ")";
    echo "\n";
    } // end of each row

  echo "</ul>\n";
  } // end of listBadNPCs


function showBadGOs ($info)
  {
  global $game_objects;

  $heading = $info ['heading'];
  $results = $info ['results'];
  $label = $info ['label'];
  $field = $info ['field'];

  $count = dbRows ($results);

  boxTitle ("$heading ($count)");

  echo "<ul>\n";
  while ($row = dbFetch ($results))
    {
    echo "<li>" . lookupThing ($game_objects,  $row ['go_key'], 'show_go');
    if ($label)
      echo " ($label " . $row [$field] . ")";
    echo "\n";
    }  // end of each row
  dbFree ($results);
  echo "</ul>\n";

  } // end of showBadGOs

function showBadQuests ($info)
  {
  global $quests;

  $heading = $info ['heading'];
  $results = $info ['results'];
  $label = $info ['label'];
  $field = $info ['field'];


  $count = dbRows ($results);

  boxTitle ("$heading ($count)");

  echo "<ul>\n";
  while ($row = dbFetch ($results))
    {
    echo "<li>" . lookupThing ($quests,  $row ['quest_key'], 'show_quest');
    if ($label)
       echo " ($label " . $row [$field] . ")\n";
    echo "\n";
    }
  dbFree ($results);
  echo "</ul>\n";
  } // end of showBadQuests

function showBadItems ($info)
  {
  global $items;

  $heading = $info ['heading'];
  $results = $info ['results'];
  $label = $info ['label'];
  $field = $info ['field'];

  $count = dbRows ($results);

  boxTitle ("$heading ($count)");

  echo "<ul>\n";
  while ($row = dbFetch ($results))
    echo "<li>" . lookupThing ($items,  $row ['item_key'], 'show_item') .
          " ($label " . $row [$field] . ")\n";
  dbFree ($results);
  echo "</ul>\n";
  } // end of showBadItems

function listBadItems ($info)
  {
  global $items;

  $heading = $info ['heading'];
  $rows = $info ['rows'];
  $label = $info ['label'];
  $field = $info ['field'];

  $count = count ($rows);

  boxTitle ("$heading ($count)");

  echo "<ul>\n";
  foreach ($rows as $row)
    {
    echo "<li>" . lookupThing ($items,  $row ['item_key'], 'show_item');
    if ($label)
       echo " ($label " . $row [$field] . ")";
    echo "\n";
    }  // end of each row

  echo "</ul>\n";
  } // end of listBadItems

function showBadSpells ($info)
  {
  global $spells;

  $heading = $info ['heading'];
  $results = $info ['results'];
  $label = $info ['label'];
  $field = $info ['field'];

  $count = dbRows ($results);

  boxTitle ("$heading ($count)");

  echo "<ul>\n";
  while ($row = dbFetch ($results))
    echo "<li>" . lookupThing ($spells,  $row ['spell_key'], 'show_spell') .
          " ($label " . $row [$field] . ")\n";
  dbFree ($results);
  echo "</ul>\n";
  } // end of showBadSpells

function showUnknownFactionDetails ()
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

  $info = array ('heading' => "NPCs with unknown faction",
                 'results' => $results,
                 'label' => 'Faction',
                 'field' => 'creature_faction');

  bottomDetails ($info, 'showBadNPCs');

//  echo "<p>(Excludes faction: 0)\n";  // TODO

  } // end of showUnknownFactionDetails

function showUnknownFaction ()
  {
  setTitle ("Unknown factions");

  pageContent (false, 'Validation', 'Faction validation',  '', function ($info)
    {
    bottomSectionMany ($info, 'showUnknownFactionDetails');
    } , FACTIONTEMPLATE);
  } // end of showUnknownFaction

function showMissingQuestItemsDetails ()
{
  global $quests;

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
      {
      $info = array ('heading' => "Quests where <$field> is missing",
                     'results' => $results,
                     'label' => 'Item',
                     'field' => 'quest_item');

      bottomDetails ($info, 'showBadQuests');
      }

    } // end of foreach

  if ($totalCount == 0)
    showNoProblems ();

} // end of showMissingQuestItemsDetails

function showMissingQuestItems ()
  {
  setTitle ("Quests with missing items");

  pageContent (false, 'Validation', 'Quest items',  '', function ($info)
    {
    bottomSectionMany ($info, 'showMissingQuestItemsDetails');
    } , QUEST_TEMPLATE);
  } // end of showMissingQuestItems

function showBadQuestCountsDetails ()
{
  global $quests;

  $quest_template = QUEST_TEMPLATE;
  $totalCount = 0;

  // game objects / creatures
  for ($i = 1; $i <= QUEST_REQUIRED_CREATURES; $i++)
    {
    $results = dbQuery ("SELECT entry AS quest_key
                        FROM $quest_template
                        WHERE ReqCreatureOrGOCount$i <> 0
                        AND ReqCreatureOrGOId$i = 0
                        AND ignored = 0
                        ORDER BY entry");

    $count = dbRows ($results);
    $totalCount += $count;

    if ($count)
      {
      $info = array ('heading' => "Quests where <ReqCreatureOrGOCount$i> is non-zero but <ReqCreatureOrGOId$i> is zero",
                     'results' => $results,
                     'label' => '',
                     'field' => '');

      bottomDetails ($info, 'showBadQuests');
      }
    } // end of foreach

  // items
  for ($i = 1; $i <= QUEST_REQUIRED_ITEMS; $i++)
    {
    $results = dbQuery ("SELECT entry AS quest_key
                        FROM $quest_template
                        WHERE ReqItemCount$i <> 0
                        AND ReqItemId$i = 0
                        AND ignored = 0
                        ORDER BY entry");

    $count = dbRows ($results);
    $totalCount += $count;

    if ($count)
      {
      $info = array ('heading' => "Quests where <ReqItemCount$i> is non-zero but <ReqItemId$i> is zero",
                     'results' => $results,
                     'label' => '',
                     'field' => '');

      bottomDetails ($info, 'showBadQuests');
      }
    } // end of foreach

  if ($totalCount == 0)
    showNoProblems ();

} // end of showBadQuestCountsDetails

function showBadQuestCounts ()
  {
  setTitle ("Quests with bad counts");

  pageContent (false, 'Validation', 'Quest items',  '', function ($info)
    {
    bottomSectionMany ($info, 'showBadQuestCountsDetails');
    } , QUEST_TEMPLATE);
  } // end of showBadQuestCounts


function showMissingQuestSpellsDetails ()
{
  global $quests;

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
      {
      $info = array ('heading' => "Quests where <$field> spell is missing",
                     'results' => $results,
                     'label' => 'Spell',
                     'field' => 'quest_spell');

      bottomDetails ($info, 'showBadQuests');
      }

    } // end of foreach

  if ($totalCount == 0)
    showNoProblems ();

} // end of showMissingQuestSpellsDetails

function showMissingQuestSpells ()
  {
  setTitle ("Quests with missing spells");

  pageContent (false, 'Validation', 'Quest/spell validation',  '', function ($info)
    {
    bottomSectionMany ($info, 'showMissingQuestSpellsDetails');
    } , QUEST_TEMPLATE);
  } // end of showMissingQuestSpells

// analyse quests to see if the previous, next or next in chain are not on file
function showMissingQuestQuestsDetails ()
{
  global $quests;
  $totalCount = 0;

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
      {
      $info = array ('heading' => "Quests where <$field> quest is missing",
                     'results' => $results,
                     'label' => 'Quest',
                     'field' => 'quest_quest');

      bottomDetails ($info, 'showBadQuests');
      }

    } // end of foreach

  if ($totalCount == 0)
    showNoProblems ();

} // end of showMissingQuestQuestsDetails

function showMissingQuestQuests ()
  {
  setTitle ("Quest chain validation");

  pageContent (false, 'Validation', 'Quest chain validation',  '', function ($info)
    {
    bottomSectionMany ($info, 'showMissingQuestQuestsDetails');
    } , QUEST_TEMPLATE);
  } // end of showMissingQuestQuests

function showNoProblems ()
{
    bottomDetails (false, function  ($info)
      {
      echo "<ul><li>No problems found.</ul>\n";
      });

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

      bottomDetails ($info, 'showBadNPCs');

      } // end of if any rows

    } // end of foreach

  if ($totalCount == 0)
    showNoProblems ();

} // end of showMissingCreatureQuestsDetails

function showMissingCreatureQuests ()
  {
  setTitle ("NPCs with missing quests");

  pageContent (false, 'Validation', 'NPC/Quest validation',  '', function ($info)
    {
    bottomSectionMany ($info, 'showMissingCreatureQuestsDetails');
    } , QUEST_TEMPLATE);
  } // end of showMissingCreatureQuests

function game_object_compare ($a, $b)
  {
  return $game_objects [$a ['go_key']] <=> $game_objects [$b ['go_key']];
  } // end of game_object_compare


// analyse game objects to see if they start a missing quest
function showMissingGameobjectQuestsDetails ()
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
                        ORDER BY T1.name");

    $count = dbRows ($results);
    $totalCount += $count;

    if ($count)
      {

      if ($table == GAMEOBJECT_QUESTRELATION)
        $label = 'start';
      else
        $label = 'finish';

      $info = array ('heading' => "Game objects which $label a missing quest",
                     'results' => $results,
                     'label' => 'Quest',
                     'field' => 'go_quest');

      bottomDetails ($info, 'showBadGOs');

      } // end of if any rows

    } // end of foreach

  if ($totalCount == 0)
    showNoProblems ();

} // end of showMissingGameobjectQuestsDetails

function showMissingGameobjectQuests ()
  {
  setTitle ("G/Os with missing quests");

  pageContent (false, 'Validation', 'Game object/Quest validation',  '', function ($info)
    {
    bottomSectionMany ($info, 'showMissingGameobjectQuestsDetails');
    } , GAMEOBJECT_TEMPLATE);
  } // end of showMissingGameobjectQuests


function showGameObjectsNotSpawnedDetails ()
  {
  $gameobject_template = GAMEOBJECT_TEMPLATE;
  $spawns_gameobjects = SPAWNS_GAMEOBJECTS;

  $results = dbQuery ("SELECT T1.entry AS go_key
                      FROM $gameobject_template AS T1
                          LEFT JOIN $spawns_gameobjects AS T2
                      ON (T1.entry = T2.spawn_entry AND T2.ignored = 0)
                      WHERE T2.spawn_entry IS NULL
                      ORDER BY T1.name");

    $info = array ('heading' => "Game objects which are not spawned",
                   'results' => $results,
                   'label' => '',
                   'field' => '');

    bottomDetails ($info, 'showBadGOs');

  } // end of showGameObjectsNotSpawnedDetails

function showGameObjectsNotSpawned ()
  {
  setTitle ("Game objects not spawned");

  pageContent (false, 'Validation', 'Game objects not spawned',  '', function ($info)
    {
    bottomSectionMany ($info, 'showGameObjectsNotSpawnedDetails');
    } , GAMEOBJECT_TEMPLATE);
  } // end of showGameObjectsNotSpawned

function showNoItemTextDetails ()
  {
  $item_template = ITEM_TEMPLATE;
  $page_text = PAGE_TEXT;

  $results = dbQuery ("SELECT T1.entry AS item_key,
                              T1.page_text AS page_text
                      FROM $item_template AS T1
                          LEFT JOIN $page_text AS T2
                      ON (T1.page_text = T2.entry)
                      WHERE (T2.entry IS NULL OR T2.text = 'Missing Text') AND T1.page_text > 0
                      AND ignored = 0
                      ORDER BY T1.name");

  $count = dbRows ($results);

  if ($count)
    {
    $info = array ('heading' => "Items which should have text to be read",
                 'results' => $results,
                 'label' => 'Item',
                 'field' => 'page_text');

    bottomDetails ($info, 'showBadItems');
    }
  else
    showNoProblems ();

  } // end of showNoItemTextDetails

function showNoItemText ()
  {
  setTitle ("Items with no page");

  pageContent (false, 'Validation', 'Items which should have pages',  '', function ($info)
    {
    bottomSectionMany ($info, 'showNoItemTextDetails');
    } , ITEM_TEMPLATE);
  } // end of showNoItemText



function showMissingSpellSpellsDetails ()
{
  global $quests;

  $spell = SPELL;
  $totalCount = 0;

  $fields = array ();

  for ($i = 1; $i <= SPELL_EFFECT_TRIGGER_SPELLS; $i++)
    $fields [] = "EffectTriggerSpell_$i";

  foreach ($fields as $field)
    {
    $results = dbQuery ("SELECT T1.ID AS spell_key,
                                T1.$field AS spell_spell
                        FROM $spell AS T1
                            LEFT JOIN $spell AS T2
                            ON T1.$field = T2.ID
                        WHERE T2.ID IS NULL
                        AND T1.$field <> 0
                        ORDER BY T1.ID");

    $count = dbRows ($results);
    $totalCount += $count;

    if ($count)
      {
      $info = array ('heading' => "Spells where <$field> spell is missing",
                     'results' => $results,
                     'label' => 'Spell',
                     'field' => 'spell_spell');

      bottomDetails ($info, 'showBadSpells');
      }

    } // end of foreach

  if ($totalCount == 0)
    showNoProblems ();

} // end of showMissingSpellSpellsDetails

function showMissingSpellSpells ()
  {
  setTitle ("Spells with missing spells");

  pageContent (false, 'Validation', 'Spell/spell validation',  '', function ($info)
    {
    bottomSectionMany ($info, 'showMissingSpellSpellsDetails');
    } , SPELL);
  } // end of showMissingSpellSpells


function showMissingSpellItemsDetails ()
{
  $spell = SPELL;
  $item_template = ITEM_TEMPLATE;

  $totalCount = 0;

  $fields = array ();

  for ($i = 1; $i <= SPELL_REAGENTS; $i++)
    $fields [] = "Reagent_$i";

  for ($i = 1; $i <= SPELL_EFFECT_ITEM_TYPES; $i++)
    $fields [] = "EffectItemType_$i";

  foreach ($fields as $field)
    {
    $results = dbQuery ("SELECT T1.ID AS spell_key,
                                T1.$field AS spell_item
                        FROM $spell AS T1
                            LEFT JOIN $item_template AS T2
                            ON (T1.$field = T2.entry AND T2.ignored = 0)
                        WHERE T2.entry IS NULL
                        AND T1.$field <> 0
                        ORDER BY T1.ID");

    $count = dbRows ($results);
    $totalCount += $count;

    if ($count)
      {
      $info = array ('heading' => "Spells where <$field> item is missing",
                     'results' => $results,
                     'label' => 'Item',
                     'field' => 'spell_item');

      bottomDetails ($info, 'showBadSpells');
      }

    } // end of foreach

  if ($totalCount == 0)
    showNoProblems ();

} // end of showMissingSpellItemsDetails

function showMissingSpellItems ()
  {
  setTitle ("Spells with missing items");

  pageContent (false, 'Validation', 'Spell/item validation',  '', function ($info)
    {
    bottomSectionMany ($info, 'showMissingSpellItemsDetails');
    } , SPELL);
  } // end of showMissingSpellItems

function npc_compare ($a, $b)
  {
  global $creatures;
  return $creatures [$a ['npc_key']] <=> $creatures [$b ['npc_key']];
  } // end of npc_compare

// analyse creatures to see if they don't have a model file
function showMissingCreatureModelsDetails ($info)
{
  global $documentRoot, $executionDir;

  $totalCount = 0;

  $creature_template = CREATURE_TEMPLATE;
  // find all spawned creatures
  $spawned = getAllSpawnedCreatures ();

  for ($i = 1; $i <= CREATURE_DISPLAY_IDS; $i++)
    $fields [] = "display_id$i";

  // for each of the display IDs ...
  foreach ($fields as $field)
    {
    // get all creatures, spawned or not
    $results = dbQuery (
           "SELECT T1.entry AS npc_key, T1.$field AS npc_model
            FROM $creature_template AS T1
            WHERE entry < " . MAX_CREATURE . "
            AND T1.$field <> 0
            ");

    $missingModels = array ();
    while ($row = dbFetch ($results))
      {
      if (array_key_exists ($row ['npc_key'], $spawned))
        {
        $model = $row ['npc_model'] . '.webp';
        if ($row ['npc_model'] > MAX_CREATURE_MODEL ||
           !file_exists ("$documentRoot$executionDir/creatures/$model"))
          {
          $missingModels [] = $row;
          }
        } // if spawned
      } // end of for each row
    dbFree ($results);

    $count = count ($missingModels);
    $totalCount += $count;
    usort ($missingModels, 'npc_compare');

    if ($count)
      {
      $info = array ('heading' => "NPCs with no model for <$field>",
                     'rows' => $missingModels,
                     'label' => 'Model',
                     'field' => 'npc_model');
      bottomDetails ($info, 'listBadNPCs');
      } // end of if any rows

    } // end of foreach

  if ($totalCount == 0)
    showNoProblems ();

} // end of showMissingCreatureModelsDetails

function showMissingCreatureModels ()
  {
  setTitle ("NPCs with missing models");

  pageContent (false, 'Validation', 'NPC with missing models',  '', function ($info)
    {
    bottomSectionMany ($info, 'showMissingCreatureModelsDetails');
    } , CREATURE_TEMPLATE);
  } // end of showMissingCreatureModels




// analyse creatures to see if they are not spawned
function showCreaturesNotSpawnedDetails ($info)
{
  global $creatures;
  $spawned = getAllSpawnedCreatures ();
  $badOnes = array ();
  foreach ($creatures as $npc_entry => $npc_name)
    {
    if (!array_key_exists ($npc_entry, $spawned))
      $badOnes [] = array ('npc_key' => $npc_entry);
    }

  usort ($badOnes, 'npc_compare');
  $info = array ('heading' => "NPCs which are not spawned by the database",
                 'rows' => $badOnes,
                 'label' => '',
                 'field' => '');
  bottomDetails ($info, 'listBadNPCs');


} // end of showCreaturesNotSpawnedDetails


function showCreaturesNotSpawned ()
  {
  setTitle ("NPCs not spawned");

  pageContent (false, 'Validation', 'NPC not spawned',  '', function ($info)
    {
    bottomSectionMany ($info, 'showCreaturesNotSpawnedDetails');
    } , CREATURE_TEMPLATE);
  } // end of showCreaturesNotSpawned


function showMissingItemSpellsDetails ()
{
  global $quests;

  $spell = SPELL;
  $item_template = ITEM_TEMPLATE;
  $totalCount = 0;

  $fields = array ();

  for ($i = 1; $i <= ITEM_SPELLS; $i++)
    $fields [] = "spellid_$i";

  foreach ($fields as $field)
    {
    $results = dbQuery ("SELECT T1.entry AS item_key,
                                T1.$field AS item_spell
                        FROM $item_template AS T1
                            LEFT JOIN $spell AS T2
                            ON T1.$field = T2.ID
                        WHERE T2.ID IS NULL
                        AND T1.$field <> 0 AND T1.ignored = 0
                        ORDER BY T1.name");

    $count = dbRows ($results);
    $totalCount += $count;

    if ($count)
      {
      $info = array ('heading' => "Items where <$field> spell is missing",
                     'results' => $results,
                     'label' => 'Spell',
                     'field' => 'item_spell');

      bottomDetails ($info, 'showBadItems');
      }

    } // end of foreach

  if ($totalCount == 0)
    showNoProblems ();

} // end of showMissingItemSpellsDetails

function showMissingItemSpells ()
  {
  setTitle ("Items with missing spells");

  pageContent (false, 'Validation', 'Item/spell validation',  '', function ($info)
    {
    bottomSectionMany ($info, 'showMissingItemSpellsDetails');
    } , ITEM_TEMPLATE);
  } // end of showMissingItemSpells


function unused_item_compare ($a, $b)
  {
  global $items;
  return $items [$a ['item_key']] <=> $items [$b ['item_key']];
  } // end of unused_item_compare

function showUnusedItemsDetails ()
{
  global $quests, $items;

  $spell = SPELL;
  $totalCount = 0;

  // here we will find if an item is used
  $used = array ();
  // initially none are
  foreach ($items as $itemKey => $item)
    $used [$itemKey] = false;

  // eliminate items which are creature loot
  $results = dbQuery ("SELECT item FROM ".CREATURE_LOOT_TEMPLATE." GROUP BY item");
  while ($row = dbFetch ($results))
    $used [$row ['item']] = true;
  dbFree ($results);

  // eliminate items which are creature reference loot
  $results = dbQuery ("SELECT item FROM ".REFERENCE_LOOT_TEMPLATE." GROUP BY item");
  while ($row = dbFetch ($results))
    $used [$row ['item']] = true;
  dbFree ($results);

  // eliminate items which vendors sell
  $results = dbQuery ("SELECT item FROM ".NPC_VENDOR." WHERE entry <= " . MAX_CREATURE);
  while ($row = dbFetch ($results))
    $used [$row ['item']] = true;
  dbFree ($results);

  // eliminate items you can skin for
  $results = dbQuery ("SELECT item FROM ".SKINNING_LOOT_TEMPLATE." WHERE entry <= " . MAX_CREATURE);
  while ($row = dbFetch ($results))
    $used [$row ['item']] = true;
  dbFree ($results);

  // eliminate items used in quests
  // I'm uncertain about the required items - if they are required how do they appear in the first place?

  $fields = array ('SrcItemId');
  // items required
  for ($i = 1; $i <= QUEST_REQUIRED_ITEMS; $i++)
    $fields [] = "ReqItemId$i";
  // item rewards
  for ($i = 1; $i <= QUEST_REWARD_ITEMS; $i++)
    $fields [] = "RewItemCount$i";
  // item choice rewards
  for ($i = 1; $i <= QUEST_REWARD_ITEM_CHOICES; $i++)
    $fields [] = "RewChoiceItemId$i";

  $fieldList = implode (', ', $fields);
  $results = dbQuery ("SELECT $fieldList FROM ".QUEST_TEMPLATE." WHERE ignored = 0");
  while ($row = dbFetch ($results))
    foreach ($fields as $field)
      if ($row [$field])
        $used [$row [$field]] = true;
  dbFree ($results);

  // eliminate items used in spells
  // I'm uncertain about the reagents - if they are required how do they appear in the first place?

  $fields = array ();
  // reagents
  for ($i = 1; $i <= SPELL_REAGENTS; $i++)
    $fields [] = "Reagent_$i";
  // things they create (like conjured bread)
  for ($i = 1; $i <= SPELL_EFFECT_ITEM_TYPES; $i++)
    $fields [] = "EffectItemType_$i";

  $fieldList = implode (', ', $fields);
  $results = dbQuery ("SELECT $fieldList FROM ".SPELL);
  while ($row = dbFetch ($results))
    foreach ($fields as $field)
      if ($row [$field])
        $used [$row [$field]] = true;
  dbFree ($results);

  // eliminate items that NPCs equip

   $fields = array ();
  for ($i = 1; $i <= CREATURE_EQUIP_ITEMS; $i++)
    $fields [] = "equipentry$i";

  $fieldList = implode (', ', $fields);
  $results = dbQuery ("SELECT $fieldList FROM ".CREATURE_EQUIP_TEMPLATE);
  while ($row = dbFetch ($results))
    foreach ($fields as $field)
      if ($row [$field])
        $used [$row [$field]] = true;
  dbFree ($results);

  // eliminate items dropped by game objects

  $results = dbQuery ("SELECT item FROM ".GAMEOBJECT_LOOT_TEMPLATE." GROUP BY item");
  while ($row = dbFetch ($results))
    $used [$row ['item']] = true;
  dbFree ($results);

  // build appropriate array of unused items

  $rows = array ();
  foreach ($used as $usedKey => $wasItUsed)
    if (!$wasItUsed)
      $rows [] = array ('item_key' => $usedKey);

  $count = count ($rows);

  if ($count > 0)
    {
    // sort into name order
    usort ($rows, 'unused_item_compare');

    $info = array ('heading' => "Items which are not used",
                     'rows' => $rows,
                     'label' => '',
                     'field' => '');

    bottomDetails ($info, 'listBadItems');
    }
  else
    showNoProblems ();

} // end of showUnusedItemsDetails

function showUnusedItems ()
  {
  setTitle ("Items which are not used");

  pageContent (false, 'Validation', 'Unused items',  '', function ($info)
    {
    bottomSectionMany ($info, 'showUnusedItemsDetails');
    } , ITEM_TEMPLATE);
  } // end of showUnusedItems




?>

