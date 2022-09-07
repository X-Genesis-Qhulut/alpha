<?php

/*
  Author: X'Genesis Qhulut <XGenesis-Qhulut@protonmail.com>
  Date:   August 2022

  See LICENSE for license details.
*/

// TABLES

function tableDetails ($info)
{
  bottomSection ($info, function ($info)
      {
      global $database, $table;

      if ($database == 'alpha_dbc')
        $realDatabase = DBC_DBNAME;
      elseif ($database == 'alpha_world')
        $realDatabase = WORLD_DBNAME;
      else
        Problem ("Invalid database specified, must be alpha_dbc or alpha_world");

      $td  = function ($s) use (&$row) { tdx ($row  [$s]); };
      $tdr = function ($s) use (&$row) { tdx ($row  [$s], 'tdr'); };

      $row = dbQueryOne ("SELECT COUNT(*) AS count FROM `" .
                  ($database == 'alpha_dbc' ? DBC_DBNAME : WORLD_DBNAME) . '`.' . $table);

      if (!$row)
        {
        ShowWarning ("Table $table is not on the database");
        return;
        } // end of not finding it

      boxTitle ($row ['count'] . " rows in this table. Schema below.");

      $results = dbQueryParam ("SELECT * FROM information_schema.COLUMNS
                                WHERE TABLE_SCHEMA = ? AND TABLE_NAME = ?",
                                array ('ss', &$realDatabase, &$table));

      echo "<table class='table-rows'>\n";
      echo "<thead>\n";

      echo "<tr>\n";
        th ('Name');
        th ('Type');
        th ('Null');
        th ('Key');
        th ('Default');
      echo "</tr>\n";
      echo "</thead>\n";
      echo "<tbody>\n";

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
      echo "</tbody>\n";
      echo "</table>\n";



      });
} // end of tableDetails

function showOneTable ()
  {
  global $database, $table;

  setTitle ("Database table: $table");

  pageContent (false, 'Table', $database . '.' . $table, 'tables', 'tableDetails', $database);

  } // end of showOneTable

function showTablesHelper ($tableName, $headingName)
{
  $results = dbQueryParam ("SELECT TABLE_NAME AS tableName FROM information_schema.TABLES
                            WHERE TABLE_SCHEMA = ?",
                            array ('s', &$tableName));

  echo "<table class='table-rows'>\n";
  echo "<thead>\n";

  echo "<tr>\n";
    th ('Tables in ' . fixHTML ($headingName));
  echo "</tr>\n";
  echo "</thead>\n";
  echo "<tbody>\n";

  foreach ($results as $row)
    {
    echo "<tr>\n";
    $name = $row ['tableName'];
    tdh ("<a href='?action=show_table&table=$name&database=$headingName'>$name</a>");
    echo "</tr>\n";
    }
  echo "</table>\n";

} // end of showTablesHelper

function showAllTables ($info)
{
  setTitle ("Database tables");

  bottomSectionMany ($info, function ($info)
      {
      showTablesHelper (DBC_DBNAME, "alpha_dbc");
      showTablesHelper (WORLD_DBNAME, "alpha_world");
      });
} // end of showAllTables

function showTables ()
  {
  pageContent (false, 'Tables', 'Database tables', 'tables', 'showAllTables', '');
  } // end of showTables

?>
