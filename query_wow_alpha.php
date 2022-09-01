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
// main menu
require ("wow_alpha_menu.php");
// what functions we support and what handles them
require ("wow_alpha_handlers.php");
// useful stuff
require ("wow_alpha_utils.php");
// tables of constants
require ("wow_alpha_constants.php");
// table handling for various tables
require ("wow_alpha_creatures.php");
require ("wow_alpha_quests.php");
require ("wow_alpha_books.php");
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
require ("wow_alpha_spell_visual.php");
// field expansions
require ("wow_alpha_fields.php");


//-----------------------------------------------------------------------------
// START HERE
//-----------------------------------------------------------------------------

$documentRoot = $_SERVER['DOCUMENT_ROOT'];
$dblink = false;

// incorporate our stylesheet
$executionDir = EXECUTIONDIR;
$time = filemtime ("$documentRoot$executionDir/alpha.css");
echo "<link rel='stylesheet' href='$executionDir/alpha.css?v=$time'>\n";
echo "<script src='editing.js' defer></script>\n";
echo "</head>\n";
echo "<body>\n";

// GET PARAMETERS FROM POST OR GET

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

// for page text, the item which leads to the text
$item      = getGP ('item',      8, $VALID_NUMBER);

// work out page number

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
  getThings ($items,        ITEM_TEMPLATE,       'entry',  'name', 'WHERE ignored = 0');     // items
  getThings ($skills,       SKILLLINE,           'ID',     'DisplayName_enUS');  // skills
  getThings ($spells,       SPELL,               'ID',     'Name_enUS');         // spells
  getThings ($factions,     FACTION,             'ID',     'Name_enUS');         // factions
  getThings ($creatures,    CREATURE_TEMPLATE,   'entry',  'name', 'WHERE entry <= ' . MAX_CREATURE); // creatures
  getThings ($game_objects, GAMEOBJECT_TEMPLATE, 'entry',  'name');              // game objects
  getThings ($quests,       QUEST_TEMPLATE,      'entry',  'Title', 'WHERE ignored = 0'); // quests
  getThings ($maps,         MAP,                 'ID',     'Directory');         // maps
  getThings ($zones,        WORLDMAPAREA,        'AreaID', 'AreaName');          // zones

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
  } // end of having an action

// if there was an action, show a smaller menu, find its handler and call it
if ($action)
  {
  showMenu ();
  $handler = $handlers [$action];
  if ($handler)
    $handler ();
  else
    ShowWarning ('Unknown action');
  echo "<hr><a href='$PHP_SELF'>Main Menu</a>\n";
  }
else  // otherwise show a bigger menu
  showBigMenu ();

// now the credits
showCredits ();

// wrap up web page
echo "\n</body>\n</html>\n";
?>
