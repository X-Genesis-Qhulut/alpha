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

define ('QUERY_LIMIT', 200);
define ('MAX_CREATURE', 5759);    // creatures > this are not in 0.5.3 alpha

$documentRoot = $_SERVER['DOCUMENT_ROOT'];

$dblink = false;

// incorporate our stylesheet
$time = filemtime ("$documentRoot$executionDir/alpha.css");
echo "<link rel='stylesheet' href='$executionDir/alpha.css?v=$time'>\n";
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
  'Zones'         => 'zones',

// more here
));


function expandField ($value, $expandType)
  {
  global $skills, $spells, $factions, $creatures, $quests, $game_objects, $maps;

  global $lastItemClass;

    switch ($expandType)
      {
      case 'item':          lookupItem ($value, 1); break;
      case 'time':          tdxr (convertTime ($value)); break;
      case 'gold':          tdxr (convertGold ($value)); break;
      case 'spell':         tdhr (lookupThing ($spells, $value, 'show_spell'));  break;
      case 'map':           tdxr ("$value: " . $maps [$value]); break;
      case 'creature':      tdhr (lookupThing ($creatures, $value, 'show_creature'));  break;
      case 'creature_or_go':
              if ($value < 0)
                tdhr (lookupThing ($game_objects, -$value, 'show_go'));
              else
                tdhr (lookupThing ($creatures, $value, 'show_creature'));
              break;
      case 'quest':         tdhr (lookupThing ($quests, $value, 'show_quest'));  break;
      case 'spell_school':  tdxr ("$value: " . SPELL_SCHOOLS [$value]); break;
      case 'spell_effect':  tdxr ("$value: " . SPELL_EFFECTS [$value]); break;
      case 'power_type':    tdxr ("$value: " . POWER_TYPES [$value]); break;
      case 'movement_type': tdxr ("$value: " . MOVEMENT_TYPE [$value]); break;
      case 'trainer_type':  tdxr ("$value: " . TRAINER_TYPE [$value]); break;
      case 'bonding':       tdxr ("$value: " . BONDING [$value]); break;
      case 'skill_type':    tdxr ("$value" . ($value ? ': ' . SKILL_TYPES [$value] : '')); break;
      case 'rank':          tdxr ("$value: " . CREATURE_RANK [$value]); break;
      case 'item_stats':          tdxr ("$value: " . ITEM_STATS [$value]); break;
      case 'gameobject_type':          tdxr ("$value: " . GAMEOBJECT_TYPE [$value]); break;
      case 'inventory_type':  tdxr ("$value: " . INVENTORY_TYPE [$value]); break;
      case 'item_class'   : tdxr (getItemClass ($value));
                              $lastItemClass = $value;    // remember for when the subclass comes along
                              break;
      case 'item_subclass'   : tdxr (getItemSubClass ($value)); break;
      case 'item_subclass_mask'   : tdxr (expandItemSubclassMask ($lastItemClass, $value)); break;
      case 'class'       : if ($value)
                             tdxr ("$value: " . CLASSES [$value]);
                           else
                             tdxr ($value);
                           break;
      case 'race'       : if ($value)
                             tdxr ("$value: " . RACES [$value]);
                           else
                             tdxr ($value);
                           break;
      case 'skill':         tdxr ($value ? "$value: " . $skills [$value]: $value); break;
      case 'faction':       tdxr (getFaction ($value));  break;
      case 'race_mask':             tdxr (expandRaceMask ($value)); break;
      case 'class_mask':            tdxr (expandClassMask ($value)); break;
      case 'inhabit_type_mask':     tdxr (inhabitTypeMask ($value)); break;
      case 'mechanic_immune_mask':  tdxr (expandMechanicImmuneMask ($value)); break;
      case 'flags_extra_mask':      tdxr (expandFlagsExtraMask ($value)); break;
      case 'npc_flags_mask':        tdxr (expandNpcFlagsMask ($value)); break;
      case 'item_flags_mask':        tdxr (expandItemFlagsMask ($value)); break;
      case 'spell_target_type_mask':  tdxr (expandSpellTargetTypeMask ($value)); break;
      case 'spell_attributes_mask':   tdxr (expandSpellAttributesMask ($value)); break;
      case 'spell_attributes_ex_mask':   tdxr (expandSpellAttributesExMask ($value)); break;
      default:              tdxr ("$expandType not known, id = $value"); break;

      } // end of switch

  } // end of expandField

// look up items for cross-referencing (eg. in spells)
function getThings (&$theArray, $table, $key, $description, $condition = '')
{
  $result = dbQuery ("SELECT $key, $description FROM $table $condition");
  while ($row = dbFetch ($result))
    {
    $theArray [$row [$key]] = $row [$description];
    }
  dbFree ($result);
} // end of getThings

function showBigMenu ()
  {
  foreach (MENU as $desc => $newAction)
    {
    echo "<div class='menu_item'><a href='?action=$newAction'>$desc</a></div>\n";
    }
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
$filter  = getP ('filter');
// get sorting order
$sort_order = getP ('sort_order', 30, $VALID_SQL_ID);

// open database, die on error
$dblink = mysqli_connect($dbserver, $dbuser, $dbpassword, $world_dbname);
if (mysqli_connect_errno())
  MajorProblem ("Cannot connect to server $dbserver: " . mysqli_connect_error());

$PHP_SELF = $_SERVER['PHP_SELF'];

echo "<div class='banner' title='Click for menu'><a href='$PHP_SELF'>WoW Alpha 0.5.3 database browser</a></div>\n";

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

      default: ShowWarning ('Unknown action'); break;

    } // end of switch on action
  echo "<hr><a href='$PHP_SELF'>Main Menu</a>\n";
  }
else
  showBigMenu ();

echo "<p><a href='$PHP_SELF'><img src='avatar.jpg' alt='Avatar' title='Click for main menu'/></a><p>
      Designed and coded by X’Genesis Qhulut.
      <br>Source code for this browser at GitHub:
      <a href='https://github.com/X-Genesis-Qhulut/alpha'>X-Genesis-Qhulut / alpha</a><br>
      WoW Alpha Project at: GitHub: <a href='https://github.com/The-Alpha-Project/alpha-core'>
      The-Alpha-Project / alpha-core</a>
      <br>Thanks to the original developers of WoW and also John Staats for writing
      “The WoW Diary: A Journal of Computer Game Development”.\n
      <details><summary>Image assets shown are Copyright ©2022 Blizzard Entertainment, Inc.</summary>\n
      <p>Images used in accordance with permission given <a href='https://www.blizzard.com/en-us/legal/c1ae32ac-7ff9-4ac3-a03b-fc04b8697010/blizzard-legal-faq'>here</a>
      “for home, noncommercial and personal use only”.
      <p><b>Blizzard Entertainment®</b>
      <br>Blizzard Entertainment is a trademark or registered trademark of Blizzard Entertainment, Inc. in the U.S. and/or other countries. All rights reserved.</details>\n";

// wrap up web page
echo "\n</body>\n</html>\n";
