<?php

/*
  Author: X'Genesis Qhulut <XGenesis-Qhulut@protonmail.com>
  Date:   August 2022

  See LICENSE for license details.
*/

// FIELD EXPANSION

// menu at top of page

define ('MENU', array (
  'Creatures'     => array ('action' => 'creatures',       'icon' => 'fa-user'),
  'Game Objects'  => array ('action' => 'game_objects',    'icon' => 'fa-cube'),
  'Items'         => array ('action' => 'items',           'icon' => 'fa-gavel'),
  'Maps'          => array ('action' => 'maps',            'icon' => 'fa-map'),
  'Pages'         => array ('action' => 'books',           'icon' => 'fa-file'),
  'Quests'        => array ('action' => 'quests',          'icon' => 'fa-list'),
  'Skills'        => array ('action' => 'skills',          'icon' => 'fa-graduation-cap'),
  'Spells'        => array ('action' => 'spells',          'icon' => 'fa-book'),
  'Tables'        => array ('action' => 'tables',          'icon' => 'fa-table'),
  'Teleports'     => array ('action' => 'ports',           'icon' => 'fa-globe'),
  'Zones'         => array ('action' => 'zones',           'icon' => 'fa-map-pin'),

// more here
));

// Extra stuff for various actions
// search: what fields to search for a string search (array)
// where: extra "where" stuff, like: 'AND entry <= ' . MAX_CREATURE (string)
// table: table to search to find this thing: string
// key: primary key of the table to be searched: string
// requires: a PHP module to "require" in the main code, which handles the action

define ('HANDLER_EXTRA', array (
  'spells' => array (
                      'search'  => array ('Name_enUS', 'Description_enUS'),
                      'table'   => SPELL,
                      'key'     => 'ID',
                      'requires' => 'wow_alpha_spells.php',
          ),

  'items' => array (
                      'search'  => array ('name', 'description'),
                      'where'   => 'AND ignored = 0',
                      'table'   => ITEM_TEMPLATE,
                      'key'     => 'entry',
                      'requires' => 'wow_alpha_items.php',
          ),

  'pages' => array (
                      'search'  => array ('text'),
                      'table'   => PAGE_TEXT,
                      'key'     => 'entry',
                      'requires' => 'wow_alpha_books.php',
          ),

  'creatures' => array (
                      'search'  => array ('name', 'subname'),
                      'where'   => 'AND entry <= ' . MAX_CREATURE,
                      'table'   => CREATURE_TEMPLATE,
                      'key'     => 'entry',
                      'requires' => 'wow_alpha_creatures.php',
          ),

  'quests' => array (
                      'search'  => array ('Title', 'Details', 'Objectives', 'OfferRewardText',
                                          'RequestItemsText', 'EndText', 'ObjectiveText1', 'ObjectiveText2',
                                          'ObjectiveText3', 'ObjectiveText4'),
                      'where'   => 'AND ignored = 0',
                      'table'   => QUEST_TEMPLATE,
                      'key'     => 'entry',
                      'requires' => 'wow_alpha_quests.php',
        ),

  'game_objects' => array (
                      'search'  => array ('name',),
                      'table'   => GAMEOBJECT_TEMPLATE,
                      'key'     => 'entry',
                      'requires' => 'wow_alpha_game_objects.php',
        ),

  'maps' => array (
                      'search'  => array ('directory'),
                      'table'   => MAP,
                      'key'     => 'id',
                      'requires' => 'wow_alpha_maps.php',
        ),

  'zones' => array (
                    'search'  => array ('AreaName'),
                    'table'   => WORLDMAPAREA,
                    'key'     => 'ID',
                    'requires' => 'wow_alpha_zones.php',
        ),

  'ports' => array (
                    'search'  => array ('name'),
                    'table'   => WORLDPORTS,
                    'key'     => 'entry',
                    'requires' => 'wow_alpha_ports.php',
        ),

  'skills' => array (
                    'search'  => array ('DisplayName_enUS',),
                    'table'   => SKILLLINE,
                    'key'     => 'ID',
                    'requires' => 'wow_alpha_skills.php',
        ),

  'tables' => array (
                    'requires' => 'wow_alpha_tables.php',
        ),

  'spell_visuals' => array (
                    'search'  => array ('Name'),
                    'table'   => SPELLVISUAL,
                    'key'     => 'ID',
                    'requires' => 'wow_alpha_spell_visual.php',
        ),

  'spell_visual_anim_names' => array (
                    'search'  => array ('Name'),
                    'table'   => SPELLVISUALANIMNAME,
                    'key'     => 'ID',
                    'requires' => 'wow_alpha_spell_visual.php',
        ),

  'spell_visual_effect_names' => array (
                    'search'  => array ('FileName'),
                    'table'   => SPELLVISUALEFFECTNAME,
                    'key'     => 'ID',
                    'requires' => 'wow_alpha_spell_visual.php',
        ),

  'proximity' => array (
                    'requires' => 'wow_alpha_proximity.php',
        ),

  'validation' => array (
                    'requires' => 'wow_alpha_validity.php',
        ),

  'area_triggers' => array (
                    'search'  => array (),
                    'table'   => AREATRIGGER,
                    'key'     => 'ID',
                    'requires' => 'wow_alpha_area_triggers.php',
        ),


   ));  // end of HANDLER_EXTRA

// Names of handler functions for actions
//
// func: the function to be called by the action  (string - being a function name)
// extra: lookup in table HANDLER_EXTRA above for shared stuff (common to a listing or an
//        individual item)

define ('HANDLERS', array (

  // main menu items

  // Spells
  'spells'        => array ('func' =>  'showSpells',        'extra' => 'spells'),
  'show_spell'    => array ('func' =>  'showOneSpell',      'extra' => 'spells'),

  // Items
  'items'         => array ('func' =>  'showItems',         'extra' => 'items'),
  'show_item'     => array ('func' =>  'showOneItem',       'extra' => 'items', 'og' => 'og_item'),
  'read_text'     => array ('func' =>  'showText',          'extra' => 'pages'),

  // Creatures (NPCs)
  'creatures'     => array ('func' => 'showCreatures',      'extra' => 'creatures'),
  'show_creature' => array ('func' =>  'showOneCreature',   'extra' => 'creatures', 'og' => 'og_creature'),

  // Quests
  'quests'        => array ('func' =>  'showQuests',        'extra' => 'quests'),
  'show_quest'    => array ('func' =>  'showOneQuest',      'extra' => 'quests'),

  // Game Objects (GOs)
  'game_objects'  => array ('func' =>  'showGameObjects',   'extra' => 'game_objects'),
  'show_go'       => array ('func' =>  'showOneGameObject', 'extra' => 'game_objects'),

  // Maps
  'maps'          => array ('func' =>  'showMaps',      'extra' => 'maps'),
  'show_map'      => array ('func' =>  'showOneMap',    'extra' => 'maps'),

  // Zones
  'zones'         => array ('func' =>  'showZones',     'extra' => 'zones'),
  'show_zone'     => array ('func' =>  'showOneZone',   'extra' => 'zones'),

  // Ports
  'ports'         => array ('func' =>  'showPorts',     'extra' => 'ports'),
  'show_port'     => array ('func' =>  'showOnePort',   'extra' => 'ports'),

  // Skills
  'skills'        => array ('func' =>  'showSkills',    'extra' => 'skills'),
  'show_skill'    => array ('func' =>  'showOneSkill',  'extra' => 'skills'),

  // Tables
  'tables'        => array ('func' =>  'showTables',    'extra' => 'tables'),
  'show_table'    => array ('func' =>  'showOneTable',  'extra' => 'tables'),

  // Books (Pages)
  'books'         => array ('func' =>  'showBooks',     'extra' => 'pages'),
  'show_book'     => array ('func' =>  'showOneBook',   'extra' => 'pages'),

  // other tables

  'spell_visuals'             => array ('func' =>  'showSpellVisuals',              'extra' => 'spell_visuals'),
  'show_spell_visual'         => array ('func' =>  'showOneSpellVisual',            'extra' => 'spell_visuals'),

  'spell_visual_anim_names'   => array ('func' =>  'showSpellVisualAnimNames',      'extra' => 'spell_visual_anim_names'),
  'show_spell_visual_anim'    => array ('func' =>  'showOneSpellVisualAnimName',    'extra' => 'spell_visual_anim_names'),

  'spell_visual_effect_names' => array ('func' =>  'showSpellVisualEffectNames',    'extra' => 'spell_visual_effect_names'),
  'show_spell_visual_effect'  => array ('func' =>  'showOneSpellVisualEffectName',  'extra' => 'spell_visual_effect_names'),

  // area triggers

  'area_triggers'             => array ('func' =>  'showAreaTriggers',              'extra' => 'area_triggers'),
  'show_area_trigger'         => array ('func' =>  'showOneAreaTrigger',            'extra' => 'area_triggers'),


  // Utilites

  'proximity'     => array ('func' =>  'showProximity', 'extra' => 'proximity'),

  // Validation

  'unknown_faction'     => array ('func' =>  'showUnknownFaction',          'extra' => 'validation'),
  'quest_missing_item'  => array ('func' =>  'showMissingQuestItems',       'extra' => 'validation'),
  'quest_missing_spell' => array ('func' =>  'showMissingQuestSpells',      'extra' => 'validation'),
  'quest_missing_quest' => array ('func' =>  'showMissingQuestQuests',      'extra' => 'validation'),
  'quest_bad_count'     => array ('func' =>  'showBadQuestCounts',          'extra' => 'validation'),
  'npc_missing_quest'   => array ('func' =>  'showMissingCreatureQuests',   'extra' => 'validation'),
  'npc_no_model'        => array ('func' =>  'showMissingCreatureModels',   'extra' => 'validation'),
  'npc_not_spawned'     => array ('func' =>  'showCreaturesNotSpawned',     'extra' => 'validation'),
  'go_missing_quest'    => array ('func' =>  'showMissingGameobjectQuests', 'extra' => 'validation'),
  'go_not_spawned'      => array ('func' =>  'showGameObjectsNotSpawned',   'extra' => 'validation'),
  'no_item_text'        => array ('func' =>  'showNoItemText',              'extra' => 'validation'),
  'spell_missing_spell' => array ('func' =>  'showMissingSpellSpells',      'extra' => 'validation'),
  'spell_missing_item'  => array ('func' =>  'showMissingSpellItems',       'extra' => 'validation'),
  'item_missing_spell'  => array ('func' =>  'showMissingItemSpells',       'extra' => 'validation'),
  'item_unused'         => array ('func' =>  'showUnusedItems',             'extra' => 'validation'),
  'item_no_model'       => array ('func' =>  'showMissingItemModels',       'extra' => 'validation'),

)); // end of HANDLERS


// ACTIONS THAT REQUIRE wow_alpha_validity.php

define ('VALIDATION_ACTIONS', array (
  'unknown_faction'     =>  true,
  'quest_missing_item'  =>  true,
  'quest_missing_spell' =>  true,
  'quest_missing_quest' =>  true,
  'npc_missing_quest'   =>  true,
  'npc_not_spawned'     =>  true,
  'npc_no_model'        =>  true,
  'go_missing_quest'    =>  true,
  'go_not_spawned'      =>  true,
  'no_item_text'        =>  true,
  'spell_missing_spell' =>  true,
  'spell_missing_item'  =>  true,
  'item_missing_spell'  =>  true,
  'item_unused'         =>  true,
  ));


?>
