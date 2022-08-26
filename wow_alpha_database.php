<?php

/*

  Author: X'Genesis Qhulut <XGenesis-Qhulut@protonmail.com>
  Date:   August 2022

  See LICENSE for license details.

*/

// general function for getting a count of something

function GetSQLcount ($query, $select = "SELECT count(*) FROM ")
  {
  $row = dbQueryOne ($select . $query);  // uncertain - need to check these
  $count = $row [0];
  return ($count);
  } // end of GetSQLcount

function fixsql ($sql)
  {
  global $dblink;

  return mysqli_real_escape_string ($dblink, $sql);
  } // end of fixsql



function showSQLerror ($sql)
  {
  global $dblink;

  echo "<hr>\n";
  echo "<h2><font color=darkred>Problem with SQL</font></h2>\n";
  echo (fixHTML (mysqli_error ($dblink)));
  echo "<hr>\n";
  echo "<div class='sqlerror'>\n";
  echo (fixHTML ($sql). "\n");
  echo "</div>\n";

  showBacktrace (2);

  // bail out
  Problem ("SQL statement failed.");
  } // end of showSQLerror


// Do a database query that returns a single row
// return that row, or false (doesn't need freeing)
// (eg. SELECT ... FROM) where you expect a single result
function dbQueryOne ($sql)
  {
  global $dblink;

  $result = mysqli_query ($dblink, $sql);
  // false here means a bad query
  if (!$result)
    showSQLerror ($sql);

  $row = dbFetch ($result);
  dbFree ($result);
  return $row;
  }  // end of dbQueryOne

// Do a database query that returns a single row
// return that row, or false (doesn't need freeing)
// (eg. SELECT ... FROM) where you expect a single result

function dbQueryOneParam ($sql, $params)
  {
  $results = dbQueryParam ($sql, $params, 1);
  if (count ($results) > 0)
    return $results [0];
  return false;
  }  // end of dbQueryOneParam

// Do a database query that updates the database.
// eg. UPDATE, INSERT INTO, DELETE FROM etc.
// Doesn't return a result.
function dbUpdate ($sql, $showError = true)
  {
  global $dblink;

  $result = mysqli_query ($dblink, $sql);
  // false here means a bad query
  if (!$result && $showError)
    showSQLerror ($sql);
  }  // end of dbUpdate

// Do a database query that updates the database.
// eg. UPDATE, INSERT INTO, DELETE FROM etc.
// Returns the number of affected rows.
// First array element in $params is a string containing field types (eg. 'ssids')
//   i  corresponding variable has type integer
//   d  corresponding variable has type double
//   s  corresponding variable has type string
// Subsequent elements are the parameters, passed by REFERENCE.
function dbUpdateParam ($sql, $params, $showError = true)
  {
  global $dblink;

  $stmt = mysqli_prepare ($dblink, $sql);
  // false here means a bad query
  if (!$stmt)
    showSQLerror ($sql);

  if (count ($params) > 1)
    if (!call_user_func_array (array($stmt, 'bind_param'), $params))
      showSQLerror ($sql);

  if (!mysqli_stmt_execute ($stmt) && $showError)
    showSQLerror ($sql);

  $count = mysqli_stmt_affected_rows ($stmt);

  mysqli_stmt_close ($stmt);

  return $count;
  }  // end of dbUpdateParam

// Do a database query that returns multiple rows
// return the result variable which must later be freed
function dbQuery ($sql)
  {
  global $dblink;

  $result = mysqli_query ($dblink, $sql);
  // false here means a bad query
  if (!$result)
    showSQLerror ($sql);
  return $result;
  }  // end of dbQuery

function dbQueryParam_helper ($sql, $params, $max_rows = -1)
  {
  global $dblink;

  $stmt = mysqli_prepare ($dblink, $sql);
  // false here means a bad query
  if (!$stmt)
    showSQLerror ($sql);

  if (count ($params) > 1)
    if (!call_user_func_array (array($stmt, 'bind_param'), $params))
      showSQLerror ($sql);

  if (!mysqli_stmt_execute ($stmt))
    showSQLerror ($sql);

  mysqli_stmt_store_result ($stmt);

  $row = array ();    // array of names/values to return
  $output = array (); // simple array to hold each result

  // get field names, build into zero-based array
  $meta = mysqli_stmt_result_metadata ($stmt);
  while ($field = mysqli_fetch_field($meta))
  {
  $row [$field->name] = 0;
  $output[] = &$row [$field->name];
  }

  // bind the output to the array we built
  if (!call_user_func_array(array($stmt, 'bind_result'), $output))
    showSQLerror ($sql);

  $results = array ();
  $row_count = 0;

  // fetch all the rows
  while (mysqli_stmt_fetch($stmt))
    {
    $item = array ();
    // have to copy the values, otherwise everything ends up being the last one
    foreach ($row as $k => $v)
      $item [$k] = $v;
    $results [] = $item;
    $row_count++;
    // stop inadvertently getting lots of rows when only one is wanted
    if ($max_rows > -1 && $row_count >= $max_rows)
      break;
    } // end of while each row

  mysqli_stmt_close ($stmt);
  return $results;

  } // end of dbQueryParam_helper

// Do a database query that returns multiple rows
// Returns an ARRAY of the resulting rows. Nothing needs to be freed later.
// First array element is a string containing field types (eg. 'ssids')
//   i  corresponding variable has type integer
//   d  corresponding variable has type double or decimal
//   s  corresponding variable has type string
// Subsequent elements are the parameters, passed by REFERENCE.
//   eg.  dbQueryOneParam ("SELECT * FROM functions WHERE name = ?", array ('s', &$name));
function dbQueryParam ($sql, $params, $max_rows = -1)
  {
  $results = dbQueryParam_helper ($sql, $params, $max_rows);
  return $results;
  }  // end of dbQueryParam

// fetches one row from the result returned by dbQuery
// glue routine in case we switch to PostGRE or something
function dbFetch ($result)
  {
  if (!($result instanceof mysqli_result))
    {
    showBacktrace (1);
    Problem ("Incorrect 'result' field passed to dbFetch");
    }

  return mysqli_fetch_array ($result);
  } // end of dbFetch

// gets the number of rows in the result returned by dbQuery
// glue routine in case we switch to PostGRE or something
function dbRows ($result)
  {
  if (!($result instanceof mysqli_result))
    {
    showBacktrace (1);
    Problem ("Incorrect 'result' field passed to dbRows");
    }

  return mysqli_num_rows ($result);
  } // end of dbRows

// gets the number of rows affected by dbUpdate
// glue routine in case we switch to PostGRE or something
function dbAffected ()
  {
  global $dblink;
  return mysqli_affected_rows ($dblink);
  } // end of dbAffected

// gets the key of a new row created by INSERT INTO
// glue routine in case we switch to PostGRE or something
function dbInsertId ()
  {
  global $dblink;
  return mysqli_insert_id ($dblink);
  } // end of dbInsertId

// seeks into the result set
// glue routine in case we switch to PostGRE or something
function dbSeek ($result, $position)
  {
  if (!($result instanceof mysqli_result))
    {
    showBacktrace (1);
    Problem ("Incorrect 'result' field passed to dbSeek");
    }

  mysqli_data_seek ($result, $position);
  } // end of dbSeek

// frees the result returned by dbQuery
// glue routine in case we switch to PostGRE or something
function dbFree ($result)
  {
  if (!($result instanceof mysqli_result))
    {
    showBacktrace (1);
    Problem ("Incorrect 'result' field passed to dbFree");
    }

  mysqli_free_result ($result);
  } // end of dbFree

?>
