<?php

/*
  Author: X'Genesis Qhulut <XGenesis-Qhulut@protonmail.com>
  Date:   August 2022

  See LICENSE for license details.
*/

// FIELD EXPANSION

// menu at top of page

define ('MENU', array (
  'Creatures'     => 'creatures',
  'Game Objects'  => 'game_objects',
  'Items'         => 'items',
  'Spells'        => 'spells',
  'Maps'          => 'maps',
  'Pages'         => 'books',
  'Ports'         => 'ports',
  'Quests'        => 'quests',
  'Skills'        => 'skills',
  'Tables'        => 'tables',
  'Zones'         => 'zones',

// more here
));

// Names of handler functions for actions

$handlers = array (

  // main menu items

  // Spells
  'spells'        => 'showSpells',
  'show_spell'    => 'showOneSpell',

  // Items
  'items'         => 'showItems',
  'show_item'     => 'showOneItem',
  'read_text'     => 'showText',

  // Creatures (NPCs)
  'creatures'     => 'showCreatures',
  'show_creature' => 'showOneCreature',

  // Quests
  'quests'        => 'showQuests',
  'show_quest'    => 'showOneQuest',

  // Game Objects (GOs)
  'game_objects'  => 'showGameObjects',
  'show_go'       => 'showOneGameObject',

  // Maps
  'maps'          => 'showMaps',
  'show_map'      => 'showOneMap',

  // Zones
  'zones'         => 'showZones',
  'show_zone'     => 'showOneZone',

  // Ports
  'ports'         => 'showPorts',
  'show_port'     => 'showOnePort',

  // Skills
  'skills'        => 'showSkills',
  'show_skill'    => 'showOneSkill',

  // Tables
  'tables'        => 'showTables',
  'show_table'    => 'showOneTable',

  // Books (Pages)
  'books'         => 'showBooks',
  'show_book'     => 'showOneBook',

  // other tables

  'spell_visuals'             => 'showSpellVisuals',
  'show_spell_visual'         => 'showOneSpellVisual',
  'spell_visual_anim_names'   => 'showSpellVisualAnimNames',
  'show_spell_visual_anim'    => 'showOneSpellVisualAnimName',
  'spell_visual_effect_names' => 'showSpellVisualEffectNames',
  'show_spell_visual_effect'  => 'showOneSpellVisualEffectName',

  // Utilites

  'proximity'     => 'showProximity',

  // Validation

  'unknown_faction'     => 'showUnknownFaction',
  'quest_missing_item'  => 'showMissingQuestItems',
  'quest_missing_spell' => 'showMissingQuestSpells',
  'quest_missing_quest' => 'showMissingQuestQuests',
  'npc_missing_quest'   => 'showMissingCreatureQuests',
  'go_missing_quest'    => 'showMissingGameobjectQuests',
  'go_not_spawned'      => 'showGameObjectsNotSpawned',
  'no_item_text'        => 'showNoItemText',
); // end of $handlers

?>
