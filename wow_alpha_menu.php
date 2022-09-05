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
    title='Main menu'
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
            <!-- CARD UPDATE -->
            <article class='card'>
              <i class='card__icon fas fa-calendar-days'></i>
              <div class='medium-title'>
                <h2 class='medium-title__heading'>Updates</h2>
                <div class='medium-title__bar'></div>
              </div>
              <div class='card__content'>
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
              </div>
            </article>
            <!-- END CARD UPDATE -->
  ";

?>

            <!-- CARD UTILITIES -->
            <article class='card'>
              <div class='medium-title'>
                <h2 class='medium-title__heading'>Utilities</h2>
                <div class='medium-title__bar'></div>
              </div>
              <i class='card__icon fas fa-tools'></i>
              <div class='card__content'>
                <h3>Spawn point</h3>
                <ul>
                  <li><a href='?action=proximity'>Proximity search</a></li>
                </ul>
              </div>
            </article>
            <!-- END CARD UTILITIES -->

            <!-- CARD OTHER TABLES -->
            <article class='card'>
              <div class='medium-title'>
                <h2 class='medium-title__heading'>Other tables</h2>
                <div class='medium-title__bar'></div>
              </div>
              <i class='card__icon fas fa-table'></i>
              <div class='card__content'>
                <h3>Spell table</h3>
                <ul>
                  <li><a href='?action=spell_visuals'>Spell visuals</a></li>
                  <li><a href='?action=spell_visual_anim_names'>Spell visuals animation names</a></li>
                  <li><a href='?action=spell_visual_effect_names'>Spell visuals effect names</a></li>
                </ul>
              </div>
            </article>
            <!-- END CARD OTHER TABLES -->

            <!-- CARD VALIDATION -->
            <article class='card card--validation'>
              <div class='medium-title'>
                <h2 class='medium-title__heading'>Validation</h2>
                <div class='medium-title__bar'></div>
              </div>
              <i class='card__icon fas fa-pen'></i>
              <div class='card__content card--validation__content'>
                <h3>NPC</h3>
                <ul>
                  <li><a href='?action=unknown_faction'>NPCs with unknown faction</a></li>
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
                </ul>
                <h3>Items</h3>
                <ul>
                  <li><a href='?action=no_item_text'>Items with no text</a></li>
                </ul>
              </div>
            </article>
            <!-- END CARD VALIDATION -->

            <!-- CARD ALPHA CORE -->
            <article class='card'>
              <div class='medium-title'>
                <h2 class='medium-title__heading'>Alpha Core</h2>
                <div class='medium-title__bar'></div>
              </div>
              <i class='card__icon fab fa-github'></i>
              <div class='card__content'>
                <img
                  class='card__content__cover'
                  src='./assets/img/alphacore.png'
                  alt=''
                />
                <h3>O.5.3 WoW Emulator</h3>
                <ul>
                  <li>
                    <a href='https://github.com/The-Alpha-Project'
                      >Alpha core main repo</a
                    >
                  </li>
                </ul>
              </div>
            </article>
            <!-- END CARD ALPHA CORE -->

            <!-- CARD ARCHIVE -->
            <article class='card'>
              <div class='medium-title'>
                <h2 class='medium-title__heading'>Alpha Archive</h2>
                <div class='medium-title__bar'></div>
              </div>
              <i class='card__icon fas fa-image'></i>
              <div class='card__content'>
                <img
                  class='card__content__cover'
                  src='./assets/img/archive.png'
                  alt=''
                />
                <h3>Browse old screenshots</h3>
                <ul>
                  <li>
                    <a href='https://archive.thealphaproject.eu/browse/'
                      >Alpha archive website</a
                    >
                  </li>
                </ul>
                <p style='font-size:small;'>(Needs Javascript)</p>
              </div>
            </article>
            <!-- END CARD ARCHIVE -->

            <!-- CARD DISCORD -->
            <article class='card'>
              <div class='medium-title'>
                <h2 class='medium-title__heading'>Discord</h2>
                <div class='medium-title__bar'></div>
              </div>
              <i class='card__icon fab fa-discord'></i>
              <div class='card__content'>
                <img
                  class='card__content__cover'
                  src='./assets/img/discord.jpeg'
                  alt=''
                />
                <h3>Join Alpha core on discord</h3>
                <ul>
                  <li>
                    <a href='https://discord.com/invite/RzBMAKU?'
                      >Discord Invitation</a
                    >
                  </li>
                </ul>
              </div>
            </article>
            <!-- END CARD DISCORD -->

            <!-- CARD ALAKAZAM-->
            <article class='card'>
              <div class='medium-title'>
                <h2 class='medium-title__heading'>Allakhazam</h2>
                <div class='medium-title__bar'></div>
              </div>
              <i class='card__icon fas fa-paperclip'></i>
              <div class='card__content'>
                <img
                  class='card__content__cover'
                  src='./assets/img/alakazam.jpg'
                  alt=''
                />
                <h3>Old WoW Beta Database</h3>
                <ul>
                  <li>
                    <a
                      href='http://web.archive.org/web/20041116024110/http://wow.allakhazam.com/'
                      >Allakhazam website</a
                    >
                  </li>
                </ul>
              </div>
            </article>
            <!-- END CARD ALAKAZAM -->

            <!-- CARD BACKEND -->
            <article class='card'>
              <div class='medium-title'>
                <h2 class='medium-title__heading'>Backend</h2>
                <div class='medium-title__bar'></div>
              </div>
              <i class='card__icon fas fa-server'></i>
              <div class='card__content'>
                <img
                  class='card__content__cover'
                  src='./assets/img/avatar.jpg'
                  alt=''
                />
                <h3>Created by  X'Genesis Qhulut</h3>

                <ul>
                  <li>
                    <a href='https://github.com/X-Genesis-Qhulut/alpha'
                      >Github profile</a
                    >
                  </li>
                </ul>
                <div style='font-size:smaller;'>
                <p>
                  Designed and coded in August 2022 by X’Genesis Qhulut.
                </p>
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
              </div>
            </article>
            <!-- END CARD BACKEND -->

            <!-- CARD FRONTEND -->
            <article class='card'>
              <div class='medium-title'>
                <h2 class='medium-title__heading'>Frontend</h2>
                <div class='medium-title__bar'></div>
              </div>
              <i class='card__icon fas fa-brush'></i>
              <div class='card__content'>
                <img
                  class='card__content__cover'
                  src='./assets/img/napoleon.jpg'
                  alt=''
                />
                <h3>Created by Geo</h3>
                <ul>
                  <li>
                    <a href='https://github.com/geo-tp/053-Database-Frontend'
                      >Frontend database repo</a
                    >
                  </li>
                  <li>
                    <a href='https://github.com/geo-tp'>Geo github profile</a>
                  </li>
                </ul>
              </div>
            </article>
            <!-- END CARD FRONTEND -->
            <!-- CARD COPYRIGHT -->
            <article class='card'>
              <div class='medium-title'>
                <h2 class='medium-title__heading'>Copyright</h2>
                <div class='medium-title__bar'></div>
              </div>
              <i class='card__icon fas fa-copyright'></i>
              <div class='card__content' style='font-size:small;'>
                Image assets shown are Copyright ©2022 Blizzard Entertainment, Inc.
                <p>Images used in accordance with permission given <a href='https://www.blizzard.com/en-us/legal/c1ae32ac-7ff9-4ac3-a03b-fc04b8697010/blizzard-legal-faq'>here</a>
                “for home, noncommercial and personal use only”.
                <hr><b>Blizzard Entertainment®</b>
                <p>Blizzard Entertainment is a trademark or registered trademark of Blizzard Entertainment, Inc. in the U.S. and/or other countries. All rights reserved.

              </div>
            </article>
            <!-- END CARD COPYRIGHT -->
          </div>
        </div>
        <!-- END PAGE CONTENT -->
      </section>
      <!-- END PAGE CONTAINER-->
    </main>
    <!-- END MAIN -->
<?php
} // end of mainPage

?>
