<!DOCTYPE html>
<html lang="en">
  <head>

  <!-- Developed using the latest technology by X'Genesis Qhulut
            Author: 1EDD3757F43D3FBA1F4933670937787C
  -->

  <!-- Front end CSS by Geo -->

  <meta charset="utf-8" >
  <meta name="viewport" content="width=device-width, initial-scale=1.0" >
  <meta name="title" content="WoW 0.5.3 Database" >
<?php

// get appropriate image/title/description

$documentRoot = $_SERVER['DOCUMENT_ROOT'];
$dblink = false;

// Configuration: database name, password, etc.
require ("wow_alpha_config.php");

//-----------------------------------------------------------------------------
// open database, die on error
//-----------------------------------------------------------------------------
$dblink = mysqli_connect(DBSERVER, DBUSER, DBPASSWORD, WORLD_DBNAME);
if (mysqli_connect_errno())
  MajorProblem ("Cannot connect to server " . DBSERVER . ':' . mysqli_connect_error());

// useful stuff (utilities)
require ("wow_alpha_utils.php");
// tables of constants
require ("wow_alpha_constants.php");
// what functions we support and what handles them
require ("wow_alpha_handlers.php");
// database management
require ("wow_alpha_database.php");

  $executionDir = EXECUTIONDIR;

  // get action
  $action  = getGP ('action', 40, $VALID_ACTION);
  // get ID (eg. spell ID)
  $id      = getGP ('id',      8, $VALID_NUMBER);

  //-----------------------------------------------------------------------------
  // GET PARAMETERS FROM POST OR GET
  //-----------------------------------------------------------------------------


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

  $PHP_SELF = $_SERVER['PHP_SELF'];

  if ($action &&                                      // we have an action
      array_key_exists ($action, HANDLERS) &&         // and it is valid and we have a handler for it
      array_key_exists ('og', HANDLERS [$action]))    // and we have an og handler (Open Graphic)
    {
    $actionInfo = HANDLERS [$action];
    $extraInfo = HANDLER_EXTRA [$actionInfo ['extra']];
    getExtraInfoForTable ($extraInfo);

    $actionInfo ['og'] ();    // call this to output the OG stuff for the particular thing
    }
  else     // otherwise use default OG stuff
    echo "
    <meta property='og:title' content='WoW Alpha Core Database Browser'>
    <meta property='og:image' content='/thumbnail.jpg'>
    <meta property='og:image:width' content='802' >
    <meta property='og:image:height' content='434' >
    <meta property='og:description' content='A database browser for the World of Warcraft 0.5.3 Alpha release'>
    <meta property='og:image:type' content='image/jpeg' >
    ";

  // back to outputting the <head> stuff

?>
  <meta property="og:type"  content="website" >
  <meta property="og:url"   content="/index.php">
  <meta property="og:site_name"   content="The Alpha Project">


  <meta name="twitter:card" content="http://wow-refugee.com/alpha/thumbnail.jpg">
  <title>WoW 0.5.3 Database</title>
<?php

/*

  Author: X'Genesis Qhulut <XGenesis-Qhulut@protonmail.com>
  Date:   August 2022

  See LICENSE for license details.

  CSS Stuff: https://github.com/geo-tp/053-Database-Frontend

  Icons: https://fontawesome.com/icons


Kalidar server:

  https://github.com/vmangos/core
  https://github.com/brotalnia/database


Early maps:

  https://www.reddit.com/r/classicwow/comments/bqd5zw/here_are_some_early_alphaconcept_maps/

  https://wow.tools/maps/Kalimdor/4/5/-41.978/31.431

*/


function getExtraInfoForTable ($extraInfo)
  {
  global $searchFields, $extraWhere, $mainTable, $keyName;

    // set up some global variables for use elsewhere

    // fields to search for string matches
    if (array_key_exists ('search', $extraInfo))
      $searchFields = $extraInfo ['search'];
    else
      $searchFields = array ();

    // extra "where" conditions, like max creature ID
    if (array_key_exists ('where', $extraInfo))
      $extraWhere = $extraInfo ['where'];
    else
      $extraWhere = '';

    // what table to search
    if (array_key_exists ('table', $extraInfo))
      $mainTable = $extraInfo ['table'];
    else
      $mainTable = '';

    // what the primary key is
    if (array_key_exists ('key', $extraInfo))
      $keyName = $extraInfo ['key'];
    else
      $keyName = '';

    // only pull in this file if we have to
    if (array_key_exists ('requires', $extraInfo))
      require_once ($extraInfo ['requires']);
  } // end of getExtraInfoForTable

// helper function for linking in stylesheets with a timestamp
function includeStylesheet ($name)
  {
  $executiondir = EXECUTIONDIR;
  $time = filemtime ($_SERVER['DOCUMENT_ROOT'] . "$executiondir/$name");
  echo "  <link rel='stylesheet' href='$executiondir/$name?v=$time' >\n";
  } // end of includeStylesheet

// helper function for linking in scripts with a timestamp
function includeScript ($name)
  {
  $executiondir = EXECUTIONDIR;
  $time = filemtime ($_SERVER['DOCUMENT_ROOT'] . "$executiondir/$name");
  echo "  <script  src='$executiondir/$name?v=$time' defer ></script>\n";
  } // end if includeScript

//-----------------------------------------------------------------------------
// show stats if wanted
//-----------------------------------------------------------------------------

  if ($action == 'stats')
    {
    ShowStats ();
    RecordStats ();     // record *this* action (stats) *after* we show the stats
                        // - otherwise *now* is always the latest query
    return;
    }

//-----------------------------------------------------------------------------
// get our style sheets
//-----------------------------------------------------------------------------
includeStylesheet ("css/normalize.css");
includeStylesheet ("css/styles.css");
includeStylesheet ("css/fontawesome/fontawesome.css");
includeStylesheet ("css/fontawesome/brands.css");
includeStylesheet ("css/fontawesome/solid.css");
//-----------------------------------------------------------------------------
// get our scripts
//-----------------------------------------------------------------------------
includeScript ("editing.js");
includeScript ("js/zoom_and_pan.js");
includeScript ("js/carousselSpawn.js");
//-----------------------------------------------------------------------------
// wrap up our head, and start our body
//-----------------------------------------------------------------------------
?>
  <link rel='icon' href='favicon.png'>
  </head>
  <body>
  <!-- MAIN -->
  <main class='main-container'>

<?php

// main menu
require ("wow_alpha_menu.php");
// field expansions
require ("wow_alpha_fields.php");

//-----------------------------------------------------------------------------
// START HERE
//-----------------------------------------------------------------------------



//-----------------------------------------------------------------------------
// grab things we are likely to cross-reference a lot
//-----------------------------------------------------------------------------
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

  $results = dbQuery ("SELECT * FROM " . AREATRIGGER);
  while ($row = dbFetch ($results))
    $area_triggers [$row ['ID']] = $row ['X'] . ' ' . $row ['Y'] . ' ' . $row ['Z'] . ' ' . $row ['ContinentID'];
  dbFree ($results);

  } // end of having an action

showBigMenu ();

// record this query

RecordStats ();

//-----------------------------------------------------------------------------
// if there was an action, find its handler and call it
//-----------------------------------------------------------------------------
if ($action)
  {
  if (array_key_exists ($action, HANDLERS))
    {
    $actionInfo = HANDLERS [$action];
    $extraInfo = HANDLER_EXTRA [$actionInfo ['extra']];

    // set up some global variables for use elsewhere
    getExtraInfoForTable ($extraInfo);

    comment ('Executing ' . $actionInfo ['func']);
    // call the handler function
    $actionInfo ['func'] ();
    }
  else
    ShowWarning ('Unknown action');
  }
else  // otherwise show a bigger menu
  mainPage ();

//-----------------------------------------------------------------------------
// wrap up web page
//-----------------------------------------------------------------------------

?>
  </main>
    <!-- END MAIN -->

    <!-- FOOTER -->
    <footer class="footer"></footer>
    <!-- END FOOTER -->
  </body>
</html>
