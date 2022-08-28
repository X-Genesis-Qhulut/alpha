<?php


/*
  Author: X'Genesis Qhulut <XGenesis-Qhulut@protonmail.com>
  Date:   August 2022

  See LICENSE for license details.

*/

// configuration

define ('QUERY_LIMIT', 200);
define ('MAX_CREATURE', 5759);    // creatures > this are not in 0.5.3 alpha
define ('TWO_COLUMN_SPLIT', 30);  // where we break long columns into two

// table names

function lwr ($s)
  {
  if (LOWER_CASE_SQL_TABLES)
    return strtolower ($s);
  return $s;
  } // end of lwr

define ('APPLIED_UPDATES_DBC',          '`' . DBC_DBNAME.   '`.' . lwr ('applied_updates'));
define ('APPLIED_UPDATES_WORLD',        '`' . WORLD_DBNAME. '`.' . lwr ('applied_updates'));
define ('CREATURE_LOOT_TEMPLATE',       '`' . WORLD_DBNAME. '`.' . lwr ('creature_loot_template'));
define ('CREATURE_QUEST_FINISHER',      '`' . WORLD_DBNAME. '`.' . lwr ('creature_quest_finisher'));
define ('CREATURE_QUEST_STARTER',       '`' . WORLD_DBNAME. '`.' . lwr ('creature_quest_starter'));
define ('CREATURE_SPELLS',              '`' . WORLD_DBNAME. '`.' . lwr ('creature_spells'));
define ('CREATURE_TEMPLATE',            '`' . WORLD_DBNAME. '`.' . lwr ('creature_template'));
define ('FACTION',                      '`' . DBC_DBNAME  . '`.' . lwr ('Faction'));
define ('FACTIONTEMPLATE',              '`' . DBC_DBNAME  . '`.' . lwr ('FactionTemplate'));
define ('GAMEOBJECT_QUESTRELATION',     '`' . WORLD_DBNAME. '`.' . lwr ('gameobject_questrelation'));
define ('GAMEOBJECT_INVOLVEDRELATION',  '`' . WORLD_DBNAME. '`.' . lwr ('gameobject_involvedrelation'));
define ('GAMEOBJECT_TEMPLATE',          '`' . WORLD_DBNAME. '`.' . lwr ('gameobject_template'));
define ('GAMEOBJECT_LOOT_TEMPLATE',     '`' . WORLD_DBNAME. '`.' . lwr ('gameobject_loot_template'));
define ('ITEM_TEMPLATE',                '`' . WORLD_DBNAME. '`.' . lwr ('item_template'));
define ('ITEMDISPLAYINFO',              '`' . DBC_DBNAME  . '`.' . lwr ('ItemDisplayInfo'));
define ('MAP',                          '`' . DBC_DBNAME  . '`.' . lwr ('Map'));
define ('NPC_VENDOR',                   '`' . WORLD_DBNAME. '`.' . lwr ('npc_vendor'));
define ('PICKPOCKETING_LOOT_TEMPLATE',  '`' . WORLD_DBNAME. '`.' . lwr ('pickpocketing_loot_template'));
define ('SKINNING_LOOT_TEMPLATE',       '`' . WORLD_DBNAME. '`.' . lwr ('skinning_loot_template'));
define ('QUEST_TEMPLATE',               '`' . WORLD_DBNAME. '`.' . lwr ('quest_template'));
define ('REFERENCE_LOOT_TEMPLATE',      '`' . WORLD_DBNAME. '`.' . lwr ('reference_loot_template'));
define ('SKILLLINE',                    '`' . DBC_DBNAME  . '`.' . lwr ('SkillLine'));
define ('SPAWNS_CREATURES',             '`' . WORLD_DBNAME. '`.' . lwr ('spawns_creatures'));
define ('SPAWNS_GAMEOBJECTS',           '`' . WORLD_DBNAME. '`.' . lwr ('spawns_gameobjects'));
define ('SPELL',                        '`' . DBC_DBNAME  . '`.' . lwr ('Spell'));
define ('SPELLICON',                    '`' . DBC_DBNAME  . '`.' . lwr ('SpellIcon'));
define ('SPELLCASTTIMES',               '`' . DBC_DBNAME  . '`.' . lwr ('SpellCastTimes'));
define ('SPELLDURATION',                '`' . DBC_DBNAME  . '`.' . lwr ('SpellDuration'));
define ('SPELLRANGE',                   '`' . DBC_DBNAME  . '`.' . lwr ('SpellRange'));
define ('TRAINER_TEMPLATE',             '`' . WORLD_DBNAME. '`.' . lwr ('trainer_template'));
define ('WORLDMAPAREA',                 '`' . DBC_DBNAME  . '`.' . lwr ('WorldMapArea'));
define ('WORLDPORTS',                   '`' . WORLD_DBNAME. '`.' . lwr ('worldports'));


define ('MAP_DOT_SIZE', 8); // pixels
define ('MAP_DOT_FILL', 'yellow');
define ('MAP_DOT_STROKE', 'black');


// replace some magic numbers with proper defines
define ('QUEST_REQUIRED_CREATURES', 4);
define ('QUEST_REQUIRED_ITEMS', 4);
define ('QUEST_REQUIRED_SPELLS', 4);
define ('QUEST_REWARD_ITEMS', 4);
define ('QUEST_REWARD_ITEM_CHOICES', 6);
define ('QUEST_REWARD_REPUTATION', 5);

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

define ('SKILL_TYPES', array (
  1 => 'Max skill',
  2 => 'Weapon skill',
  3 => 'Class skill',
  4 => 'Secondary skill',
));

define ('QUEST_FLAGS', array (
  0x00000000 => 'None',
  0x00000001 => 'Stay alive',
  0x00000002 => 'Party accept',
  0x00000004 => 'Exploration',
  0x00000008 => 'Sharable',
  0x00000020 => 'Epic',
  0x00000040 => 'Raid',
  0x00000200 => 'Hidden rewards',
  0x00000400 => 'Auto rewarded',
));

define ('QUEST_SPECIAL_FLAGS', array (
  0 => 'None',
  1 => 'Repeatable',
  2 => 'Script',
));



define ('SPELL_EFFECTS', array (
  0x0 => 'None',
  0x1 => 'Instakill',
  0x2 => 'School damage',
  0x3 => 'Dummy',
  0x5 => 'Teleport units',
  0x6 => 'Apply aura',
  0x8 => 'Power drain',
  0x9 => 'Health leech',
  0xA => 'Heal',
  0xB => 'Bind',
  0xC => 'Portal',
  0x10 => 'Quest complete',
  0x11 => 'Weapon damage',
  0x12 => 'Resurrect',
  0x13 => 'Extra attacks',
  0x14 => 'Dodge',
  0x15 => 'Evade',
  0x16 => 'Parry',
  0x17 => 'Block',
  0x18 => 'Create item',
  0x19 => 'Weapon',
  0x1A => 'Defense',
  0x1B => 'Persistent area aura',
  0x1C => 'Summon',
  0x1D => 'Leap',
  0x1E => 'Energize',
  0x1F => 'Weapon perc dmg',
  0x21 => 'Open lock',
  0x22 => 'Summon mount',
  0x23 => 'Apply area aura',
  0x24 => 'Learn spell',
  0x25 => 'Spell defense',
  0x26 => 'Dispel',
  0x27 => 'Language',
  0x28 => 'Dual wield',
  0x29 => 'Summon wild',
  0x2A => 'Summon guardian',
  0x2C => 'Skill step',
  0x2E => 'Spawn',
  0x2F => 'Spell cast ui',
  0x30 => 'Stealth',
  0x31 => 'Detect',
  0x32 => 'Summon object',
  0x34 => 'Guarantee hit',
  0x35 => 'Enchant item permanent',
  0x36 => 'Enchant item temporary',
  0x37 => 'Tame creature',
  0x38 => 'Summon pet',
  0x39 => 'Learn pet spell',
  0x3A => 'Weapon damage plus',
  0x3B => 'Open lock item',
  0x3C => 'Proficiency',
  0x3D => 'Send event',
  0x3E => 'Power burn',
  0x3F => 'Threat',
  0x40 => 'Trigger spell',
  0x43 => 'Heal max health',
  0x44 => 'Interrupt cast',
  0x45 => 'Distract',
  0x46 => 'Pull',
  0x47 => 'Pickpocket',
  0x48 => 'Add farsight',
  0x49 => 'Summon possessed',
  0x4A => 'Summon totem',
  0x4C => 'Summon object wild',
  0x4D => 'Script effect',
  0x4E => 'Attack',
  0x4F => 'Sanctuary',
  0x50 => 'Add combo points',
  0x51 => 'Create house',
  0x52 => 'Bind sight',
  0x53 => 'Duel',
  0x54 => 'Stuck',
  0x55 => 'Summon player',
  0x56 => 'Activate object',
));

define ('SPELL_AURAS', array (
   0 => 'None',
   1 => 'Bind sight',
   2 => 'Mod possess',
   3 => 'Periodic damage',
   4 => 'Dummy',
   5 => 'Mod confuse',
   6 => 'Mod charm',
   7 => 'Mod fear',
   8 => 'Periodic heal',
   9 => 'Mod attackspeed',
  10 => 'Mod threat',
  11 => 'Mod taunt',
  12 => 'Mod stun',
  13 => 'Mod damage done',
  14 => 'Mod damage taken',
  15 => 'Damage shield',
  16 => 'Mod stealth',
  17 => 'Mod stealth detect',
  18 => 'Mod invisibility',
  19 => 'Mod invisibility detection',
  22 => 'Mod resistance',
  23 => 'Periodic trigger spell',
  24 => 'Periodic energize',
  25 => 'Mod pacify',
  26 => 'Mod root',
  27 => 'Mod silence',
  28 => 'Reflect spells',
  29 => 'Mod stat',
  30 => 'Mod skill',
  31 => 'Mod increase speed',
  32 => 'Mod increase mounted speed',
  33 => 'Mod decrease speed',
  34 => 'Mod increase health',
  35 => 'Mod increase mana',
  36 => 'Mod shapeshift',
  37 => 'Effect immunity',
  38 => 'State immunity',
  39 => 'School immunity',
  40 => 'Damage immunity',
  41 => 'Dispel immunity',
  42 => 'Proc trigger spell',
  43 => 'Proc trigger damage',
  44 => 'Track creatures',
  45 => 'Track resources',
  47 => 'Mod parry percent',
  49 => 'Mod dodge percent',
  51 => 'Mod block percent',
  52 => 'Mod crit percent',
  53 => 'Periodic leech',
  54 => 'Mod hit chance',
  56 => 'Transform',
  57 => 'Mod spell crit chance',
  58 => 'Mod increase swim speed',
  59 => 'Mod damage done creature',
  60 => 'Mod pacify silence',
  61 => 'Mod scale',
  63 => 'Periodic mana funnel',
  64 => 'Periodic mana leech',
  65 => 'Mod casting speed not stack',
  66 => 'Feign death',
  67 => 'Mod disarm',
  68 => 'Mod stalked',
  69 => 'School absorb',
  71 => 'Mod spell crit chance school',
  73 => 'Mod power cost school',
  74 => 'Reflect spells school',
  75 => 'Mod language',
  76 => 'Far sight',
  77 => 'Mechanic immunity',
  78 => 'Mounted',
  79 => 'Mod damage percent done',
  80 => 'Mod percent stat',
  81 => 'Split damage pct',
  82 => 'Water breathing',
  83 => 'Mod base resistance',
  84 => 'Mod regen',
  85 => 'Mod power regen',
  86 => 'Channel death item',
  87 => 'Mod damage percent taken',
  88 => 'Mod health regen percent',
));

define ('SPELL_IMPLICIT_TARGET', array (
   0 => 'Initial',
   1 => 'Self',
   2 => 'Random enemy chain in area',
   4 => 'Unit near caster',
   5 => 'Pet',
   6 => 'Enemy unit',
   7 => 'Areaeffect custom',
   9 => 'Innkeeper coordinates',
  11 => '11',
  15 => 'All enemy in area',
  16 => 'All enemy in area instant',
  17 => 'Table x y z coordinates',
  18 => 'Effect select',
  20 => 'Around caster party',
  21 => 'Selected friend',
  22 => 'All around caster',
  23 => 'Selected gameobject',
  24 => 'Infront',
  25 => 'Unit',
  26 => 'Gameobject and item',
  27 => 'Master',
  28 => 'Area effect enemy channel',
  30 => 'All friendly units around caster',
  31 => 'All friendly units in area',
  32 => 'Minion',
  33 => 'All party',
  34 => 'All party around caster 2',
  35 => 'Single party',
  36 => 'Hostile unit selection',
  37 => 'Areaeffect party',
  38 => 'Script',
  39 => 'Self fishing',
  40 => 'Gameobject script near caster',
));

define ('SPELL_INTERRUPT_FLAGS', array (
  0x01 => 'Movement',
  0x02 => 'Damage',
  0x04 => 'Interrupt',
  0x08 => 'Autoattack',
  0x10 => 'Partial',
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

define ('CREATURE_STATIC_FLAGS', array (
       1 => 'Mountable',
       2 => 'No XP',
       4 => 'No loot',
       8 => 'Unkillable',
      16 => 'Tameable',
      32 => 'Immune player',
      64 => 'Immune NPC',
     128 => 'Can wield loot',
     256 => 'Sessile',
     512 => 'Unselectable',
    1024 => 'No auto regen',
    2048 => 'Corpse none',
    4096 => 'Corpse raid',
    8192 => 'Creator loot',
   16384 => 'No defense',
   32768 => 'No spell defense',
   65536 => 'Tabard vendor',
  131072 => 'Combat ping',
  262144 => 'Aquatic',
  524288 => 'Amphibious',
 1048576 => 'No melee',

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

define ('ITEM_STATS', array (
  0 => 'Mana',
  1 => 'Health',
  3 => 'Agility',
  4 => 'Strength',
  5 => 'Intellect',
  6 => 'Spirit',
  7 => 'Stamina',
));

define ('ITEM_QUALITY_COLOR', array (
    0 => '#9d9d9d',
    1 => '#ffc600',
    2 => '#1eff00',
    3 => '#0070dd',
    4 => '#a335ee',
    5 => '#ff0000',
    6 => '#f1e38a'
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
  1  => 'Two handed axe',
  2  => 'Bow',
  3  => 'Gun',
  4  => 'Mace',
  5  => 'Two handed mace',
  6  => 'Polearm',
  7  => 'Sword',
  8  => 'Twohanded sword',
  9  => 'Weapon - obsolete',
  10 => 'Staff',
  11 => 'Weapon exotic',
  12 => 'Weapon exotic2',
  13 => 'Fist weapon',
  14 => 'Misc weapon',
  15 => 'Dagger',
  16 => 'Thrown',
  17 => 'Spear',
  18 => 'Crossbow',
  19 => 'Wand',
  20 => 'Fishing pole',
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
  0x00 => 'Cannot Equip',
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

define ('BONDING', array (
  0 => 'No bind',
  1 => 'Binds when picked up',
  2 => 'Binds when equipped',
  3 => 'Binds when used',
  4 => 'Quest item',
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

define ('GAMEOBJECT_TYPE_CHEST', 3);

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

define ('SPELL_CAST_FLAGS', array (
  0x001 => 'Interrupt previous',
  0x002 => 'Triggered',
  0x004 => 'Force cast',
  0x008 => 'Main ranged spell',
  0x010 => 'Target unreachable',
  0x020 => 'Aura not present',
  0x040 => 'Only in melee',
  0x080 => 'Not in melee',
  0x100 => 'Target casting',
));

define ('TARGET_TYPE', array (
   0 => 'Provided target',
   1 => 'Hostile',
   2 => 'Hostile second aggro',
   3 => 'Hostile last aggro',
   4 => 'Hostile random',
   5 => 'Hostile random not top',
   6 => 'Owner or self',
   7 => 'Owner',
   8 => 'Nearest creature with entry',
   9 => 'Creature with guid',
  10 => 'Creature from instance data',
  11 => 'Nearest gameobject with entry',
  12 => 'Gameobject with guid',
  13 => 'Gameobject from instance data',
  14 => 'Friendly',
  15 => 'Friendly injured',
  16 => 'Friendly injured except provided target',
  17 => 'Friendly missing buff',
  18 => 'Friendly missing buff except provided target',
  19 => 'Friendly cc',
  20 => 'Map event source',
  21 => 'Map event target',
  22 => 'Map event extra target',
  23 => 'Nearest player',
  24 => 'Nearest hostile player',
  25 => 'Nearest friendly player',
  26 => 'Random creature with entry',
  27 => 'Random gameobject with entry',
));

// I got this from: https://mangoszero-docs.readthedocs.io/en/latest/database/world/quest-template.html

define ('QUEST_TYPE', array (
  0 	=> 'Normal',
  1 	=> 'Elite',
  21 	=> 'Life',
  41 	=> 'PvP',
  62 	=> 'Raid',
  81 	=> 'Dungeon',
  82 	=> 'World Event',
  83 	=> 'Legendary',
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
