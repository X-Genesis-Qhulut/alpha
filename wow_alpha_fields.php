<?php

/*
  Author: X'Genesis Qhulut <XGenesis-Qhulut@protonmail.com>
  Date:   August 2022

  See LICENSE for license details.
*/

// FIELD EXPANSION

function expandField ($value, $expandType)
  {
  global $skills, $spells, $factions, $npc_factions, $creatures, $quests, $game_objects, $maps;

  global $lastItemClass;

    switch ($expandType)
      {

      // not table lookups, just formatting
      case 'time':          tdxr (convertTimeGeneral ($value)); break;
      case 'time_secs':     tdxr (convertTimeGeneral ($value * 1000)); break;
      case 'gold':          tdxr (convertGold ($value)); break;
      case 'mask':          tdxr (getMask ($value)); break;

      // these things give hyperlinks
      case 'spell':         tdhr (lookupThing ($spells,     $value, 'show_spell'));  break;
      case 'creature':      tdhr (lookupThing ($creatures,  $value, 'show_creature'));  break;
      case 'quest':         tdhr (lookupThing ($quests,     abs($value), 'show_quest'));  break;
      case 'spell_visual':  tdhr (makeLink ($value, 'show_spell_visual'));  break;
      case 'item':          lookupItem ($value, 1); break;

      case 'creature_or_go':
                            if ($value < 0)
                              tdhr (lookupThing ($game_objects, -$value, 'show_go'));
                            else
                              tdhr (lookupThing ($creatures, $value, 'show_creature'));
                            break;

      // table lookups
      case 'spell_school':    tdxr (expandSimple (SPELL_SCHOOLS,   $value)); break;
      case 'spell_effect':    tdxr (expandSimple (SPELL_EFFECTS,   $value)); break;
      case 'spell_aura':      tdxr (expandSimple (SPELL_AURAS,     $value)); break;
      case 'spell_implicit_target':  tdxr (expandSimple (SPELL_IMPLICIT_TARGET, $value)); break;
      case 'power_type':      tdxr (expandSimple (POWER_TYPES,     $value)); break;
      case 'movement_type':   tdxr (expandSimple (MOVEMENT_TYPE,   $value)); break;
      case 'trainer_type':    tdxr (expandSimple (TRAINER_TYPE,    $value)); break;
      case 'bonding':         tdxr (expandSimple (BONDING,         $value)); break;
      case 'skill_type':      tdxr (expandSimple (SKILL_TYPES,     $value)); break;
      case 'rank':            tdxr (expandSimple (CREATURE_RANK,   $value)); break;
      case 'creature_type':   tdxr (expandSimple (CREATURE_TYPES,   $value)); break;
      case 'item_stats':      tdxr (expandSimple (ITEM_STATS,      $value)); break;
      case 'gameobject_type': tdxr (expandSimple (GAMEOBJECT_TYPE, $value)); break;
      case 'inventory_type':  tdxr (expandSimple (INVENTORY_TYPE,  $value)); break;
      case 'class'       :    tdxr (expandSimple (CLASSES,         $value)); break;
      case 'race'       :     tdxr (expandSimple (RACES,           $value)); break;
      case 'map':             tdxr (expandSimple ($maps,           $value)); break;
      case 'skill':           tdxr (expandSimple ($skills,         $value)); break;
      case 'quest_type':      tdxr (expandSimple (QUEST_TYPE,      $value)); break;
      case 'faction':         tdxr (getFaction ($value));                    break;
      case 'npc_faction':     tdxr (expandSimple ($npc_factions,   $value)); break;

      case 'item_class'   :   tdxr (getItemClass ($value));
                              $lastItemClass = $value;    // remember for when the subclass comes along
                              break;

      case 'item_subclass'   :      tdxr (getItemSubClass ($value)); break;

      // masks (ie. possible multiple results depending on the bits matching)
      case 'item_subclass_mask'   :    tdxr (expandItemSubclassMask ($lastItemClass, $value)); break;
      case 'race_mask':                tdxr (expandRaceMask ($value));              break;
      case 'class_mask':               tdxr (expandClassMask ($value));             break;
      case 'school_mask':              tdxr (expandMask (SPELL_SCHOOLS, $value));   break;
      case 'inhabit_type_mask':        tdxr (inhabitTypeMask ($value));             break;
      case 'mechanic_mask':            tdxr (expandMechanicMask ($value));          break;
      case 'flags_extra_mask':         tdxr (expandFlagsExtraMask ($value));        break;
      case 'npc_flags_mask':           tdxr (expandNpcFlagsMask ($value));          break;
      case 'item_flags_mask':          tdxr (expandItemFlagsMask ($value));         break;
      case 'spell_target_type_mask':   tdxr (expandSpellTargetTypeMask ($value));   break;
      case 'spell_attributes_mask':    tdxr (expandSpellAttributesMask ($value));   break;
      case 'spell_attributes_ex_mask': tdxr (expandSpellAttributesExMask ($value)); break;
      case 'creature_static_flags'   : tdxr (expandShiftedMask (CREATURE_STATIC_FLAGS, $value)); break;
      case 'quest_flags'   :           tdxr (expandShiftedMask (QUEST_FLAGS, $value)); break;
      case 'quest_special_flags'   :   tdxr (expandShiftedMask (QUEST_SPECIAL_FLAGS, $value)); break;
      case 'spell_interrupt_flags'   : tdxr (expandShiftedMask (SPELL_INTERRUPT_FLAGS, $value)); break;
      default:                         tdxr ("$expandType not known, id = $value"); break;

      } // end of switch

  } // end of expandField

?>
