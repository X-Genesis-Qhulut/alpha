<?php

/*
  Author: X'Genesis Qhulut <XGenesis-Qhulut@protonmail.com>
  Date:   August 2022

  See LICENSE for license details.
*/

// AREA TRIGGERS

function areaTriggerTopLeft ($info)
{
  global $id;

  $row = $info ['row'];
  $extras = $info ['extras'];
  $limit = $info ['limit'];

  comment ('SHORT LISTING OF FIELDS');
  showOneThing (AREATRIGGER, 'ID',
              $id, "", "name", $extras, $limit);

} // end of areaTriggerTopLeft

function areatriggerTopMiddle ($info)
  {
 global $id;

  $row = $info ['row'];

  comment ('AREA TRIGGER ON MAP');

  $results = array ($row);

  if ($row ['ContinentID'] == 0)
    listSpawnPoints ($results, 'Area trigger - Eastern Kingdoms', AREATRIGGER,
                  'ID', 'X', 'Y', 'Z', 'ContinentID');

  comment ('KALIMDOR');

  if ($row ['ContinentID'] == 1)
    listSpawnPoints ($results, 'Area trigger - Kalimdor', AREATRIGGER,
                  'ID', 'X', 'Y', 'Z', 'ContinentID');

  comment ('END AREA TRIGGER ON MAP');

  } // end of areatriggerTopMiddle

function areatriggerTopRight ($info)
  {
 global $id;

  $row = $info ['row'];

  comment ('AREA TRIGGER ON MAP');

  $results = array ($row);


  comment ('KALIMDOR');

  if ($row ['ContinentID'] == 1)
    showSpawnPoints ($results, 'Area trigger - Kalimdor', AREATRIGGER,
                  'ID', 'X', 'Y', 'Z', 'ContinentID');

  comment ('EASTERN KINGDONS');

  if ($row ['ContinentID'] == 0)
    showSpawnPoints ($results, 'Area trigger - Eastern Kingdoms', AREATRIGGER,
                  'ID', 'X', 'Y', 'Z', 'ContinentID');

  comment ('END AREA TRIGGER ON MAP');


  } // end of areatriggerTopRight

function showAreaTriggerQuest ()
{
  global $id, $quests;

  comment ('ITEM STARTS A QUEST');

  $results = dbQueryParam ("SELECT quest FROM ".AREATRIGGER_QUEST_RELATION." WHERE id = ?", array ('i', &$id));

 listItems ('Related quest', QUEST_TEMPLATE,
          count ($results), $results,
  function ($row) use ($quests)
    {
    listThing ($quests, $row ['quest'], 'show_quest');
    } // end listing function
    );

} // end of showAreaTriggerQuest

function showAreaTriggerTeleport ()
{
  global $id, $quests;
  global $listedItemsCount;

  comment ('ITEM STARTS A QUEST');

  $row = dbQueryOneParam ("SELECT * FROM ".AREATRIGGER_TELEPORT." WHERE id = ?", array ('i', &$id));

  if ($row)
    {
    showOneThing (AREATRIGGER_TELEPORT, 'id', $id, "Teleport details", "name", array ());
    $listedItemsCount++;
    }

} // end of showAreaTriggerTeleport

function areaTriggerDetails ($info)
{
  global $id;

  $row = $info ['row'];

  topSection    ($info, function ($info) use ($id)
      {
      topLeft   ($info, 'areatriggerTopLeft');
      topMiddle ($info, 'areatriggerTopMiddle');
      topRight  ($info , 'areatriggerTopRight');
      });

  middleSection ($info, function ($info) use ($id)
      {
      global $listedItemsCount;

      $listedItemsCount = 0;

      showAreaTriggerQuest ();
      showAreaTriggerTeleport ();

      if ($listedItemsCount == 0)
        middleDetails ($info, function ($info) use ($listedItemsCount)
          {
          showNoSpawnPoints ('Unused area trigger', 'This area trigger appears to be unused.');
          });

      });

} // end of areaTriggerDetails

function showOneAreaTrigger ()
  {
  global $id;

  if (($id === false && !repositionSearch()) || !checkID ())
    return;

 // we need the game object info in this function
  $row = dbQueryOneParam ("SELECT * FROM ".AREATRIGGER." WHERE ID = ?", array ('i', &$id));

  if (!$row)
    {
    ShowWarning ("Area trigger $id is not on the database");
    return;
    } // end of not finding it

  // this is the short summary fields
  $limit = array (
    'ID',
    'ContinentID',
    'X',
    'Y',
    'Z',
    'Radius',
  );

  // stuff to be displayed differently
  $extras = array ();

  setTitle ("Area trigger $id");

  // we pass this stuff around to the helper functions
  $info = array ('row' => $row, 'extras' => $extras, 'limit' => $limit);

  // ready to go! show the page info and work our way down into the sub-functions
  pageContent ($info, 'Gameobject', "Area trigger $id", 'area_triggers', 'areaTriggerDetails', AREATRIGGER);
  } // end of showOneAreaTrigger

function showAreaTriggersHelper ($tableName, $headingName)
{
  $results = dbQueryParam ("SELECT * FROM " . AREATRIGGER, array ());

  echo "<table class='table-rows'>\n";
  echo "<thead>\n";

  echo "<tr>\n";
    th ('ID');
    th ('ContinentID');
    th ('X');
    th ('Y');
    th ('Z');
    th ('Radius');
  echo "</tr>\n";
  echo "</thead>\n";
  echo "<tbody>\n";

  $td  = function ($s) use (&$row) { tdx ($row  [$s]); };

  foreach ($results as $row)
    {
    $id = $row ['ID'];
    echo "<tr>\n";
    tdh ("<a href='?action=show_area_trigger&id=$id'>$id</a>");
    $td ('ContinentID');
    $td ('X');
    $td ('Y');
    $td ('Z');
    $td ('Radius');
    echo "</tr>\n";
    }
  echo "</table>\n";

} // end of showAreaTriggersHelper

function showAllAreaTriggers ($info)
{
  setTitle ("Area triggers");

  bottomSectionMany ($info, function ($info)
      {
      showAreaTriggersHelper (DBC_DBNAME, LIVE_DBC_DBNAME);
      });
} // end of showAllAreaTriggers

function showAreaTriggers ()
  {
  pageContent (false, 'Area Triggers', 'Area triggers', 'whatever', 'showAllAreaTriggers', '');
  } // end of showAreaTriggers

?>
