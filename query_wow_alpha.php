<!DOCTYPE html>
<html lang="en">
  <head>
  <meta charset="utf-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0" />
  <meta name="title" content="WoW 0.5.3 Database" />
  <meta name="description" content="Database browser for Alpha core 0.5.3" />

  <meta property="og:title" content="WoW Alpha Core Database Browser">
  <meta property="og:type"  content="website" />
  <meta property="og:image" content="http://wow-refugee.com/alpha/thumbnail.jpg">
  <meta property="og:url"   content="http://wow-refugee.com/alpha/index.php">
  <meta property="og:description" content="A database browser for the World of Warcraft 0.5.3 Alpha release">
  <meta property="og:site_name"   content="WoW Refugee">

  <meta property="og:image" content="http://wow-refugee.com/alpha/thumbnail.jpg" />
  <meta property="og:image:secure_url" content="https://wow-refugee.com/alpha/thumbnail.jpg" />
  <meta property="og:image:type" content="image/jpeg" />
  <meta property="og:image:width" content="800" />
  <meta property="og:image:height" content="434" />

  <meta name="twitter:card" content="http://wow-refugee.com/alpha/thumbnail.jpg">

  <title>WoW 0.5.3 Database</title>
  <link rel="stylesheet" href="./css/normalize.css" />
  <link rel="stylesheet" href="./css/styles.css" />
  <link rel="icon" href="favicon.png" />
  <link href="./css/fontawesome/fontawesome.css" rel="stylesheet" />
  <link href="./css/fontawesome/brands.css" rel="stylesheet" />
  <link href="./css/fontawesome/solid.css" rel="stylesheet" />
  <script src='editing.js' defer></script>
  <script src='js/magnifier.js' defer></script>
  </head>
  <body>
    <!-- MAIN -->
    <main class="main-container">


<?php

/*

  Author: X'Genesis Qhulut <XGenesis-Qhulut@protonmail.com>
  Date:   August 2022

  See LICENSE for license details.

  CSS Stuff: https://github.com/geo-tp/053-Database-Frontend

  Icons: https://fontawesome.com/icons

*/

// Configuration: database name, password, etc.
require ("wow_alpha_config.php");
// database management
require ("wow_alpha_database.php");
// main menu
require ("wow_alpha_menu.php");
// tables of constants
require ("wow_alpha_constants.php");
// what functions we support and what handles them
require ("wow_alpha_handlers.php");
// useful stuff
require ("wow_alpha_utils.php");
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
require ("wow_alpha_spell_visual.php");
// field expansions
require ("wow_alpha_fields.php");


//-----------------------------------------------------------------------------
// START HERE
//-----------------------------------------------------------------------------

$documentRoot = $_SERVER['DOCUMENT_ROOT'];
$dblink = false;
$executionDir = EXECUTIONDIR;

// incorporate our stylesheet

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
$sort_order = getGP ('sort_order', 30, $VALID_SQL_ID);
// get page number
$page = getGP ('page',      8, $VALID_NUMBER);

// table name
$table  = getG ('table', 30, $VALID_SQL_ID);
// database name
$database  = getG ('database', 15, $VALID_SQL_ID);

// for page text, the item which leads to the text
$item      = getGP ('item',      8, $VALID_NUMBER);

// work out page number

if (!$page || $page < 1)
  $page = 1;

// open database, die on error
$dblink = mysqli_connect(DBSERVER, DBUSER, DBPASSWORD, WORLD_DBNAME);
if (mysqli_connect_errno())
  MajorProblem ("Cannot connect to server " . DBSERVER . ':' . mysqli_connect_error());

$PHP_SELF = $_SERVER['PHP_SELF'];

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

showBigMenu ();

// if there was an action, show a smaller menu, find its handler and call it
if ($action)
  {
  if (array_key_exists ($action, HANDLERS))
    {
    $actionInfo = HANDLERS [$action];
    // set up some global variables for use elsewhere

    // fields to search for string matches
    if (array_key_exists ('search', $actionInfo))
      $searchFields = $actionInfo ['search'];
    else
      $searchFields = array ();

    // extra "where" conditions, like max creature ID
    if (array_key_exists ('where', $actionInfo))
      $extraWhere = $actionInfo ['where'];
    else
      $extraWhere = '';

    // what table to search
    if (array_key_exists ('table', $actionInfo))
      $mainTable = $actionInfo ['table'];
    else
      $mainTable = '';

    // what the primary key is
    if (array_key_exists ('key', $actionInfo))
      $keyName = $actionInfo ['key'];
    else
      $keyName = '';

    // only pull in this file if we have to
    if (array_key_exists ('validation', $actionInfo))
      require ("wow_alpha_validity.php");

    comment ('Executing ' . $actionInfo ['func']);
    // call the handler function
    $actionInfo ['func'] ();
    }
  else
    ShowWarning ('Unknown action');
  }
else  // otherwise show a bigger menu
  mainPage ();

// wrap up web page

?>
  </main>
    <!-- END MAIN -->

    <!-- FOOTER -->
    <footer class="footer"></footer>
    <!-- END FOOTER -->
  </body>
</html>
