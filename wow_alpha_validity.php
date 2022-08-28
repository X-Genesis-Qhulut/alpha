<?php

/*
  Author: X'Genesis Qhulut <XGenesis-Qhulut@protonmail.com>
  Date:   August 2022

  See LICENSE for license details.
*/

// VALIDITY OF DATABASE

// See: https://mangoszero-docs.readthedocs.io/en/latest/database/world/creature-loot-template.html

function showUnknownFaction ()
  {
  global $creatures;

  $factiontemplate = FACTIONTEMPLATE;
  $creature_template = CREATURE_TEMPLATE;

  $results = dbQuery ("SELECT $creature_template.entry AS creature_key,
                              $creature_template.faction AS creature_faction
                      FROM $creature_template
                        LEFT JOIN $factiontemplate ON $factiontemplate.ID =  $creature_template.faction
                        WHERE $factiontemplate.FactionGroup IS NULL
                        AND $creature_template.faction <> 0
                        AND $creature_template.entry <= " . MAX_CREATURE .
                        " ORDER BY name");

  echo "<h2>Creatures with unknown faction</h2>\n";

  echo "<p>(Excludes faction: 0)\n";

  $count = dbRows ($results);

  echo "<ul>\n";
  while ($row = dbFetch ($results))
    echo "<li>" . lookupThing ($creatures,  $row ['creature_key'], 'show_creature') .
          " — Faction: " . $row ['creature_faction'] . "\n";
  dbFree ($results);
  echo "</ul>\n";

  echo "$count row(s) returned.\n";

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
    $results = dbQuery ("SELECT $quest_template.entry AS quest_key,
                                $quest_template.$field AS quest_item
                        FROM $quest_template
                            LEFT JOIN $item_template
                            ON ($quest_template.$field = $item_template.entry
                                AND $item_template.ignored = 0)
                        WHERE $item_template.entry IS NULL
                        AND $quest_template.$field <> 0
                        AND $quest_template.ignored = 0
                        ORDER BY $quest_template.entry");

    $count = dbRows ($results);

    if ($count)
      {
      echo "<h3>" . fixHTML ("Quests where <$field> item is not on file") . "</h3>\n";
      echo "<ul>\n";
      while ($row = dbFetch ($results))
        echo "<li>" . lookupThing ($quests,  $row ['quest_key'], 'show_quest') .
              " — Item: " . $row ['quest_item'] . "\n";
      dbFree ($results);
      echo "</ul>\n";

      echo "$count row(s) returned.\n";
      }
    $totalCount += $count;
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
    $results = dbQuery ("SELECT $quest_template.entry AS quest_key,
                                $quest_template.$field AS quest_spell
                        FROM $quest_template
                            LEFT JOIN $spell
                            ON $quest_template.$field = $spell.ID
                        WHERE $spell.ID IS NULL
                        AND $quest_template.$field <> 0
                        AND $quest_template.ignored = 0
                        ORDER BY $quest_template.entry");

    $count = dbRows ($results);

    if ($count)
      {
      echo "<h3>" . fixHTML ("Quests where <$field> spell is not on file") . "</h3>\n";
      echo "<ul>\n";
      while ($row = dbFetch ($results))
        echo "<li>" . lookupThing ($quests,  $row ['quest_key'], 'show_quest') .
              " — Spell: " . $row ['quest_spell'] . "\n";
      dbFree ($results);
      echo "</ul>\n";

      echo "$count row(s) returned.\n";
      }
    $totalCount += $count;
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

    if ($count)
      {
      echo "<h3>" . fixHTML ("Quests where <$field> quest is not on file") . "</h3>\n";
      echo "<ul>\n";
      while ($row = dbFetch ($results))
        echo "<li>" . lookupThing ($quests,  $row ['quest_key'], 'show_quest') .
              " — Quest: " . $row ['quest_quest'] . "\n";
      dbFree ($results);
      echo "</ul>\n";

      echo "$count row(s) returned.\n";
      }
    $totalCount += $count;

    } // end of foreach

  if ($totalCount == 0)
    echo "<p>No problems found.\n";

} // end of showMissingQuestQuests

?>
