<?php

/*
  Author: X'Genesis Qhulut <XGenesis-Qhulut@protonmail.com>
  Date:   August 2022

  See LICENSE for license details.
*/


/*

Regarding positioning:

When doing a search, which may be on text (eg. "bread") and also a field (eg. level > 5) we get a bunch
of results, which are presented in pages of QUERY_LIMIT (200) items per page.

Users can select which page they want to view, and then choose an item, ie. from the first (item 1) to the
last (item 200).

When viewing an item they can go forwards or backwards without returning to the search listing. Thus we need
to know where they are. We record, in the URL of the item being viewed (by calling makeSearchURI):

* The filter (eg. "filter=bread")
* The other field (eg. "filter_column=level")
* The condition (eg. "filter_compare=greater") -- see SECONDARY_FILTER below
* The thing to compare to (eg. "filter_value=5")
* The page we are on (eg. "page=5")
* The position in that page (eg. "pos=20")
* The sort order (eg. "sort_order=name")
* The maximum number available after doing that particular search (eg. max=1000)

Using that information we can show where this item is in the list. For example, item 20 on page 5 would be 804.
  That is: ((page - 1) * QUERY_LIMIT) + (pos - 1). For the user, we show one-relative, that is 805/1000.

With that number, and the search information we can go forwards or backwards by adding or subtracting 1 from "pos".
In the case of crossing a page boundary backwards pos may become negative but that is OK.

The table name we establish from the $handlers table (eg. for action "showOneSpell") we can find out that the table is
the SPELL table. That table also tells us the key for this particular database table.

The we can reconstruct the exact search (filter, sort order and so on) and use the LIMIT clause to narrow down to that
particular record. Then we read that, find its ID (key) and then press on as if the user had selected that key in the
first place.


Finding where you stuffed something up (say around line 1014):

  git log --pretty=short -u -L 1010,1020:wow_alpha_utils.php

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
  ShowWarning ($theerror);    // pffft!
  } // end of ShowError

function ShowWarningH ($theWarning)
  {
  pageContent (false, '', '',  '',
    function ($info) use ($theWarning)
      {
      topSection (false, function ($info)  use ($theWarning)
          {
          topMiddle (false, function ($info) use ($theWarning)
            {
            boxTitle ('Warning');
            echo "<div style='color:yellow'>\n";
            echo ("<ul><li>$theWarning</ul>");
            echo "</div>\n";
            });
          });
      } , '');
  } // end of ShowWarningH

function ShowWarning ($theWarning)
  {
  ShowWarningH (nl2br_http (fixHTML ($theWarning)));
  } // end of ShowWarning

function Problem ($why)
  {
  ShowError ($why);
  echo "</main></body></html>\n";

  die ();
  } // end of Problem


// Use this before database opened
function MajorProblem ($why)
  {
echo "
<div class='modal-container'>
  <div class='modal'>
    <p class='modal__header'>Error occurred at " . strftime ("%Y-%m-%d %H:%M:%S", time()) . "</p>
    <p class='modal__body'>We apologise that there has been a problem with the server ...</p>
    <p class='modal__footer'>" . fixHTML ($why) . "</p>
  </div>
  </main>
  </body>
</html>";

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

function searchContainerStart ($title, $description)
{
echo "
 <!-- PAGE CONTAINER-->
  <section class='main-page-container'>
    <!-- PAGE TITLE -->
    <div class='page-title page-title--search'>
      <div class='big-title'>
        <i class='page-title__database fas fa-database'></i>
        <i class='page-title__angle fas fa-angle-right'></i>
        <h1 class='big-title__heading' title='" . fixHTML ($title) . "'>" . fixHTML ($description) . "</h1>
      </div>
      <!-- SEARCH CONTAINER -->
      <div class='search-container'>
    ";
} // end of searchContainerStart

function searchContainerEnd ()
  {
  endDiv ('search-container');
  endDiv ('page-title page-title--search');
  } // end of searchContainerEnd

function columns_sort ($a, $b)
  {
  return $a ['Field'] <=> $b ['Field'];
  } // end of columns_sort

function showSearchForm ($description, $sortFields, $headings, $results, $where, $searchError)
  {
  global $filter, $action, $sort_order, $params, $page, $matches;
  global $filter_column, $filter_compare, $filter_value;
  global $searchFields, $extraWhere, $mainTable, $keyName;

  if ($sort_order)
    $order_clause = "ORDER BY $sort_order";
  else
    $order_clause = '';

  // find maximum matches
  $row = dbQueryOneParam ("SELECT COUNT(*) AS count FROM $mainTable $where $order_clause", $params);

  $matches = $row ['count'];

  $pages = ceil ($matches / QUERY_LIMIT);

  if ($page > $pages)
    $page = $pages;

  $PHP_SELF = $_SERVER['PHP_SELF'];

  $sortOptions = array ();
  foreach ($sortFields as $field)
    {
    if ($field == $sort_order)
      $selected = "selected = 'selected'";
    else
      $selected = '';
    $sortOptions [] =  "               <option value='" . fixHTML ($field) . "' $selected>" .
                        fixHTML (($field)) . "</option>";
    } // end of foreach sort field

  $mainTableFields = dbQueryParam ("SHOW COLUMNS FROM $mainTable", array ());

  $filterOptions = array ();
  usort ($mainTableFields, 'columns_sort');
  foreach ($mainTableFields as $field)
    {
    if (preg_match ('`int|float`', $field ['Type']))
      {
      if ($field ['Field'] == $filter_column)
        $selected = "selected = 'selected'";
      else
        $selected = '';
      $filterOptions [] = "               <option value='" . fixHTML ($field ['Field']) . "' $selected>" .
                          fixHTML ($field ['Field']) . "</option>";
      } // end of being a number
    }   // end of foreach field

  $comparisonOptions = array ();
  foreach (SECONDARY_FILTER as $compare => $operator)
    {
    if ($compare == $filter_compare)
      $selected = "selected = 'selected'";
    else
      $selected = '';
    $comparisonOptions [] =  "              <option value='" . fixHTML ($compare) . "' $selected>" .
                            fixHTML ($compare) . "</option>";
    } // end of foreach comparison

/*
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

*/

$resultsCount = count ($results);

$searchURI = makeSearchURI ();

$nextPage = $page + 1;
$prevPage = $page - 1;

searchContainerStart ($mainTable, $description);

echo "
        <!-- SEARCH FORM -->
        <form method='post' action='$PHP_SELF'>
            <input Type=hidden Name=action Value='$action'>
            <input Type=hidden Name=page Value='$page'>
            <div class='search-bar'>";

// error message here
if ($searchError)
  echo "<span>" . fixHTML ($searchError) . "</span>\n";

echo "
            <div class='search-bar__main'>
              <input
                class='custom-input'
                type='text'
                name='filter'
                size='40'
                value='" . fixHTML ($filter) . "'
                placeholder='ID or regular expression'
                autofocus='autofocus'
                title='Enter a number, text, or a regular expression'
              />
              <label for='sort_order'>Sort by</label>
              <select
                class='custom-selector'
                id='sort_order'
                name='sort_order'
                size='1'
                title='Which column to sort on'
              >"
              . "\n" . implode("\n", $sortOptions) . "
              </select>
            </div>
            <div class='search-bar__filters'>
              <label for='filter_column'>Also match</label>
              <select
                class='custom-selector'
                id='filter_column'
                name='filter_column'
                size='1'
                title='Which database column to filter on'
              >"
              . "\n" . implode ("\n", $filterOptions) . "
              </select>
              <select
                class='custom-selector'
                id='filter_compare'
                name='filter_compare'
                size='1'
                title='What comparison to do'
              >"
              . "\n" . implode ("\n", $comparisonOptions) . "
              </select>
              <input
                class='custom-input'
                type='text'
                name='filter_value'
                size='15'
                value='" . fixHTML ($filter_value)  . "'
                placeholder='Number/hex/bin'
                title='Leave empty for no secondary filtering.
                          Hex numbers: 0x0123ABCD
                          Binary numbers: 0b01010
                          Set: 1,7,9,15
                          Range: 20 to 30'
              />
            </div>
          </div>
          <button class='search-button'>
            <i class='fas fa-search'></i>
          </button>
        </form>   <!-- END SEARCH FORM -->
";
  searchContainerEnd ();

echo"

    <!-- PAGE CONTENT -->
    <div class='creature-details page-content'>
      <!-- TABLE CONTAINER -->
      <div class='table-container table-container--full'>
        <!-- PAGE COUNT -->
        <div class='page-counter'>
          <div>";
    if ($page > 1)
      echo "<a href='" . fixHTML ("$PHP_SELF?action=$action$searchURI&page=$prevPage") ."'
              ><i class='page-counter__left fas fa-angle-double-left'></i
            ></a>";
    if ($pages > 0)
      echo "<span>Page $page of $pages</span>";
    if ($page < $pages)
      echo "<a href='" . fixHTML ("$PHP_SELF?action=$action$searchURI&page=$nextPage") ."'
              ><i class='page-counter__right fas fa-angle-double-right'></i
            ></a>";
    echo "
          </div>
          <div>
            <span class='page-counter__results-count'>Showing $resultsCount on this page ($matches total matches)</span>
          </div>
        </div>
        <!-- END PAGE COUNT -->

        <!-- TABLE -->
        <table class='table-rows'>
         <thead>";

    if (count ($results) > 0)
      headings ($headings);

    echo "</thead>
        <tbody>";

  comment ("THE SEARCH RESULTS");

  if (count ($results) == 0)
    return false;

  return true;
  } // end of showSearchForm

function wrapUpSearch ()
  {
echo "
          </tbody>
        </table>
        <!-- END TABLE -->
      </div>
      <!-- END TABLE CONTAINER -->
    </div>
    <!-- END PAGE CONTENT -->
  </section>
  <!-- END PAGE CONTAINER-->
  ";

  } // end of wrapUpSearch

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
// eg.  $count = getCount ($row, 'EffectTriggerSpell_', 3);

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

function td ($s)
  {
  echo "  <td>";
  echo (fixHTML ($s));
  echo "</td>\n";
  } // end of td

function tdx ($s, $c='tdl')
  {
  echo "  <td>";
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
  echo "  <td>$s</td>\n";
  } // end of tdhr

function tdh ($s)
  {
  echo "  <td>$s</td>\n";
  } // end of tdh

function th ($s)
  {
  echo "  <th>";
  echo (fixHTML ($s));
  echo "</th>\n";
  } // end of th


function headings ($what)
{
  global $filter_column, $filter_value;
  if (strlen ($filter_value) > 0 && strlen ($filter_column) > 0)
    $what [] = $filter_column;

  echo "  <tr>\n";
  foreach ($what as $hdg)
    th ('  ' . $hdg);
  echo "  </tr>\n";
} // end of headings

function showFilterColumn ($row)
  {
    global $filter_column, $filter_value;
    if (strlen ($filter_value) > 0 && strlen ($filter_column) > 0)
      tdx ($row  [$filter_column], 'tdr');
  } // end of showFilterColumn

// helper function for initial search, or moving forwards or backwards one item
function setUpSearchHelper ()
  {
  global $filter, $where, $params, $sort_order;
  global $filter_column, $filter_compare, $filter_value, $fixed_filter_value;
  global $searchFields, $extraWhere, $mainTable, $keyName;

  $where = 'WHERE TRUE ';
  $params = array ();
  $params [0] = '';
  $filter = trim ($filter);
  $searchError = '';

  if ($filter)
    {
    // check filter regexp is OK
    $ok = @preg_match ("`$filter`", "whatever", $matches);
    if ($ok === false)
      {
      $warnings = error_get_last();
      $warning = $warnings ['message'];
      $searchError = "Error evaluating regular expression: $filter\n\n$warning.\n\nFILTER IGNORED.";
      } // if not OK
    else
      {
      if (preg_match ('|^\d+$|', $filter))
        {
        $where .= " AND $keyName = ?";
        $params = array ('i', &$filter);
        }
      else
        {
        $where = '';
        $strings = '';
        $params = array ('');
        foreach ($searchFields as $field)
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
      }   // end of if no error in regexp
    } // end of having a filter

  // secondary comparison
  if (strlen ($filter_value) > 0 && strlen ($filter_column) > 0)
    {
    // IN SET
    if ($filter_compare == 'in set' || $filter_compare == 'not in set')
      {
      if (!preg_match ('/\s*([+\-]?\d+)(\s*,([+\-]?\d+))*\s*$/', $filter_value))
        {
        $searchError = 'Filter comparison value "' . $filter_value  . '" not a series of comma-separated numbers. SECONDARY FILTER IGNORED.';
        }
      else
        {
        $where .= " AND ($filter_column " . SECONDARY_FILTER [$filter_compare];  // ie. IN or NOT IN
        $where .= '(' . $filter_value . ')';
        $where .= ')';
        } // end of if not ignored
      } // end of in or not in
   // IN RANGE
   elseif ($filter_compare == 'in range' || $filter_compare == 'not in range')
      {
      if (!preg_match ('/\s*([+\-]?\d+)\s+to\s+([+\-]?\d+)\s*$/i', $filter_value, $matches))
        {
        $searchError = 'Filter comparison value "' . $filter_value . '" not in format: (number) TO (number). '  .
                     'SECONDARY FILTER IGNORED.';
        }
      else
        {
        $where .= " AND (" . SECONDARY_FILTER [$filter_compare];  // ie. NOT or space
        $where .= ' (' . $filter_column . ' >= ' . $matches [1] . ' AND ' .
                         $filter_column . ' <= ' . $matches [2] . ')';
        $where .= ')';
        } // end of if not ignored
      } // end of in range or not in range
    // JUST A NUMBER
    else
      {
      if (!preg_match ('/(^[+\-]?\d+$)|(^0[xX][0-9A-Fa-f]+$)|(^0[bB][01]+$)|^[+\-]?(\d*\.)?(\d+)$/', $filter_value))
        {
        $searchError = 'Filter comparison value "'. $filter_value  . '" not decimal, float, hex or binary number. SECONDARY FILTER IGNORED.';
        }
      else
        {
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
        } // end of if not ignored
      } // end of NOT (in or not in)
    } // end of secondary comparison

  if ($sort_order)
    $order_clause = "ORDER BY $sort_order";
  else
    $order_clause = '';

  return array ('select' => "SELECT * FROM $mainTable $where $extraWhere $order_clause",
                'params' => $params,
                'where'  => $where,
                'error'  => $searchError);

  } // end of setUpSearchHelper

function setUpSearch ($description, $sortFields, $headings)
  {
  global $searchFields, $extraWhere, $mainTable, $keyName;

  $searchParams = setUpSearchHelper ();

  $offset = getQueryOffset(); // based on the requested page number

  // do the search
  $results = dbQueryParam ($searchParams ['select'] . " LIMIT $offset, " . QUERY_LIMIT,
                           $searchParams ['params']);

  // now show the search form
  if (!showSearchForm ($description, $sortFields, $headings, $results,  $searchParams ['where'] . ' ' . $extraWhere, $searchParams ['error']))
    {
    comment ("SETTING UP SEARCH FORM FAILED");
    return false;
    }

  // let them display the results
  return $results;

  } // end of setUpSearch

// shows all fields from any table
// $limit is a table of the only keys we are interested in
function showOneThing ($table, $key, $id, $description, $nameField, $expand, $limit = false)
  {
  $info = dbQueryParam ("SHOW COLUMNS FROM $table", array ());

  $row = dbQueryOneParam ("SELECT * FROM $table WHERE $key = ?", array ('i', &$id));
  if (!$row)
    {
    ShowWarning ("$description $id is not on the database");
    return;
    }

  if ($description)
    boxTitle ($description);

  if  (!$limit && preg_match ('|\.(.+)$|', $table, $matches))
    {
    $tableOnly = $matches [1];
    // add a box for displaying SQL update information for copy/paste into an update line
    echo "<div id='editing_sql' class='sql-statement'>UPDATE `$tableOnly` SET `<span id='sql_field_name'>field</span>`
        = xxxx WHERE (`$key` = " .
         $row [$key] . ");\n
         </div>\n";
    }

  if ($limit)
    echo "<table class='table-fields'>\n";
  else
    echo "<table class='table-rows'>\n";

  echo "<thead>\n";
  echo "<tr>\n";
  th ('Field');
  th ('Value');
  echo "</tr>\n";
  echo "</thead>\n";
  echo "<tbody>\n";

  foreach ($info as $col)
    {
    $fieldName = $col ['Field'];
    if ($limit && !in_array ($fieldName, $limit))
      continue;
    // the row will generate an SQL update if you Alt+click it
    if ($limit)
      echo " <tr>\n";
    else
      echo " <tr onclick='onRowClick(event,this.id)' id='field_$fieldName'>\n";
    tdx ($fieldName);
    // check if we can be more informative, like show an item name
    if (isset ($expand [$fieldName]))
      expandField ($row [$fieldName], $expand [$fieldName]);
    else
      tdx ($row [$fieldName], preg_match ('/text/', $col ['Type']) ? 'tdl' : 'tdr');
    echo " </tr>\n";
    } // end of foreach
  echo "</tbody>\n";
  echo "</table>\n";


/*
  // extra stuff
  if ($func)
    {
    echo "<div class='one_thing_section'>\n";
    $func ($id, $row);
    echo "</div>\n";  // end of other stuff
    }

  echo "</div>\n";  // end of flex container

*/

  } // end of showOneThing


// for hyperlinks to things with a name we can show (like a quest name)
function lookupThing ($array, $id, $action)
  {
  $link = "<a href='?action=$action&id=$id'>$id</a>";
  if (!$id)
    return ('-');
  elseif (array_key_exists ($id, $array))
    return ("$link: <a href='?action=$action&id=$id'>" . fixHTML ($array  [$id]) . '</a>' );
  return ("$id (not found)");
  } // end of lookupThing

// for hyperlinks to things with no name to be shown
function makeLink ($id, $action)
  {
  if (!$id)
    return ('-');
  return "<a href='?action=$action&id=$id'>$id</a>";
  } // end of makeLink

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

function listThing ($array, $id, $action, $info = '', $info2 = '', $title = '')
  {
  if (array_key_exists ($action, ICONS))
    $icon = ICONS [$action];
  else
    $icon = 'fa-table';

  $title = fixHTML ($title);

  // if they are known, make a hyperlink
  if (array_key_exists ($id, $array ))
    {
    $link = "<a href='?action=$action&id=$id' class='row-card' title='$title'>";
    $unlink = '</a>';
    $description = fixHTML ($array  [$id]);
    }
  else
    {
    $link = "<div class='row-card' title='$title'>";
    $unlink = '</div>';
    $description = "(not found)";
    }

  echo "
  $link
  <span class='row-card__head'>
  <i class='fas $icon'></i>$id</span>
  <span class='row-card__content'>$description</span>
  ";
  if ($info)
    echo "<span class='row-card__extra'>" . fixHTML ($info) . "</span>\n";
  if ($info2)
    echo "<span class='row-card__extra'>" . fixHTML ($info2) . "</span>\n";

  echo "
  $unlink
  ";

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

function showSpawnPoints ($results, $heading, $tableName, $idName, $xName, $yName, $zName, $mName, $mTypeName = false)
{
  global $maps;

  if (count ($results) == 0)
    return;

  $map0 = 0;
  $map1 = 0;

  foreach ($results as $spawnRow)
    if ($spawnRow [$mName] == 0)
      $map0 ++;   // Eastern Kingdoms
    elseif ($spawnRow [$mName] == 1)
      $map1 ++;   // Kalimdor


  // the display size
  $imageWidth = 345;
  $imageHeight = 650;


  $mapName = '';
  if ($map0 > 0)
    {
    $mapName = 'Eastern_Kingdoms';
    // see: map_calculations.lua to get these figures
    $mapLeftPoint = 4267.7658363136;
    $mapTopPoint  = 4657.9751308793;
    $mapWidth     = 10568.022008253;
    $mapHeight    = 19980.94603272;

    }
  elseif ($map1 > 0)
    {
    $mapName = 'Kalimdor';
    // see: map_calculations.lua to get these figures
    $mapLeftPoint = 5042.4865925889;
    $mapTopPoint  = 11763.324337648;
    $mapWidth     = 13100.059130778;
    $mapHeight    = 24611.604572322;

    }

  if (!$mapName)
    return;

  comment ("Drawing $mapName");

  // get width and height

/*
  $imageSize = getimagesize ("maps/$mapName.jpg");
  $imageWidth  = $imageSize [0];
  $imageHeight = $imageSize [1];
*/


  echo "<div class='map-container'
          id='map-container-$mapName'
          onmousedown='onMouseDownMapContainer(event)'
          onmouseup='onMouseUpMapContainer(event)'
          onmouseleave='onMouseLeaveMapContainer(event)'
          onmousemove='onMouseMoveMapContainer(event)'
          onwheel='onMouseWheelMapContainer(event)'
          draggable='false'
          style='position:relative; background-color:#172e46;'
          >\n";
  echo "<img src='maps/{$mapName}.webp'
          style='display:block;
          width:{$imageWidth}px; height:{$imageHeight}px;
          max-width:initial;
          max-height:initial;
          position:absolute;
          left:0;
          top:0;
          margin:0;'
          id='{$mapName}_map'
          data-mapleft='$mapLeftPoint'
          data-maptop='$mapTopPoint'
          data-mapwidth='$mapWidth'
          data-mapheight='$mapHeight'
          data-width='$imageWidth'
          data-height='$imageHeight'
          onmouseenter='onMouseEnterImg(event)'
          draggable='false'
          alt='{$mapName} map' title='{$mapName} map' >\n";

  // draw an SVG circle for each spawn point
  foreach ($results as $spawnRow)
    {
    $x = $spawnRow [$xName];
    $y = $spawnRow [$yName];
    $z = $spawnRow [$zName];
    $map = $spawnRow [$mName];
    $spawn_id = $spawnRow [$idName];
    if (array_key_exists ('color', $spawnRow))
      {
      $strokeColor = 'darkblue';
      $fillColor = $spawnRow ['color'];
      }
    else
      {
      $strokeColor = MAP_DOT_STROKE;
      $fillColor = MAP_DOT_FILL;
      }
    if (array_key_exists ('name', $spawnRow))
      $name = $spawnRow ['name'];
    else
      $name = '';
    if ($mTypeName)
      {
      $movement_type = $spawnRow [$mTypeName];
      if ($movement_type == 1)  // random
        $wander_distance = ': ' . $spawnRow ['wander_distance'] . ' yards';
      else
        $wander_distance = '';
      $movementType = ' [' . expandSimple (MOVEMENT_TYPE, $movement_type, false) . "$wander_distance] " . $name;
      }
    else
      $movementType = $name;

    if ($mapName)
      {
      // draw on map

      $mapx = (1 - ($y - $mapLeftPoint)) / $mapWidth ;
      $mapy = (1 - ($x - $mapTopPoint)) / $mapHeight;

      $mapx *= $imageWidth;     // width of JPG
      $mapy *= $imageHeight;    // height of JPG

      $mapx = round ($mapx, 3);
      $mapy = round ($mapy, 3);

      $mapDotSize = MAP_DOT_SIZE;
      $halfMapDotSize = MAP_DOT_SIZE / 2;

      $location = "$x $y $z $map";
      echo "<div onclick='clickOnSpawnPoint (event, \"$location\")' >\n";
      echo "<svg width='{$mapDotSize}px' height='{$mapDotSize}px' class='spawn_point' style='top:{$mapy}px; left:{$mapx}px;'
            data-left='{$mapx}' data-top='{$mapy}' data-spawnid='$spawn_id'>\n";
      echo "  <circle cx='$halfMapDotSize' cy='$halfMapDotSize' r='$halfMapDotSize' fill='$fillColor' stroke='$strokeColor'/>\n";
      echo "  <title>$location (click to copy)\nID: $spawn_id. $movementType</title>\n";
      echo "</svg></div>\n";

      } // end of if we have a mapName

    } // for each spawn point

    endDiv ('map-container');

} // end of showSpawnPoints

function startElementInformation ($heading, $table, $uptop = false)
  {
  if (!$uptop)
    echo "<div class='element-information element-information--independant'>\n";
  else
    echo "<div class='element-information'>\n";

  echo "
  <h2 class='element-information__title' title='" . fixHTML ($table) . "'>" . fixHTML ($heading) . "</h2>
  <div class='element-information__bar'></div>
  <div class='element-information__content'>
  ";

  } // end of startElementInformation

function endElementInformation ()
  {
  endDiv ('element-information__content');
  endDiv ('element-information [element-information--independant]');
  } // end of endElementInformation

function listSpawnPoints ($results, $heading, $table, $idName, $xName, $yName, $zName, $mName, $mTypeName = false)
  {
  global $maps;
  $count = count ($results);

  if ($count == 0)
    return 0;

  startElementInformation ($heading . " ($count)", $table, true);
  echo "<div class='row-card-container'>\n";

  foreach ($results as $row)
    {
    $x = $row [$xName];
    $y = $row [$yName];
    $z = $row [$zName];
    $map = $row [$mName];
    $spawn_id = $row [$idName];
    if ($mTypeName)
      {
      $movement_type = $row [$mTypeName];
      if ($movement_type == 1)  // random
        $wander_distance = ': ' . $row ['wander_distance'] . ' yards';
      else
        $wander_distance = '';
      $movementType = ' [' . expandSimple (MOVEMENT_TYPE, $movement_type, false) . "$wander_distance]";
      }
    else
      $movementType = '';

    if ($map < 2)
       $description = "$x $y $z $map$movementType";
    else
       $description = "$x $y $z $map (" . fixHTML ($maps [$map]) . ")";
    echo "
    <div  class='row-card'>
    <span class='row-card__head'><i class='fas fa-globe'></i></span>
    <span class='row-card__content' title='Spawn ID: $spawn_id'>$description</span>
    <span class='row-card__extra'>
     <button  data-location='$x $y $z $map' onclick='copyContents(event)'  title='Click to copy spawn point to clipboard' >
     <i class='fas fa-copy'></i>
    </button>
    </span>
    ";
    endDiv ('row-card');
    } // end of foreach

  endDiv ('row-card-container');
  endElementInformation ();

  return $count;
  } // end of listSpawnPoints

function comment ($what)
  {
  echo "\n  <!-- " . str_replace ('--', '—', $what) . " -->\n\n";
  } // end of comment

function showItemCount ($n)
  {
  if ($n == 0 || $n == 1)
    return '';

  return " x$n";
  } // end of showItemCount

function spellRoll ($dieSides, $baseDice, $dicePerLevel, $basePoints, $minOnly = false)
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

  if ($min != $max && !$minOnly)
    return "$min to $max";
  else
    return $min;
  }

// generic handler for listing things (like items, spells, creatures) wit
// the function $listItemFunc does the actual listing. It returns false (or null)
// if the item was listed, and true if it was skipped for some reason.
function listItems ($heading, $table, $totalCount, $results, $listItemFunc, $uptop = false)
{
  global $listedItemsCount;

  // trivial case - nothing to list
  if ($totalCount <= 0)
    return;

  $listedItemsCount += $totalCount;

  $running_count = 0;

  startElementInformation ($heading . " ($totalCount)", $table, $uptop);
  echo "<div class='row-card-container'>\n";

  foreach ($results as $row)
    {
    if (!$listItemFunc ($row))
      {
      $running_count++;
      } // end of if this row actually got listed
    } // for each row

  endDiv ('row-card-container');
  endElementInformation ();
  return $running_count;
} // end of fancyListItems


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

  return ($showMask ? (getMask ($mask) . ': ') : '') . implode (", ", $s);
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

  return ($showMask ? (getMask ($mask) . ': ') : '') . implode (", ", $s);
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

function makeSearchURI ($withPage = false)
  {
  global $filter, $action, $sort_order, $params, $page, $matches;
  global $filter_column, $filter_compare, $filter_value;

  $searchURI = '';

  if ($filter)
    $searchURI .= '&filter=' . urlencode ($filter);

  if ($sort_order)
    $searchURI .= '&sort_order=' . urlencode ($sort_order);

  if ($filter_value)
    {
    if ($filter_column)
      $searchURI .= '&filter_column=' . urlencode ($filter_column);
    if ($filter_compare)
      $searchURI .= '&filter_compare=' . urlencode ($filter_compare);
    $searchURI .= '&filter_value=' . urlencode ($filter_value);
    }

  if ($page && $withPage && $page > 1)
    $searchURI .= '&page=' . urlencode ($page);

  return $searchURI;
  }

function endDiv ($what)
{
    echo "</div>  <!-- End of DIV: " . str_replace ('--', '—', $what) . " -->\n\n";
}

function getFunctionName ($func)
  {
  if (is_string ($func))
    $funcName = ': ' . $func;
  else
    $funcName = '';
  return $funcName;
  } // end of getFunctionName

// OUTER CONTAINER (WHOLE PAGE EXCEPT FOR MENU)
function pageContent ($userInfo, $pageType, $name, $goback, $func, $table)
  {
  $searchURI = makeSearchURI (true);

  $funcName = getFunctionName ($func);
  $scheme = $_SERVER['REQUEST_SCHEME'];
  if (!$scheme)
    $scheme = 'https';

  $fallbackPage = $scheme . ':' . fixHTML ($_SERVER['SCRIPT_NAME']);

  echo "
  <!-- PAGE CONTAINER-->
  <section class='main-page-container'>

    <!-- PAGE TITLE -->
    <div class='page-title'>
      <div>
        <button class='page-title__goback'>
          <i class='fas fa-angle-left' onclick='goBack(\"$fallbackPage\")' ></i>
        </button>";
    if ($name)
      echo "
        <h1>" . fixHTML ($name) . "</h1>
        ";
    echo "
      </div>
      <div>
        <i class='page-title__database fas fa-database'></i>
        <i class='page-title__angle fas fa-angle-right'></i>
        <p class='page-title__table' title='" . fixHTML ($table) . "'>" . fixHTML ($pageType) . "</p>
      </div>
    </div>
    <!-- END PAGE TITLE -->

    <!-- PAGE CONTENT -->
    <div class='object-container page-content'>
  ";
  comment ("CONTENT STARTS NOW$funcName");

  $func ($userInfo);   // output contents

  comment ('CONTENT FINISHED');

  echo "
  </div>  <!-- END PAGE CONTENT -->
  </section>  <!-- END PAGE CONTAINER-->
  ";
  } // end of pageContent

// TOP ROW OF DETAILS
function topSection ($userInfo, $func)
{

  $funcName = getFunctionName ($func);

  comment ("TOP ROW OF DETAILS$funcName");

  echo "<div class='object-container__informations'>\n";

  $func ($userInfo);   // output contents

  endDiv ('object-container__informations');

} // end of topSection

// THIS GOES INSIDE: topSection
// $title is for the box title
// $pos is which one we are out of $max (eg. 4/10)
function topLeft ($userInfo, $func, $title = 'General')
{
  global $VALID_NUMBER, $action;
  global $filter;
  global $filter_column, $filter_compare, $filter_value;

  $funcName = getFunctionName ($func);

  // how far through the list we are
  $pos = getG ('pos', 8, $VALID_NUMBER);
  $max = getG ('max', 8, $VALID_NUMBER);

  comment ("TOP-LEFT BOX$funcName");

  echo "<div class='object-container__informations__details1'>";

  // the title has to come out first
  boxTitle ($title);
  $offset = getQueryOffset ();   // allow for page number

  // we have to be at least at entry 1 for the bars to appear
  // also we need more than one (so $max has to be more than 1)
  if (($pos + $offset) > 0 && $max > 1)
    {
    echo "
    <!-- ENTRY NAV -->
    <div class='entry-navigation'>
    ";

    $searchURI = "?action=$action" . makeSearchURI (true) . "&max=$max";
    $nextPos = $pos + 1;
    $prevPos = $pos - 1;

    $searchTitle = 'Full listing';
    if ($filter || $filter_value)
      {
      $s = array ();
      if ($filter)
        $s [] = "Text matching: \"$filter\"";
      if ($filter_value)
        $s [] = "`$filter_column` $filter_compare $filter_value";
      $searchTitle = fixHTML (implode (" AND ", $s));
      } // if we have some sort of filter

    // more than entry 1, we can go back
    if (($pos + $offset) > 1)
      echo
      " <a href='$searchURI&pos=$prevPos' class='entry-navigation__left'>
        <i class='fas fa-angle-left'></i>
      </a>
      ";
    $actualPos = $pos + getQueryOffset ();
    // show how far we are through, eg. 50/200
    echo "<span class='entry-navigation__entry' title='$searchTitle'>$actualPos/$max</span>\n";

    // less than entry max, we can go forwards
    if (($pos + $offset) < $max)
      echo
      "  <a href='$searchURI&pos=$nextPos' class='entry-navigation__right'>
        <i class='fas fa-angle-right'></i>
      </a>
      ";
    endDiv ('entry-navigation');
    } // end of having navigation bars

  $func ($userInfo);   // output contents

  endDiv ('object-container__informations__details1');

} // end of topLeft

// THIS GOES INSIDE: topSection
function topMiddle ($userInfo, $func)
{
  $funcName = getFunctionName ($func);

  comment ("TOP-MIDDLE BOX$funcName");

  echo "<div class='object-container__informations__details2'>\n";

  $func ($userInfo);   // output contents

  endDiv ('object-container__informations__details2');

} // end of topLeft


// THIS GOES INSIDE: topSection
function topRight ($userInfo, $func)
{
  $funcName = getFunctionName ($func);

  comment ("TOP-RIGHT BOX : CAROUSSEL$funcName");

  echo "<aside class='caroussel'
        onmousemove='onMouseMoveArea(event)'
        id='spawn-map-caroussel'>
    <div class='help-box' id='spawn-map-help-box'>
    <i class='fas fa-arrow-pointer help-box__info-icon'></i>
    <span class='help-box__content'> drag and zoom </span>";
  endDiv ('help-box');

  $func ($userInfo);   // output contents

  echo "
  <!-- MAP HIGHLIGHTER -->
  <div class='map-highlighter' id='spawn-map-highlighter'>
    <i class='fas fa-arrows-to-circle'></i>
     ";
  endDiv ('spawn-map-highlighter');

  echo "</aside>\n";  // end of caroussel

} // end of topLeft

// THIS IS AFTER: topSection
function middleSection ($userInfo, $func)
{
  $funcName = getFunctionName ($func);

  comment ("MIDDLE ROW$funcName");

  echo "<div class='details-container'>\n";

  $func ($userInfo);   // output contents

  endDiv ('details-container');

} // end of middleSection

function middleDetails ($userInfo, $func)
{
  $funcName = getFunctionName ($func);

  comment ("DETAILS IN MIDDLE$funcName");

  echo "<div class='element-information element-information--independant'>\n";
  echo "<div class='element-information__content'>\n";

  $func ($userInfo);   // output contents

  endDiv ('element-information__content');
  endDiv ('element-information element-information--independant');

} // end of middleDetails

function bottomSection ($userInfo, $func)
{
  $funcName = getFunctionName ($func);

  comment ("DETAILS AT BOTTOM$funcName");

  echo "<div class='table-container'>\n";

  $func ($userInfo);   // output contents

  endDiv ('table-container');

} // end of bottomSection

function bottomSectionMany ($userInfo, $func)
{
  $funcName = getFunctionName ($func);

  comment ("DETAILS AT BOTTOM$funcName");

  echo "<div class='table-container table-container--many'>\n";

  $func ($userInfo);   // output contents

  endDiv ('table-container table-container--many');

} // end of bottomSectionMany

function bottomDetails ($userInfo, $func)
{
  $funcName = getFunctionName ($func);

  comment ("DETAILS AT BOTTOM$funcName");

  echo "<div class='table-rows'>\n";

  $func ($userInfo);   // output contents

  endDiv ('table-rows');

} // end of bottomDetails


function showNoSpawnPoints ($heading = 'Spawn points', $message = 'None')
  {
  comment ('NO SOMETHING MESSAGE');

  echo "
  <div class='element-information'>
     <h2  class='element-information__title'>$heading</h2>
       <div class='element-information__bar'></div>
          <div class='element-information__content'>
            <div class='row-card-container'>
               <div  class='row-card'>
               <span class='row-card__head'><i class='fas fa-circle-info'></i></span>
               <span class='row-card__content'>$message</span>
               </div>
            </div>
        </div>
      </div>
     ";
  }   // end of showNoSpawnPoints

function boxTitle ($what)
{
  comment ("BOX TITLE: $what");
  echo "
    <div class='tiny-title'>
    <h2 class='tiny-title__heading'>". fixHTML ($what) . "</h2>
    <div class='tiny-title__bar'></div>
  ";

  endDiv ('tiny-title');

} // end of boxTitle

function doArrowsForMap ($table, $where, $param, $mName)
{
  // see if we need arrows
  $results = dbQueryParam ("SELECT * FROM $table WHERE $where", $param);

  $map0 = 0;
  $map1 = 0;
  foreach ($results as $spawnRow)
    {
    if ($spawnRow [$mName] == 0)
      $map0 ++;   // Eastern Kingdoms
    elseif ($spawnRow [$mName] == 1)
      $map1 ++;   // Kalimdor
    } // end of foreach

  if ($map0 && $map1)
    {
    echo "<div id='caroussel_arrows'>\n";
    echo "<a class='caroussel__left-arrow' href='#Kalimdor_map'
        ><i class='fas fa-angle-left'></i></a>\n";
    echo "<a class='caroussel__right-arrow' href='#Eastern_Kingdoms_map'
        ><i class='fas fa-angle-right'></i></a>\n";
    endDiv ('caroussel_arrows');
    } // we are showing both maps so we need both arrows

} // end of doArrowsForMap

function checkID ()
{
  global $id;
  if ($id == '' || $id === false || $id < 0)
    {
    ShowWarning ("Parameter 'id' required but is not supplied or negative");
    return false;
    }
  return true;
} // end of checkID

function setTitle ($what)
  {
  // we don't want single quotes in the title, so we put a backslash in front of them
  echo "\n<script> document.title = 'WoW DB | " .  str_replace("'", "\\'", $what) . "' </script>\n";
  } // end of setTitle

// so we can tweak later how many decimal points for chance displays
function roundChance ($chance)
  {
  return round (abs ($chance), 2) . '%';
  } // end of roundChance

function getAllSpawnedCreatures ()
{
  $spawns_creatures = SPAWNS_CREATURES;

  // find all spawned creatures
  $spawned = array ();

  for ($i = 1; $i <= 4; $i++)
    {
    $results = dbQuery (
            "SELECT spawn_entry$i AS entry
             FROM $spawns_creatures
             WHERE spawn_entry$i < " . MAX_CREATURE . "
             GROUP BY spawn_entry$i");

    while ($row = dbFetch ($results))
      {
      $spawned [$row ['entry']] = true;
      }
    dbFree ($results);
    } // end of all 4 spawn point entries

  return $spawned;
} // end of getAllSpawnedCreatures

function repositionSearch ()
  {
  global $VALID_NUMBER, $id, $keyName;

  // how far through the list we are
  $pos = getG ('pos', 8, $VALID_NUMBER);
  $max = getG ('max', 8, $VALID_NUMBER);

  $offset = getQueryOffset () + $pos - 1;   // allow for page number

  // if an ID supplied, we can't be repositioning
  if ($id !== false)
    return false;

  // can't reposition if we don't know where we are
  if (($pos + $offset) <= 0 || !$max)
    {
    ShowWarning ("Position out of range");
    return false;
    }

  $searchParams = setUpSearchHelper ();

  // give up if we couldn't generate the SQL again
  if ($searchParams ['error'])
    return false;

  // do the search
  $results = dbQueryParam ($searchParams ['select'] . " LIMIT $offset, 1",
                           $searchParams ['params']);

  // give up if this is not on file (they fiddled with the page or pos
  if (!$results)
    {
    ShowWarning ("Record not on file");
    return false;
    }

  $id = $results [0] [$keyName];
  return true;
  }


function lookupItemHelper ($id, $count)
  {
  global $items;
  $countStr = '';
  if ($count > 1)
    $countStr = "&nbsp;x$count";

  $link = "<a href='?action=show_item&id=$id'>$id</a>";
  if (!$id)
    return ('');

  if (!isset ($items [$id]))
    return ("$id (not found)$countStr");

  return ("$link: " . $items  [$id] . $countStr);

  } // end of lookupItem

function lookupItem ($id, $count)
  {
  tdh (lookupItemHelper ($id, $count));
  } // end of lookupItem

function lookupItems ($row, $items, $counts)
  {
  $s = '';
  foreach ($items as $n => $item)
    {
    if ($row [$item])
      {
      if ($s)
        $s .= '<br>';
      $s .= lookupItemHelper ($row [$item], $row [$counts [$n]]);
      }
    } // end of foreach

  return $s;
  } // end of lookupItems


function fixQuestText ($s)
  {
  $s = str_ireplace ('$n', "<name>", $s);
  $s = str_ireplace ('$r', "<race>", $s);
  $s = str_ireplace ('$c', "<class>", $s);

  // gendered alternatives, eg. lad/lass, brother/sister, good sir/my lady etc.
  // Example: "$gmister:lady;"    becomes "<mister/lady>"

  $s = preg_replace ('/\$g ?([^:]+):([^;]+);/i', '<\1/\2>', $s);

  $s = fixHTML ($s);
  return str_ireplace ('$b', "<br>", $s);
  } // end of fixQuestText

function fixSpellText ($s, $duration)
  {
  // numbered alternatives, eg. loaf/loaves
  // Example: "$lloaf:loaves;"    becomes "<loaf/loaves>"

  $s = preg_replace ('/\$l ?([^:]+):([^;]+);/i', '<\1/\2>', $s);
  $s = str_ireplace ('$d',  convertTimeGeneral ($duration), $s);

  // gendered alternatives, eg. lad/lass, brother/sister, good sir/my lady etc.
  // Example: "$gmister:lady;"    becomes "<mister/lady>"

  $s = preg_replace ('/\$g ?([^:]+):([^;]+);/i', '<\1/\2>', $s);
  $s = fixHTML ($s);
  return str_ireplace ('$b', "<br>", $s);
  } // end of fixSpellText

function trainer_spell_compare ($a, $b)
  {
  global $spells;
  if ($spells [$a ['playerspell']] == $spells [$b ['playerspell']])
    return $a ['playerspell'] <=> $b ['playerspell'];
  return $spells [$a ['playerspell']] <=> $spells [$b ['playerspell']];
  } // end of trainer_spell_compare

function item_compare ($a, $b)
  {
  global $items;
  return $items [$a ['item']] <=> $items [$b ['item']];
  } // end of item_compare

function loot_item_compare ($a, $b)
  {
  global $items;
  if ($a ['ChanceOrQuestChance'] == $b ['ChanceOrQuestChance'])
    return $items [$a ['item']] <=> $items [$b ['item']];
  return - (abs ($a ['ChanceOrQuestChance']) <=> abs ($b ['ChanceOrQuestChance']));
  } // end of loot_item_compare

function reference_item_compare ($a, $b)
  {
  global $items;
  if ($a ['chance'] == $b ['chance'])
    return $items [$a ['refItem']] <=> $items [$b ['refItem']];
  return - ($a ['chance'] <=> $b ['chance']);
  } // end of reference_item_compare

function creature_compare ($a, $b)
  {
  global $creatures;
  return $creatures [$a ['npc']] <=> $creatures [$b ['npc']];
  } // end of item_compare

function sendOgMeta ($title, $image, $imageType, $description)
  {
  global $documentRoot, $executionDir;

  $width = 800;
  $height = 500;

  $imageInfo = array ();
  if (file_exists ("$documentRoot$executionDir$image"))
    {
    list($width, $height, $type, $attr) = getimagesize ($documentRoot . $executionDir . $image);
    } // if file exists


  echo "
      <meta property='og:title' content='". fixHTML ($title) ."'>
      <meta property='og:image' content='". fixHTML ($image) ."'>
      <meta property='og:image:width'   content='$width' >
      <meta property='og:image:height'  content='$height' >
      <meta property='og:image:type'    content='image/$imageType' >
      <meta property='og:description'   content='". fixHTML ($description) . "'>
  ";

} // end of sendOgMeta

?>
