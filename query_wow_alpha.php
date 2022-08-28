<!DOCTYPE html>
<html lang="en">
<head>
<meta http-equiv="Content-Type" content="text/html;charset=utf-8" >
<title>WoW Alpha database query</title>
<?php

/*

  Author: X'Genesis Qhulut <XGenesis-Qhulut@protonmail.com>
  Date:   August 2022

  See LICENSE for license details.

*/

// Configuration: database name, password, etc.
require ("wow_alpha_config.php");
// database management
require ("wow_alpha_database.php");
// useful stuff
require ("wow_alpha_utils.php");
// tables of constants
require ("wow_alpha_constants.php");
// table handling for various tables
require ("wow_alpha_creatures.php");
require ("wow_alpha_quests.php");
require ("wow_alpha_items.php");
require ("wow_alpha_spells.php");
require ("wow_alpha_game_objects.php");
require ("wow_alpha_maps.php");
require ("wow_alpha_zones.php");
require ("wow_alpha_ports.php");
require ("wow_alpha_skills.php");
require ("wow_alpha_tables.php");
require ("wow_alpha_proximity.php");
require ("wow_alpha_validity.php");

$documentRoot = $_SERVER['DOCUMENT_ROOT'];

$dblink = false;

// incorporate our stylesheet
$executionDir = EXECUTIONDIR;
$time = filemtime ("$documentRoot$executionDir/alpha.css");
echo "<link rel='stylesheet' href='$executionDir/alpha.css?v=$time'>\n";
echo "<script src='editing.js' defer></script>\n";
echo "</head>\n";
echo "<body>\n";

// menu at top of page

define ('MENU', array (
  'Creatures'     => 'creatures',
  'Game Objects'  => 'game_objects',
  'Items'         => 'items',
  'Spells'        => 'spells',
  'Maps'          => 'maps',
  'Ports'         => 'ports',
  'Quests'        => 'quests',
  'Skills'        => 'skills',
  'Tables'        => 'tables',
  'Zones'         => 'zones',

// more here
));


function expandField ($value, $expandType)
  {
  global $skills, $spells, $factions, $npc_factions, $creatures, $quests, $game_objects, $maps;

  global $lastItemClass;

    switch ($expandType)
      {

      // not table lookups, just formatting
      case 'time':          tdxr (convertTimeGeneral ($value)); break;
      case 'time_secs':     tdxr (convertTimeGeneral ($value * 1000)); break;
      case 'gold':          tdxr (convertGold ($value)); break;
      case 'mask':          tdxr (getMask ($value)); break;

      // these things give hyperlinks
      case 'spell':         tdhr (lookupThing ($spells,     $value, 'show_spell'));  break;
      case 'creature':      tdhr (lookupThing ($creatures,  $value, 'show_creature'));  break;
      case 'quest':         tdhr (lookupThing ($quests,     abs($value), 'show_quest'));  break;
      case 'item':          lookupItem ($value, 1); break;

      case 'creature_or_go':
                            if ($value < 0)
                              tdhr (lookupThing ($game_objects, -$value, 'show_go'));
                            else
                              tdhr (lookupThing ($creatures, $value, 'show_creature'));
                            break;

      // table lookups
      case 'spell_school':    tdxr (expandSimple (SPELL_SCHOOLS,   $value)); break;
      case 'spell_effect':    tdxr (expandSimple (SPELL_EFFECTS,   $value)); break;
      case 'spell_aura':      tdxr (expandSimple (SPELL_AURAS,     $value)); break;
      case 'spell_implicit_target':  tdxr (expandSimple (SPELL_IMPLICIT_TARGET, $value)); break;
      case 'power_type':      tdxr (expandSimple (POWER_TYPES,     $value)); break;
      case 'movement_type':   tdxr (expandSimple (MOVEMENT_TYPE,   $value)); break;
      case 'trainer_type':    tdxr (expandSimple (TRAINER_TYPE,    $value)); break;
      case 'bonding':         tdxr (expandSimple (BONDING,         $value)); break;
      case 'skill_type':      tdxr (expandSimple (SKILL_TYPES,     $value)); break;
      case 'rank':            tdxr (expandSimple (CREATURE_RANK,   $value)); break;
      case 'creature_type':   tdxr (expandSimple (CREATURE_TYPES,   $value)); break;
      case 'item_stats':      tdxr (expandSimple (ITEM_STATS,      $value)); break;
      case 'gameobject_type': tdxr (expandSimple (GAMEOBJECT_TYPE, $value)); break;
      case 'inventory_type':  tdxr (expandSimple (INVENTORY_TYPE,  $value)); break;
      case 'class'       :    tdxr (expandSimple (CLASSES,         $value)); break;
      case 'race'       :     tdxr (expandSimple (RACES,           $value)); break;
      case 'map':             tdxr (expandSimple ($maps,           $value)); break;
      case 'skill':           tdxr (expandSimple ($skills,         $value)); break;
      case 'quest_type':      tdxr (expandSimple (QUEST_TYPE,      $value)); break;
      case 'faction':         tdxr (getFaction ($value));                    break;
      case 'npc_faction':     tdxr (expandSimple ($npc_factions,   $value)); break;

      case 'item_class'   :   tdxr (getItemClass ($value));
                              $lastItemClass = $value;    // remember for when the subclass comes along
                              break;

      case 'item_subclass'   :      tdxr (getItemSubClass ($value)); break;

      // masks (ie. possible multiple results depending on the bits matching)
      case 'item_subclass_mask'   :    tdxr (expandItemSubclassMask ($lastItemClass, $value)); break;
      case 'race_mask':                tdxr (expandRaceMask ($value));              break;
      case 'class_mask':               tdxr (expandClassMask ($value));             break;
      case 'school_mask':              tdxr (expandMask (SPELL_SCHOOLS, $value));   break;
      case 'inhabit_type_mask':        tdxr (inhabitTypeMask ($value));             break;
      case 'mechanic_mask':            tdxr (expandMechanicMask ($value));          break;
      case 'flags_extra_mask':         tdxr (expandFlagsExtraMask ($value));        break;
      case 'npc_flags_mask':           tdxr (expandNpcFlagsMask ($value));          break;
      case 'item_flags_mask':          tdxr (expandItemFlagsMask ($value));         break;
      case 'spell_target_type_mask':   tdxr (expandSpellTargetTypeMask ($value));   break;
      case 'spell_attributes_mask':    tdxr (expandSpellAttributesMask ($value));   break;
      case 'spell_attributes_ex_mask': tdxr (expandSpellAttributesExMask ($value)); break;
      case 'creature_static_flags'   : tdxr (expandShiftedMask (CREATURE_STATIC_FLAGS, $value)); break;
      case 'quest_flags'   :           tdxr (expandShiftedMask (QUEST_FLAGS, $value)); break;
      case 'quest_special_flags'   :   tdxr (expandShiftedMask (QUEST_SPECIAL_FLAGS, $value)); break;
      case 'spell_interrupt_flags'   : tdxr (expandShiftedMask (SPELL_INTERRUPT_FLAGS, $value)); break;
      default:                         tdxr ("$expandType not known, id = $value"); break;

      } // end of switch

  } // end of expandField

// look up items for cross-referencing (eg. in spells)
function getThings (&$theArray, $table, $key, $description, $condition = '')
{
  $results = dbQuery ("SELECT $key, $description FROM $table $condition");
  while ($row = dbFetch ($results))
    {
    $theArray [$row [$key]] = $row [$description];
    }
  dbFree ($results);
} // end of getThings

define ('MONTHS', array (
  'Jan', 'Feb', 'Mar', 'Apr', 'May', 'Jun',
  'Jul', 'Aug', 'Sep', 'Oct', 'Nov', 'Dec',
  ));

// The applied updates dates are in the format: DDMMYYYYs where s is a sequence number
// To get the latest we need to get them into YYYYMMDDs sequence, then find the highest
function checkAppliedUpdates ($table)
  {

  $latest = 0;

  $result = dbQuery ("SELECT * FROM $table");
  while ($row = dbFetch ($result))
    {
    $s = strval ($row ['id']);
    $year = substr ($s, 4, 4);
    $month = substr ($s, 2, 2);
    $day = substr ($s, 0, 2);
    $seq = substr ($s, 8, 1);
    // ignore bad dates, <sigh>
    if (intval ($day) < 1 || intval ($day) > 31)
      continue;
    if (intval ($month) < 1 || intval ($month) > 12)
      continue;
    $revDate = $year . $month . $day . $seq;
    if ($revDate > $latest)
      $latest = $revDate;
    }
  dbFree ($result);

  return $latest;
  } // end of checkAppliedUpdates

function convertDate ($date)
{
  echo substr ($date, 6, 2) . ' ' .
       MONTHS [intval (substr ($date, 4, 2)) - 1] . ' ' .
       substr ($date, 0, 4);
  if (strlen ($date) > 8)
        echo " - sequence: " . substr ($date, 8, 1);
} // end of convertDate

function showBigMenu ()
  {
  foreach (MENU as $desc => $newAction)
    {
    echo "<div class='menu_item'><a href='?action=$newAction'>$desc</a></div>\n";
    }

  // find last database updates

  echo "<h3>Database updates</h3>\n";
  $latest_dbc   = checkAppliedUpdates (APPLIED_UPDATES_DBC);
  echo "<p>Latest DBC table update: ";
  convertDate ($latest_dbc) . "\n";

  $latest_world = checkAppliedUpdates (APPLIED_UPDATES_WORLD);
  echo "<br>Latest World table update: ";
  convertDate ($latest_world) . "\n";

  echo "<p>Databases from <a href='https://github.com/The-Alpha-Project/alpha-core/tree/master/etc/databases'>
    GitHub: alpha-core/etc/databases/</a>\n";

  echo "<hr>\n";
  echo "<h3>Utilities</h3>
  <ul>
    <li><a href='?action=proximity'>Spawn point proximity search</a>
  </ul>
  <h3>Database validation</h3>
  <h4>NPCs</h4>
  <ul>
    <li><a href='?action=unknown_faction'>NPCs with unknown faction</a>
    <li><a href='?action=npc_missing_quest'>NPCs which start/finish a missing quest</a>
  </ul>

  <h4>Game objects</h4>
  <ul>
    <li><a href='?action=go_missing_quest'>Game objects which start/finish a missing quest</a>
  </ul>


  <h4>Quests</h4>
  <ul>
  <li><a href='?action=quest_missing_item' >Quests with missing items</a>
  <li><a href='?action=quest_missing_spell'>Quests with missing spells</a>
  <li><a href='?action=quest_missing_quest'>Quests with missing quest chains</a>
  </ul>
  ";
  } // end of showBigMenu


function showMenu ()
  {
  echo "<div class='links_at_top'>\n";
  foreach (MENU as $desc => $newAction)
    {
    echo "<a href='?action=$newAction'>$desc</a>\n";
    }
  echo "</div>\n";

  } // end of showMenu


//-----------------------------------------------------------------------------
// START HERE
//-----------------------------------------------------------------------------

// get action
$action  = getGP ('action', 40, $VALID_ACTION);
// get ID (eg. spell ID)
$id      = getGP ('id',      8, $VALID_NUMBER);
// get filter
$filter  = getGP ('filter');
// and secondary filter
$filter_column = getGP ('filter_column', 30, $VALID_SQL_ID);
// secondary filter comparison
$filter_compare = getGP ('filter_compare', 30, $VALID_ACTION);
// secondary filter value - a number, a hex number or a float
$filter_value = getGP ('filter_value');
// convert from hex or binary to decimal for the SQL query
if (preg_match ('/^0[xX]([0-9A-Fa-f]+)$/', $filter_value, $matches))
  $fixed_filter_value = hexdec ($matches [1]);
elseif (preg_match ('/^0[bB]([01]+)$/', $filter_value, $matches))
  $fixed_filter_value = bindec ($matches [1]);
else
  $fixed_filter_value = $filter_value;

// get sorting order
$sort_order = getP ('sort_order', 30, $VALID_SQL_ID);
// get page number
$page = getGP ('page',      8, $VALID_NUMBER);

// table name
$table  = getG ('table', 30, $VALID_SQL_ID);
// database name
$database  = getG ('database', 15, $VALID_SQL_ID);

// see if they used the right arrow
$rightArrow = getP ('RightArrow_x',    8, $VALID_NUMBER);
// see if they used the left arrow
$leftArrow = getP ('LeftArrow_x',      8, $VALID_NUMBER);

if (!$page || $page < 1)
  $page = 1;
if ($rightArrow)
  $page++;
elseif ($leftArrow)
  $page--;
else
  $page = 1;  // if no arrow pressed, show page 1

// open database, die on error
$dblink = mysqli_connect(DBSERVER, DBUSER, DBPASSWORD, WORLD_DBNAME);
if (mysqli_connect_errno())
  MajorProblem ("Cannot connect to server " . DBSERVER . ':' . mysqli_connect_error());

$PHP_SELF = $_SERVER['PHP_SELF'];

echo "<div class='banner' title='Click for menu'><a href='$PHP_SELF'>WoW Alpha 0.5.3 database browser</a></div>\n";

/*
// The <noscript> tag doesn't detect if NoScript is in use - so put up a message and use scripting to hide it
    echo "
<div id='noscript_warning_id'>
<noscript>
<p><i>(Enable Javascript to view spawn points on the map.)</i></p>
</noscript>
</div>
<script>
document.getElementById('noscript_warning_id').style.display = 'none';
</script>
";

*/

// grab things we are likely to cross-reference a lot
if ($action)
  {
  getThings ($items,        ITEM_TEMPLATE,       'entry', 'name', 'WHERE ignored = 0');     // items
  getThings ($skills,       SKILLLINE,           'ID',    'DisplayName_enUS');  // skills
  getThings ($spells,       SPELL,               'ID',    'Name_enUS');         // spells
  getThings ($factions,     FACTION,             'ID',    'Name_enUS');         // factions
  getThings ($creatures,    CREATURE_TEMPLATE,   'entry', 'name', 'WHERE entry <= ' . MAX_CREATURE); // creatures
  getThings ($game_objects, GAMEOBJECT_TEMPLATE, 'entry', 'name');              // game objects
  getThings ($quests,       QUEST_TEMPLATE,      'entry', 'Title', 'WHERE ignored = 0'); // quests
  getThings ($maps,         MAP,                 'ID',    'Directory');         // maps
  getThings ($zones,        WORLDMAPAREA,        'AreaID',    'AreaName');          // zones

  $skills [0] = 'None';
  $factions [0] = 'None';
  $npc_factions [0] = 'None';

  // NPC factions are more indirect
  $faction = FACTION;
  $factiontemplate = FACTIONTEMPLATE;
  $results = dbQuery ("SELECT $factiontemplate.ID AS template_key, Name_enUS FROM $faction
                       LEFT JOIN $factiontemplate ON ($faction.ID = $factiontemplate.Faction)");
  while ($row = dbFetch ($results))
    $npc_factions [$row ['template_key']] = $row ['Name_enUS'];
  dbFree ($results);
  }

if ($action)
  {
  showMenu ();
  switch ($action)
    {
      case 'spells'     : showSpells (); break;
      case 'show_spell' : showOneSpell ($id); break;

      case 'items'      : showItems (); break;
      case 'show_item'  : showOneItem ($id); break;

      case 'creatures'  : showCreatures (); break;
      case 'show_creature'  : showOneCreature ($id); break;

      case 'quests'      : showQuests (); break;
      case 'show_quest'  : showOneQuest ($id); break;

      case 'game_objects' : showGameObjects (); break;
      case 'show_go'      : showOneGameObject ($id); break;

      case 'maps'         : showMaps (); break;
      case 'show_map'     : showOneMap ($id); break;

      case 'zones'        : showZones (); break;
      case 'show_zone'    : showOneZone ($id); break;

      case 'ports'       : showPorts (); break;
      case 'show_port'   : showOnePort ($id); break;

      case 'skills'      : showSkills (); break;
      case 'show_skill'  : showOneSkill ($id); break;

      case 'tables'      : showTables (); break;
      case 'show_table'  : showOneTable ($id); break;

      case 'proximity'   : showProximity (); break;
      case 'unknown_faction'      : showUnknownFaction (); break;
      case 'quest_missing_item'   : showMissingQuestItems (); break;
      case 'quest_missing_spell'   : showMissingQuestSpells (); break;
      case 'quest_missing_quest'   : showMissingQuestQuests (); break;
      case 'npc_missing_quest'   : showMissingCreatureQuests (); break;
      case 'go_missing_quest'   : showMissingGameobjectQuests (); break;

      default: ShowWarning ('Unknown action'); break;

    } // end of switch on action
  echo "<hr><a href='$PHP_SELF'>Main Menu</a>\n";
  }
else
  showBigMenu ();


echo "<div class='credits'><a href='$PHP_SELF'><img style='width:50px; float:left; margin-left: 0px; margin-right:5px;' src='avatar.jpg' alt='Avatar' title='Click for main menu'/></a>
      Designed and coded in August 2022 by X’Genesis Qhulut.
      <br>This browser at GitHub:
      <a href='https://github.com/X-Genesis-Qhulut/alpha' style='white-space: nowrap;'>X-Genesis-Qhulut / alpha</a><br>
      <b>WoW Alpha Project</b> at GitHub: <a href='https://github.com/The-Alpha-Project/alpha-core'  style='white-space: nowrap;'>
      The-Alpha-Project / alpha-core</a>
      <br><b>Discord channel</b>: <a href='https://discord.gg/RzBMAKU'>Alpha Project</a>.
      Thanks to Grender!\n
      <p>Thanks to the original developers of WoW and also John Staats for writing
      <br><i>The WoW Diary: A Journal of Computer Game Development.</i>\n
      <br>Maps courtesy of Entropy and <a href='https://wow.tools/maps/Kalimdor/'>WoW.tools</a>.
      Creature screenshots by Daribon.\n
      <details><summary>Image assets shown are Copyright ©2022 Blizzard Entertainment, Inc.</summary>\n
      <p>Images used in accordance with permission given <a href='https://www.blizzard.com/en-us/legal/c1ae32ac-7ff9-4ac3-a03b-fc04b8697010/blizzard-legal-faq'>here</a>
      “for home, noncommercial and personal use only”.
      <p><b>Blizzard Entertainment®</b>
      <br>Blizzard Entertainment is a trademark or registered trademark of Blizzard Entertainment, Inc. in the U.S. and/or other countries. All rights reserved.</details>\n
      </div>\n";

// wrap up web page
echo "\n</body>\n</html>\n";
