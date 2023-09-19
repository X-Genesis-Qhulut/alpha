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
  return substr ($date, 6, 2) . ' ' .
       MONTHS [intval (substr ($date, 4, 2)) - 1] . ' ' .
       substr ($date, 0, 4) . ((strlen ($date) > 8) ? " - sequence: " . substr ($date, 8, 1) : '');
} // end of convertDate

function getLatestCommit ()
{
    global $documentRoot;

    $executionDir = EXECUTIONDIR;
    if (!$executionDir)
      $executionDir = '/';

    $cmd = "git --git-dir $documentRoot$executionDir/.git log --oneline -7 ";

    $descriptorspec = array(
       0 => array('pipe', 'r'),  // stdin is a pipe that the child will read from
       1 => array('pipe', 'w'),  // stdout is a pipe that the child will write to
       2 => array('pipe', 'w')   // stderr is a pipe that the child will write to
    );

    $process = proc_open($cmd, $descriptorspec, $pipes);

    $value = '';

    if (is_resource($process))
      {
      fwrite($pipes[0], $value);
      fclose($pipes[0]);

      $gitInfo = stream_get_contents($pipes[1]);
      fclose($pipes[1]);
      $error = stream_get_contents($pipes[2]);
      fclose($pipes[2]);
      if ($error)
        comment ("Error: $error");
      $return_value = proc_close($process);
      return $gitInfo;
      }  // end of process opened OK
    else
      return false;

} // end of getLatestCommit


function showBigMenu ()
  {

$executionDir = EXECUTIONDIR;
if (!$executionDir)
  $executionDir = '/';

echo "
<!-- MENU NAV -->
<header>
  <div class='menu-bar-main-container'>
    <div class='menu-bar-header-container'>

<a href='$executionDir'>
  <img class='menu-bar-logo' src='logo-small.png' alt='Alpha Core' title='Main menu'>
</a>
<div class='menu-bar-title'>
  <p>Database</p>
  <p>Alpha 0.5.3</p>
";

endDiv ('menu-bar-title');
endDiv ('menu-bar-header-container');
echo "
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

  // We add link to display id browser in main menu
  echo "<a href='https://models.thealphaproject.eu/' class='menu-bar-button' target='_blank'>";
  echo "<i class='fas fa-file-image'></i>";
  echo "<span>Models</span>";
  echo "</a>\n";
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

  } // end of showBigMenu

// For showing a card. $details are in HTML.
function oneCard ($title, $icon, $details, $extra_class = '')
{
comment ("CARD: $title");
  echo "
 <article class='card'>
    <div class='medium-title'>
      <h2 class='medium-title__heading'>" . fixHTML ($title) . "</h2>
      <div class='medium-title__bar'></div>
    </div>
    <i class='card__icon $icon'></i>
    <div class='card__content $extra_class'>
      $details
    </div>
  </article>
  ";
comment ("END CARD: $title");
} // end of oneCard

function showMenu ()
  {
  echo "<div class='links_at_top'>\n";
  foreach (MENU as $desc => $newAction)
    {
    echo "<a href='?action=$newAction'>$desc</a>\n";
    }
  echo "</div>\n";

  } // end of showMenu

function mainPage ()
{

  $latest_dbc   = checkAppliedUpdates (APPLIED_UPDATES_DBC);
  $dbcUpdate = convertDate ($latest_dbc);

  $latest_world = checkAppliedUpdates (APPLIED_UPDATES_WORLD);
  $worldUpdate = convertDate ($latest_world);

  echo "
  <!-- PAGE CONTAINER-->
  <section class='main-page-container'>
   <!-- PAGE TITLE -->
    <div class='page-title'>
      <div class='big-title'>
        <h1 class='big-title__heading'>Alpha Core Database Browser</h1>
        <div class='big-title__bar'></div>
        <div class='big-title__subtitle'>
          World Of Warcraft - Version 3368 0.5.3
        </div>
      </div>
      <div>
        <i class='page-title__database fas fa-database'></i>
      </div>
    </div>
    <!-- END PAGE TITLE -->


    <!-- PAGE CONTENT -->
    <div class='creature-details page-content'>
      <div class='cards-container'>
      ";


    echo "
<div id='noscript_warning_id'>";

  // UPDATES
  oneCard ('JavaScript required', 'fas fa-cog', "
      <h3>Please enable JavaScript</h3>
      <hr>
      <p>For full functionality please enable JavaScript for this site.
      ");

echo "
</div>

<script>
document.getElementById('noscript_warning_id').style.display = 'none';
</script>
      ";



  // UPDATES
  oneCard ('Updates', 'fas fa-calendar-days', "
      <h3>Latest DBC table update</h3>
      <ul>
        <li>$dbcUpdate</li>
      </ul>
      <h3>Latest World table update</h3>
      <ul>
        <li>$worldUpdate</li>
      </ul>
      <h3>Source</h3>
      <ul>
        <li><a href='https://github.com/The-Alpha-Project/alpha-core/tree/master/etc/databases'>GitHub: alpha-core/etc/databases/</a></li>
      </ul>
      ");

  // TOOLS
  oneCard ('Utilities', 'fas fa-tools', "
      <h3>Spawn point</h3>
      <ul>
        <li><a href='?action=proximity'>Proximity search</a></li>
      </ul>
    ");

  // OTHER TABLES
  oneCard ('Other tables', 'fas fa-table', "
      <h3>Spell visuals</h3>
      <ul>
        <li><a href='?action=spell_visuals'>Spell visuals</a></li>
        <li><a href='?action=spell_visual_anim_names'>Spell visuals animation names</a></li>
        <li><a href='?action=spell_visual_effect_names'>Spell visuals effect names</a></li>
      </ul>
      <h3>Area triggers</h3>
      <ul>
        <li><a href='?action=area_triggers'>Area triggers</a></li>
      </ul>


    ");

  // VALIDATION
  oneCard ('Validation', 'fas fa-cog', "
      <h3>NPC</h3>
      <ul>
        <li><a href='?action=unknown_faction'>NPCs with unknown faction</a></li>
        <li><a href='?action=npc_no_model'>NPCs with missing model</a></li>
        <li><a href='?action=npc_not_spawned'>NPCs which are not spawned</a></li>
        <li><a href='?action=npc_missing_quest'>NPCs which start/finish a missing quest</a></li>
      </ul>

      <h3>Game objects</h3>
      <ul>
        <li><a href='?action=go_missing_quest'>Game objects which start/finish a missing quest</a></li>
        <li><a href='?action=go_not_spawned'>Game objects which are not spawned</a></li>
      </ul>

      <h3>Quests</h3>
      <ul>
        <li><a href='?action=quest_missing_item'>Quests with missing items</a></li>
        <li><a href='?action=quest_missing_spell'>Quests with missing spells</a></li>
        <li><a href='?action=quest_missing_quest'>Quests with missing quest chains</a></li>
        <li><a href='?action=quest_bad_count'>Quests with bad item/game object counts</a></li>
      </ul>

      <h3>Items</h3>
      <ul>
        <li><a href='?action=no_item_text'>Items with no text</a></li>
        <li><a href='?action=item_missing_spell'>Items with missing spells</a></li>
        <li><a href='?action=item_unused'>Items which are not used</a></li>
        <li><a href='?action=item_no_model'>Items with missing model</a></li>
      </ul>

      <h3>Spells</h3>
      <ul>
        <li><a href='?action=spell_missing_spell'>Spells with missing spells</a></li>
        <li><a href='?action=spell_missing_item'>Spells with missing items</a></li>
      </ul>

  ", 'card--validation__content');

  // ALPHA CORE
  oneCard ('Alpha Core', 'fab fa-github', "
      <img
        class='card__content__cover'
        src='./assets/img/alphacore.png'
        alt=''
      >
      <h3>O.5.3 WoW Emulator</h3>
      <ul>
        <li>
          <a href='https://github.com/The-Alpha-Project/alpha-core'
            >Alpha core main repo</a
          >
        </li>
        <li>
          <a href='https://github.com/The-Alpha-Project/alpha-core/issues'
            >Issues (bugs)</a
          >
        </li>

      </ul>
");

  // ALPHA ARCHIVE
  oneCard ('Alpha Archive', 'fas fa-image', "
      <img
        class='card__content__cover'
        src='./assets/img/archive.webp'
        alt=''
      >
      <h3>Browse old screenshots</h3>
      <ul>
        <li>
          <a href='https://archive.thealphaproject.eu/browse/'
            >Alpha archive website</a
          >
        </li>
      </ul>
  ");

  // DISCORD
  oneCard ('Discord', 'fab fa-discord', "
      <img
        class='card__content__cover'
        src='./assets/img/discord.jpeg'
        alt=''
      >
      <h3>Join Alpha core on discord</h3>
      <ul>
        <li>
          <a href='https://discord.com/invite/RzBMAKU?'
            >Discord Invitation</a
          >
        </li>
      </ul>
");

  // ALLAKHAZAM
  oneCard ('Allakhazam', 'fas fa-paperclip', "
      <img
        class='card__content__cover'
        src='./assets/img/alakazam.jpg'
        alt=''
      >
      <h3>Old WoW Beta Database</h3>
      <ul>
        <li>
          <a
            href='http://web.archive.org/web/20041116024110/http://wow.allakhazam.com/'
            >Allakhazam website</a
          >
        </li>
      </ul>
");

  // BACK END
  oneCard ('Back end', 'fas fa-server', "
      <img
        class='card__content__cover'
        src='./assets/img/avatar.jpg'
        alt=''
      >
      <h3>Created by  X'Genesis Qhulut</h3>

      <ul>
        <li>
          <a href='https://github.com/X-Genesis-Qhulut/alpha'>Back end database repo</a>
        </li>
        <li>
          <a href='https://github.com/X-Genesis-Qhulut/'>Github profile</a>
        </li>
      </ul>
      <p>
      <br>
      <div style='font-size:smaller;'>
        Designed and coded in August 2022 by X’Genesis Qhulut.
      <p>Thanks to Grender from the Alpha Project.
      </p>
      <hr>
      <p>
        Thanks to the original developers of WoW and also John Staats
        for writing <i>The WoW Diary: A Journal of Computer Game
        Development</i>. Maps courtesy of Entropy and WoW.tools. Creature
        screenshots by Daribon.
        Thanks also to Geo for developing the database front-end CSS and HTML code.
      </p>
      </div>
");

  // FRONT END
  oneCard ('Front end', 'fas fa-brush', "
      <img
        class='card__content__cover'
        src='./assets/img/napoleon.jpg'
        alt=''
      >
      <h3>Created by Geo</h3>
      <ul>
        <li>
          <a href='https://github.com/geo-tp/053-Database-Frontend'>Front end database repo</a>
        </li>
        <li>
          <a href='https://github.com/geo-tp'>Github profile</a>
        </li>
      </ul>
");

  // COPYRIGHT
  oneCard ('Copyright', 'fas fa-copyright', "
      <div style='font-size:smaller;'>

      <p>Image assets shown are Copyright ©2022 Blizzard Entertainment, Inc.</p>
      <p>Images used in accordance with permission given <a href='https://www.blizzard.com/en-us/legal/c1ae32ac-7ff9-4ac3-a03b-fc04b8697010/blizzard-legal-faq'>here</a>
      “for home, noncommercial and personal use only”.
      <hr>
      <p><b>Blizzard Entertainment®</b></p>
      <p>Blizzard Entertainment is a trademark or registered trademark of Blizzard Entertainment, Inc. in the U.S. and/or other countries. All rights reserved.</p>
      </div>
");

  $commitInfo = getLatestCommit ();

  if ($commitInfo)
    {
    $commitInfo = fixHTML ($commitInfo);
    $commitInfo = str_replace ("\n", "\n<li>", Trim($commitInfo));

    // BACK-END INFO
    oneCard ('Recent changes', 'fas fa-info', "
      <h3>Latest commits to browser</h3>

        <div style='font-size:smaller;'><ul><li>$commitInfo</ul></div>
      ");
  } // end of having commit info

  // WRAP UP PAGE
echo "

      </div>
    </div>
    <!-- END PAGE CONTENT -->
  </section>
  <!-- END PAGE CONTAINER-->
";

} // end of mainPage

?>
