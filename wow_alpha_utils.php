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


function nl2br_http ($text)
  {
  return str_replace ("\n", "<br>", $text);
  } // end of nl2br_http

function ShowError ($theerror)
  {
  echo "<div class='error_message'>\n";
  echo (nl2br_http (htmlspecialchars ($theerror, ENT_SUBSTITUTE | ENT_QUOTES | ENT_HTML5) . "\n"));
  echo "</div>\n";
  } // end of ShowError

function ShowWarningH ($theWarning)
  {
  echo ("<p class='warning_message'>" . $theWarning . "</p>\n");
  } // end of ShowWarningH

function ShowWarning ($theWarning)
  {
  ShowWarningH (nl2br_http (htmlspecialchars ($theWarning, ENT_SUBSTITUTE | ENT_QUOTES | ENT_HTML5)));
  } // end of ShowWarning

function ShowInfoH ($theInfo)
  {
  echo ("<p class='info_message'>" . $theInfo . "</p>\n");
  } // end of ShowInfoH

function ShowInfo ($theInfo)
  {
  ShowInfoH (nl2br_http (htmlspecialchars ($theInfo, ENT_SUBSTITUTE | ENT_QUOTES | ENT_HTML5)));
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
    Problem ("Parameter '$name' is too long");
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
    echo ("<li>" . "Function: "     . htmlspecialchars ($item ['function'], ENT_SUBSTITUTE | ENT_QUOTES | ENT_HTML5));
    echo ("<li>" . "Called from: "  . htmlspecialchars ($item ['file'], ENT_SUBSTITUTE | ENT_QUOTES | ENT_HTML5));
    echo ("<li>" . "Line: "         . htmlspecialchars ($item ['line'], ENT_SUBSTITUTE | ENT_QUOTES | ENT_HTML5));
    echo "</ul><p>\n";
    }
  echo "</ol>\n";
  echo "<hr>\n";
  }   // end of showBacktrace

function showSearchForm ($results)
  {
  global $filter, $action;

  $PHP_SELF = $_SERVER['PHP_SELF'];

  echo "<form METHOD=\"post\" ACTION=$PHP_SELF>\n";
  echo "Filter: ";
  echo "<input type=text Name='filter' size=40 Value='$filter' autofocus>\n";
  echo "<input Type=submit Name=Submit Value='Filter'>\n";
  echo "<details><summary>Regular expression tips</summary>\n";
  echo "<ul>\n";
  echo "<li><i>A number on its own</i>: the item database ID (key)\n";
  echo "<li>At beginning: ^\n";
  echo "<li>At end: $\n";
  echo "<li>Word boundaries: [[:<:]]word[[:>:]]\n";
  echo "<li>Choice: this|that\n";
  echo "<li>Numbers: [0-9]+\n";
  echo "<li>Zero or one: x?\n";
  echo "<li>One or more: x+\n";
  echo "<li>Zero or more: x*\n";
  echo "<li>Any character: .\n";
  echo "<li>Groups: (this is a group)\n";
  echo "</ul>\n";
  echo "</details>\n";
  echo "</td>\n";
  echo "<input Type=hidden Name=action Value=$action>\n";

  echo "</p>\n";
  echo "</form>\n";

  if (count ($results) == 0)
    {
    echo "No matches.";
    return false;
    } // end of nothing

  return true;
  } // end of showSearchForm

// show how many rows matched a query
function showCount ($results)
  {
  $s = 's';
  if (count ($results) == 1)
    $s = '';
  echo ("<p>Showing " . count ($results) . " row$s.");
  if (count ($results) >= QUERY_LIMIT)
    echo ("<p>Warning: Only " . QUERY_LIMIT . " rows will be displayed - there may be more.");
  } // end of showCount

function addSign ($value)
  {
  if ($value > 0)
    return '+' . $value;
  return $value;
  } // end of addSign

function tdx ($s, $c='tdl')
  {
  echo "<td class='$c'>";
  echo (htmlspecialchars ($s, ENT_SUBSTITUTE | ENT_QUOTES | ENT_HTML5));
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
  echo (htmlspecialchars ($s, ENT_SUBSTITUTE | ENT_QUOTES | ENT_HTML5));
  echo "</th>\n";
  } // end of th


function headings ($what)
{
  echo "<tr>\n";
  foreach ($what as $hdg)
    th ($hdg);
  echo "</tr>\n";
} // end of headings



function setUpSearch ($keyname, $fieldsToSearch)
  {
  global $filter, $where, $params;

  $where = 'WHERE TRUE ';
  $params = array ();
  $filter = trim ($filter);

  if ($filter)
    {
    // check filter regexp is OK
    $ok = @preg_match ("`$filter`", "whatever", $matches);
    if ($ok === false)
      {
      echo "<h2>Filter error</h2>\n";
      $warnings = error_get_last();
      $warning = $warnings ['message'];
      ShowWarning ("Error evaluating regular expression: $filter\n\n$warning");
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

  } // end of setUpSearch

function lookupThing ($array, $id, $action)
  {
  $link = "<a href='?action=$action&id=$id'>$id</a>";
  if (!$id)
    return ('-');
  elseif (! isset ($array [$id]))
    return ("$id (not found)");
  else return ("$link: " . $array  [$id] );
  } // end of lookupThing


function listThing ($what, $array, $id, $action)
  {
  echo "<li>";
  if ($what)
    echo "$what: ";
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

function showItemCount ($n)
  {
  if ($n == 0 || $n == 1)
    return '';

  return " x$n";
  } // end of showItemCount

function getFaction ($which)
  {
  global $factions;

  if (isset ($factions [$which]))
    return $which . ': ' . $factions [$which];
  else
    return $which . ': (not known)';

  } // end of getFaction

function getItemClass ($which)
{
  if ($which >= 0)
    return "$which: " . ITEM_CLASS [$which];
  else
    return $which;
} // end of getItemClass

function getItemSubClass ($which)
{
  global $lastItemClass;

  if ($which >= 0 && $lastItemClass >= 0)
    return "$which: " . ITEM_SUBCLASSES [$lastItemClass] [$which];
  else
    return $which;
} // end of getItemSubClass

function expandRaceMask ($mask)
{
  $s = array ();
  for ($i = 0; $i < count (RACES); $i++)
    if ($mask & (1 << $i))
      $s [] = RACES [$i + 1];

  return implode (", ", $s);
} // end of expandRaceMask

function expandClassMask ($mask)
{
  $s = array ();
  for ($i = 0; $i < count (CLASSES); $i++)
    if ($mask & (1 << $i))
      $s [] = CLASSES [$i + 1];

  return implode (", ", $s);
} // end of expandClassMask

function inhabitTypeMask ($mask)
{
  $s = array ();
  for ($i = 0; $i < count (INHABIT_TYPE); $i++)
    if ($mask & (1 << $i))
      $s [] = INHABIT_TYPE [$i + 1];

  return implode (", ", $s);
} // end of inhabitTypeMask

function expandItemFlagsMask ($mask)
{
  if ($mask == 0)
    return 'None';

  $s = array ();
  for ($i = 0; $i < count (ITEM_FLAGS); $i++)
    if ($mask & (1 << $i))
      $s [] = isset (ITEM_FLAGS [1 << $i]) ? ITEM_FLAGS [1 << $i] : '(unknown)';

  return $mask . ': ' . implode (", ", $s);
} // end of expandItemFlagsMask

function expandMechanicImmuneMask ($mask)
{
  $s = array ();
  for ($i = 0; $i < count (MECHANIC_IMMUNE); $i++)
    if ($mask & (1 << $i))
      $s [] = MECHANIC_IMMUNE [1 << $i];

  return implode (", ", $s);
} // end of expandMechanicImmuneMask

function expandFlagsExtraMask ($mask)
{
  $s = array ();
  for ($i = 0; $i < count (FLAGS_EXTRA); $i++)
    if ($mask & (1 << $i))
      $s [] = FLAGS_EXTRA [1 << $i];

  return implode (", ", $s);
} // end of expandFlagsExtraMask

function expandItemSubclassMask ($itemClass, $mask)
{
  if ($itemClass < 0)
    return $mask;

  $s = array ();
  for ($i = 0; $i < count (ITEM_SUBCLASSES [$itemClass]); $i++)
    if ($mask & (1 << $i))
      $s [] = ITEM_SUBCLASSES [$itemClass] [$i];

  return $mask . ': ' . implode (", ", $s);
} // end of expandItemSubclassMask

function expandNpcFlagsMask ($mask)
{
  $s = array ();
  for ($i = 0; $i < count (NPC_FLAG); $i++)
    if ($mask & (1 << $i))
      $s [] = isset (NPC_FLAG [1 << $i]) ? NPC_FLAG [1 << $i] : '(unknown)';

  return $mask . ': ' . implode (", ", $s);
} // end of expandNpcFlagsMask

function expandSpellTargetTypeMask ($mask)
{
  if ($mask == 0)
    return 'None';

  $s = array ();
  for ($i = 0; $i < count (SPELL_TARGET_TYPE); $i++)
    if ($mask & (1 << $i))
      $s [] = isset (SPELL_TARGET_TYPE [1 << $i]) ? SPELL_TARGET_TYPE [1 << $i] : '(unknown)';

  return $mask . ': ' . implode (", ", $s);
} // end of expandSpellTargetTypeMask

function expandSpellAttributesMask ($mask)
{
  if ($mask == 0)
    return 'None';

  $s = array ();
  for ($i = 0; $i < count (SPELL_ATTRIBUTES); $i++)
    if ($mask & (1 << $i))
      $s [] = isset (SPELL_ATTRIBUTES [1 << $i]) ? SPELL_ATTRIBUTES [1 << $i] : '(unknown)';

  return $mask . ': ' . implode (", ", $s);
} // end of expandSpellAttributesMask

function expandSpellAttributesExMask ($mask)
{
  if ($mask == 0)
    return 'None';

  $s = array ();
  for ($i = 0; $i < count (SPELL_ATTRIBUTES_EX); $i++)
    if ($mask & (1 << $i))
      $s [] = isset (SPELL_ATTRIBUTES_EX [1 << $i]) ? SPELL_ATTRIBUTES_EX [1 << $i] : '(unknown)';

  return $mask . ': ' . implode (", ", $s);
} // end of expandSpellAttributesExMask


?>
