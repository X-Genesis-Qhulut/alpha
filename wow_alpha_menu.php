<?php

/*
  Author: X'Genesis Qhulut <XGenesis-Qhulut@protonmail.com>
  Date:   August 2022

  See LICENSE for license details.
*/

// MAIN MENU


// The applied updates dates are in the format: DDMMYYYYs where s is a sequence number
// To get the latest we need to get them into YYYYMMDDs sequence, then find the highest
function checkAppliedUpdates ($table)
  {

  $latest = 0;

  $result = dbQuery ("SELECT * FROM $table");
  while ($row = dbFetch ($result))
    {
    $s = strval ($row ['id']);
    $year = substr ($s, 4, 4);
    $month = substr ($s, 2, 2);
    $day = substr ($s, 0, 2);
    $seq = substr ($s, 8, 1);
    // ignore bad dates, <sigh>
    if (intval ($day) < 1 || intval ($day) > 31)
      continue;
    if (intval ($month) < 1 || intval ($month) > 12)
      continue;
    $revDate = $year . $month . $day . $seq;
    if ($revDate > $latest)
      $latest = $revDate;
    }
  dbFree ($result);

  return $latest;
  } // end of checkAppliedUpdates

function convertDate ($date)
{
  echo substr ($date, 6, 2) . ' ' .
       MONTHS [intval (substr ($date, 4, 2)) - 1] . ' ' .
       substr ($date, 0, 4);
  if (strlen ($date) > 8)
        echo " - sequence: " . substr ($date, 8, 1);
} // end of convertDate

function showBigMenu ()
  {


/*
<div id='noscript_warning_id'>
<p><i>(Enable Javascript to see nice icons on the menus.)</i></p>
</div>

<script>
document.getElementById('noscript_warning_id').style.display = 'none';
</script>
*/

echo "
<!-- MENU NAV -->
<header>
  <div class='menu-bar-main-container'>
    <div class='menu-bar-header-container'>

<a href='".EXECUTIONDIR."'>
  <img
    class='menu-bar-logo'
    src='logo-small.png'
    alt='Alpha Core'
  />
</a>
<div class='menu-bar-title'>
  <p>Database</p>
  <p>Alpha 0.5.3</p>
</div>
</div>
<nav class='menu-bar-nav-container'>
";

  comment ("MENU ITEMS");
  foreach (MENU as $desc => $menuInfo)
    {
    $newAction = $menuInfo ['action'];
    $icon      = $menuInfo ['icon'];
    echo "<a href='?action=$newAction' class='menu-bar-button'>";
    echo "<i class='fas $icon'></i>";
    echo "<span>$desc</span>";
    echo "</a>\n";
    }
?>
    </nav>
    <div class="menu-bar-social-container">
      <a href="https://github.com/The-Alpha-Project/alpha-core"
              title="Alpha Core project on GitHub"><i class="menu-bar-social fab fa-github"></i></a>
      <a href="https://discord.gg/RzBMAKU"
              title="Invite to Alpha Core project on Discord"><i class="menu-bar-social fab fa-discord"></i></a>
    </div>
  </div>
  <nav class="header__nav"></nav>
</header>
      <!-- END MENU NAV -->
<?php

  /*

  // find last database updates

  echo "<h3>Database updates</h3>\n";
  $latest_dbc   = checkAppliedUpdates (APPLIED_UPDATES_DBC);
  echo "<p>Latest DBC table update: ";
  convertDate ($latest_dbc) . "\n";

  $latest_world = checkAppliedUpdates (APPLIED_UPDATES_WORLD);
  echo "<br>Latest World table update: ";
  convertDate ($latest_world) . "\n";

  echo "<p>Databases from <a href='https://github.com/The-Alpha-Project/alpha-core/tree/master/etc/databases'>
    GitHub: alpha-core/etc/databases/</a>\n";

  echo "<hr>\n";
  echo "<h3>Utilities</h3>
  <ul>
    <li><a href='?action=proximity'>Spawn point proximity search</a>
  </ul>

  <hr>

  <h3>Other tables</h3>
  <ul>
  <li><a href='?action=spell_visuals'>Spell visuals</a>
  <li><a href='?action=spell_visual_anim_names'>Spell visual animation names</a>
  <li><a href='?action=spell_visual_effect_names'>Spell visual effect names</a>
  </ul>

  <hr>

  <h3>Database validation</h3>
  <h4>NPCs</h4>
  <ul>
    <li><a href='?action=unknown_faction'>NPCs with unknown faction</a>
    <li><a href='?action=npc_missing_quest'>NPCs which start/finish a missing quest</a>
  </ul>

  <h4>Game objects</h4>
  <ul>
    <li><a href='?action=go_missing_quest'>Game objects which start/finish a missing quest</a>
    <li><a href='?action=go_not_spawned'>Game objects which are not spawned</a>
  </ul>


  <h4>Quests</h4>
  <ul>
  <li><a href='?action=quest_missing_item' >Quests with missing items</a>
  <li><a href='?action=quest_missing_spell'>Quests with missing spells</a>
  <li><a href='?action=quest_missing_quest'>Quests with missing quest chains</a>
  </ul>

  <h4>Items</h4>
  <ul>
  <li><a href='?action=no_item_text'>Items with no text</a>
  </ul>

  ";

*/

  } // end of showBigMenu


function showMenu ()
  {
  echo "<div class='links_at_top'>\n";
  foreach (MENU as $desc => $newAction)
    {
    echo "<a href='?action=$newAction'>$desc</a>\n";
    }
  echo "</div>\n";

  } // end of showMenu

function showCredits ()
{
global $PHP_SELF;

/*
echo "<div class='credits'><a href='$PHP_SELF'><img style='width:50px; float:left; margin-left: 0px; margin-right:5px;'
      src='avatar.jpg' alt='Avatar' title='Click for main menu'/></a>
      Designed and coded in August 2022 by X’Genesis Qhulut.
      <br>This browser at GitHub:
      <a href='https://github.com/X-Genesis-Qhulut/alpha' style='white-space: nowrap;'>X-Genesis-Qhulut / alpha</a><br>
      <b>WoW Alpha Project</b> at GitHub: <a href='https://github.com/The-Alpha-Project/alpha-core'  style='white-space: nowrap;'>
      The-Alpha-Project / alpha-core</a>
      <br><b>Discord channel</b>: <a href='https://discord.gg/RzBMAKU'>Alpha Project</a>.
      Thanks to Grender!\n
      <p>Thanks to the original developers of WoW and also John Staats for writing
      <br><i>The WoW Diary: A Journal of Computer Game Development.</i>\n
      <br>Maps courtesy of Entropy and <a href='https://wow.tools/maps/Kalimdor/'>WoW.tools</a>.
      Creature screenshots by Daribon.\n
      <details><summary>Image assets shown are Copyright ©2022 Blizzard Entertainment, Inc.</summary>\n
      <p>Images used in accordance with permission given <a href='https://www.blizzard.com/en-us/legal/c1ae32ac-7ff9-4ac3-a03b-fc04b8697010/blizzard-legal-faq'>here</a>
      “for home, noncommercial and personal use only”.
      <p><b>Blizzard Entertainment®</b>
      <br>Blizzard Entertainment is a trademark or registered trademark of Blizzard Entertainment, Inc. in the U.S. and/or other countries. All rights reserved.</details>\n
      </div>\n";

*/

} // end of showCredits
?>
