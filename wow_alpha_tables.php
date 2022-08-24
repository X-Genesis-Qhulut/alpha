<?php

/*
  Author: X'Genesis Qhulut <XGenesis-Qhulut@protonmail.com>
  Date:   August 2022

  See LICENSE for license details.
*/

// TABLES

function showOneTable ()
  {
  global $database, $table;

  if ($database == 'alpha_dbc')
    $realDatabase = DBC_DBNAME;
  elseif ($database == 'alpha_world')
    $realDatabase = WORLD_DBNAME;
  else
    Problem ("Invalid database specified, must be alpha_dbc or alpha_world");

  echo "<h2>Table: " . fixHTML ("$database.$table") . "</h2>\n";


  $td  = function ($s) use (&$row) { tdx ($row  [$s]); };
  $tdr = function ($s) use (&$row) { tdx ($row  [$s], 'tdr'); };

  $results = dbQueryParam ("SELECT * FROM information_schema.COLUMNS
                            WHERE TABLE_SCHEMA = ? AND TABLE_NAME = ?",
                            array ('ss', &$realDatabase, &$table));

  echo "<table class='search_results'>\n";

  echo "<tr>\n";
    th ('Name');
    th ('Type');
    th ('Null');
    th ('Key');
    th ('Default');
  echo "</tr>\n";

  foreach ($results as $row)
    {
    echo "<tr>\n";
    $td ('COLUMN_NAME');
    $td ('COLUMN_TYPE');
    $td ('IS_NULLABLE');
    $td ('COLUMN_KEY');
    $td ('COLUMN_DEFAULT');
    echo "</tr>\n";
    }
  echo "</table>\n";

  $row = dbQueryOne ("SELECT COUNT(*) AS count FROM " .
              ($database == 'alpha_dbc' ? DBC_DBNAME : WORLD_DBNAME) . '.' . $table);

  echo ('<p>' . $row ['count'] . " rows in this table.\n");


  } // end of showOneTable

function showTablesHelper ($tableName, $headingName)
{
  echo "<div class='one_thing_section'>\n";

  $results = dbQueryParam ("SELECT TABLE_NAME AS tableName FROM information_schema.TABLES
                            WHERE TABLE_SCHEMA = ?",
                            array ('s', &$tableName));

  echo "<table class='search_results'>\n";

  echo "<tr>\n";
    th ('Tables in ' . fixHTML ($headingName));
  echo "</tr>\n";

  foreach ($results as $row)
    {
    echo "<tr>\n";
    $name = $row ['tableName'];
    tdh ("<a href='?action=show_table&table=$name&database=$headingName'>$name</a>");
    echo "</tr>\n";
    }
  echo "</table>\n";

  echo "</div>\n";  // end of database details

} // end of showTablesHelper

function showTables ()
  {

  echo "<h2>Tables</h2>\n";

  echo "<div class='one_thing_container'>\n";
  showTablesHelper (DBC_DBNAME, "alpha_dbc");
  showTablesHelper (WORLD_DBNAME, "alpha_world");
  echo "</div>\n";  // end of flex container

  } // end of showTables







?>
