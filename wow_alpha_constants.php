<?php


/*
  Author: X'Genesis Qhulut <XGenesis-Qhulut@protonmail.com>
  Date:   August 2022

  See LICENSE for license details.

*/

define ('CREATURE_LOOT_TEMPLATE', 'alpha_world.creature_loot_template');
define ('CREATURE_QUEST_FINISHER', 'alpha_world.creature_quest_finisher');
define ('CREATURE_QUEST_STARTER', 'alpha_world.creature_quest_starter');
define ('CREATURE_TEMPLATE', 'alpha_world.creature_template');
define ('FACTION', 'alpha_dbc.faction');
define ('GAMEOBJECT_QUESTRELATION', 'alpha_world.gameobject_questrelation');
define ('GAMEOBJECT_TEMPLATE', 'alpha_world.gameobject_template');
define ('ITEM_TEMPLATE', 'alpha_world.item_template');
define ('MAP', 'alpha_dbc.map');
define ('NPC_VENDOR', 'alpha_world.npc_vendor');
define ('PICKPOCKETING_LOOT_TEMPLATE', 'alpha_world.pickpocketing_loot_template');
define ('QUEST_TEMPLATE', 'alpha_world.quest_template');
define ('REFERENCE_LOOT_TEMPLATE', 'alpha_world.reference_loot_template');
define ('SKILLLINE', 'alpha_dbc.skillline');
define ('SPAWNS_CREATURES', 'alpha_world.spawns_creatures');
define ('SPAWNS_GAMEOBJECTS', 'alpha_world.spawns_gameobjects');
define ('SPELL', 'alpha_dbc.spell');


define ('SPELL_SCHOOLS', array (
   0 => 'Normal',
   1 => 'Holy',
   2 => 'Fire',
   3 => 'Nature',
   4 => 'Frost ',
   5 => 'Shadow',
    ));

define ('POWER_TYPES', array (
   -2 =>'Health',
    0 =>'Mana',
    1 =>'Rage',
    2 =>'Focus',
    3 =>'Energy',
    4 =>'Happiness',
    ));

define ('CLASSES', array (
   1  =>'Warrior',
   2  =>'Paladin',
   3  =>'Hunter',
   4  =>'Rogue',
   5  =>'Priest',
   7  =>'Shaman',
   8  =>'Mage',
   9  =>'Warlock',
   11 =>'Druid',
  ));

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

define ('CREATURE_TYPES', array (
   1  =>'Beast',
   2  =>'Dragon',
   3  =>'Demon',
   4  =>'Elemental',
   5  =>'Giant',
   6  =>'Undead',
   7  =>'Humanoid',
   8  =>'Ambient',  // Critter
   9  =>'Mechanical',
   10 =>'Not_specified',
  ));

define ('SPELL_EFFECTS', array (
  0x0 => 'None',
  0x1 => 'Instakill',
  0x2 => 'School_damage',
  0x3 => 'Dummy',
  0x5 => 'Teleport_units',
  0x6 => 'Apply_aura',
  0x8 => 'Power_drain',
  0x9 => 'Health_leech',
  0xA => 'Heal',
  0xB => 'Bind',
  0xC => 'Portal',
  0x10 => 'Quest_complete',
  0x11 => 'Weapon_damage',
  0x12 => 'Resurrect',
  0x13 => 'Extra_attacks',
  0x14 => 'Dodge',
  0x15 => 'Evade',
  0x16 => 'Parry',
  0x17 => 'Block',
  0x18 => 'Create_item',
  0x19 => 'Weapon',
  0x1A => 'Defense',
  0x1B => 'Persistent_area_aura',
  0x1C => 'Summon',
  0x1D => 'Leap',
  0x1E => 'Energize',
  0x1F => 'Weapon_perc_dmg',
  0x21 => 'Open_lock',
  0x22 => 'Summon_mount',
  0x23 => 'Apply_area_aura',
  0x24 => 'Learn_spell',
  0x25 => 'Spell_defense',
  0x26 => 'Dispel',
  0x27 => 'Language',
  0x28 => 'Dual_wield',
  0x29 => 'Summon_wild',
  0x2A => 'Summon_guardian',
  0x2C => 'Skill_step',
  0x2E => 'Spawn',
  0x2F => 'Spell_cast_ui',
  0x30 => 'Stealth',
  0x31 => 'Detect',
  0x32 => 'Summon_object',
  0x34 => 'Guarantee_hit',
  0x35 => 'Enchant_item_permanent',
  0x36 => 'Enchant_item_temporary',
  0x37 => 'Tame_creature',
  0x38 => 'Summon_pet',
  0x39 => 'Learn_pet_spell',
  0x3A => 'Weapon_damage_plus',
  0x3B => 'Open_lock_item',
  0x3C => 'Proficiency',
  0x3D => 'Send_event',
  0x3E => 'Power_burn',
  0x3F => 'Threat',
  0x40 => 'Trigger_spell',
  0x43 => 'Heal_max_health',
  0x44 => 'Interrupt_cast',
  0x45 => 'Distract',
  0x46 => 'Pull',
  0x47 => 'Pickpocket',
  0x48 => 'Add_farsight',
  0x49 => 'Summon_possessed',
  0x4A => 'Summon_totem',
  0x4C => 'Summon_object_wild',
  0x4D => 'Script_effect',
  0x4E => 'Attack',
  0x4F => 'Sanctuary',
  0x50 => 'Add_combo_points',
  0x51 => 'Create_house',
  0x52 => 'Bind_sight',
  0x53 => 'Duel',
  0x54 => 'Stuck',
  0x55 => 'Summon_player',
  0x56 => 'Activate_object',
));

define ('TRAINER_FLAG', 0x8);

define ('NPC_FLAG', array (
   0x1 => 'Vendor',            //  If creature has more gossip options, add this flag to bring up a menu.
   0x2 => 'Questgiver',        //  Any creature giving or taking quests needs to have this flag.
   0x4 => 'Flightmaster',
   0x8 => 'Trainer',           //  Allows the creature to have a trainer list to teach spells
  0x10 => 'Binder',
  0x20 => 'Banker',
  0x40 => 'Tabard Designer',
  0x80 => 'Petitioner',
));

define ('ITEM_CLASS', array (
  0  => 'Consumable',
  1  => 'Container',
  2  => 'Weapon',
  3  => 'Jewelry',
  4  => 'Armor',
  5  => 'Reagent',
  6  => 'Projectile',
  7  => 'Trade_goods',
  8  => 'Generic',
  9  => 'Book',
  10 => 'Money',
  11 => 'Quiver',
  12 => 'Quest',
  13 => 'Key',
  14 => 'Permanent',
  15 => 'Junk',
));


// 0: Consumable

define ('ITEM_SUBCLASS_0', array (
  0  => 'Consumable',
  1  => 'Food',
  2  => 'Liquid',
  3  => 'Potion',
  4  => 'Scroll',
  5  => 'Bandage',
  6  => 'Healthstone',
  7  => 'Combat_effect',
));

// 1: Container

define ('ITEM_SUBCLASS_1', array (
  0  => 'Bag',
  1  => 'Soul_bag',
  2  => 'Herb_bag',
  3  => 'Enchanting_bag',
));

// 2: Weapon

define ('ITEM_SUBCLASS_2', array (
  0  => 'Axe',
  1  => 'Twohand_axe',
  2  => 'Bow',
  3  => 'Gun',
  4  => 'Mace',
  5  => 'Twohand_mace',
  6  => 'Polearm',
  7  => 'Sword',
  8  => 'Twohand_sword',
  9  => 'Weapon_obsolete',
  10 => 'Staff',
  11 => 'Weapon_exotic',
  12 => 'Weapon_exotic2',
  13 => 'Fist_weapon',
  14 => 'Misc_weapon',
  15 => 'Dagger',
  16 => 'Thrown',
  17 => 'Spear',
  18 => 'Crossbow',
  19 => 'Wand',
  20 => 'Fishing_pole',
));

// 3: Jewellery

define ('ITEM_SUBCLASS_3', array (
  0 => 'None',
));

// 4: Armor

define ('ITEM_SUBCLASS_4', array (
  0  => 'Misc',
  1  => 'Cloth',
  2  => 'Leather',
  3  => 'Mail',
  4  => 'Plate',
  5  => 'Buckler',
  6  => 'Shield',
  7  => 'Libram',
  8  => 'Idol',
  9  => 'Totem',
));

// 5: Reagent

define ('ITEM_SUBCLASS_5', array (
  0 => 'None',
));

// 6: Projectile

define ('ITEM_SUBCLASS_6', array (
  0  => 'Wand_obslete',
  1  => 'Bolt_obslete',
  2  => 'Arrow',
  3  => 'Bullet',
  4  => 'Thrown_obslete',
));

// 7: Trade goods

define ('ITEM_SUBCLASS_7', array (
  0  => 'Trade_goods',
  1  => 'Parts',
  2  => 'Explosives',
  3  => 'Devices',
  4  => 'Gems',
  5  => 'Cloths',
  6  => 'Leathers',
  7  => 'Metal_and_stone',
  8  => 'Meat',
  9  => 'Herb',
  10 => 'Elemental',
  11 => 'Others',
  12 => 'Enchantants',
  13 => 'Materials',
));


// 8: Generic

define ('ITEM_SUBCLASS_8', array (
  0 => 'None',
));

// 9: Recipe

define ('ITEM_SUBCLASS_9', array (
  0  => 'Book',
  1  => 'Leatherworking',
  2  => 'Tailoring',
  3  => 'Engineering',
  4  => 'Blacksmithing',
  5  => 'Cooking',
  6  => 'Alchemy',
  7  => 'First_aid',
  8  => 'Enchanting',
  9  => 'Fishing',
  10 => 'Jewelcrafting',
));

// 10: Money

define ('ITEM_SUBCLASS_10', array (
  0 => 'None',
));

// 11: Quiver

define ('ITEM_SUBCLASS_11', array (
  0  => 'Quiver0_obsolete',
  1  => 'Quiver1_obsolete',
  2  => 'Quiver',
  3  => 'Ammo_pouch',
));

// 12: Quest

define ('ITEM_SUBCLASS_12', array (
  0 => 'None',
));

// 13: Key

define ('ITEM_SUBCLASS_13', array (
  0  => 'Key',
  1  => 'Lockpick',
));

// 14: Permanent?

define ('ITEM_SUBCLASS_14', array (
  0  => 'Junk',
  1  => 'Reagent',
  2  => 'Pet',
  3  => 'Holiday',
  4  => 'Other',
  5  => 'Mount',
));

// 15: Junk

define ('ITEM_SUBCLASS_15', array (
  0 => 'None',
));


define ('ITEM_SUBCLASSES', array (
  ITEM_SUBCLASS_0,
  ITEM_SUBCLASS_1,
  ITEM_SUBCLASS_2,
  ITEM_SUBCLASS_3,
  ITEM_SUBCLASS_4,
  ITEM_SUBCLASS_5,
  ITEM_SUBCLASS_6,
  ITEM_SUBCLASS_7,
  ITEM_SUBCLASS_8,
  ITEM_SUBCLASS_9,
  ITEM_SUBCLASS_10,
  ITEM_SUBCLASS_11,
  ITEM_SUBCLASS_12,
  ITEM_SUBCLASS_13,
  ITEM_SUBCLASS_14,
  ITEM_SUBCLASS_15,
  ));


define ('INVENTORY_TYPE', array (
  0x00 => 'None Equip',
  0x01 => 'Head',
  0x02 => 'Neck',
  0x03 => 'Shoulder',
  0x04 => 'Body',
  0x05 => 'Chest',
  0x06 => 'Waist',
  0x07 => 'Legs',
  0x08 => 'Feet',
  0x09 => 'Wrist',
  0x0A => 'Hand',
  0x0B => 'Finger',
  0x0C => 'Trinket',
  0x0D => 'Weapon',
  0x0E => 'Shield',
  0x0F => 'Ranged',
  0x10 => 'Cloak',
  0x11 => 'Two Handed Weapon',
  0x12 => 'Bag',
  0x13 => 'Tabard',
  0x14 => 'Robe',
  0x15 => 'Weapon Main Hand',
  0x16 => 'Weapon Off Hand',
  0x17 => 'Holdable',
  0x18 => 'Ammo',
  0x19 => 'Thrown',
  0x1A => 'Ranged Right',
));

define ('ITEM_FLAGS', array (
     0x1 => 'No Pickup',
     0x2 => 'Conjured',
     0x4 => 'Has Loot',
     0x8 => 'Exotic',
    0x10 => 'Deprecated',
    0x20 => 'Obsolete',
    0x40 => 'Player Cast',
    0x80 => 'No Equip Cooldown',
   0x100 => 'Int Bonus Instead',
   0x200 => 'Is Wrapper',
   0x400 => 'Uses Resources',
   0x800 => 'Multi Drop',
  0x1000 => 'Brief Spell Effects',
  0x2000 => 'Petition',
));

define ('CREATURE_RANK', array (
  0 => 'Normal',
  1 => 'Elite',
  2 => 'Rare Elite',
  3 => 'World Boss',
  4 => 'Rare',
));


// for creatures
define ('FLAGS_EXTRA', array (
  0x00000001 => 'Instance bind',
  0x00000002 => 'No aggro',
  0x00000004 => 'No parry',
  0x00000008 => 'Summon guard',
  0x00000010 => 'No block',
  0x00000020 => 'No crush',
  0x00000040 => 'Fixed Z',
  0x00000080 => 'Invisible',
  0x00000100 => 'Not tauntable',
  0x00000200 => 'Aggro zone',
  0x00000400 => 'Guard',
  0x00000800 => 'No threat list',
  0x00001000 => 'Keep positive auras on evade',
  0x00002000 => 'Always crush',
  0x00004000 => 'Immune aoe',
  0x00008000 => 'Chase gen no backing',
  0x00010000 => 'No assist',
  0x00020000 => 'No target',
  0x00040000 => 'Only visible to friendly',
  0x00080000 => 'PvP',
  0x00100000 => 'Can assist',
  0x00200000 => 'Large aoi',
  0x00400000 => 'Gigantic aoi',
  0x00800000 => 'Infinite aoi',
  0x01000000 => 'No movement pause',
  0x02000000 => 'Always run',
  0x04000000 => 'No unreachable evade',
));

define ('GAMEOBJECT_TYPE', array (
   0x0 => 'Door',
   0x1 => 'Button',
   0x2 => 'Quest Giver',
   0x3 => 'Chest',
   0x4 => 'Binder',
   0x5 => 'Generic',
   0x6 => 'Trap',
   0x7 => 'Chair',
   0x8 => 'Spell Focus',
   0x9 => 'Text',
   0xA => 'Goober',
   0xB => 'Transport',
   0xC => 'Areadamage',
   0xD => 'Camera',
   0xE => 'Map Object',
   0xF => 'Mo Transport',
  0x10 => 'Duel Arbiter',
  0x11 => 'Fishing Node',
  0x12 => 'Ritual',
));

define ('SPELL_TARGET_TYPE', array (
       0 => 'Self',
     0x2 => 'Unit',
     0x8 => 'Player',
    0x10 => 'Item',
    0x20 => 'Source Location',
    0x40 => 'Dest Location',
    0x80 => 'Enemies',
   0x100 => 'Unit Self',
   0x400 => 'Unit Dead',
   0x800 => 'Gameobject',
  0x1000 => 'Trade Item',
  0x2000 => 'Target String',
  0x4000 => 'Game Object Item',
));

define ('SPELL_ATTRIBUTES', array (
  0x00000001 => 'Proc failure burns charge',
  0x00000002 => 'Ranged',
  0x00000004 => 'On next swing 1',
  0x00000008 => 'Req exotic ammo',
  0x00000010 => 'Is ability',
  0x00000020 => 'Tradespell',
  0x00000040 => 'Passive',
  0x00000080 => 'Do not display',
  0x00000100 => 'Do not log',
  0x00000200 => 'Held item only',
  0x00000400 => 'On next swing 2',
  0x00000800 => 'Wearer casts proc trigger',
  0x00001000 => 'Daytime only',
  0x00002000 => 'Night only',
  0x00004000 => 'Indoors only',
  0x00008000 => 'Outdoors only',
  0x00010000 => 'Not shapeshift',
  0x00020000 => 'Only stealthed',
  0x00040000 => 'Do not stealth',
  0x00080000 => 'Level damage calculation',
  0x00100000 => 'Stop attack target',
  0x00200000 => 'Impossible dodge parry block',
  0x00400000 => 'Set tracking target',
  0x00800000 => 'Allow cast while dead',
  0x01000000 => 'Castable while mounted',
  0x02000000 => 'Disabled while active',
  0x04000000 => 'Aura is debuff',
  0x08000000 => 'Castable while sitting',
  0x10000000 => 'Can\'t used in combat',
  0x20000000 => 'Unaffected by invulnerability',
  0x40000000 => 'Heartbeat resist',
  0x80000000 => 'Can\'t cancel',
));

define ('SPELL_ATTRIBUTES_EX', array (
  0x00000001 => 'Dismiss pet first',
  0x00000002 => 'Drain all power',
  0x00000004 => 'Channeled',
  0x00000008 => 'Can\'t be redirected',
  0x00000010 => 'No skill increase',
  0x00000020 => 'Not break stealth',
  0x00000040 => 'Channeled 2',
  0x00000080 => 'Negative',
  0x00000100 => 'Not in combat target',
  0x00000200 => 'Melee combat start',
  0x00000400 => 'No threat',
  0x00000800 => 'Aura unique',
  0x00001000 => 'Failure breaks stealth',
  0x00002000 => 'Farsight',
  0x00004000 => 'Channel track target',
  0x00008000 => 'Dispel auras on immunity',
  0x00010000 => 'Immunity hostile friendly effects',
  0x00020000 => 'No autocast ai',
  0x00040000 => 'Prevents anim',
  0x00080000 => 'Can\'t target self',
  0x00100000 => 'Req target combo points',
  0x00200000 => 'Threat on miss',
  0x00400000 => 'Req combo points',
  0x00800000 => 'Ignore owner death',
  0x01000000 => 'Is fishing',
  0x02000000 => 'Aura stays after combat',
  0x04000000 => 'Require all targets',
  0x08000000 => 'Refund power',
  0x10000000 => 'Dont display in aura bar',
  0x20000000 => 'Channel display spell name',
  0x40000000 => 'Enable at dodge',
  0x80000000 => 'Cast when learned',
));


// ---------------------------------------

// I got these from: https://trinitycore.atlassian.net/wiki/spaces/tc/pages/74677606/creature+template335


define ('MECHANIC_IMMUNE', array (
  0x00000001  =>  'Charm',
  0x00000002  =>  'Disoriented',
  0x00000004  =>  'Disarm',
  0x00000008  =>  'Distract',
  0x00000010  =>  'Fear',
  0x00000020  =>  'Grip',
  0x00000040  =>  'Root',
  0x00000080  =>  'Pacify',
  0x00000100  =>  'Silence',
  0x00000200  =>  'Sleep',
  0x00000400  =>  'Snare',
  0x00000800  =>  'Stun',
  0x00001000  =>  'Freeze',
  0x00002000  =>  'Knockout',
  0x00004000  =>  'Bleed',
  0x00008000  =>  'Bandage',
  0x00010000  =>  'Polymorph',
  0x00020000  =>  'Banish',
  0x00040000  =>  'Shield',
  0x00080000  =>  'Shackle',
  0x00100000  =>  'Mount',
  0x00200000  =>  'Infected',
  0x00400000  =>  'Turn',
  0x00800000  =>  'Horror',
  0x01000000  =>  'Invulnerability',
  0x02000000  =>  'Interrupt',
  0x04000000  =>  'Daze',
  0x08000000  =>  'Discovery',
  0x10000000  =>  'Immune_shield',
  0x20000000  =>  'Sapped',
  0x40000000  =>  'Enraged',
));



define ('INHABIT_TYPE', array (
  1 => 'Ground',
  2 => 'Water',
  4 => 'Flying',
  8 => 'Rooted',
));

define ('MOVEMENT_TYPE', array (
  0 => 'Idle',
  1 => 'Random',
  2 => 'Waypoints',
));

define ('TRAINER_TYPE', array (
    0 =>'Class',
    1 =>'Mounts',
    2 =>'Trade skills',
    3 =>'Pets',
));





?>
