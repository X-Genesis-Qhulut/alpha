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
  'Ports'         => array ('action' => 'ports',           'icon' => 'fa-globe'),
  'Quests'        => array ('action' => 'quests',          'icon' => 'fa-list'),
  'Skills'        => array ('action' => 'skills',          'icon' => 'fa-graduation-cap'),
  'Spells'        => array ('action' => 'spells',          'icon' => 'fa-book'),
  'Tables'        => array ('action' => 'tables',          'icon' => 'fa-table'),
  'Zones'         => array ('action' => 'zones',           'icon' => 'fa-map-pin'),

// more here
));

// Names of handler functions for actions
//
// func: the function to be called by the action  (string - being a function name)
// validation: true if we need module: wow_alpha_validity.php  (boolean)
// search: what fields to search for a string search (array)
// where: extra "where" stuff, like: 'AND entry <= ' . MAX_CREATURE (string)
// table: table to search to find this thing: string
// key: primary key of the table to be searched: string

$handlers = array (

  // main menu items

  // Spells
  'spells'        => array ('func' =>  'showSpells',
                            'search'  => array ('Name_enUS', 'Description_enUS'),
                            'table'   => SPELL,
                            'key'     => 'ID',
                           ),
  'show_spell'    => array ('func' =>  'showOneSpell',
                            'search'  => array ('Name_enUS', 'Description_enUS'),
                            'table'   => SPELL,
                            'key'     => 'ID',
                           ),

  // Items
  'items'         => array ('func' =>  'showItems',
                            'search'  => array ('name', 'description'),
                            'where'   => 'AND ignored = 0',
                            'table'   => ITEM_TEMPLATE,
                            'key'     => 'entry',
                           ),
  'show_item'     => array ('func' =>  'showOneItem',
                            'search'  => array ('name', 'description'),
                            'where'   => 'AND ignored = 0',
                            'table'   => ITEM_TEMPLATE,
                            'key'     => 'entry',
                           ),
  'read_text'     => array ('func' =>  'showText',
                           ),

  // Creatures (NPCs)
  'creatures'     => array ('func'    => 'showCreatures',
                            'search'  => array ('name', 'subname'),
                            'where'   => 'AND entry <= ' . MAX_CREATURE,
                            'table'   => CREATURE_TEMPLATE,
                            'key'     => 'entry',
                           ),
  'show_creature' => array ('func' =>  'showOneCreature',
                            'search'  => array ('name', 'subname'),
                            'where'   => 'AND entry <= ' . MAX_CREATURE,
                            'table'   => CREATURE_TEMPLATE,
                            'key'     => 'entry',
                           ),

  // Quests
  'quests'        => array ('func' =>  'showQuests',
                            'search'  => array ('Title', 'Details', 'Objectives', 'OfferRewardText',
                                                'RequestItemsText', 'EndText', 'ObjectiveText1', 'ObjectiveText2',
                                                'ObjectiveText3', 'ObjectiveText4'),
                            'where'   => 'AND ignored = 0',
                            'table'   => QUEST_TEMPLATE,
                            'key'     => 'entry',
                           ),
  'show_quest'    => array ('func' =>  'showOneQuest',
                            'search'  => array ('Title', 'Details', 'Objectives', 'OfferRewardText',
                                                'RequestItemsText', 'EndText', 'ObjectiveText1', 'ObjectiveText2',
                                                'ObjectiveText3', 'ObjectiveText4'),
                            'where'   => 'AND ignored = 0',
                            'table'   => QUEST_TEMPLATE,
                            'key'     => 'entry',
                           ),

  // Game Objects (GOs)
  'game_objects'  => array ('func' =>  'showGameObjects',
                            'search'  => array ('name',),
                            'table'   => GAMEOBJECT_TEMPLATE,
                            'key'     => 'entry',
                           ),
  'show_go'       => array ('func' =>  'showOneGameObject',
                            'search'  => array ('name',),
                            'table'   => GAMEOBJECT_TEMPLATE,
                            'key'     => 'entry',
                           ),

  // Maps
  'maps'          => array ('func' =>  'showMaps',
                            'search'  => array ('directory'),
                            'table'   => MAP,
                            'key'     => 'id',
                           ),
  'show_map'      => array ('func' =>  'showOneMap',
                            'search'  => array ('directory'),
                            'table'   => MAP,
                            'key'     => 'id',
                           ),

  // Zones
  'zones'         => array ('func' =>  'showZones',
                            'search'  => array ('directory'),
                            'table'   => WORLDMAPAREA,
                            'key'     => 'ID',
                           ),
  'show_zone'     => array ('func' =>  'showOneZone',
                            'search'  => array ('directory'),
                            'table'   => WORLDMAPAREA,
                            'key'     => 'ID',
                           ),

  // Ports
  'ports'         => array ('func' =>  'showPorts',
                            'search'  => array ('name'),
                            'table'   => WORLDPORTS,
                            'key'     => 'entry',
                           ),
  'show_port'     => array ('func' =>  'showOnePort',
                            'search'  => array ('name'),
                            'table'   => WORLDPORTS,
                            'key'     => 'entry',
                           ),

  // Skills
  'skills'        => array ('func' =>  'showSkills',
                            'search'  => array ('DisplayName_enUS',),
                            'table'   => SKILLLINE,
                            'key'     => 'ID',
                           ),
  'show_skill'    => array ('func' =>  'showOneSkill',
                            'search'  => array ('DisplayName_enUS',),
                            'table'   => SKILLLINE,
                            'key'     => 'ID',
                           ),

  // Tables
  'tables'        => array ('func' =>  'showTables',
                           ),
  'show_table'    => array ('func' =>  'showOneTable',
                           ),

  // Books (Pages)
  'books'         => array ('func' =>  'showBooks',
                            'search'  => array ('text'),
                            'table'   => PAGE_TEXT,
                            'key'     => 'entry',
                           ),
  'show_book'     => array ('func' =>  'showOneBook',
                            'search'  => array ('text'),
                            'table'   => PAGE_TEXT,
                            'key'     => 'entry',
                           ),

  // other tables

  'spell_visuals'             => array ('func' =>  'showSpellVisuals',
                            'search'  => array ('Name'),
                            'table'   => SPELLVISUAL,
                            'key'     => 'ID',
                           ),

  'show_spell_visual'         => array ('func' =>  'showOneSpellVisual',
                            'search'  => array ('Name'),
                            'table'   => SPELLVISUAL,
                            'key'     => 'ID',
                           ),

  'spell_visual_anim_names'   => array ('func' =>  'showSpellVisualAnimNames',
                            'search'  => array ('Name'),
                            'table'   => SPELLVISUALANIMNAME,
                            'key'     => 'ID',
                           ),
  'show_spell_visual_anim'    => array ('func' =>  'showOneSpellVisualAnimName',
                            'search'  => array ('Name'),
                            'table'   => SPELLVISUALANIMNAME,
                            'key'     => 'ID',
                           ),

  'spell_visual_effect_names' => array ('func' =>  'showSpellVisualEffectNames',
                            'search'  => array ('FileName'),
                            'table'   => SPELLVISUALEFFECTNAME,
                            'key'     => 'ID',
                           ),
  'show_spell_visual_effect'  => array ('func' =>  'showOneSpellVisualEffectName',
                            'search'  => array ('FileName'),
                            'table'   => SPELLVISUALEFFECTNAME,
                            'key'     => 'ID',
                           ),

  // Utilites

  'proximity'     => array ('func' =>  'showProximity',
                           ),

  // Validation

  'unknown_faction'     => array ('func' =>  'showUnknownFaction',
                                  'validation' => true,
                           ),
  'quest_missing_item'  => array ('func' =>  'showMissingQuestItems',
                                  'validation' => true,
                           ),
  'quest_missing_spell' => array ('func' =>  'showMissingQuestSpells',
                                  'validation' => true,
                           ),
  'quest_missing_quest' => array ('func' =>  'showMissingQuestQuests',
                                  'validation' => true,
                           ),
  'npc_missing_quest'   => array ('func' =>  'showMissingCreatureQuests',
                                  'validation' => true,
                           ),
  'npc_no_model'        => array ('func' =>  'showMissingCreatureModels',
                                  'validation' => true,
                           ),
  'npc_not_spawned'     => array ('func' =>  'showCreaturesNotSpawned',
                                  'validation' => true,
                           ),
  'go_missing_quest'    => array ('func' =>  'showMissingGameobjectQuests',
                                  'validation' => true,
                           ),
  'go_not_spawned'      => array ('func' =>  'showGameObjectsNotSpawned',
                                  'validation' => true,
                           ),
  'no_item_text'        => array ('func' =>  'showNoItemText',
                                  'validation' => true,
                           ),
  'spell_missing_spell' => array ('func' =>  'showMissingSpellSpells',
                                  'validation' => true,
                           ),
  'spell_missing_item'  => array ('func' =>  'showMissingSpellItems',
                                  'validation' => true,
                           ),
  'item_missing_spell'  => array ('func' =>  'showMissingItemSpells',
                                  'validation' => true,
                           ),
  'item_unused'         => array ('func' =>  'showUnusedItems',
                                  'validation' => true,
                           ),

); // end of $handlers


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
