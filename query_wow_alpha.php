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

define ('QUERY_LIMIT', 200);
define ('MAX_CREATURE', 5759);    // creatures > this are not in 0.5.3 alpha

$documentRoot = $_SERVER['DOCUMENT_ROOT'];

$dblink = false;

// incorporate our stylesheet
$time = filemtime ("$documentRoot$executionDir/alpha.css");
echo "<link rel='stylesheet' href='$executionDir/alpha.css?v=$time'>\n";
echo "</head>\n";
echo "<body>\n";

function expandField ($value, $expandType)
  {
  global $skills, $spells, $factions, $creatures, $quests, $game_objects, $maps;

  global $lastItemClass;

    switch ($expandType)
      {
      case 'item':          lookupItem ($value, 1); break;
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
      case 'rank':          tdxr ("$value: " . CREATURE_RANK [$value]); break;
      case 'gameobject_type':          tdxr ("$value: " . GAMEOBJECT_TYPE [$value]); break;
      case 'inventory_type':  tdxr ("$value: " . INVENTORY_TYPE [$value]); break;
      case 'item_class'   : tdxr ("$value: " . ITEM_CLASS [$value]);
                              $lastItemClass = $value;    // remember for when the subclass comes along
                              break;
      case 'item_subclass'   : tdxr ("$value: " . ITEM_SUBCLASSES [$lastItemClass] [$value]); break;
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

// shows all fields from any table
function showOneThing ($table, $key, $id, $description, $nameField, $expand)
  {
  $info = dbQueryParam ("SHOW COLUMNS FROM $table", array ());

  $row = dbQueryOneParam ("SELECT * FROM $table WHERE $key = ?", array ('i', &$id));
  if (!$row)
    {
    ShowWarning ("$description $id is not on the database");
    return;
    }

  $name = htmlspecialchars ($row [$nameField]);

  echo "<h1 class='one_item'>" . htmlspecialchars ($description) . " $id - $name</h1>\n";
  echo "<h2 class='one_item_table'>Table: " . htmlspecialchars ($table) . "</h2>\n";
  echo "<table>\n";
  echo "<tr>\n";

  th ('Field');
  th ('Value');
  echo "</tr>\n";

  foreach ($info as $col)
    {
    echo "<tr>\n";
    $fieldName = $col ['Field'];
    tdx ($fieldName);
    // check if we can be more informative, like show an item name
    if (isset ($expand [$fieldName]))
      expandField ($row [$fieldName], $expand [$fieldName]);
    else
      tdx ($row [$fieldName], $col ['Type'] == 'text' ? 'tdl' : 'tdr');
    echo "</tr>\n";
    } // end of foreach
  echo "</table>\n";

  } // end of showOneThing


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

function showMenu ()
  {
  $menu = array (
    'Spells'        => 'spells',
    'Items'         => 'items',
    'Creatures'     => 'creatures',
    'Quests'        => 'quests',
    'Game Objects'  => 'game_objects',

  // more here
  );

  asort ($menu);

  foreach ($menu as $desc => $newAction)
    {
    echo "<div class='menu_item'><a href='?action=$newAction'>$desc</a></div>\n";
    }

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

// check filter regexp is OK
if ($filter)
  {
  $ok = @preg_match ("`$filter`", "whatever", $matches);
  if ($ok === false)
    {
    echo "<h2>Filter error</h2>\n";
    $warnings = error_get_last();
    $warning = $warnings ['message'];
    showSearchForm ();
    Problem ("Error evaluating regular expression: $filter\n\n$warning");
    } // if not OK
  } // if we have a filter


// open database, die on error
$dblink = mysqli_connect($dbserver, $dbuser, $dbpassword, $world_dbname);
if (mysqli_connect_errno())
  MajorProblem ("Cannot connect to server $dbserver: " . mysqli_connect_error());

$PHP_SELF = $_SERVER['PHP_SELF'];

echo "<div class='banner' title='Click for menu'><a href='$PHP_SELF'>WoW Alpha 0.5.3 database</a></div>\n";

// grab things we are likely to cross-reference a lot
if ($action)
  {
  getThings ($items,    ITEM_TEMPLATE,  'entry',    'name', 'WHERE ignored = 0');              // items
  getThings ($skills,   SKILLLINE,      'ID',       'DisplayName_enUS');  // skills
  getThings ($spells,   SPELL,          'ID',       'Name_enUS');         // spells
  getThings ($factions, FACTION,        'ID',       'Name_enUS');         // factions
  getThings ($creatures, CREATURE_TEMPLATE, 'entry', 'name', 'WHERE entry <= ' . MAX_CREATURE);              // creatures
  getThings ($game_objects,GAMEOBJECT_TEMPLATE, 'entry', 'name');         // game objects
  getThings ($quests,   QUEST_TEMPLATE, 'entry',     'Title', 'WHERE ignored = 0');            // quests
  getThings ($maps,     MAP, 'ID',     'Directory');                      // maps
  }

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

  default: showMenu (); break;

} // end of switch on action

if ($action)
  echo "<p><a href='$PHP_SELF'>Main Menu</a>\n";

echo "<p><a href='$PHP_SELF'><img src='avatar.jpg' alt='Avatar' title='Click for main menu'/></a> Designed and coded by X’Genesis Qhulut.</p>\n";

// wrap up web page
echo "\n</body>\n</html>\n";
