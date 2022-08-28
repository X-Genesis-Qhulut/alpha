<?php

/*
  Author: X'Genesis Qhulut <XGenesis-Qhulut@protonmail.com>
  Date:   August 2022

  See LICENSE for license details.
*/


$VALID_NUMBER     = '^[+\-]?\d+$';             // just digits with optional sign
$VALID_FLOAT      = '^[+\-]?(\d*\.)?(\d+)$';   // optional sign, optional number and decimal point, then number
$VALID_DATE       = '^[\w \-]+$';              // Expect letters, numbers spaces, hyphens
$VALID_DATE_TIME  = '^[\w :\-]+$';             // Expect letters, numbers spaces, hyphens, and colons
$VALID_ACTION     = '^[\w ]+$';                // actions are usually just words with underscore and maybe numbers and spaces
$VALID_BOOLEAN    = '^[01]$';                  // must be 0 or 1
$VALID_SQL_ID     = '^\w+$';                   // SQL names are usually just words with underscore and maybe numbers (max 30 probably)

// filter comparison operators
define ('SECONDARY_FILTER', array (
        'equals' => ' = ? ',
        'not equals' => ' <> ? ',
        'less than' => ' < ? ',
        'less than or equals' => ' <= ? ',
        'greater than' => ' > ? ',
        'greater than or equals' => ' >= ? ',
        'masked by any bit' => ' & ? <> 0 ',
        'not masked by any bit' => ' & ? = 0 ',
        'masked by all bits' => ' & ? = ? ',
        'not masked by all bits' => ' & ? <> ? ',
        'in set' => ' IN ',
        'not in set' => ' NOT IN',
        'in range' => ' ',            // field >= a AND field <= b
        'not in range' => ' NOT ',    // NOT (field >= a AND field <= b)

));

function nl2br_http ($text)
  {
  return str_replace ("\n", "<br>", $text);
  } // end of nl2br_http

function fixHTML ($s)
  {
  return htmlspecialchars ($s, ENT_SUBSTITUTE | ENT_QUOTES | ENT_HTML5);
  } // end of fixHTML

function ShowError ($theerror)
  {
  echo "<div class='error_message'>\n";
  echo (nl2br_http (fixHTML ($theerror) . "\n"));
  echo "</div>\n";
  } // end of ShowError

function ShowWarningH ($theWarning)
  {
  echo ("<p class='warning_message'>" . $theWarning . "</p>\n");
  } // end of ShowWarningH

function ShowWarning ($theWarning)
  {
  ShowWarningH (nl2br_http (fixHTML ($theWarning)));
  } // end of ShowWarning

function ShowInfoH ($theInfo)
  {
  echo ("<p class='info_message'>" . $theInfo . "</p>\n");
  } // end of ShowInfoH

function ShowInfo ($theInfo)
  {
  ShowInfoH (nl2br_http (fixHTML ($theInfo)));
  } // end of ShowInfo


function Problem ($why)
  {
  echo "<h2>There is a problem ...</h2><p>\n";
  ShowError ($why);
  echo "</body></html>\n";

  die ();
  } // end of Problem


// Use this before database opened
function MajorProblem ($why)
  {
  echo "<div class='major_problem' >
        <p>We apologise that there has been a problem with the server ...</p>";

  ShowError ($why);
  echo "<p>Error occurred at " . strftime ("%Y-%m-%d %H:%M:%S", time()) . "</p>
        </div>
        </body></html>\n";
  die ();
  } // end of MajorProblem



function validateArgument ($name, $value, $maxLength, $validation, $decode = false)
  {
  $value = trim ($value);
  // first decode it if required
  if ($decode)
    $value = urldecode ($value);
  if ($maxLength > 0 && strlen ($value) > $maxLength)
    {
    Problem ("Parameter '$name' is too long");
    }
  if (strlen ($value) && $validation)
    {
    if (!preg_match ("\xFF" . $validation . "\xFF" . 'i', $value))
      {
      Problem  ("Parameter '$name' is not in the expected format (unexpected characters).");
      }
    }
  return $value;
  } // end of validateArgument


function getGP ($name, $maxLength = 0, $validation = "", $decode = false)
  {
  if (isset ($_GET [$name]))
    return validateArgument ($name, $_GET [$name], $maxLength, $validation, $decode);
  if (isset ($_POST [$name]))
    return validateArgument ($name, $_POST [$name], $maxLength, $validation, $decode);
  return false;
  }  // getGP

function getP ($name, $maxLength = 0, $validation = "", $decode = false)
  {
  if (isset ($_POST [$name]))
    return validateArgument ($name, $_POST [$name], $maxLength, $validation, $decode);

  return false;
  }  // getP

function getG ($name, $maxLength = 0, $validation = "", $decode = false)
  {
  if (isset ($_GET [$name]))
    return validateArgument ($name, $_GET [$name], $maxLength, $validation, $decode);

  return false;
  }  // getG

function showBacktrace ($howFarBack = 1)
  {
  echo "<hr><b>Backtrace</b>\n";

  echo "<ol>\n";
  $bt = debug_backtrace ();
  $count = sizeof($bt);
  for ($i = $howFarBack; $i < $count; $i++)
    {
    $item = $bt [$i];
    echo "<li>\n";
    echo "<ul>\n";
    echo ("<li>" . "Function: "     . fixHTML ($item ['function']));
    echo ("<li>" . "Called from: "  . fixHTML ($item ['file']));
    echo ("<li>" . "Line: "         . fixHTML ($item ['line']));
    echo "</ul><p>\n";
    }
  echo "</ol>\n";
  echo "<hr>\n";
  }   // end of showBacktrace

function columns_compare ($a, $b)
  {
  return $a ['Field'] <=> $b ['Field'];
  } // end of columns_compare

function showSearchForm ($sortFields, $results, $table, $where)
  {
  global $filter, $action, $sort_order, $params, $page, $matches;
  global $filter_column, $filter_compare, $filter_value;

  // find maximum matches
  $row = dbQueryOneParam ("SELECT COUNT(*) AS count FROM $table $where ORDER BY $sort_order", $params);

  $matches = $row ['count'];

  $pages = ceil ($matches / QUERY_LIMIT);

  $PHP_SELF = $_SERVER['PHP_SELF'];

  echo "<form METHOD=\"post\" ACTION=$PHP_SELF>\n";
  echo "<input Type=hidden Name=action Value=$action>\n";
  echo "<input Type=hidden Name=page Value=$page>\n";
  echo "Filter: ";
  echo " <input style='margin-right:1em;' type=text Name='filter' size=40 Value='" . fixHTML ($filter) .
        "' placeholder='ID or regular expression' autofocus title='Enter a number, text, or a regular expression'>\n";

  echo " Sort by: ";
  echo "<select name='sort_order' size='1' title='Which column to sort on'>\n";
  foreach ($sortFields as $field)
    {
    if ($field == $sort_order)
      $selected = "selected = 'selected'";
    else
      $selected = '';
    echo "<option value='" . fixHTML ($field) . "' $selected>" .
          fixHTML (($field)) . "</option>\n";
    }
  echo "</select>\n";
  echo "<input style='margin-left:1em;' Type=submit Name=SubmitFilter Value='Filter' title='Click to search'>\n";
  echo "<div class='navigation'>\n";
  if ($page > 1)
    echo "<input type='image' class='arrow' name='LeftArrow' title='Previous page' alt='Previous page' src='left-arrow.png'>\n";
  if ($pages > 0)
    echo " Page $page of $pages ";
  if ($page < $pages)
    echo "<input type='image' class='arrow' name='RightArrow' title='Next page' alt='Next page' src='right-arrow.png'>\n";
  echo "</div>\n";  // end of navigation

  // secondary filter - field name

  echo "<p>Also match: ";
  $tableFields = dbQueryParam ("SHOW COLUMNS FROM $table", array ());
  usort ($tableFields, 'columns_compare');
  echo "<select name='filter_column' size='1' title='Which database column to filter on'>\n";
  foreach ($tableFields as $field)
    {
    if (preg_match ('`int|float`', $field ['Type']))
      {
      if ($field ['Field'] == $filter_column)
        $selected = "selected = 'selected'";
      else
        $selected = '';
      echo "<option value='" . fixHTML ($field ['Field']) . "' $selected>" .
            fixHTML ($field ['Field']) . "</option>\n";
      } // end of being a number
    }   // end of foreach field
  echo "</select>\n";

  // secondary filter - comparison

  echo "<select name='filter_compare' size='1' title='What comparison to do'>\n";
  foreach (SECONDARY_FILTER as $compare => $operator)
    {
    if ($compare == $filter_compare)
      $selected = "selected = 'selected'";
    else
      $selected = '';
    echo "<option value='" . fixHTML ($compare) . "' $selected>" .
          fixHTML ($compare) . "</option>\n";
    } // end of foreach comparison
  echo "</select>\n";

  // secondary filter - value to compare to

  echo " <input type=text Name='filter_value' size=15 Value='" . fixHTML ($filter_value) .
        "' placeholder='Number/hex/bin'
        title='Leave empty for no secondary filtering.\nHex numbers: 0x0123ABCD\nBinary numbers: 0b01010\nSet: 1,7,9,15\nRange: 20 to 30'>\n";

  echo
  "<details style='margin-top:3px;'><summary>Regular expression tips</summary>
  <ul>
  <li><i>A number on its own</i>: the item database ID (key)
  <li>Ordinary text: itself
  <li>At beginning: ^
  <li>At end: $
  <li>Word boundaries: [[:&lt;:]]word[[:&gt;:]]
  <li>Choice: this|that
  <li>Letters: [A-Z]+
  <li>Numbers: [0-9]+
  <li>Zero or one: x?
  <li>One or more: x+
  <li>Zero or more: x*
  <li>Any character: .
  <li>Groups: (this is a group)
  </ul>
  </details>
  </form>";

  if (count ($results) == 0)
    {
    echo "No matches.";
    return false;
    } // end of nothing

  return true;
  } // end of showSearchForm

// work out what our query offset should be
function getQueryOffset ()
  {
  global $page;
  if (!$page || $page < 1)
    $page = 1;

  return ($page - 1) * QUERY_LIMIT; // first page starts at offset zero
  } // end of getQueryOffset

// show how many rows matched a query
function showCount ($results)
  {
  global $matches, $page;
  $s = 's';
  if (count ($results) == 1)
    $s = '';
  echo ("<p>Showing " . count ($results) . " row$s.");
  $pages = ceil ($matches / QUERY_LIMIT);
  $s1 = 'es';
  if ($matches == 1)
    $s1 = '';
  $s2 = 's';
  if ($pages == 1)
    $s2 = '';
  echo ("<p>$matches match$s1 for this query ($pages page$s2).");
  if ($pages > 1)
    echo "This is page $page of $pages.";
  echo "\n";
  } // end of showCount

// This for stuff like EffectTriggerSpell_1, EffectTriggerSpell_2, EffectTriggerSpell_3
// We want to know if any of them are non-zero, because then we display a heading
// eg.  $count = getCount ('EffectTriggerSpell_', 3);

function getCount ($row, $field, $n)
  {
  $count = 0;
  for ($i = 1; $i <= $n; $i++)
    if ($row [$field . $i])
      $count++;
  return $count;
  } // end of getCount

function addSign ($value)
  {
  if ($value > 0)
    return '+' . $value;
  return $value;
  } // end of addSign

function tdx ($s, $c='tdl')
  {
  echo "<td class='$c'>";
  echo (fixHTML ($s));
  echo "</td>\n";
  } // end of tdx

function tdxr ($s)
  {
  tdx ($s, 'tdr');
  } // end of tdxr

// for hyperlinks
function tdhr ($s)
  {
  echo "<td class='tdr'>$s</td>\n";
  } // end of tdhr

function tdh ($s)
  {
  echo "<td class='tdl'>$s</td>\n";
  } // end of tdh

function th ($s)
  {
  echo "<th>";
  echo (fixHTML ($s));
  echo "</th>\n";
  } // end of th


function headings ($what)
{
  global $filter_column, $filter_value;
  if (strlen ($filter_value) > 0 && strlen ($filter_column) > 0)
    $what [] = $filter_column;

  echo "<tr>\n";
  foreach ($what as $hdg)
    th ($hdg);
  echo "</tr>\n";
} // end of headings

function showFilterColumn ($row)
  {
    global $filter_column, $filter_value;
    if (strlen ($filter_value) > 0 && strlen ($filter_column) > 0)
      tdx ($row  [$filter_column], 'tdr');
  } // end of showFilterColumn

//

function setUpSearch ($keyname, $fieldsToSearch)
  {
  global $filter, $where, $params;
  global $filter_column, $filter_compare, $filter_value, $fixed_filter_value;

  $where = 'WHERE TRUE ';
  $params = array ();
  $params [0] = '';
  $filter = trim ($filter);

  if ($filter)
    {
    // check filter regexp is OK
    $ok = @preg_match ("`$filter`", "whatever", $matches);
    if ($ok === false)
      {
      $warnings = error_get_last();
      $warning = $warnings ['message'];
      ShowWarning ("Error evaluating regular expression: $filter\n\n$warning\n\nFilter ignored.");
      return;
      } // if not OK


    if (preg_match ('|^\d+$|', $filter))
      {
      $where .= " AND $keyname = ?";
      $params = array ('i', &$filter);
      }
    else
      {
      $where = '';
      $strings = '';
      $params = array ('');
      foreach ($fieldsToSearch as $field)
        {
        if ($where == '')
          $where .= "WHERE ($field REGEXP ? ";
        else
          $where .= " OR $field REGEXP ? ";
        $params [0] .= 's';
        $params [] = &$filter;
        } // end of for each field to search
      $where .= ') ';
      } // end of if
    } // end of having a filter

  // secondary comparison
  if (strlen ($filter_value) > 0 && strlen ($filter_column) > 0)
    {
    // IN SET
    if ($filter_compare == 'in set' || $filter_compare == 'not in set')
      {
      if (!preg_match ('/\s*([+\-]?\d+)(\s*,([+\-]?\d+))*\s*$/', $filter_value))
        {
        ShowWarningH ('Filter comparison value "' . fixHTML ($filter_value)  . '" not a series of comma-separated numbers: ' .
                     "<br><br><b>Secondary</b> filter ignored.");
        return;
        }
      $where .= " AND ($filter_column " . SECONDARY_FILTER [$filter_compare];  // ie. IN or NOT IN
      $where .= '(' . $filter_value . ')';
      $where .= ')';
      } // end of in or not in
   // IN RANGE
   elseif ($filter_compare == 'in range' || $filter_compare == 'not in range')
      {
      if (!preg_match ('/\s*([+\-]?\d+)\s+to\s+([+\-]?\d+)\s*$/i', $filter_value, $matches))
        {
        ShowWarningH ('Filter comparison value "' . fixHTML ($filter_value) . '" not in format: (number) TO (number) '  .
                     "<br><br><b>Secondary</b> filter ignored.");
        return;
        }
      $where .= " AND (" . SECONDARY_FILTER [$filter_compare];  // ie. NOT or space
      $where .= ' (' . $filter_column . ' >= ' . $matches [1] . ' AND ' .
                       $filter_column . ' <= ' . $matches [2] . ')';
      $where .= ')';
      } // end of in range or not in range
    // JUST A NUMBER
    else
      {
      if (!preg_match ('/(^[+\-]?\d+$)|(^0[xX][0-9A-Fa-f]+$)|(^0[bB][01]+$)|^[+\-]?(\d*\.)?(\d+)$/', $filter_value))
        {
        ShowWarningH ('Filter comparison value "'. fixHTML ($filter_value)  . '" not decimal, float, hex or binary number: ' .
                     "<br><br><b>Secondary</b> filter ignored.");
        return;
        }
      $where .= " AND ($filter_column " . SECONDARY_FILTER [$filter_compare];
      $paramType = 'i';
      if (strpos ($fixed_filter_value, '.') !== false)
        $paramType = 'd';   // turn to a double since it has a decimal place
      $params [0] .= $paramType;
      $params [] = &$fixed_filter_value;
      // [not] masked by all bits needs the value again
      if (strstr ($filter_compare, 'all'))
        {
        $params [0] .= $paramType;
        $params [] = &$fixed_filter_value;
        }
      $where .= ')';
      } // end of NOT (in or not in)
    } // end of secondary comparison

  } // end of setUpSearch

// shows all fields from any table
function showOneThing ($table, $table_display_name, $key, $id, $description, $nameField, $expand, $func = false)
  {
  $info = dbQueryParam ("SHOW COLUMNS FROM $table", array ());

  $row = dbQueryOneParam ("SELECT * FROM $table WHERE $key = ?", array ('i', &$id));
  if (!$row)
    {
    ShowWarning ("$description $id is not on the database");
    return;
    }

  $name = fixHTML ($row [$nameField]);


  echo "<h1 class='one_item'>" . fixHTML ($description) . " $id — $name</h1>\n";
  echo "<h2 class='one_item_table'>Table: " . fixHTML ($table_display_name) . "</h2>\n";

  if  (preg_match ('|\.(.+)$|', $table, $matches))
    {
    $tableOnly = $matches [1];
    // add a box for displaying SQL update information for copy/paste into an update line
    echo "<div id='editing_sql' class='sql'>UPDATE `$tableOnly` SET `<span id='sql_field_name'>field</span>`
        = xxxx WHERE (`$key` = " .
         $row [$key] . ");\n
         </div>\n";
    }

  echo "<div class='one_thing_container'>\n";
  echo "<div class='one_thing_section'>\n";

  echo "<table class='one_row'>\n";
  echo "<tr>\n";

  th ('Field');
  th ('Value');
  echo "</tr>\n";

  foreach ($info as $col)
    {
    $fieldName = $col ['Field'];
    // the row will generate an SQL update if you Ctrl+click it
    echo "<tr onclick='onClick(event,this.id)' id='field_$fieldName'>\n";
    tdx ($fieldName);
    // check if we can be more informative, like show an item name
    if (isset ($expand [$fieldName]))
      expandField ($row [$fieldName], $expand [$fieldName]);
    else
      tdx ($row [$fieldName], $col ['Type'] == 'text' ? 'tdl' : 'tdr');
    echo "</tr>\n";
    } // end of foreach
  echo "</table>\n";
  echo "</div>\n";  // end of database details

  // extra stuff
  if ($func)
    {
    echo "<div class='one_thing_section'>\n";
    $func ($id, $row);
    echo "</div>\n";  // end of other stuff
    }

  echo "</div>\n";  // end of flex container

  } // end of showOneThing



function lookupThing ($array, $id, $action)
  {
  $link = "<a href='?action=$action&id=$id'>$id</a>";
  if (!$id)
    return ('-');
  elseif (! isset ($array [$id]))
    return ("$id (not found)");
  else return ("$link: " . $array  [$id] );
  } // end of lookupThing


function convertTimeMinutes ($time, $ms = true)
  {
  // some things seem to have negative time
  if ($time <= 0)
    return $time;

  if ($ms)
    $time /= 1000;

  return round ($time / 60, 1);
  } // end of convertTimeMinutes

function convertTimeSeconds ($time)
  {
  // some things seem to have negative time
  if ($time <= 0)
    return $time;

  $time /= 1000;

  return $time;
  } // end of convertTimeSeconds

function convertTimeGeneral ($time)
  {
  // some things seem to have negative time
  if ($time <= 0)
    return $time;

  $time /= 1000;

  // small times show in seconds
  if ($time < 60)
    return $time . ' sec';

  // up to an hour show in minutes
  if ($time < 3600)
    return round ($time / 60, 1) . ' min';

  // otherwise show in hours
  return round ($time / 3600, 1) . ' hour';

  } // end of convertTimeGeneral


function listThing ($array, $id, $action)
  {
  echo "<li>";
  $link = "<a href='?action=$action&id=$id'>$id</a>";
  if (! isset ($array [$id]))
    echo ("$id (not found)");
  else
    echo ("$link: " . $array  [$id] );
  echo "\n";
  } // end of listThing

function convertGold ($amount)
{
  if ($amount == 0)
    return '0';

  $copper = $amount;
  $gold = intval ($copper / 10000);
  $copper -= $gold * 10000;
  $silver = intval ($copper / 100);
  $copper -= $silver * 100;

  $result = '';
  if ($gold)
    $result .= $gold . 'g ';
  if ($silver)
    $result .= $silver . 's ';
  if ($copper)
    $result .= $copper . 'c';
  return $result;
} // end of convertGold

function showSpawnPoints ($results, $heading, $tableName, $xName, $yName, $zName, $mName)
{
  global $maps;

  $map0 = 0;
  $map1 = 0;

  foreach ($results as $spawnRow)
    if ($spawnRow [$mName] == 0)
      $map0 ++;   // Eastern Kingdoms
    elseif ($spawnRow [$mName] == 1)
      $map1 ++;   // Kalimdor

  $mapName = '';
  if ($map0 > 0)
    {
    $mapName = 'Eastern_Kingdoms';
    $mapLeftPoint = 3300;
    $mapTopPoint = 4600;
    $mapWidth = 9500;
    $mapHeight = 19700;
    }
  elseif ($map1 > 0)
    {
    $mapName = 'Kalimdor';
    $mapLeftPoint = 4200;
    $mapTopPoint = 11700;
    $mapWidth = 11950;
    $mapHeight = 21050;
    }

  if ($mapName)
    {
      // get width and height
    $imageSize = getimagesize ("maps/$mapName.jpg");
    $imageWidth  = $imageSize [0];
    $imageHeight = $imageSize [1];

    echo "<div style='position:relative;'>\n";    // make a position context
    echo "<img src='maps/{$mapName}.jpg' style='display:block;
          max-width:initial; max-height:initial; margin:0;' id='{$mapName}_map'
          alt='{$mapName} map' title='{$mapName} map' >\n";
    } // if we are showing a map

  // draw an SVG circle for each spawn point
  foreach ($results as $spawnRow)
    {
    $x = $spawnRow [$xName];
    $y = $spawnRow [$yName];
    $z = $spawnRow [$zName];
    $map = $spawnRow [$mName];

    if ($mapName)
      {
      // draw on map

      $mapx = (1 - ($y - $mapLeftPoint)) / $mapWidth ;
      $mapy = (1 - ($x - $mapTopPoint)) / $mapHeight;

      $mapx *= $imageWidth;     // width of JPG
      $mapy *= $imageHeight;    // height of JPG

      $mapx = round ($mapx);
      $mapy = round ($mapy);

      $mapDotSize = MAP_DOT_SIZE;
      $halfMapDotSize = MAP_DOT_SIZE / 2;

      $mapx -= $halfMapDotSize;
      $mapy -= $halfMapDotSize;

      echo "<svg width='$mapDotSize' height='$mapDotSize' class='spawn_point' style='top:{$mapy}px; left:{$mapx}px;'>\n";
      echo "<circle cx='$halfMapDotSize' cy='$halfMapDotSize' r='$halfMapDotSize' fill='".MAP_DOT_FILL."' stroke='".MAP_DOT_STROKE."'/>\n";
      echo "<title>$x $y $z $map</title>\n";
      echo "</svg>\n";

      } // end of if we have a mapName

    } // for each spawn point

  if ($mapName)   // end of position context
    echo "</div><p>\n";


  listItems ($heading, $tableName, count ($results), $results,
    function ($row) use ($maps, $xName, $yName, $zName, $mName)
      {
      $x = $row [$xName];
      $y = $row [$yName];
      $z = $row [$zName];
      $map = $row [$mName];
      if ($map < 2)
        echo "<li>$x $y $z $map";
      else
        echo "<li>$x $y $z $map (" . fixHTML ($maps [$map]) . ")";
      } // end of listing function
      );


} // end of showSpawnPoints

function showItemCount ($n)
  {
  if ($n == 0 || $n == 1)
    return '';

  return " x$n";
  } // end of showItemCount

function spellRoll ($dieSides, $baseDice, $dicePerLevel, $basePoints)
  {
  // lowest roll
  if ($dieSides)
    $rolled_points = 1 + $dicePerLevel;
  else
    $rolled_points = 0;
  $min = $basePoints + $rolled_points;

  // highest level
  if ($dieSides)
    $rolled_points = $dieSides + $dicePerLevel;
  else
    $rolled_points = 0;
  $max = $basePoints + $rolled_points;

  if ($min != $max)
    return "$min to $max";
  else
    return $min;
  }

// generic handler for listing things (like items, spells, creatures) wit
// automatic splitting into two columns if there are a lot of them
// the function $listItemFunc does the actual listing. It returns false (or null)
// if the item was listed, and true if it was skipped for some reason.
function listItems ($heading, $table, $totalCount, $results, $listItemFunc)
{
  // trivial case - nothing to list
  if ($totalCount <= 0)
    return;

  echo "<div class='item_list' >\n";
  echo "<h2 title='Table: $table' >" . fixHTML ($heading) . "</h2>\n";
  $running_count = 0;

  // make two columns if there are a lot of them
  if ($totalCount > TWO_COLUMN_SPLIT)
    {
    echo "<div class='one_thing_container'>\n";
    echo "<div class='one_thing_section' style='max-width:30em;'>\n";
    }

  // we'll make a bulleted list
  echo "<ul>\n";

  foreach ($results as $row)
    {
    if (!$listItemFunc ($row))
      {
      $running_count++;
      // start the second column half-way through (+1 so the longer list is on the left)
      if ($totalCount > TWO_COLUMN_SPLIT && $running_count == intval ($totalCount / 2) + 1)
        {
        echo "</ul></div>\n";
        echo "<div class='one_thing_section'>\n<ul>\n";
        }
      } // end of if this row actually got listed

    } // for each row

  echo "</ul>\n";

  // end of two columns
  if ($totalCount > TWO_COLUMN_SPLIT)
    echo "</div></div>\n";

  echo "</div>\n";  // end of div holding this listing
  return $running_count;
}

/*

There are three types of expansions of numbers/bitmasks. Take for example the RACES array:

define ('RACES', array (
   1 =>'Human',
   2 =>'Orc',
   3 =>'Dwarf',
   4 =>'Night Elf',
   5 =>'Undead',
   6 =>'Tauren',
   7 =>'Gnome',
   8 =>'Troll',
  ));

1. The simple expansion is to turn a number into its equivalent text, eg. 2 becomes Orc.

    This case is handled by expandSimple. It returns the original number if wanted, then
    sees if the table has that key. If so it returns it, otherwise '(unknown)'.

    This is done by: expandSimple ($table, $ID, $showID = true)

2. A bitmask is supplied for the above table, for example 6 would be Orc,Dwarf (2 + 4).

    In this case we have to test each bit in the supplied mask, and if it matches we add
    the corresponding string to our results.

    This is done by expandMask ($table, $mask, $showMask = true)

3. A bitmask where the table already has the masks shifted. For example, ITEM_FLAGS:

    define ('ITEM_FLAGS', array (
         0x1 => 'No Pickup',
         0x2 => 'Conjured',
         0x4 => 'Has Loot',
         0x8 => 'Exotic',
     ...
    ));

    In this case we don't have to shift bits left as that has been done in the table.
    We just compare each bit in the supplied mask to see if it exists in the table,
    and if so return the description, and if not show the mask bit and '(unknown)'.

    This is done by expandShiftedMask ($table, $mask, $showMask = true)

*/

// Case 1: A simple lookup
function expandSimple ($table, $ID, $showID = true)
  {

  // sometimes -1 means "nothing"
  if ($ID < 0)
    return $ID;

  // if we can find it, return it, optionally with the ID in front
  if (array_key_exists ($ID, $table))
    return ($showID ? ($ID . ': ') : '') . $table [$ID];

  // we can't find it, so return it without the ID
  return $ID . ': (unknown)';

  } // end of expandSimple

// Case 2: The table has sequential numbers, not a shifted mask
function expandMask ($table, $mask, $showMask = true)
{
  $s = array ();  // results

  // for each bit we know of (others will be ignored)
  for ($i = 0; $i < count ($table); $i++)
    {
    if ($mask & (1 << $i))
      {
      if (array_key_exists ($i + 1, $table))
        $s [] = $table [$i + 1];
      else
        $s [] = 'Unknown: ' . ($i + 1);
      }   // end of this bit is set
    } // end of for each bit

  return ($showMask ? ($mask . ': ') : '') . implode (", ", $s);
} // end of expandMask

// Case 3: The table has mask values (eg. 0x01, 0x02, 0x04, 0x08)
function expandShiftedMask ($table, $mask, $showMask = true)
{
  if ($mask == 0)
    return 'None';    // we don't need to show zero I don't think

  $s = array ();
  for ($i = 0; $i < count ($table); $i++)
    if ($mask & (1 << $i))
      $s [] = array_key_exists (1 << $i, $table) ? $table [1 << $i] : "$i: (unknown)";

  return ($showMask ? ($mask . ': ') : '') . implode (", ", $s);
} // end of expandShiftedMask

// ------------------------------------------------------------
// Array and mask expansion functions
// ------------------------------------------------------------

function getFaction ($which, $showID = true)
  {
  global $factions;
  return expandSimple ($factions, $which, $showID);
  } // end of getFaction

function getMask ($which)
  {
  if ($which > 0)
    return '0x' . dechex ($which);
  return $which;
  } // end of getMask

function getItemClass ($which)
{
  return expandSimple (ITEM_CLASS, $which, false);
} // end of getItemClass

function getItemSubClass ($which)
{
  global $lastItemClass;

  if ($which >= 0 && $lastItemClass >= 0 && array_key_exists ($lastItemClass, ITEM_SUBCLASSES))
    return expandSimple (ITEM_SUBCLASSES [$lastItemClass], $which, false);
  else
    return $which;
} // end of getItemSubClass

function expandRaceMask ($mask)
{
  return expandMask (RACES, $mask, false);
} // end of expandRaceMask

function expandClassMask ($mask)
{
  return expandMask (CLASSES, $mask, false);
} // end of expandClassMask

function inhabitTypeMask ($mask)
{
  return expandMask (INHABIT_TYPE, $mask, false);
} // end of inhabitTypeMask

function expandItemFlagsMask ($mask)
{
  return expandShiftedMask (ITEM_FLAGS, $mask);
} // end of expandItemFlagsMask

function expandMechanicMask ($mask)
{
  return expandShiftedMask (MECHANIC_IMMUNE, $mask);
} // end of expandMechanicImmuneMask

function expandFlagsExtraMask ($mask)
{
  return expandShiftedMask (FLAGS_EXTRA, $mask);
} // end of expandFlagsExtraMask

function expandItemSubclassMask ($itemClass, $mask)
{
  // give up if we don't know the item class or it is negative
  if ($itemClass < 0 || !array_key_exists ($itemClass, ITEM_SUBCLASSES))
    return $mask;

  return expandMask (ITEM_SUBCLASSES [$itemClass], $mask);
} // end of expandItemSubclassMask

function expandNpcFlagsMask ($mask)
{
  return expandShiftedMask (NPC_FLAG, $mask, true);
} // end of expandNpcFlagsMask

function expandSpellTargetTypeMask ($mask)
{
  return expandShiftedMask (SPELL_TARGET_TYPE, $mask, true);
} // end of expandSpellTargetTypeMask

function expandSpellAttributesMask ($mask)
{
  return expandShiftedMask (SPELL_ATTRIBUTES, $mask, true);
} // end of expandSpellAttributesMask

function expandSpellAttributesExMask ($mask)
{
  return expandShiftedMask (SPELL_ATTRIBUTES_EX, $mask, true);
} // end of expandSpellAttributesExMask


?>
