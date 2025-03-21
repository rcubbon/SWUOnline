<?php

include "Constants.php";
include "GeneratedCode/GeneratedCardDictionaries.php";

/**
 * @param $cardName
 * @return string UUID of the card in question
 */
function CardIdFromName($cardName):string{
  return CardUUIDFromName(trim(strtolower($cardName)) . ";");
}

function CardName($cardID) {
  if(!$cardID || $cardID == "" || strlen($cardID) < 3) return "";
  return CardTitle($cardID) . " " . CardSubtitle($cardID);
}

function CardType($cardID)
{
  if(!$cardID) return "";
  $definedCardType = DefinedCardType($cardID);
  if($definedCardType == "Leader") return "C";
  else if($definedCardType == "Base") return "W";
  return "A";
}

function CardSubType($cardID)
{
  if(!$cardID) return "";
  return "";
  //return CardSubTypes($cardID);
}

function CharacterHealth($cardID)
{
  if($cardID == "DUMMY") return 1000;
  return CardLife($cardID);
}

function CharacterIntellect($cardID)
{
  switch($cardID) {
    default: return 4;
  }
}

function CardClass($cardID)
{
  return CardClasses($cardID);
}

function NumResources($player) {
  $resources = &GetResourceCards($player);
  return count($resources)/ResourcePieces();
}

function NumResourcesAvailable($player) {
  $resources = &GetResourceCards($player);
  $numAvailable = 0;
  for($i=0; $i<count($resources); $i+=ResourcePieces()) {
    if($resources[$i+4] == 0) ++$numAvailable;
  }
  return $numAvailable;
}

function CardTalent($cardID)
{
  $set = substr($cardID, 0, 3);
  if($set == "MON") return MONCardTalent($cardID);
  else if($set == "ELE") return ELECardTalent($cardID);
  else if($set == "UPR") return UPRCardTalent($cardID);
  else if($set == "DYN") return DYNCardTalent($cardID);
  else if($set == "ROG") return ROGUECardTalent($cardID);
  return "NONE";
}

function RestoreAmount($cardID, $player, $index)
{
  global $initiativePlayer, $currentTurnEffects;
  $amount = 0;
  $allies = &GetAllies($player);
  for($i=0; $i<count($allies); $i+=AllyPieces())
  {
    switch($allies[$i])
    {
      case "4919000710"://Home One
        if($index != $i) $amount += 1;
        break;
      default: break;
    }
  }
  $ally = new Ally("MYALLY-" . $index, $player);
  for($i=0; $i<count($currentTurnEffects); $i+=CurrentTurnPieces()) {
    if($currentTurnEffects[$i+1] != $player) continue;
    if($currentTurnEffects[$i+2] != -1 && $currentTurnEffects[$i+2] != $ally->UniqueID()) continue;
    switch($currentTurnEffects[$i]) {
      case "1272825113"://In Defense of Kamino
        if(TraitContains($ally->CardID(), "Republic", $player, $index)) $amount += 2;
        break;
      default: break;
    }
  }
  $upgrades = $ally->GetUpgrades();
  for($i=0; $i<count($upgrades); ++$i) {
    $upgradeCardID = $upgrades[$i];

    switch($upgradeCardID) {
      case "8788948272":
        $amount += 2;
        break;
      case "7884488904"://For The Republic
        $amount += IsCoordinateActive($player) ? 2 : 0;
        break;
    }
  }
  switch($cardID)
  {
    case "0074718689": $amount += 1; break;//Restored Arc 170
    case "1081012039": $amount += 2; break;//Regional Sympathizers
    case "1611702639": $amount += $initiativePlayer == $player ? 2 : 0; break;//Consortium Starviper
    case "4405415770": $amount += 2; break;//Yoda (Old Master)
    case "0827076106": $amount += 1; break;//Admiral Ackbar
    case "4919000710": $amount += 2; break;//Home One
    case "9412277544": $amount += 1; break;//Del Meeko
    case "e2c6231b35": $amount += !LeaderAbilitiesIgnored() ? 2 : 0; break;//Director Krennic Leader Unit
    case "7109944284": $amount += 3; break;//Luke Skywalker unit
    case "8142386948": $amount += 2; break;//Razor Crest
    case "4327133297": $amount += 2; break;//Moisture Farmer
    case "5977238053": $amount += 2; break;//Sundari Peacekeeper
    case "9503028597": $amount += 1; break;//Clone Deserter
    case "5511838014": $amount += 1; break;//Kuil
    case "e091d2a983": $amount += 3; break;//Rey
    case "7022736145": $amount += 2; break;//Tarfful
    case "6870437193": $amount += 2; break;//Twin Pod Cloud Car
    case "3671559022": $amount += 2; break;//Echo
    case "9185282472": $amount += 2; break;//ETA-2 Light Interceptor
    case "5350889336": $amount += 3; break;//AT-TE Vanguard
    case "3420865217": $amount += $ally->IsDamaged() ? 0 : 2; break;//Daughter of Dathomir
    case "6412545836": $amount += 1; break;//Morgan Elsbeth
    case "0268657344": $amount += 1; break;//Admiral Yularen
    case "e71f6f766c": $amount += 2; break;//Yoda
    case "3381931079": $amount += 2; break;//Malevolence
    case "4ae6d91ddc": $amount += 1; break;//Padme Amidala
    default: break;
  }
  if($amount > 0 && $ally->LostAbilities()) return 0;
  return $amount;
}

function ExploitAmount($cardID, $player, $reportMode=true) {
  global $currentTurnEffects;
  $amount = 0;
  for($i=count($currentTurnEffects)-CurrentTurnPieces(); $i>=0; $i-=CurrentTurnPieces()) {
    if($currentTurnEffects[$i+1] != $player) continue;
    $remove = false;
    switch($currentTurnEffects[$i]) {
      case "5683908835"://Count Dooku
        $amount += 1;
        $remove = true;
        break;
      case "6fa73a45ed"://Count Dooku Leader Unit
        if(TraitContains($cardID, "Separatist", $player)) {
          $amount += 3;
          $remove = true;
        }
        break;
      default: break;
    }
    if($remove) {
      if(!$reportMode) RemoveCurrentTurnEffect($i);
    }
  }
  switch($cardID) {
    case "6772128891": $amount += 2; break;//Hailfire Tank
    case "6623894685": $amount += 1; break;//Infiltrating Demolisher
    case "6700679522": $amount += 2; break;//Tri-Droid Suppressor
    case "8201333805": $amount += 3; break;//Squadron of Vultures
    case "9283787549": $amount += 3; break;//Separatist Super Tank
    case "3348783048": $amount += 2; break;//Geonosis Patrol Fighter
    case "2554988743": $amount += 3; break;//Gor
    case "1320229479": $amount += 2; break;//Multi-Troop Transport
    case "1083333786": $amount += 2; break;//Battle Droid Legion
    case "5243634234": $amount += 2; break;//Baktoid Spider Droid
    case "5084084838": $amount += 2; break;//Droideka Security
    case "6436543702": $amount += 2; break;//Providence Destroyer
    case "8655450523": $amount += 2; break;//Count Dooku
    case "0021045666": $amount += 3; break;//San Hill
    case "4210027426": $amount += 2; break;//Heavy Persuader Tank
    case "7013591351": $amount += 1; break;//Admiral Trench
    case "2565830105": $amount += 4; break;//Invastion of Christophsis
    case "2041344712": $amount += 3; break;//Osi Sobeck
    case "3381931079": $amount += 4; break;//Malevolence
    case "3556557330": $amount += 2; break;//Asajj Ventress
    case "3589814405": $amount += 2; break;//Tactical Droid Commander
    case "1167572655": $amount += 3; break;//Planetary Invasion
    default: break;
  }
  return $amount;
}

function RaidAmount($cardID, $player, $index, $reportMode = false)
{
  global $currentTurnEffects, $combatChain;
  if(count($combatChain) == 0 && !$reportMode) return 0;
  $amount = 0;
  $allies = &GetAllies($player);
  for($i=0; $i<count($allies); $i+=AllyPieces())
  {
    switch($allies[$i])
    {
      case "8995892693"://Red Three
        if($index != $i && AspectContains($cardID, "Heroism", $player)) $amount += 1;
        break;
      case "fb475d4ea4"://IG-88 Leader Unit
        if($index != $i) $amount += 1;
        break;
      default: break;
    }
  }
  $ally = new Ally("MYALLY-" . $index, $player);
  for($i=0; $i<count($currentTurnEffects); $i+=CurrentTurnPieces()) {
    if($currentTurnEffects[$i+1] != $player) continue;
    if($currentTurnEffects[$i+2] != -1 && $currentTurnEffects[$i+2] != $ally->UniqueID()) continue;
    switch($currentTurnEffects[$i]) {
      case "0256267292"://Benthic "Two Tubes"
        $amount += 2;
        break;
      case "1208707254"://Rallying Cry
        $amount += 2;
        break;
      case "8719468890"://Sword and Shielf Maneuver
        $amount += TraitContains($cardID, "Trooper", $player) ? 1 : 0;
        break;
      default: break;
    }
  }
  $upgrades = $ally->GetUpgrades();
  for($i=0; $i<count($upgrades); ++$i)
  {
    if($upgrades[$i] == "2007876522") $amount += 2;//Clone Cohort
  }
  switch($cardID)
  {
    case "1017822723": $amount += 2; break; //Rogue Operative
    case "2404916657": $amount += 2; break; //Cantina Braggart
    case "7495752423": $amount += 2; break; //Green Squadron A-Wing
    case "4642322279": $amount += SearchCount(SearchAllies($player, aspect:"Aggression")) > 1 ? 2 : 0; break;//Partisan Insurgent
    case "6028207223": $amount += 1; break; //Pirated Starfighter
    case "8995892693": $amount += 1; break; //Red Three
    case "3613174521": $amount += 1; break; //Outer Rim Headhunter
    case "4111616117": $amount += 1; break; //Volunteer Soldier
    case "87e8807695": $amount += !LeaderAbilitiesIgnored() ? 1 : 0; break; //Leia Leader Unit
    case "8395007579": $amount += $ally->MaxHealth() - $ally->Health(); break;//Fifth Brother
    case "6208347478": $amount += SearchCount(SearchAllies($player, trait:"Spectre")) > 1 ? 1 : 0; break;//Chopper
    case "3487311898": $amount += 3; break;//Clan Challengers
    case "5977238053": $amount += 2; break;//Sundari Peacekeeper
    case "1805986989": $amount += 2; break;//Modded Cohort
    case "415bde775d": $amount += 1; break;//Hondo Ohnaka
    case "724979d608": $amount += !LeaderAbilitiesIgnored() ? 2 : 0; break;//Cad Bane Leader Unit
    case "5818136044": $amount += 2; break;//Xanadu Blood
    case "8991513192": $amount += SearchCount(SearchAllies($player, aspect:"Aggression")) > 1 ? 2 : 0; break;//Hunting Nexu
    case "1810342362": $amount += 2; break;//Lurking TIE Phantom
    case "8426882030": $amount += 1; break;//Ryloth Militia
    case "5936350569": $amount += 1; break;//Jesse
    case "2800918480": $amount += 1; break;//Soldier of the 501st
    case "7494987248": $amount += IsCoordinateActive($player) ? 3 : 0; break;//Plo Koon
    case "5027991609": $amount += SearchCount(SearchAllies($player, trait:"Separatist")) > 1 ? 2 : 0; break;//Separatist Commando
    case "0354710662": $amount += 2; break;//Saw Gerrera
    case "0683052393": $amount += IsCoordinateActive($player) ? 2 : 0; break;//Hevy
    case "9964112400": $amount += 2; break;//Rush Clovis
    case "0249398533": $amount += 1; break;//Obedient Vanguard
    default: break;
  }
  if($amount > 0 && $ally->LostAbilities()) return 0;
  return $amount;
}

function HasSentinel($cardID, $player, $index)
{
  global $initiativePlayer, $currentTurnEffects;
  $ally = new Ally("MYALLY-" . $index, $player);
  if($ally->LostAbilities()) return false;
  $hasSentinel = false;
  for($i=0; $i<count($currentTurnEffects); $i+=CurrentTurnPieces()) {
    if($currentTurnEffects[$i+1] != $player) continue;
    if($currentTurnEffects[$i+2] != -1 && $currentTurnEffects[$i+2] != $ally->UniqueID()) continue;
    $effectParams = explode("_", $currentTurnEffects[$i]);
    $effectCardID = $effectParams[0];
    switch($effectCardID) {
      case "8294130780": $hasSentinel = true; break;//Gladiator Star Destroyer
      case "3572356139": $hasSentinel = true; break;//Chewbacca (Walking Carpet)
      case "3468546373": $hasSentinel = true; break;//General Rieekan
      case "2359136621": $hasSentinel = true; break;//Guarding The Way
      case "9070397522": return false;//SpecForce Soldier
      case "2872203891": $hasSentinel = true; break;//General Grievous
      case "fb7af4616c": $hasSentinel = true; break;//General Grievous
      case "1039828081": if ($cardID == "1039828081") {$hasSentinel = true;} break;//Calculating MagnaGuard
      case "3033790509": $hasSentinel = true; break;//Captain Typho
      case "8719468890"://Sword and Shielf Maneuver
        if(TraitContains($cardID, "Jedi", $player)) $hasSentinel = true;
        break;
      default: break;
    }
  }
  if($hasSentinel) return true;
  $upgrades = $ally->GetUpgrades();
  for($i=0; $i<count($upgrades); ++$i)
  {
    if($upgrades[$i] == "4550121827") return true;//Protector
    if($upgrades[$i] == "4991712618") return true;//Unshakeable Will
  }
  switch($cardID)
  {
    case "2524528997":
    case "6385228745":
    case "6912684909":
    case "7751685516":
    case "9702250295":
    case "6253392993":
    case "7596515127":
    case "5707383130":
    case "8918765832":
    case "4631297392":
    case "8301e8d7ef":
    case "4786320542":
    case "3896582249":
    case "2855740390":
    case "1982478444"://Vigilant Pursuit Craft
    case "1747533523"://Village Protectors
    case "6585115122"://The Mandalorian unit
    case "2969011922"://Pyke Sentinel
    case "8552719712"://Pirate Battle Tank
    case "4843225228"://Phase-III Dark Trooper
    case "7486516061"://Concord Dawn Interceptors
    case "6409922374"://Niima Outpost Constables
    case "0315522200"://Black Sun Starfighter
    case "8228196561"://Clan Saxon Gauntlet
      return true;
    case "2739464284"://Gamorrean Guards
      return SearchCount(SearchAllies($player, aspect:"Cunning")) > 1;
    case "3138552659"://Homestead Militia
      return NumResources($player) >= 6;
    case "7622279662"://Vigilant Honor Guards
      $ally = new Ally("MYALLY-" . $index, $player);
      return !$ally->IsDamaged();
    case "5879557998"://Baze Melbus
      return $initiativePlayer == $player;
    case "1780978508"://Emperor's Royal Guard
      return SearchCount(SearchAllies($player, trait:"Official")) > 0;
    case "9405733493"://Protector of the Throne
      $ally = new Ally("MYALLY-" . $index, $player);
      return $ally->IsUpgraded();
    case "4590862665"://Gamorrean Retainer
        return SearchCount(SearchAllies($player, aspect:"Command")) > 1;
    case "4341703515"://Supercommando Squad
      $ally = new Ally("MYALLY-" . $index, $player);
      return $ally->IsUpgraded();
    case "9871430123"://Sugi
      $otherPlayer = $player == 1 ? 2 : 1;
      return SearchCount(SearchAllies($otherPlayer, hasUpgradeOnly:true)) > 0;
    case "8845972926"://Falchion Ion Tank
      return true;
    case "8919416985"://Outspoken Representative
      return SearchCount(SearchAllies($player, trait:"Republic")) > 1;
    case "7884088000"://Armored Saber Tank
      return true;
    case "6330903136"://B2 Legionnaires
      return true;
    case "6257858302"://B1 Security Team
      return true;
    case "6238512843"://Republic Defense Carrier
      return true;
    case "4179773207"://Infantry of the 212th
      return IsCoordinateActive($player);
    case "9927473096"://Patrolling AAT
      return true;
    case "2554988743"://Gor
      return true;
    case "7289764651"://Duchess's Champion
      $otherPlayer = $player == 1 ? 2 : 1;
      return IsCoordinateActive($otherPlayer);
    case "5084084838"://Droideka Security
      return true;
    case "0ee1e18cf4"://Obi-wan Kenobi
      return true;
    default: return false;
  }
}

function HasGrit($cardID, $player, $index)
{
  global $currentTurnEffects;
  $ally = new Ally("MYALLY-" . $index, $player);
  if($ally->LostAbilities()) return false;
  if(!IsLeader($ally->CardID(), $player)) {
    $allies = &GetAllies($player);
    for ($i = 0; $i < count($allies); $i += AllyPieces()) {
      switch ($allies[$i]) {
        case "4783554451"://First Light
          return true;
        default:
          break;
      }
    }
  }
  for($i=0; $i<count($currentTurnEffects); $i+=CurrentTurnPieces()) {
    if($currentTurnEffects[$i+1] != $player) continue;
    if($currentTurnEffects[$i+2] != -1 && $currentTurnEffects[$i+2] != $ally->UniqueID()) continue;
    switch($currentTurnEffects[$i]) {
      case "6669050232": return true;//Grim Resolve
      default: break;
    }
  }
  switch($cardID)
  {
    case "5335160564":
    case "9633997311":
    case "8098293047":
    case "5879557998":
    case "4599464590":
    case "8301e8d7ef":
    case "5557494276"://Death Watch Loyalist
    case "6878039039"://Hylobon Enforcer
    case "8190373087"://Gentle Giant
    case "1304452249"://Covetous Rivals
    case "4383889628"://Wroshyr Tree Tender
    case "0252207505"://Synara San
    case "4783554451"://First Light
    case "4aa0804b2b"://Qi'Ra
    case "1477806735"://Wookiee Warrior
    case "9195624101"://Heroic Renegade
    case "5169472456"://Chewbacca Pykesbane
    case "8552292852"://Kashyyyk Defender
    case "6787851182"://Dwarf Spider Droid
    case "2761325938"://Devastating Gunship
      return true;
    case "9832122703"://Luminara Unduli
      return IsCoordinateActive($player);
    default:
      return false;
  }
}

function HasCoordinate($cardID, $player, $index)
{
  $ally = new Ally("MYALLY-" . $index, $player);
  if($ally->LostAbilities()) return false;
  $upgrades = $ally->GetUpgrades();
  for ($i = 0; $i < count($upgrades); ++$i) {
    if($upgrades[$i] == "7884488904") return true;//For the republic
  }
  return match ($cardID) {
    "2260777958",//41st Elite Corps
    "9832122703",//Luminara Unduli
    "4179773207",//Infantry of the 212th
    "7200475001",//Ki-Adi-Mundi
    "2265363405",//Echo
    "9966134941",//Pelta Supply Frigate
    "6190335038",//Aayla Secura
    "7380773849",//Coruscant Guard
    "9017877021",//Clone Commander Cody
    "2282198576",//Anakin Skywalker
    "9227411088",//Clone Heavy Gunner
    "2298508689",//Reckless Torrent
    "0683052393",//Hevy
    "1641175580",//Kit Fisto
    "8307804692",//Padme Abmidala
    "7494987248",//Plo Koon
    "5445166624",//Clone Dive Trooper
    "4512764429",//Sanctioner's Shuttle
    "1209133362",//332nd Stalwart
    "8187818742",//Republic Commando
    "7224a2074a",//Ahsoka Tano
    "4ae6d91ddc" => true,//Padme Amidala
    default => false,
  };
}

function HasOverwhelm($cardID, $player, $index)
{
  global $defPlayer, $currentTurnEffects, $mainPlayer;
  $ally = new Ally("MYALLY-" . $index, $player);
  if($ally->LostAbilities()) return false;
  $allies = &GetAllies($player);
  for($i=0; $i<count($allies); $i+=AllyPieces())
  {
    switch($allies[$i])
    {
      case "4484318969"://Moff Gideon Leader Unit
        if(CardCost($cardID) <= 3 && IsAllyAttackTarget()) return !LeaderAbilitiesIgnored();
      case "40b649e6f6"://Maul Leader Unit
        if($index != $i) return !LeaderAbilitiesIgnored();
      case "9017877021"://Clone Commander Cody
        if($index != $i && IsCoordinateActive($player)) return true;
      default: break;
    }
  }
  for($i=0; $i<count($currentTurnEffects); $i+=CurrentTurnPieces()) {
    if($currentTurnEffects[$i+1] != $player) continue;
    if($currentTurnEffects[$i+2] != -1 && $currentTurnEffects[$i+2] != $ally->UniqueID()) continue;
    switch($currentTurnEffects[$i]) {
      case "4085341914": return true;//Heroic Resolve
      case "6461101372": return !LeaderAbilitiesIgnored();//Maul Leader
      case "1167572655": return true;//Planetary Invasion
      default: break;
    }
  }
  // Check upgrades
  $upgrades = $ally->GetUpgrades();
  for($i=0; $i<count($upgrades); ++$i) {
    if($upgrades[$i] == "0875550518") return true;//Grievous's Wheel Bike
    if($upgrades[$i] == "4886127868") return true;//Nameless Valor
  }
  switch($cardID)
  {//TODO: overwhelm comments
    case "6072239164":
    case "6577517407":
    case "6718924441":
    case "9097316363":
    case "3232845719":
    case "4631297392":
    case "6432884726":
    case "5557494276"://Death Watch Loyalist
    case "2470093702"://Wrecker
    case "4721657243"://Kihraxz Heavy Fighter
    case "5351496853"://Gideon's Light Cruiser
    case "4935319539"://Krayt Dragon
    case "8862896760"://Maul
    case "9270539174"://Wild Rancor
    case "3803148745"://Ruthless Assassin
    case "1743599390"://Trandoshan Hunters
    case "c9ff9863d7"://Hunter (Outcast Sergeant)
    case "9752523457"://Finalizer
      return true;
    case "4619930426"://First Legion Snowtrooper
      $target = GetAttackTarget();
      if($target == "THEIRCHAR-0") return false;
      $targetAlly = new Ally($target, $defPlayer);
      return $targetAlly->IsDamaged();
    case "3487311898"://Clan Challengers
      return $ally->IsUpgraded();
    case "6769342445"://Jango Fett (Renowned Bounty Hunter)
      if(IsAllyAttackTarget() && $mainPlayer == $player) {
        $targetAlly = new Ally(GetAttackTarget(), $defPlayer);
        if($targetAlly->HasBounty()) return true;
      }
      return false;
    case "8640210306"://Advanced Recon Commando
    case "8084593619"://Dendup's Loyalist
    case "6330903136"://B2 Legionnaires
    case "2554988743"://Gor
    case "3693364726"://Aurra Sing
    case "3476041913"://Low Altitude Gunship
    case "8655450523"://Count Dooku (Fallen Jedi)
    case "9017877021"://Clone Commander Cody
      return true;
    case "4484318969"://Moff Gideon Leader Unit
    case "24a81d97b5"://Anakin Skywalker Leader Unit
    case "6fa73a45ed"://Count Dooku Leader Unit
    case "40b649e6f6"://Maul Leader Unit
      return !LeaderAbilitiesIgnored();
    case "8139901441"://Bo-Katan Kryze
      return SearchCount(SearchAllies($player, trait:"Mandalorian")) > 1;
    default: return false;
  }
}

function HasAmbush($cardID, $player, $index, $from)
{
  if ($cardID == "0345124206") return false; //Clone - Prevent bugs related to ECL and Timely.

  global $currentTurnEffects;
  $ally = new Ally("MYALLY-" . $index, $player);
  for($i=count($currentTurnEffects)-CurrentTurnPieces(); $i>=0; $i-=CurrentTurnPieces()) {
    if($currentTurnEffects[$i+1] != $player) continue;
    if($currentTurnEffects[$i+2] != -1 && $currentTurnEffects[$i+2] != $ally->UniqueID()) continue;
    switch($currentTurnEffects[$i]) {
      case "8327910265":
        AddDecisionQueue("REMOVECURRENTEFFECT", $player, "8327910265");
        return true;//Energy Conversion Lab (ECL)
      case "6847268098"://Timely Intervention
        AddDecisionQueue("REMOVECURRENTEFFECT", $player, "6847268098");
        return true;
      case "0911874487"://Fennec Shand
        AddDecisionQueue("REMOVECURRENTEFFECT", $player, "0911874487");
        return true;
      case "2b13cefced"://Fennec Shand
        AddDecisionQueue("REMOVECURRENTEFFECT", $player, "2b13cefced");
        return true;
      default: break;
    }
  }
  $allies = &GetAllies($player);
  for($i=0; $i<count($allies); $i+=AllyPieces())
  {
    switch($allies[$i])
    {
      case "4566580942"://Admiral Piett
        if(CardCost($cardID) >= 6 && DefinedCardType($cardID) == "Unit" && $from != "EQUIP") return true;
        break;
      case "4339330745"://Wedge Antilles
        if(TraitContains($cardID, "Vehicle", $player)) return true;
        break;
      case "6097248635"://4-LOM
        if(CardTitle($cardID) == "Zuckuss") return true;
        break;
      default: break;
    }
  }
  switch($cardID)
  {
    case "5346983501"://Syndicate Lackeys
    case "6718924441"://Mercenary Company
    case "7285270931"://Auzituck Liberator Gunship
    case "3377409249"://Rogue Squadron Skirmisher
    case "5230572435"://Mace Windu (Party Crasher)
    case "0052542605"://Bossk (Deadly Stalker)
    case "2649829005"://Agent Kallus
    case "1862616109"://Snowspeeder
    case "3684950815"://Bounty Hunter Crew
    case "9500514827"://Han Solo (Reluctant Hero)
    case "8506660490"://Darth Vader unit
    case "1805986989"://Modded Cohort
    case "7171636330"://Chain Code Collector
    case "7982524453"://Fennec Shand
    case "8862896760"://Maul
    case "2143627819"://The Marauder
    case "2121724481"://Cloud-Rider
    case "8107876051"://Enfys Nest
    case "6097248635"://4-LOM
    case "9483244696"://Weequay Pirate Gang
    case "1086021299"://Arquitens Assault Cruiser
      return true;
    case "2027289177"://Escort Skiff
      return SearchCount(SearchAllies($player, aspect:"Command")) > 1;
    case "4685993945"://Frontier AT-RT
      return SearchCount(SearchAllies($player, trait:"Vehicle")) > 1;
    case "5752414373"://Millennium Falcon
      return $from == "HAND";
    case "7953154930"://Hidden Sharpshooter
      return true;
    case "1988887369"://Phase II Clone Trooper
      return true;
    case "4824842849"://Subjugating Starfighter
      return true;
    case "2554988743"://Gor
      return true;
    case "7494987248"://Plo Koon
      return true;
    case "7380773849"://Coruscant Guard
      return IsCoordinateActive($player);
    case "6999668340"://Droid Commando
      return SearchCount(SearchAllies($player, trait:"Separatist")) > 1;
    case "5243634234"://Baktoid Spider Droid
      return true;
    case "7144880397"://Ahsoka Tano
      return HasMoreUnits($player == 1 ? 2 : 1);
    default: return false;
  }
}

function HasShielded($cardID, $player, $index)
{
  switch($cardID)
  {
    case "b0dbca5c05"://Iden Versio Leader Unit
      return !LeaderAbilitiesIgnored();
    case "0700214503"://Crafty Smuggler
    case "5264521057"://Wilderness Fighter
    case "9950828238"://Seventh Fleet Defender
    case "9459170449"://Cargo Juggernaut
    case "6931439330"://The Ghost
    case "9624333142"://Count Dooku
    case "3280523224"://Rukh
    case "7728042035"://Chimaera
    case "7870435409"://Bib Fortuna
    case "6135081953"://Doctor Evazan
    case "1747533523"://Village Protectors
    case "1090660242"://The Client
    case "5080989992"://Rose Tico
    case "0598830553"://Dryden Vos
    case "6635692731"://Hutt's Henchman
    case "4341703515"://Supercommando Squad
      return true;
    case "6939947927"://Hunter of the Haxion Brood
      $otherPlayer = $player == 1 ? 2 : 1;
      return SearchCount(SearchAllies($otherPlayer, hasBountyOnly:true)) > 0;
    case "0088477218"://Privateer Scyk
      return SearchCount(SearchAllies($player, aspect:"Cunning")) > 1;
    default: return false;
  }
}

function HasSaboteur($cardID, $player, $index)
{
  global $currentTurnEffects;
  $ally = new Ally("MYALLY-" . $index, $player);
  if($ally->LostAbilities()) return false;
  for($i=0; $i<count($currentTurnEffects); $i+=CurrentTurnPieces()) {
    if($currentTurnEffects[$i+1] != $player) continue;
    if($currentTurnEffects[$i+2] != -1 && $currentTurnEffects[$i+2] != $ally->UniqueID()) continue;
    switch($currentTurnEffects[$i]) {
      case "4663781580": return true;//Swoop Down
      case "9210902604": return true;//Precision Fire
      case "4910017138": return true;//Breaking In
      case "5610901450": return true;//Heroes on Both Sides
      default: break;
    }
  }
  $upgrades = $ally->GetUpgrades();
  for($i=0; $i<count($upgrades); ++$i)
  {
    if($upgrades[$i] == "0797226725") return true;//Infiltrator's Skill
  }
  $allies = &GetAllies($player);
  for($i=0; $i<count($allies); $i+=AllyPieces())
  {
    switch($allies[$i])
    {
      case "1690726274"://Zuckuss
        if(CardTitle($cardID) == "4-LOM") return true;
        break;
      default: break;
    }
  }
  switch($cardID)
  {
    case "1017822723"://Rogue Operative
    case "9859536518"://Jawa Scavenger
    case "0046930738"://Rebel Pathfinder
    case "7533529264"://Wolffe
    case "1746195484"://Jedha Agitator
    case "5907868016"://Fighters for Freedom
    case "0828695133"://Seventh Sister
    case "9250443409"://Lando Calrissian (Responsible Businessman)
    case "3c60596a7a"://Cassian Andor (Dedicated to the Rebellion)
    case "1690726274"://Zuckuss
    case "4595532978"://Ketsu Onyo
    case "3786602643"://House Kast Soldier
    case "2b13cefced"://Fennec Shand
    case "7922308768"://Valiant Assault Ship
    case "2151430798"://Guavian Antagonizer
    case "2556508706"://Resourceful Pursuers
    case "2965702252"://Unlicensed Headhunter
    case "6404471739"://Senatorial Corvette
    case "4050810437"://Droid Starfighter
    case "3600744650"://Bold Recon Commando
    case "6623894685"://Infiltrating Demolisher
    case "1641175580"://Kit Fisto
    case "8414572243"://Enfys Nest
    case "3434956158"://Fives
      return true;
    case "8187818742"://Republic Commando
      return IsCoordinateActive($player);
    case "11299cc72f"://Pre Viszla
      $hand = &GetHand($player);
      if(count($hand)/HandPieces() >= 3) return true;
      break;
    case "8139901441"://Bo-Katan Kryze
      return SearchCount(SearchAllies($player, trait:"Mandalorian")) > 1;
    case "7099699830"://Jyn Erso
      global $CS_NumAlliesDestroyed;
      $otherPlayer = $player == 1 ? 2 : 1;
      return GetClassState($otherPlayer, $CS_NumAlliesDestroyed) > 0;
    default: return false;
  }
  return false;
}

function MemoryCost($cardID, $player)
{
  $cost = CardMemoryCost($cardID);
  switch($cardID)
  {
    case "s23UHXgcZq": if(IsClassBonusActive($player, "ASSASSIN")) --$cost; break;//Luxera's Map
    default: break;
  }
  $allies = &GetAllies($player);
  for($i=0; $i<count($allies); $i+=AllyPieces())
  {
    switch($allies[$i])
    {
      case "kk39i1f0ht": if(CardType($cardID) == "C") --$cost; break;//Academy Guide
      default: break;
    }
  }
  return $cost;
}

function AbilityCost($cardID, $index=-1, $theirCard = false)
{
  global $currentPlayer;
  $abilityName = $theirCard ? GetOpponentControlledAbilityNames($cardID) : GetResolvedAbilityName($cardID);
  if($abilityName == "Heroic Resolve") return 2;
  switch($cardID) {
    case "2579145458"://Luke Skywalker
      return $abilityName == "Give Shield" ? 1 : 0;
    case "2912358777"://Grand Moff Tarkin
      return $abilityName == "Give Experience" ? 1 : 0;
    case "3187874229"://Cassian Andor
      return $abilityName == "Draw Card" ? 1 : 0;
    case "4300219753"://Fett's Firespray
      return $abilityName == "Exhaust" ? 2 : 0;
    case "3258646001"://Steadfast Senator
      return $abilityName == "Buff" ? 2 : 0;
    case "0595607848"://Disaffected Senator
      return $abilityName == "Deal Damage" ? 2 : 0;
    case "5784497124"://Emperor Palpatine
      return $abilityName == "Deal Damage" ? 1 : 0;
    case "6088773439"://Darth Vader
      return $abilityName == "Deal Damage" ? 1 : 0;
    case "1951911851"://Grand Admiral Thrawn
      return $abilityName == "Exhaust" ? 1 : 0;
    case "1885628519"://Crosshair
      return $abilityName == "Buff" ? 2 : 0;
    case "2432897157"://Qi'Ra
      return $abilityName == "Shield" ? 1 : 0;
    case "4352150438"://Rey
      return $abilityName == "Experience" ? 1 : 0;
    case "0911874487"://Fennec Shand
      return $abilityName == "Ambush" ? 1 : 0;
    case "8709191884"://Hunter (Outcast Sergeant)
      return $abilityName == "Replace Resource" ? 1 : 0;
    case "3577961001"://Mercenary Gunship
      return $abilityName == "Take Control" ? 4 : 0;
    case "5157630261"://Compassionate Senator
      return $abilityName == "Heal" ? 2 : 0;
    case "9262288850"://Independent Senator
      return $abilityName == "Exhaust" ? 2 : 0;
    case "5081383630"://Pre Viszla
      return $abilityName == "Deal Damage" ? 1 : 0;
    case "4628885755"://Mace Windu
      return $abilityName == "Deal Damage" ? 1 : 0;
    case "7734824762"://Captain Rex
      return $abilityName == "Clone" ? 2 : 0;
    case "2870878795"://Padme Amidala
      return $abilityName == "Draw" ? 1 : 0;
    default: break;
  }
  if(IsAlly($cardID)) return 0;
  return 0;
}

function DynamicCost($cardID)
{
  global $currentPlayer;
  switch($cardID) {
    case "2639435822"://Force Lightning
      if(SearchCount(SearchAllies($currentPlayer, trait:"Force")) > 0) return "1,2,3,4,5,6,7,8,9,10";
      return "1";
    case "2267524398"://The Clone Wars
      return "2,3,4,5,6,7,8,9,10,11,12,13,14,15,16,17,18,19,20";
    default: return "";
  }
}

function PitchValue($cardID)
{
  return 0;
}

function BlockValue($cardID)
{
  return 0;
}

function AttackValue($cardID)
{
  global $combatChainState, $CCS_NumBoosted, $mainPlayer, $currentPlayer;
  if(!$cardID) return "";
  switch($cardID)
  {
    case "4897501399": return 2;//Ruthlessness
    case "7687006104": return 1;//Foundling
    case "5738033724": return 2;//Boba Fett's Armor
    case "3514010297": return 1;//Mandalorian Armor
    case "4843813137": return 1;//Brutal Traditions
    case "3141660491": return 4;//The Darksaber
    case "4886127868": return 2;//Nameless Valor
    default: return CardPower($cardID);
  }
}

function HasGoAgain($cardID)
{
  return true;
}

function GetAbilityType($cardID, $index = -1, $from="-")
{
  global $currentPlayer;
  if($from == "PLAY" && IsAlly($cardID)) return "AA";
  switch($cardID)
  {
    case "2569134232"://Jedha City
      return "A";
    case "1393827469"://Tarkintown
      return "A";
    case "2429341052"://Security Complex
      return "A";
    case "8327910265"://Energy Conversion Lab (ECL)
      return "A";
    case "4626028465"://Boba Fett Leader
    case "7440067052"://Hera Syndulla Leader
    case "8560666697"://Director Krennic Leader
      if(LeaderAbilitiesIgnored()) return "";
      $char = &GetPlayerCharacter($currentPlayer);
      return $char[CharacterPieces() + 2] == 0 ? "A" : "";
    default: return "";
  }
}

function GetOpponentAbilityTypes($cardID, $index = -1, $from="-") {
  $abilityTypes = "";
  switch($cardID) {
    case "3577961001": {
      $abilityTypes = "A";
    }
  }
  return $abilityTypes;
}

function GetAbilityTypes($cardID, $index = -1, $from="-")
{
  global $currentPlayer;
  $abilityTypes = "";
  switch($cardID) {
    case "2554951775"://Bail Organa
      $abilityTypes = "A,AA";
      break;
    case "2756312994"://Alliance Dispatcher
      $abilityTypes = "A,AA";
      break;
    case "3572356139"://Chewbacca (Walking Carpet)
      $abilityTypes = LeaderAbilitiesIgnored() ? "" : "A";
      break;
    case "2579145458"://Luke Skywalker
      $abilityTypes = LeaderAbilitiesIgnored() ? "" : "A";
      break;
    case "2912358777"://Grand Moff Tarkin
      $abilityTypes = LeaderAbilitiesIgnored() ? "" : "A";
      break;
    case "3187874229"://Cassian Andor
      $abilityTypes = LeaderAbilitiesIgnored() ? "" : "A";
      break;
    case "4841169874"://Sabine Wren
      $abilityTypes = LeaderAbilitiesIgnored() ? "" : "A";
      break;
    case "2048866729"://Iden Versio
      $abilityTypes = LeaderAbilitiesIgnored() ? "" : "A";
      break;
    case "6088773439"://Darth Vader
      $abilityTypes = LeaderAbilitiesIgnored() ? "" : "A";
      break;
    case "4263394087"://Chirrut Imwe
      $abilityTypes = LeaderAbilitiesIgnored() ? "" : "A";
      break;
    case "1480894253"://Kylo Ren
      $abilityTypes = LeaderAbilitiesIgnored() ? "" : "A";
      break;
    case "5630404651"://MagnaGuard Wing Leader
      $abilityTypes = "A,AA";
      break;
    case "4300219753"://Fett's Firespray
      $abilityTypes = "A,AA";
      break;
    case "0595607848"://Disaffected Senator
      $abilityTypes = "A,AA";
      break;
    case "7911083239"://Grand Inquisitor
      $abilityTypes = LeaderAbilitiesIgnored() ? "" : "A";
      break;
    case "2870878795"://Padme Amidala
      $abilityTypes = LeaderAbilitiesIgnored() ? "" : "A";
      break;
    case "2872203891"://General Grievious
      $abilityTypes = LeaderAbilitiesIgnored() ? "" : "A";
      break;
    case "6064906790"://Nute Gunray
      $abilityTypes = LeaderAbilitiesIgnored() ? "" : "A";
      break;
    case "2847868671"://Yoda
      $abilityTypes = LeaderAbilitiesIgnored() ? "" : "A";
      break;
    case "1686059165"://Wat Tambor
      $abilityTypes = LeaderAbilitiesIgnored() ? "" : "A";
      break;
    case "0026166404"://Chancellor Palpatine Leader
    case "ad86d54e97"://Darth Sidious Leader
      $abilityTypes = LeaderAbilitiesIgnored() ? "" : "A";
      break;
    case "7734824762"://Captain Rex
      $abilityTypes = LeaderAbilitiesIgnored() ? "" : "A";
      break;
    case "4628885755"://Mace Windu
      $abilityTypes = LeaderAbilitiesIgnored() ? "" : "A";
      break;
    case "5683908835"://Count Dooku
      $abilityTypes = LeaderAbilitiesIgnored() ? "" : "A";
      break;
    case "5081383630"://Pre Viszla
      $abilityTypes = LeaderAbilitiesIgnored() ? "" : "A";
      break;
    case "2155351882"://Ahsoka Tano
      $abilityTypes = IsCoordinateActive($currentPlayer) && !LeaderAbilitiesIgnored() ? "A" : "";
      break;
    case "6461101372"://Maul
      $abilityTypes = LeaderAbilitiesIgnored() ? "" : "A";
      break;
    case "8929774056"://Asajj Ventress
      $abilityTypes = LeaderAbilitiesIgnored() ? "" : "A";
      break;
    case "2784756758"://Obi-wan Kenobi
      $abilityTypes = LeaderAbilitiesIgnored() ? "" : "A";
      break;
    case "8777351722"://Anakin Skywalker
      $abilityTypes = LeaderAbilitiesIgnored() ? "" : "A";
      break;
    case "5954056864"://Han Solo
      $abilityTypes = LeaderAbilitiesIgnored() ? "" : "A";
      break;
    case "6514927936"://Leia Organa
      $abilityTypes = LeaderAbilitiesIgnored() ? "" : "A";
      break;
    case "8244682354"://Jyn Erso
      $abilityTypes = LeaderAbilitiesIgnored() ? "" : "A";
      break;
    case "8600121285"://IG-88
      $abilityTypes = LeaderAbilitiesIgnored() ? "" : "A";
      break;
    case "7870435409"://Bib Fortuna
      $abilityTypes = "A,AA";
      break;
    case "5784497124"://Emperor Palpatine
      $allies = &GetAllies($currentPlayer);
      if(count($allies) == 0) break;
      $abilityTypes = LeaderAbilitiesIgnored() ? "" : "A";
      break;
    case "8117080217"://Admiral Ozzel
      $abilityTypes = "A,AA";
      break;
    case "2471223947"://Frontline Shuttle
      $abilityTypes = "A,AA";
      break;
    case "1951911851"://Grand Admiral Thrawn
      $abilityTypes = LeaderAbilitiesIgnored() ? "" : "A";
      break;
    case "6722700037"://Doctor Pershing
      $abilityTypes = "A,AA";
      break;
    case "6536128825"://Grogu
      $abilityTypes = "A,AA";
      break;
    case "3258646001"://Steadfast Senator
      $abilityTypes = "A,AA";
      break;
    case "9262288850"://Independent Senator
      $abilityTypes = "A,AA";
      break;
    case "1090660242"://The Client
      $abilityTypes = "A,AA";
      break;
    case "1885628519"://Crosshair
      $abilityTypes = "A,A,AA";
      break;
    case "2503039837"://Moff Gideon
      $abilityTypes = LeaderAbilitiesIgnored() ? "" : "A";
      break;
    case "2526288781"://Bossk
      $abilityTypes = LeaderAbilitiesIgnored() ? "" : "A";
      break;
    case "7424360283"://Bo-Katan Kryze
      $abilityTypes = LeaderAbilitiesIgnored() ? "" : "A";
      break;
    case "5440730550"://Lando Calrissian
      $abilityTypes = LeaderAbilitiesIgnored() ? "" : "A";
      break;
    case "040a3e81f3"://Lando Leader Unit
      $abilityTypes = LeaderAbilitiesIgnored() ? "AA": "A,AA";
      break;
    case "2432897157"://Qi'Ra
      $abilityTypes = LeaderAbilitiesIgnored() ? "" : "A";
      break;
    case "4352150438"://Rey
      $abilityTypes = LeaderAbilitiesIgnored() ? "" : "A";
      break;
    case "0911874487"://Fennec Shand
      $abilityTypes = LeaderAbilitiesIgnored() ? "" : "A";
      break;
    case "2b13cefced"://Fennec Shand Unit
      $abilityTypes = "A,AA";
      break;
    case "9226435975"://Han Solo Red
      $abilityTypes = LeaderAbilitiesIgnored() ? "" : "A";
      break;
    case "a742dea1f1"://Han Solo Red Unit
      $abilityTypes = "A,AA";
      break;
    case "2744523125"://Salacious Crumb
      $abilityTypes = "A,AA";
      break;
    case "5157630261"://Compassionate Senator
      $abilityTypes = "A,AA";
      break;
    case "0622803599"://Jabba the Hutt
      $abilityTypes = LeaderAbilitiesIgnored() ? "" : "A";
      break;
    case "f928681d36"://Jabba the Hutt Leader Unit
      $abilityTypes = LeaderAbilitiesIgnored()? "AA" : "A,AA";
      break;
    case "9596662994"://Finn
      $abilityTypes = LeaderAbilitiesIgnored() ? "" : "A";
      break;
    case "8709191884"://Hunter (Outcast Sergeant)
      $abilityTypes = LeaderAbilitiesIgnored() ? "" : "A";
      break;
    default: break;
  }
  if(IsAlly($cardID, $currentPlayer)) {
    if($abilityTypes == "") $abilityTypes = "AA";
    $ally = new Ally("MYALLY-" . $index, $currentPlayer);
    $upgrades = $ally->GetUpgrades();
    for($i=0; $i<count($upgrades); ++$i) {
      switch($upgrades[$i]) {
        case "4085341914"://Heroic Resolve
          if($abilityTypes != "") $abilityTypes .= ",";
          $abilityTypes .= "A";
          break;
        case "2397845395"://Strategic Acumen
          if($abilityTypes != "") $abilityTypes .= ",";
          $abilityTypes .= "A";
          break;
        default: break;
      }
    }

    if (AnyPlayerHasAlly("6384086894")) { //Satine Kryze
      $abilityTypes = "A," . $abilityTypes;
    }
  }
  else if(DefinedTypesContains($cardID, "Leader", $currentPlayer)) {
    $char = &GetPlayerCharacter($currentPlayer);
    if($char[CharacterPieces() + 1] == 1) $abilityTypes = "";
    if($char[CharacterPieces() + 2] == 0) {
      if($abilityTypes != "") $abilityTypes .= ",";
      $abilityTypes .= "A";
    }
  }

  return $abilityTypes;
}

function IsLeader($cardID, $playerID = "") {
  return DefinedTypesContains($cardID, "Leader", $playerID);
}

function GetAbilityNames($cardID, $index = -1, $validate=false)
{
  global $currentPlayer;
  $abilityNames = "";
  switch ($cardID) {
    case "2554951775"://Bail Organa
      $abilityNames = "Give Experience,Attack";
      break;
    case "2756312994"://Alliance Dispatcher
      $abilityNames = "Play Unit,Attack";
      break;
    case "3572356139"://Chewbacca (Walking Carpet)
      $abilityNames = LeaderAbilitiesIgnored() ? "" : "Play Taunt";
      break;
    case "2579145458"://Luke Skywalker
      $abilityNames = LeaderAbilitiesIgnored() ? "" : "Give Shield";
      break;
    case "2912358777"://Grand Moff Tarkin
      $abilityNames = LeaderAbilitiesIgnored() ? "" : "Give Experience";
      break;
    case "3187874229"://Cassian Andor
      $abilityNames = LeaderAbilitiesIgnored() ? "" : "Draw Card";
      break;
    case "4841169874"://Sabine Wren
      $abilityNames = LeaderAbilitiesIgnored() ? "" : "Deal Damage";
      break;
    case "2048866729"://Iden Versio
      $abilityNames = LeaderAbilitiesIgnored() ? "" : "Heal";
      break;
    case "6088773439"://Darth Vader
      $abilityNames = LeaderAbilitiesIgnored() ? "" : "Deal Damage";
      break;
    case "4263394087"://Chirrut Imwe
      $abilityNames = LeaderAbilitiesIgnored() ? "" : "Buff HP";
      break;
    case "1480894253"://Kylo Ren
      $abilityNames = LeaderAbilitiesIgnored() ? "" : "Buff Attack";
      break;
    case "5630404651"://MagnaGuard Wing Leader
      $ally = new Ally("MYALLY-" . $index, $currentPlayer);
      if($validate) $abilityNames = $ally->IsExhausted() ? "Droid Attack" : "Droid Attack,Attack";
      else $abilityNames = "Droid Attack,Attack";
      break;
    case "4300219753"://Fett's Firespray
      $ally = new Ally("MYALLY-" . $index, $currentPlayer);
      if($validate) $abilityNames = $ally->IsExhausted() ? "Exhaust" : "Exhaust,Attack";
      else $abilityNames = "Exhaust,Attack";
      break;
    case "0595607848"://Disaffected Senator
      $abilityNames = "Deal Damage,Attack";
      break;
    case "7911083239"://Grand Inquisitor
      $abilityNames = LeaderAbilitiesIgnored() ? "" : "Deal Damage";
      break;
    case "2870878795"://Padme Amidala
      $abilityNames = LeaderAbilitiesIgnored() ? "" : "Draw";
      break;
    case "2872203891"://General Grievious
      $abilityNames = LeaderAbilitiesIgnored() ? "" : "Sentinel";
      break;
    case "6064906790"://Nute Gunray
      $abilityNames = LeaderAbilitiesIgnored() ? "" : "Droid";
      break;
    case "2847868671"://Yoda
      $abilityNames = LeaderAbilitiesIgnored() ? "" : "Draw";
      break;
    case "1686059165"://Wat Tambor
      $abilityNames = LeaderAbilitiesIgnored() ? "" : "Buff";
      break;
    case "7734824762"://Captain Rex
      $abilityNames = LeaderAbilitiesIgnored() ? "" : "Clone";
      break;
    case "0026166404"://Chancellor Palpatine Leader
    case "ad86d54e97"://Darth Sidious Leader
      $abilityNames = LeaderAbilitiesIgnored() ? "" : "Activate";
      break;
    case "4628885755"://Mace Windu
      $abilityNames = LeaderAbilitiesIgnored() ? "" : "Deal Damage";
      break;
    case "5683908835"://Count Dooku
      $abilityNames = LeaderAbilitiesIgnored() ? "" : "Exploit";
      break;
    case "5081383630"://Pre Viszla
      $abilityNames = LeaderAbilitiesIgnored() ? "" : "Deal Damage";
      break;
    case "2155351882"://Ahsoka Tano
      $abilityNames = IsCoordinateActive($currentPlayer) && !LeaderAbilitiesIgnored() ? "Attack" : "";
      break;
    case "6461101372"://Maul
      $abilityNames = LeaderAbilitiesIgnored() ? "" : "Attack";
      break;
    case "8929774056"://Asajj Ventress
      $abilityNames = LeaderAbilitiesIgnored() ? "" : "Attack";
      break;
    case "2784756758"://Obi-wan Kenobi
      $abilityNames = LeaderAbilitiesIgnored() ? "" : "Heal";
      break;
    case "8777351722"://Anakin Skywalker
      $abilityNames = LeaderAbilitiesIgnored() ? "" : "Attack";
      break;
    case "5954056864"://Han Solo
      $abilityNames = LeaderAbilitiesIgnored() ? "" : "Play Resource";
      break;
    case "6514927936"://Leia Organa
      $abilityNames = LeaderAbilitiesIgnored() ? "" : "Attack";
      break;
    case "8244682354"://Jyn Erso
      $abilityNames = LeaderAbilitiesIgnored() ? "" : "Attack";
      break;
    case "8600121285"://IG-88
      $abilityNames = LeaderAbilitiesIgnored() ? "" : "Attack";
      break;
    case "7870435409"://Bib Fortuna
      $abilityNames = "Play Event,Attack";
      break;
    case "5784497124"://Emperor Palpatine
      $allies = &GetAllies($currentPlayer);
      if(count($allies) == 0) break;
      $abilityNames = LeaderAbilitiesIgnored() ? "" : "Deal Damage";
      break;
    case "8117080217"://Admiral Ozzel
      $abilityNames = "Play Imperial Unit,Attack";
      break;
    case "2471223947"://Frontline Shuttle
      $abilityNames = "Shuttle,Attack";
      break;
    case "1951911851"://Grand Admiral Thrawn
      $abilityNames = LeaderAbilitiesIgnored() ? "" : "Exhaust";
      break;
    case "6722700037"://Doctor Pershing
      $abilityNames = "Draw,Attack";
      break;
    case "6536128825"://Grogu
      $abilityNames = "Exhaust,Attack";
      break;
    case "3258646001"://Steadfast Senator
      $abilityNames = "Buff,Attack";
      break;
    case "9262288850"://Independent Senator
      $abilityNames = "Exhaust,Attack";
      break;
    case "1090660242"://The Client
      $abilityNames = "Bounty,Attack";
      break;
    case "1885628519"://Crosshair
      $abilityNames = "Buff,Snipe,Attack";
      break;
    case "2503039837"://Moff Gideon
      $abilityNames = LeaderAbilitiesIgnored() ? "" : "Attack";
      break;
    case "2526288781"://Bossk
      $abilityNames = LeaderAbilitiesIgnored() ? "" : "Deal Damage/Buff";
      break;
    case "7424360283"://Bo-Katan Kryze
      $abilityNames = LeaderAbilitiesIgnored() ? "" : "Deal Damage";
      break;
    case "5440730550"://Lando Calrissian
      $abilityNames = LeaderAbilitiesIgnored() ? "" : "Smuggle";
      break;
    case "040a3e81f3"://Lando Leader Unit
      $abilityNames = LeaderAbilitiesIgnored() ? "Attack" : "Smuggle,Attack";
      break;
    case "2432897157"://Qi'Ra
      $abilityNames = LeaderAbilitiesIgnored() ? "" : "Shield";
      break;
    case "4352150438"://Rey
      $abilityNames = LeaderAbilitiesIgnored() ? "" : "Experience";
      break;
    case "0911874487"://Fennec Shand
      $abilityNames = LeaderAbilitiesIgnored() ? "" : "Ambush";
      break;
    case "2b13cefced"://Fennec Shand Unit
      $abilityNames = "Ambush,Attack";
      break;
    case "9226435975"://Han Solo Red
      $abilityNames = LeaderAbilitiesIgnored() ? "" : "Play";
      break;
    case "a742dea1f1"://Han Solo Red Unit
      $abilityNames = "Play,Attack";
      break;
    case "2744523125"://Salacious Crumb
      $abilityNames = "Bounce,Attack";
      break;
    case "5157630261"://Compassionate Senator
      $abilityNames = "Heal,Attack";
      break;
    case "0622803599"://Jabba the Hutt
      $abilityNames = LeaderAbilitiesIgnored() ? "" : "Bounty";
      break;
    case "f928681d36"://Jabba the Hutt Leader Unit
      $abilityNames = LeaderAbilitiesIgnored() ? "Attack" : "Bounty,Attack";
      break;
    case "9596662994"://Finn
      $abilityNames = LeaderAbilitiesIgnored() ? "" : "Shield";
      break;
    case "8709191884"://Hunter (Outcast Sergeant)
      $abilityNames = LeaderAbilitiesIgnored() ? "" : "Replace Resource";
      break;
    default: break;
  }
  if(IsAlly($cardID, $currentPlayer)) {
    if($abilityNames == "") $abilityNames = "Attack";
    $ally = new Ally("MYALLY-" . $index, $currentPlayer);
    $upgrades = $ally->GetUpgrades();
    for($i=0; $i<count($upgrades); ++$i) {
      switch($upgrades[$i]) {
        case "4085341914"://Heroic Resolve
          if($abilityNames != "") $abilityNames .= ",";
          $abilityNames .= "Heroic Resolve";
          break;
        case "2397845395"://Strategic Acumen
          if($abilityNames != "") $abilityNames .= ",";
          $abilityNames .= "Strategic Acumen";
          break;
        default: break;
      }
    }

    if (AnyPlayerHasAlly("6384086894")) { //Satine Kryze
      $abilityNames = "Mill," . $abilityNames;
    }
  }
  else if(DefinedTypesContains($cardID, "Leader", $currentPlayer)) {
    $char = &GetPlayerCharacter($currentPlayer);
    if($char[CharacterPieces() + 1] == 1) $abilityNames = "";
    if($char[CharacterPieces() + 2] == 0) {
      //Chancellor Palpatine Leader + Darth Sidious Leader
      if($char[CharacterPieces()] != "0026166404" && $char[CharacterPieces()] != "ad86d54e97") {
        if($abilityNames != "") $abilityNames .= ",";
        $abilityNames .= "Deploy";
      }
    }
  }

  return $abilityNames;
}

function GetOpponentControlledAbilityNames($cardID) {
  $abilityNames = "";

  switch($cardID) {
    case "3577961001":
      $abilityNames = "Take Control";
      break;
    default: break;
  }

  return $abilityNames;
}

function GetAbilityIndex($cardID, $index, $abilityName, $theirCard = false)
{
  $abilityName = str_replace("_", " ", $abilityName);
  $names = explode(",", GetAbilityNames($cardID, $index));
  for($i = 0; $i < count($names); ++$i) {
    if($abilityName == $names[$i]) return $i;
  }
  return 0;
}

function GetResolvedAbilityType($cardID, $from="-", $theirCard = false)
{
  global $currentPlayer, $CS_AbilityIndex, $CS_PlayIndex;
  if($from == "HAND") return "";
  $abilityIndex = GetClassState($currentPlayer, $CS_AbilityIndex);
  $abilityTypes = GetAbilityTypes($cardID, GetClassState($currentPlayer, $CS_PlayIndex));
  if($abilityTypes == "" || $abilityIndex == "-") return GetAbilityType($cardID, -1, $from);
  $abilityTypes = explode(",", $abilityTypes);
  return $theirCard ? "A" : $abilityTypes[$abilityIndex]; //This will need to be updated if there are ever non-action abilities that can be activated on opponent's cards.
}

function GetResolvedAbilityName($cardID, $from="-")
{
  global $currentPlayer, $CS_AbilityIndex, $CS_PlayIndex;
  if($from != "PLAY" && $from != "EQUIP" && $from != "-") return "";
  $abilityIndex = GetClassState($currentPlayer, $CS_AbilityIndex);
  $abilityNames = GetAbilityNames($cardID, GetClassState($currentPlayer, $CS_PlayIndex));
  if($abilityNames == "" || $abilityIndex == "-") return "";
  $abilityNames = explode(",", $abilityNames);
  return $abilityNames[$abilityIndex];
}

function IsPlayable($cardID, $phase, $from, $index = -1, &$restriction = null, $player = "")
{
  global $currentPlayer, $CS_NumActionsPlayed, $combatChainState, $CCS_BaseAttackDefenseMax, $CS_NumNonAttackCards, $CS_NumAttackCards;
  global $CCS_ResourceCostDefenseMin, $actionPoints, $mainPlayer, $defPlayer;
  global $combatChain;
  if($from == "ARS" || $from == "BANISH") return false;
  if($player == "") $player = $currentPlayer;
  if($phase == "P" && $from == "HAND") return true;
  if(IsPlayRestricted($cardID, $restriction, $from, $index, $player)) return false;
  $cardType = CardType($cardID);
  if($cardType == "W" || $cardType == "AA")
  {
    $char = &GetPlayerCharacter($player);
    if($char[1] != 2) return false;//Can't attack if rested
  }
  $otherPlayer = ($player == 1 ? 2 : 1);
  if($from == "HAND" && ((CardCost($cardID) + SelfCostModifier($cardID, $from, reportMode: true) + CurrentEffectCostModifiers($cardID, $from, reportMode:true) + CharacterCostModifier($cardID, $from) - ExploitAmount($cardID, $currentPlayer) * 2) > NumResourcesAvailable($currentPlayer)) && !HasAlternativeCost($cardID)) return false;
  if($from == "RESOURCES") {
    if(!PlayableFromResources($cardID, index:$index)) return false;
    if((SmuggleCost($cardID, index:$index) + SelfCostModifier($cardID, $from, reportMode:true)) > NumResourcesAvailable($currentPlayer) && !HasAlternativeCost($cardID)) return false;
    if(!SmuggleAdditionalCosts($cardID)) return false;
  }
  if(DefinedTypesContains($cardID, "Upgrade", $player) && SearchCount(SearchAllies($player)) == 0 && SearchCount(SearchAllies($otherPlayer)) == 0) return false;
  if($phase == "M" && $from == "HAND") return true;
  if($phase == "M" && $from == "GY") {
    $discard = &GetDiscard($player);
    if($discard[$index] == "4843813137") return true;//Brutal Traditions
    return str_starts_with($discard[$index+1], "TT");
  }
  $isStaticType = IsStaticType($cardType, $from, $cardID);
  if($isStaticType) {
    $cardType = GetAbilityType($cardID, $index, $from);
    if($cardType == "") {
      $abilityTypes = GetAbilityTypes($cardID, $index);
      $typeArr = explode(",", $abilityTypes);
      $cardType = $typeArr[0];
    }
  }
  if($phase == "M" && ($cardType == "A" || $cardType == "AA" || $cardType == "I")) return true;
  if($cardType == "I" && ($phase == "INSTANT" || $phase == "A" || $phase == "D")) return true;
  return false;

}

function HasAlternativeCost($cardID) {
  switch($cardID) {
    case "9644107128"://Bamboozle
      return true;
    default: return false;
  }
}

//Preserve
function GoesWhereAfterResolving($cardID, $from = null, $player = "", $playedFrom="", $resourcesPaid="", $additionalCosts="")
{
  global $currentPlayer, $mainPlayer;
  if($player == "") $player = $currentPlayer;
  if(DefinedTypesContains($cardID, "Upgrade", $currentPlayer)) return "ATTACHTARGET";
  if(IsAlly($cardID)) return "ALLY";
  switch($cardID) {
    case "2703877689": return "RESOURCE";//Resupply
    default: return "GY";
  }
}


function UpgradeFilter($cardID)
{
  switch($cardID) {
    case "0160548661"://Fallen Lightsaber
    case "8495694166"://Jedi Lightsaber
    case "0705773109"://Vader's Lightsaber
    case "6903722220"://Luke's Lightsaber
    case "1323728003"://Electrostaff
    case "3514010297"://Mandalorian Armor
    case "3525325147"://Vambrace Grappleshot
    case "5874342508"://Hotshot DL-44 Blaster
    case "0754286363"://The Mandalorian's Rifle
    case "5738033724"://Boba Fett's Armor
    case "6471336466"://Vambrace Flamethrower
    case "3141660491"://The Darksaber
    case "6775521270"://Inspiring Mentor
    case "6117103324"://Jetpack
    case "7280804443"://Hold-Out Blaster
    case "6410481716"://Mace Windu's Lightsaber
    case "0414253215"://General's Blade
    case "0741296536"://Ahsoka's Padawan Lightsaber
    case "0875550518"://Grievous's Wheel Bike
      return "trait=Vehicle";
    case "3987987905"://Hardpoint Heavy Blaster
    case "7280213969"://Smuggling Compartment
      return "trait!=Vehicle";
    case "8055390529"://Traitorous
      return "maxCost=3";
    case "1368144544"://Imprisoned
    case "7718080954"://Frozen in Carbonite
    case "6911505367"://Second Chance
    case "7270736993"://Unrefusable Offer
      return "leader=1";
    case "4886127868"://Nameless Valor
      return "token=0";
    default: return "";
  }
}

function CanPlayInstant($phase)
{
  if($phase == "M") return true;
  if($phase == "A") return true;
  if($phase == "D") return true;
  if($phase == "INSTANT") return true;
  return false;
}

function IsCloned($uniqueID) {
  $ally = new Ally($uniqueID);
  return $ally->IsCloned();
}

function IsToken($cardID)
{
  switch($cardID) {
    case "8752877738": return true;
    case "2007868442": return true;
    case "3463348370": return true;//Battle Droid
    case "3941784506": return true;//Clone Trooper
    default: return false;
  }
}

function IsPitchRestricted($cardID, &$restriction, $from = "", $index = -1)
{
  global $playerID;
  return false;
}

function IsPlayRestricted($cardID, &$restriction, $from = "", $index = -1, $player = "")
{
  global $currentPlayer, $mainPlayer;
  if($player == "") $player = $currentPlayer;
  $otherPlayer = ($currentPlayer == 1 ? 2 : 1);
  switch($cardID) {
    case "0523973552"://I Am Your Father
      $theirAllies = &GetAllies($otherPlayer);
      return count($theirAllies) == 0;
    default: return false;
  }
}

function IsDefenseReactionPlayable($cardID, $from)
{
  global $combatChain, $mainPlayer;
  if(CurrentEffectPreventsDefenseReaction($from)) return false;
  return true;
}

function IsAction($cardID)
{
  $cardType = CardType($cardID);
  if($cardType == "A" || $cardType == "AA") return true;
  $abilityType = GetAbilityType($cardID);
  if($abilityType == "A" || $abilityType == "AA") return true;
  return false;
}

function GoesOnCombatChain($phase, $cardID, $from, $theirCard = false)
{
  global $layers;
  if($theirCard) return false;
  if($phase != "B" && $from == "EQUIP" || $from == "PLAY") $cardType = GetResolvedAbilityType($cardID, $from, $theirCard);
  else $cardType = CardType($cardID);
  if($cardType == "I") return false; //Instants as yet never go on the combat chain
  if($phase == "B" && count($layers) == 0) return true; //Anything you play during these combat phases would go on the chain
  if(($phase == "A" || $phase == "D") && $cardType == "A") return false; //Non-attacks played as instants never go on combat chain
  if($cardType == "AR") return true;
  if($cardType == "DR") return true;
  if(($phase == "M" || $phase == "ATTACKWITHIT") && $cardType == "AA") return true; //If it's an attack action, it goes on the chain
  return false;
}

function IsStaticType($cardType, $from = "", $cardID = "")
{
  if($cardType == "C" || $cardType == "E" || $cardType == "W") return true;
  if($from == "PLAY") return true;
  if($cardID != "" && $from == "BANISH" && AbilityPlayableFromBanish($cardID)) return true;
  return false;
}

function LeaderUnit($cardID) {
  switch($cardID) {
    //Spark of Rebellion
    case "3572356139"://Chewbacca (Walking Carpet)
      return "8301e8d7ef";
    case "2579145458"://Luke Skywalker
      return "0dcb77795c";
    case "2912358777"://Grand Moff Tarkin
      return "59cd013a2d";
    case "3187874229"://Cassian Andor
      return "3c60596a7a";
    case "4841169874"://Sabine Wren
      return "51e8757e4c";
    case "2048866729"://Iden Versio
      return "b0dbca5c05";
    case "6088773439"://Darth Vader
      return "0ca1902a46";
    case "4263394087"://Chirrut Imwe
      return "d1a7b76ae7";
    case "4626028465"://Boba Fett
      return "0e65f012f5";
    case "7911083239"://Grand Inquisitor
      return "6827598372";
    case "5954056864"://Han Solo
      return "5e90bd91b0";
    case "6514927936"://Leia Organa
      return "87e8807695";
    case "8244682354"://Jyn Erso
      return "20f21b4948";
    case "8600121285"://IG-88
      return "fb475d4ea4";
    case "5784497124"://Emperor Palpatine
      return "6c5b96c7ef";
    case "8560666697"://Director Krennic
      return "e2c6231b35";
    case "7440067052"://Hera Syndulla
      return "80df3928eb";
    case "1951911851"://Grand Admiral Thrawn
      return "02199f9f1e";
    //Shadows of the Galaxy
    case "1480894253"://Kylo Ren
      return "8def61a58e";
    case "2503039837"://Moff Gideon Leader
      return "4484318969";
    case "3045538805"://Hondo Ohnaka
      return "415bde775d";
    case "2526288781"://Bossk
      return "d2bbda6982";
    case "7424360283"://Bo-Katan Kryze
      return "a579b400c0";
    case "5440730550"://Lando Calrissian
      return "040a3e81f3";
    case "1384530409"://Cad Bane
      return "724979d608";
    case "2432897157"://Qi'Ra
      return "4aa0804b2b";
    case "4352150438"://Rey
      return "e091d2a983";
    case "9005139831"://The Mandalorian
      return "4088c46c4d";
    case "0911874487"://Fennec Shand
      return "2b13cefced";
    case "9794215464"://Gar Saxon
      return "3feee05e13";
    case "9334480612"://Boba Fett (Daimyo)
      return "919facb76d";
    case "0254929700"://Doctor Aphra
      return "58f9f2d4a0";
    case "9226435975"://Han Solo Red
      return "a742dea1f1";
    case "0622803599"://Jabba the Hutt
      return "f928681d36";
    case "9596662994"://Finn
      return "8903067778";
    case "8709191884"://Hunter (Outcast Sergeant)
      return "c9ff9863d7";
    //Twilight of the Republic
    case "8777351722"://Anakin Skywalker
      return "24a81d97b5";
    case "2784756758"://Obi-wan Kenobi
      return "0ee1e18cf4";
    case "8929774056"://Asajj Ventress
      return "f8e0c65364";
    case "6461101372"://Maul
      return "40b649e6f6";
    case "2155351882"://Ahsoka Tano
      return "7224a2074a";
    case "5081383630"://Pre Viszla
      return "11299cc72f";
    case "5683908835"://Count Dooku
      return "6fa73a45ed";
    case "2358113881"://Quinlan Vos
      return "3f7f027abd";
    case "4628885755"://Mace Windu
      return "9b212e2eeb";
    case "7734824762"://Captain Rex
      return "47557288d6";
    case "9155536481"://Jango Fett
      return "cfdcbd005a";
    case "1686059165"://Wat Tambor
      return "12122bc0b1";
    case "2742665601"://Nala Se
      return "f05184bd91";
    case "2847868671"://Yoda
      return "e71f6f766c";
    case "6064906790"://Nute Gunray
      return "b7caecf9a3";
    case "2872203891"://General Grievious
      return "fb7af4616c";
    case "2870878795"://Padme Amidala
      return "4ae6d91ddc";
    default: return "";
  }
}

function LeaderUndeployed($cardID) {
  switch($cardID) {
    //Spark of Rebellion
    case "8301e8d7ef"://Chewbacca (Walking Carpet)
      return "3572356139";
    case "0dcb77795c"://Luke Skywalker
      return "2579145458";
    case "59cd013a2d"://Grand Moff Tarkin
      return "2912358777";
    case "3c60596a7a"://Cassian Andor
      return "3187874229";
    case "51e8757e4c"://Sabine Wren
      return "4841169874";
    case "b0dbca5c05"://Iden Versio
      return "2048866729";
    case "0ca1902a46"://Darth Vader
      return "6088773439";
    case "d1a7b76ae7"://Chirrut Imwe
      return "4263394087";
    case "0e65f012f5"://Boba Fett
      return "4626028465";
    case "6827598372"://Grand Inquisitor
      return "7911083239";
    case "5e90bd91b0"://Han Solo
      return "5954056864";
    case "87e8807695"://Leia Organa
      return "6514927936";
    case "20f21b4948"://Jyn Erso
      return "8244682354";
    case "fb475d4ea4"://IG-88
      return "8600121285";
    case "6c5b96c7ef"://Emperor Palpatine
      return "5784497124";
    case "e2c6231b35"://Director Krennic
      return "8560666697";
    case "80df3928eb"://Hera Syndulla
      return "7440067052";
    case "02199f9f1e"://Grand Admiral Thrawn
      return "1951911851";
    //Shadows of the Galaxy
    case "8def61a58e"://Kylo Ren
      return "1480894253";
    case "4484318969"://Moff Gideon Leader
      return "2503039837";
    case "415bde775d"://Hondo Ohnaka
      return "3045538805";
    case "d2bbda6982"://Bossk
      return "2526288781";
    case "a579b400c0"://Bo-Katan Kryze
      return "7424360283";
    case "040a3e81f3"://Lando Calrissian
      return "5440730550";
    case "724979d608"://Cad Bane
      return "1384530409";
    case "4aa0804b2b"://Qi'Ra
      return "2432897157";
    case "e091d2a983"://Rey
      return "4352150438";
    case "4088c46c4d"://The Mandalorian
      return "9005139831";
    case "2b13cefced"://Fennec Shand
      return "0911874487";
    case "3feee05e13"://Gar Saxon
      return "9794215464";
    case "919facb76d"://Boba Fett (Daimyo)
      return "9334480612";
    case "58f9f2d4a0"://Doctor Aphra
      return "0254929700";
    case "a742dea1f1"://Han Solo Red
      return "9226435975";
    case "f928681d36"://Jabba the Hutt
      return "0622803599";
    case "8903067778"://Finn
      return "9596662994";
    case "c9ff9863d7"://Hunter (Outcast Sergeant)
      return "8709191884";
    //Twilight of the Republic
    case "24a81d97b5"://Anakin Skywalker
      return "8777351722";
    case "0ee1e18cf4"://Obi-wan Kenobi
      return "2784756758";
    case "f8e0c65364"://Asajj Ventress
      return "8929774056";
    case "40b649e6f6"://Maul
      return "6461101372";
    case "7224a2074a"://Ahsoka Tano
      return "2155351882";
    case "11299cc72f"://Pre Viszla
      return "5081383630";
    case "6fa73a45ed"://Count Dooku
      return "5683908835";
    case "3f7f027abd"://Quinlan Vos
      return "2358113881";
    case "9b212e2eeb"://Mace Windu
      return "4628885755";
    case "47557288d6"://Captain Rex
      return "7734824762";
    case "cfdcbd005a"://Jango Fett
      return "9155536481";
    case "12122bc0b1"://Wat Tambor
      return "1686059165";
    case "f05184bd91"://Nala Se
      return "2742665601";
    case "e71f6f766c"://Yoda
      return "2847868671";
    case "b7caecf9a3"://Nute Gunray
      return "6064906790";
    case "fb7af4616c"://General Grievious
      return "2872203891";
    case "4ae6d91ddc"://Padme Amidala
      return "2870878795";
    default: return "";
  }
}

function HasAttackAbility($cardID) {
  switch($cardID) {
    case "1746195484"://Jedha Agitator
    case "5707383130"://Bendu
    case "1862616109"://Snowspeeder
    case "3613174521"://Outer Rim Headhunter
    case "4599464590"://Rugged Survivors
    case "4299027717"://Mining Guild Tie Fighter
    case "7728042035"://Chimaera
    case "8691800148"://Reinforcement Walker
    case "9568000754"://R2-D2
    case "8009713136"://C-3PO
    case "7533529264"://Wolffe
    case "5818136044"://Xanadu Blood
    case "1304452249"://Covetous Rivals
    case "3086868510"://Pre Vizsla
    case "8380936981"://Jabba's Rancor
    case "1503633301"://Survivors' Gauntlet
    case "8240629990"://Avenger
    case "6931439330"://The Ghost
    case "3468546373"://General Rieekan
      return true;
    default: return false;
  }
}

function CardHP($cardID) {
  switch($cardID)
  {
    case "5738033724": return 2;//Boba Fett's Armor
    case "8877249477": return 2;//Legal Authority
    case "7687006104": return 1;//Foundling
    case "3514010297": return 3;//Mandalorian Armor
    case "4843813137": return 2;//Brutal Traditions
    case "3141660491": return 3;//The Darksaber
    case "4886127868": return 2;//Nameless Valor
    default: return CardHPDictionary($cardID);
  }
}

function HasBladeBreak($cardID)
{
  global $defPlayer;
  switch($cardID) {

    default: return false;
  }
}

function HasBattleworn($cardID)
{
  switch($cardID) {

    default: return false;
  }
}

function HasTemper($cardID)
{
  switch($cardID) {

    default: return false;
  }
}

function RequiresDiscard($cardID)
{
  switch($cardID) {

    default: return false;
  }
}

function ETASteamCounters($cardID)
{
  switch ($cardID) {

    default: return 0;
  }
}

function AbilityHasGoAgain($cardID)
{
  return true;
}

function DoesEffectGrantDominate($cardID)
{
  global $combatChainState;
  switch ($cardID) {

    default: return false;
  }
}

function CharacterNumUsesPerTurn($cardID)
{
  switch ($cardID) {

    default: return 1;
  }
}

//Active (2 = Always Active, 1 = Yes, 0 = No)
function CharacterDefaultActiveState($cardID)
{
  switch ($cardID) {

    default: return 2;
  }
}

//Hold priority for triggers (2 = Always hold, 1 = Hold, 0 = Don't Hold)
function AuraDefaultHoldTriggerState($cardID)
{
  switch ($cardID) {

    default: return 2;
  }
}

function ItemDefaultHoldTriggerState($cardID)
{
  switch($cardID) {

    default: return 2;
  }
}

function IsCharacterActive($player, $index)
{
  $character = &GetPlayerCharacter($player);
  return $character[$index + 9] == "1";
}

function HasReprise($cardID)
{
  switch($cardID) {

    default: return false;
  }
}

//Is it active AS OF THIS MOMENT?
function RepriseActive()
{
  global $currentPlayer, $mainPlayer;
  return 0;
}

function HasCombo($cardID)
{
  switch ($cardID) {
    case "WTR081": case "WTR083": case "WTR084": case "WTR085": case "WTR086": case "WTR087":
    case "WTR088": case "WTR089": case "WTR090": case "WTR091": case "WTR095": case "WTR096":
    case "WTR097": case "WTR104": case "WTR105": case "WTR106": case "WTR110": case "WTR111": case "WTR112":
      return true;
    case "CRU054": case "CRU055": case "CRU056": case "CRU057": case "CRU058": case "CRU059":
    case "CRU060": case "CRU061": case "CRU062":
      return true;
    case "EVR038": case "EVR040": case "EVR041": case "EVR042": case "EVR043":
      return true;
    case "DYN047":
    case "DYN056": case "DYN057": case "DYN058":
    case "DYN059": case "DYN060": case "DYN061":
      return true;
    case "OUT050":
    case "OUT051":
    case "OUT056": case "OUT057": case "OUT058":
    case "OUT059": case "OUT060": case "OUT061":
    case "OUT062": case "OUT063": case "OUT064":
    case "OUT065": case "OUT066": case "OUT067":
    case "OUT074": case "OUT075": case "OUT076":
    case "OUT080": case "OUT081": case "OUT082":
      return true;
  }
  return false;
}

function ComboActive($cardID = "")
{
  global $combatChainState, $combatChain, $chainLinkSummary, $mainPlayer;
  if ($cardID == "" && count($combatChain) > 0) $cardID = $combatChain[0];
  if ($cardID == "") return false;
  if(count($chainLinkSummary) == 0) return false;//No combat active if no previous chain links
  $lastAttackNames = explode(",", $chainLinkSummary[count($chainLinkSummary)-ChainLinkSummaryPieces()+4]);
  for($i=0; $i<count($lastAttackNames); ++$i)
  {
    $lastAttackName = GamestateUnsanitize($lastAttackNames[$i]);
    switch ($cardID) {

      default: break;
    }
  }
  return false;
}

function SmuggleCost($cardID, $player="", $index="")
{
  global $currentPlayer;
  if($player == "") $player = $currentPlayer;
  $minCost = -1;
  switch($cardID) {
    case "1982478444": $minCost = 7; break;//Vigilant Pursuit Craft
    case "0866321455": $minCost = 3; break;//Smuggler's Aid
    case "6037778228": $minCost = 5; break;//Night Owl Skirmisher
    case "2288926269": $minCost = 6; break;//Privateer Crew
    case "5752414373": $minCost = 6; break;//Millennium Falcon
    case "8552719712": $minCost = 7; break;//Pirate Battle Tank
    case "2522489681": $minCost = 6; break;//Zorii Bliss
    case "4534554684": $minCost = 4; break;//Freetown Backup
    case "9690731982": $minCost = 3; break;//Reckless Gunslinger
    case "5874342508": $minCost = 3; break;//Hotshot DL-44 Blaster
    case "3881257511": $minCost = 4; break;//Tech
    case "5830140660": $minCost = 4; break;//Bazine Netal
    case "8645125292": $minCost = 3; break;//Covert Strength
    case "4783554451": $minCost = 7; break;//First Light
    case "6847268098": $minCost = 2; break;//Timely Intervention
    case "5632569775": $minCost = 5; break;//Lom Pyke
    case "9552605383": $minCost = 4; break;//L3-37
    case "1312599620": $minCost = 4; break;//Smuggler's Starfighter
    case "8305828130": $minCost = 4; break;//Warbird Stowaway
    case "9483244696": $minCost = 5; break;//Weequay Pirate Gang
    case "5171970586": $minCost = 3; break;//Collections Starhopper
    case "6234506067": $minCost = 5; break;//Cassian Andor
    case "5169472456": $minCost = 9; break;//Chewbacca Pykesbane
    case "9871430123": $minCost = 6; break;//Sugi
    case "9040137775": $minCost = 6; break;//Principled Outlaw
    case "1938453783": $minCost = 4; break;//Armed to the Teeth
    case "1141018768": $minCost = 3; break;//Commission
    case "4002861992": $minCost = 7; break;//DJ (Blatant Thief)
    case "6117103324": $minCost = 4; break;//Jetpack
    case "7204838421": $minCost = 6; break;//Enterprising Lackeys
    case "3010720738": $minCost = 5; break;//Tobias Beckett
    default: break;
  }
  $allies = &GetAllies($player);
  for($i=0; $i<count($allies); $i+=AllyPieces())
  {
    switch($allies[$i]) {
      case "3881257511"://Tech
        $techAlly = new Ally("MYALLY-" . $allies[$i], $player);
        if(!$techAlly->LostAbilities()) {
          $cost = CardCost($cardID)+2;
          if($minCost == -1 || $minCost > $cost) $minCost = $cost;
        }
        break;
      default: break;
    }
  }
  return $minCost;
}

function SmuggleAdditionalCosts($cardID, $player = ""): bool {
  global $currentPlayer;
  if($player == "") $player = $currentPlayer;
  switch($cardID) {
    case "4783554451"://First Light
      return count(GetAllies($player)) > 0;
    default: return true;
  }
}

function isBountyRecollectable($cardID) {
  switch ($cardID) {
    case "7642980906"://Stolen Landspeeder
    case "7270736993"://Unrefusable Offer
      return false;
    default: return true;
  }
}

function PlayableFromBanish($cardID, $mod="")
{
  global $currentPlayer, $CS_NumNonAttackCards, $CS_Num6PowBan;
  $mod = explode("-", $mod)[0];
  if($mod == "TCL" || $mod == "TT" || $mod == "TTFREE" || $mod == "TCC" || $mod == "NT" || $mod == "INST") return true;
  switch($cardID) {

    default: return false;
  }
}

function PlayableFromResources($cardID, $player="", $index="") {
  global $currentPlayer;
  if($player == "") $player = $currentPlayer;
  if(SmuggleCost($cardID, $player, $index) > 0) return true;
  switch($cardID) {
    default: return false;
  }
}

function AbilityPlayableFromBanish($cardID)
{
  global $currentPlayer, $mainPlayer;
  switch($cardID) {
    default: return false;
  }
}

function RequiresDieRoll($cardID, $from, $player)
{
  global $turn;
  if(GetDieRoll($player) > 0) return false;
  if($turn[0] == "B") return false;
  return false;
}

function IsSpecialization($cardID)
{
  switch ($cardID) {

    default:
      return false;
  }
}

function Is1H($cardID)
{
  switch ($cardID) {

    default:
      return false;
  }
}

function AbilityPlayableFromCombatChain($cardID)
{
  switch($cardID) {

    default: return false;
  }
}

function CardHasAltArt($cardID)
{
  switch ($cardID) {

  default:
      return false;
  }
}

function IsIyslander($character)
{
  switch($character) {
    default: return false;
  }
}

function WardAmount($cardID)
{
  switch($cardID)
  {
    default: return 0;
  }
}

function HasWard($cardID)
{
  switch ($cardID) {

    default: return false;
  }
}

//Used to correct inconsistencies from the data set
function DefinedCardType2Wrapper($cardID)
{
  switch($cardID)
  {
    case "1480894253"://Kylo Ren
    case "2503039837"://Moff Gideon
    case "3045538805"://Hondo Ohnaka
    case "2526288781"://Bossk
    case "7424360283"://Bo-Katan Kryze
    case "5440730550"://Lando Calrissian
    case "1384530409"://Cad Bane
    case "2432897157"://Qi'Ra
    case "4352150438"://Rey
    case "9005139831"://The Mandalorian
    case "0911874487"://Fennec Shand
    case "9794215464"://Gar Saxon
    case "9334480612"://Boba Fett (Daimyo)
    case "0254929700"://Doctor Aphra
    case "9226435975"://Han Solo Red
    case "0622803599"://Jabba the Hutt
    case "9596662994"://Finn
    case "8777351722"://Anakin Skywalker
      return "";
    case "8752877738":
    case "2007868442":
      return "Upgrade";
    default: return DefinedCardType2($cardID);
  }
}

function HasDominate($cardID)
{
  global $mainPlayer, $combatChainState;
  global $CS_NumAuras, $CCS_NumBoosted;
  switch ($cardID)
  {

    default: break;
  }
  return false;
}

function Rarity($cardID)
{
  return GeneratedRarity($cardID);
}

function CardTitles() {
  return ["2-1B Surgical Droid","332nd Stalwart","4-LOM","41st Elite Corps","501st Liberator","97th Legion","A Fine Addition","A New Adventure","AT-AT Suppressor","AT-ST","AT-TE Vanguard","Aayla Secura","Academy Defense Walker","Academy Training","Adelphi Patrol Wing","Administrator's Tower","Admiral Ackbar","Admiral Motti","Admiral Ozzel","Admiral Piett","Admiral Trench","Admiral Yularen","Advanced Recon Commando","Agent Kallus","Aggression","Aggrieved Parliamentarian","Ahsoka Tano","Ahsoka's Padawan Lightsaber","Aid from the Innocent","Alliance Dispatcher","Alliance X-Wing","Altering the Deal","Anakin Skywalker","Anakin's Interceptor","Ardent Sympathizer","Armed to the Teeth","Armored Saber Tank","Arquitens Assault Cruiser","Asajj Ventress","Asteroid Sanctuary","Attack Pattern Delta","Aurra Sing","Auzituck Liberator Gunship","Avenger","B1 Attack Platform","B1 Security Team","B2 Legionnaires","Bail Organa","Baktoid Spider Droid","Bamboozle","Barriss Offee","Batch Brothers","Battle Droid","Battle Droid Escort","Battle Droid Legion","Battlefield Marine","Baze Malbus","Bazine Netal","Bendu","Benthic \"Two Tubes\"","Bib Fortuna","Black One","Black Sun Starfighter","Blizzard Assault AT-AT","Blood Sport","Bo-Katan Kryze","Boba Fett","Boba Fett's Armor","Bodhi Rook","Bold Recon Commando","Bold Resistance","Bombing Run","Bossk","Bounty Guild Initiate","Bounty Hunter Crew","Bounty Hunter's Quarry","Bounty Posting","Brain Invaders","Bravado","Breaking In","Bright Hope","Brutal Traditions","C-3PO","Cad Bane","Calculated Lethality","Calculating MagnaGuard","Cantina Bouncer","Cantina Braggart","Capital City","Captain Rex","Captain Typho","Cargo Juggernaut","Cartel Spacer","Cartel Turncoat","Cassian Andor","Catacombs of Cadera","Caught in the Crossfire","Cell Block Guard","Chain Code Collector","Chancellor Palpatine","Change of Heart","Chewbacca","Chimaera","Chirrut Îmwe","Choose Sides","Chopper","Chopper Base","Clan Challengers","Clan Saxon Gauntlet","Clan Wren Rescuer","Clear the Field","Clone","Clone Cohort","Clone Commander Cody","Clone Deserter","Clone Dive Trooper","Clone Heavy Gunner","Clone Trooper","Cloud City Wing Guard","Cloud-Rider","Cobb Vanth","Collections Starhopper","Colonel Yularen","Command","Command Center","Commission","Compassionate Senator","Concord Dawn Interceptors","Confederate Courier","Confederate Tri-Fighter","Confiscate","Consolidation of Power","Consortium StarViper","Consular Security Force","Corellian Freighter","Corner the Prey","Coronet City","Coruscant Dissident","Coruscant Guard","Count Dooku","Covert Strength","Covetous Rivals","Crafty Smuggler","Creative Thinking","Criminal Muscle","Cripple Authority","Crosshair","Cunning","DJ","Dagobah Swamp","Daring Raid","Darth Maul","Darth Vader","Daughter of Dathomir","Death Mark","Death Star Stormtrooper","Death Trooper","Death Watch Hideout","Death Watch Loyalist","Death by Droids","Del Meeko","Dendup's Loyalist","Dengar","Desperado Freighter","Desperate Attack","Detention Block Rescue","Devastating Gunship","Devastator","Devotion","Director Krennic","Disabling Fang Fighter","Disaffected Senator","Disarm","Discerning Veteran","Disruptive Burst","Distant Patroller","Doctor Aphra","Doctor Evazan","Doctor Pershing","Don't Get Cocky","Droid Cohort","Droid Commando","Droid Deployment","Droid Manufactory","Droid Starfighter","Droideka Security","Drop In","Dryden Vos","Duchess's Champion","Dwarf Spider Droid","Echo","Echo Base","Echo Base Defender","Electrostaff","Elite P-38 Starfighter","Embo","Emperor Palpatine","Emperor's Royal Guard","Encouraging Leadership","Endless Legions","Energy Conversion Lab","Enforced Loyalty","Enfys Nest","Enterprising Lackeys","Enticing Reward","Entrenched","Ephant Mon","Equalize","Escort Skiff","Eta-2 Light Interceptor","Evacuate","Evidence of the Crime","Execute Order 66","Experience","Experience","Ezra Bridger","Falchion Ion Tank","Fallen Lightsaber","Favorable Delegate","Fell the Dragon","Fenn Rau","Fennec Shand","Fett's Firespray","Fifth Brother","Fighters for Freedom","Final Showdown","Finalizer","Finn","First Legion Snowtrooper","First Light","Fives","Fleet Lieutenant","Follower of The Way","For The Republic","For a Cause I Believe In","Force Choke","Force Lightning","Force Throw","Forced Surrender","Foresight","Foundling","Freelance Assassin","Freetown Backup","Frontier AT-RT","Frontier Trader","Frontline Shuttle","Frozen in Carbonite","Fugitive Wookiee","Galactic Ambition","Gamorrean Guards","Gamorrean Retainer","Gar Saxon","General Dodonna","General Grievous","General Krell","General Rieekan","General Tagge","General Veers","General's Blade","General's Guardian","Gentle Giant","Geonosis Patrol Fighter","Gideon Hask","Gideon's Light Cruiser","Give In to Your Anger","Gladiator Star Destroyer","Gor","Grand Admiral Thrawn","Grand Inquisitor","Grand Moff Tarkin","Greedo","Greef Karga","Green Squadron A-Wing","Grenade Strike","Grey Squadron Y-Wing","Grievous Reassembly","Grievous's Wheel Bike","Grim Resolve","Grogu","Guardian of the Whills","Guarding the Way","Guavian Antagonizer","Guerilla Attack Pod","Guerilla Insurgency","Guild Target","HWK-290 Freighter","Hailfire Tank","Han Solo","Hardpoint Heavy Blaster","Headhunter Squadron","Headhunting","Heavy Persuader Tank","Hello There","Hera Syndulla","Heroes on Both Sides","Heroic Renegade","Heroic Resolve","Heroic Sacrifice","Hevy","Hidden Sharpshooter","Hold-Out Blaster","Home One","Homestead Militia","Hondo Ohnaka","Hotshot DL-44 Blaster","Hotshot V-Wing","House Kast Soldier","Hunter","Hunter of the Haxion Brood","Hunting Nexu","Hutt's Henchmen","Huyang","Hylobon Enforcer","I Am Your Father","I Had No Choice","I Have the High Ground","IG-11","IG-88","ISB Agent","Iden Versio","Imperial Interceptor","Imprisoned","Impropriety Among Thieves","In Defense of Kamino","In Pursuit","Incinerator Trooper","Independent Senator","Infantry of the 212th","Inferno Four","Infiltrating Demolisher","Infiltrator's Skill","Inspiring Mentor","It Binds All Things","Jabba the Hutt","Jabba's Palace","Jabba's Rancor","Jango Fett","Jar Jar Binks","Jawa Scavenger","Jedha Agitator","Jedha City","Jedi Lightsaber","Jesse","Jetpack","Jyn Erso","K-2SO","KCM Mining Facility","Kalani","Kanan Jarrus","Karabast","Kashyyyk Defender","Keep Fighting","Kestro City","Ketsu Onyo","Ki-Adi-Mundi","Kihraxz Heavy Fighter","Kintan Intimidator","Kit Fisto","Knight of the Republic","Koska Reeves","Kragan Gorr","Kraken","Krayt Dragon","Krrsantan","Kuiil","Kylo Ren","Kylo's TIE Silencer","L3-37","Lady Proxima","Lair of Grievous","Lando Calrissian","Legal Authority","Leia Organa","Let the Wookiee Win","Lethal Crackdown","Level 1313","Liberated Slaves","Lieutenant Childsen","Lom Pyke","Look the Other Way","Lothal Insurgent","Low Altitude Gunship","Luke Skywalker","Luke's Lightsaber","Luminara Unduli","Lurking TIE Phantom","Lux Bonteri","Ma Klounkee","Mace Windu","Mace Windu's Lightsaber","MagnaGuard Wing Leader","Make an Opening","Malevolence","Mandalorian Armor","Mandalorian Warrior","Manufactured Soldiers","Mas Amedda","Maul","Maximum Firepower","Maz Kanata","Maz Kanata's Castle","Medal Ceremony","Mercenary Company","Mercenary Gunship","Merciless Contest","Midnight Repairs","Migs Mayfeld","Millennium Falcon","Mining Guild TIE Fighter","Mission Briefing","Mister Bones","Modded Cohort","Moff Gideon","Moisture Farmer","Moment of Glory","Moment of Peace","Mon Mothma","Morgan Elsbeth","Multi-Troop Transport","Mystic Reflection","Nala Se","Nameless Valor","Nevarro City","Niima Outpost Constables","Nite Owl Skirmisher","No Bargain","No Good to Me Dead","Now There Are Two of Them","Nute Gunray","OOM-Series Officer","Obedient Vanguard","Obi-Wan Kenobi","Obi-Wan's Aethersprite","Occupier Siege Tank","Old Access Codes","Omega","On Top of Things","On the Doorstep","Open Fire","Osi Sobeck","Outer Rim Headhunter","Outflank","Outland TIE Vanguard","Outlaw Corona","Outmaneuver","Outspoken Representative","Overwhelming Barrage","Padawan Starfighter","Padmé Amidala","Palpatine's Return","Partisan Insurgent","Patrolling AAT","Patrolling V-Wing","Pau City","Pelta Supply Frigate","Perilous Position","Petition the Senate","Petranaki Arena","Phase I Clone Trooper","Phase II Clone Trooper","Phase-III Dark Trooper","Pillage","Pirate Battle Tank","Pirated Starfighter","Planetary Invasion","Plo Koon","Poe Dameron","Poggle the Lesser","Political Pressure","Power Failure","Power of the Dark Side","Pre Vizsla","Precision Fire","Prepare for Takeoff","Price on Your Head","Principled Outlaw","Prisoner of War","Private Manufacturing","Privateer Crew","Privateer Scyk","Protector","Protector of the Throne","Providence Destroyer","Public Enemy","Punishing One","Pyke Palace","Pyke Sentinel","Pyrrhic Assault","Qi'ra","Quinlan Vos","R2-D2","Rallying Cry","Razor Crest","Rebel Assault","Rebel Pathfinder","Reckless Gunslinger","Reckless Torrent","Recruit","Red Three","Redemption","Regional Governor","Regional Sympathizers","Reinforcement Walker","Relentless","Relentless Pursuit","Relentless Rocket Droid","Remnant Reserves","Remnant Science Facility","Remote Village","Repair","Reprocess","Republic ARC-170","Republic Attack Pod","Republic Commando","Republic Defense Carrier","Republic Tactical Officer","Reputable Hunter","Resilient","Resolute","Resourceful Pursuers","Restock","Restored ARC-170","Resupply","Rey","Rhokai Gunship","Rich Reward","Rickety Quadjumper","Rival's Fall","Roger Roger","Rogue Operative","Rogue Squadron Skirmisher","Rose Tico","Royal Guard Attaché","Rugged Survivors","Rukh","Rule with Respect","Rune Haako","Rush Clovis","Ruthless Assassin","Ruthless Raider","Ruthlessness","Ryloth Militia","Sabine Wren","Salacious Crumb","San Hill","Sanctioner's Shuttle","Satine Kryze","Savage Opress","Saw Gerrera","Scanning Officer","Scout Bike Pursuer","Search Your Feelings","Seasoned Shoretrooper","Second Chance","Security Complex","Self-Destruct","Senatorial Corvette","Separatist Commando","Separatist Super Tank","Seventh Fleet Defender","Seventh Sister","Shaak Ti","Shadow Collective Camp","Shadowed Intentions","Shield","Shield","Shoot First","Slaver's Freighter","Sly Moore","Smoke and Cinders","Smuggler's Aid","Smuggler's Starfighter","Smuggling Compartment","Snapshot Reflexes","Sneak Attack","Snowspeeder","Snowtrooper Lieutenant","Soldier of the 501st","Soulless One","Spare the Target","Spark of Hope","Spark of Rebellion","SpecForce Soldier","Spice Mines","Squad Support","Squadron of Vultures","Star Wing Scout","Steadfast Battalion","Steadfast Senator","Steela Gerrera","Stolen Landspeeder","Strafing Gunship","Strategic Acumen","Strategic Analysis","Street Gang Recruiter","Strike True","Subjugating Starfighter","Sugi","Sundari","Sundari Peacekeeper","Super Battle Droid","Supercommando Squad","Superlaser Blast","Superlaser Technician","Supreme Leader Snoke","Surprise Strike","Survivors' Gauntlet","Swoop Down","Swoop Racer","Sword and Shield Maneuver","Synara San","Synchronized Strike","Syndicate Lackeys","System Patrol Craft","TIE Advanced","TIE/ln Fighter","Tactical Advantage","Tactical Droid Commander","Take Captive","Takedown","Tarfful","Tarkintown","Tech","The Armorer","The Chaos of War","The Client","The Clone Wars","The Crystal City","The Darksaber","The Emperor's Legion","The Force Is With Me","The Ghost","The Invasion of Christophsis","The Invisible Hand","The Mandalorian","The Mandalorian's Rifle","The Marauder","The Nest","The Zillo Beast","This Is The Way","Timely Intervention","Tipoca City","Tobias Beckett","Top Target","Toro Calican","Trade Federation Shuttle","Traitorous","Trandoshan Hunters","Tranquility","Tri-Droid Suppressor","Triple Dark Raid","Twice the Pride","Twin Pod Cloud Car","U-Wing Reinforcement","Underworld Thug","Unexpected Escape","Unlicensed Headhunter","Unlimited Power","Unmasking the Conspiracy","Unnatural Life","Unrefusable Offer","Unshakeable Will","Vader's Lightsaber","Val","Valiant Assault Ship","Vambrace Flamethrower","Vambrace Grappleshot","Vanguard Ace","Vanguard Droid Bomber","Vanguard Infantry","Vanquish","Vigilance","Vigilant Honor Guards","Vigilant Pursuit Craft","Village Protectors","Viper Probe Droid","Volunteer Soldier","Vulture Interceptor Wing","Wampa","Wanted","Wanted Insurgents","Warbird Stowaway","Warrior Drone","Wartime Profiteering","Wartime Trade Official","Warzone Lieutenant","Wat Tambor","Waylay","Wedge Antilles","Weequay Pirate Gang","Wild Rancor","Wilderness Fighter","Wing Leader","Wolf Pack Escort","Wolffe","Wookiee Warrior","Wrecker","Wroshyr Tree Tender","Xanadu Blood","Yoda","You're My Only Hope","Zeb Orrelios","Ziro the Hutt","Zorii Bliss","Zuckuss"];
}