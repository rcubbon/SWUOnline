<?php
// Ally Class to handle interactions involving allies

class Ally {

  // Properties
  private $allies = [];
  private $playerID;
  private $index;

  // Constructor
  function __construct($MZIndexOrUniqueID, $player="") {
    global $currentPlayer;
    $mzArr = explode("-", $MZIndexOrUniqueID);

    if ($mzArr[0] != "MYALLY" && $mzArr[0] != "THEIRALLY") {
      $mzArr = ["MYALLY", ""]; // Default non-existent ally
      $players = ($player == 1 || $player == 2) ? [$player] : [1, 2];
      foreach ($players as $p) {
        $index = SearchAlliesForUniqueID($MZIndexOrUniqueID, $p);
        if ($index > -1) {
          $mzArr = ["MYALLY", $index];
          $player = $p;
          break;
        }
      }
    }

    if ($player == "") {
      $otherPlayer = $currentPlayer == 1 ? 2 : 1;
      $player = $mzArr[0] == "MYALLY" ? $currentPlayer : $otherPlayer;
    }

    if ($mzArr[1] == "") {
      for($i=0; $i<AllyPieces(); ++$i) $this->allies[] = 9999;
      $this->index = -1;
    } else {
      $this->index = intval($mzArr[1]);
      $this->allies = &GetAllies($player);
    }

    $this->playerID = $player;
  }

  // Methods
  function MZIndex() {
    global $currentPlayer;
    return ($currentPlayer == $this->Controller() ? "MYALLY-" : "THEIRALLY-") . $this->index;
  }

  function CardID() {
    return $this->allies[$this->index];
  }

  function UniqueID() {
    return $this->allies[$this->index+5];
  }

  function Exists() {
    return $this->index > -1;
  }

  //Controller
  function PlayerID() {
    return $this->playerID;
  }

  function Index() {
    return $this->index;
  }

  function Damage() {
    return $this->allies[$this->index+2];
  }

  function AddDamage($amount) {
    $this->allies[$this->index+2] += $amount;
  }

  function RemoveDamage($amount) {
    if($this->allies[$this->index+2] > 0) $this->allies[$this->index+2] -= $amount;
  }

  function Owner() {
    return $this->allies[$this->index+11];
  }

  function Controller() {
    return $this->playerID;
  }

  function TurnsInPlay() {
    return $this->allies[$this->index+12];
  }

  function Heal($amount) {
    $healed = $amount;
    if($amount > $this->Damage()) {
      $healed = $this->Damage();
      $this->allies[$this->index+2] = 0;
    } else {
      $this->allies[$this->index+2] -= $amount;
    }
    $this->allies[$this->index+14] = 1;//Track that the ally was healed this round
    AddEvent("RESTORE", $this->UniqueID() . "!" . $healed);
    return $healed;
  }

  function MaxHealth() {
    $max = AllyHealth($this->CardID(), $this->PlayerID());
    $upgrades = $this->GetUpgrades();

    // Upgrades buffs
    for($i=0; $i<count($upgrades); ++$i) {
      if ($upgrades[$i] != "-") {
        $max += CardHP($upgrades[$i]);
      }

      switch ($upgrades[$i]) {
        case "3292172753"://Squad Support
          $max += SearchCount(SearchAlliesUniqueIDForTrait($this->Controller(), "Trooper"));
          break;
        default:
          break;
      }
    }

    $max += $this->allies[$this->index+9];
    for($i=count($this->allies)-AllyPieces(); $i>=0; $i-=AllyPieces()) {
      if(AllyHasStaticHealthModifier($this->allies[$i])) {
        $max += AllyStaticHealthModifier($this->CardID(), $this->Index(), $this->PlayerID(), $this->allies[$i], $i, $this->PlayerID());
      }
    }
    $otherPlayer = $this->PlayerID() == 1 ? 2 : 1;
    $theirAllies = &GetAllies($otherPlayer);
    for($i=0; $i<count($theirAllies); $i+=AllyPieces()) {
      if(AllyHasStaticHealthModifier($theirAllies[$i])) {
        $max += AllyStaticHealthModifier($this->CardID(), $this->Index(), $this->PlayerID(), $theirAllies[$i], $i, $otherPlayer);
      }
    }
    $max += CharacterStaticHealthModifiers($this->CardID(), $this->Index(), $this->PlayerID());
    $max += NameBasedHealthModifiers($this->CardID(), $this->Index(), $this->PlayerID());
    $max += BaseHealthModifiers($this->CardID(), $this->Index(), $this->PlayerID());
    return $max;
  }

  function Health() {
    return $this->MaxHealth() - $this->Damage();
  }

  //Returns true if the ally is destroyed
  function DefeatIfNoRemainingHP() {
    if (!$this->Exists()) return true;
    if ($this->Health() <= 0 && ($this->CardID() != "d1a7b76ae7" || $this->LostAbilities()) && ($this->CardID() != "0345124206")) {  //Clone - Ensure that Clone remains in play while resolving its ability
      DestroyAlly($this->playerID, $this->index);
      return true;
    }
    return false;
  }

  function IsDamaged() {
    return $this->Damage() > 0;
  }

  function IsExhausted() {
    return $this->allies[$this->index+1] == 1;
  }

  function WasHealed() {
    return $this->allies[$this->index+14] == 1;
  }

  function Destroy($enemyEffects = true) {
    if($this->index == -1) return "";
    if($enemyEffects && $this->AvoidsDestroyByEnemyEffects()) {
      WriteLog(CardLink($this->CardID(), $this->CardID()) . " cannot be defeated by enemy card effects.");
      return "";
    }
    return DestroyAlly($this->playerID, $this->index);
  }

  //Returns true if the ally is destroyed
  function DealDamage($amount, $bypassShield = false, $fromCombat = false, &$damageDealt = NULL, $enemyDamage = false, $fromUnitEffect=false) {
    global $currentTurnEffects;
    if($this->index == -1 || $amount <= 0) return false;
    global $mainPlayer;
    if(!$fromCombat && $this->AvoidsDamage($enemyDamage)) return;
    if($fromCombat && !$this->LostAbilities()) {
      if($this->CardID() == "6190335038" && $this->PlayerID() == $mainPlayer && IsCoordinateActive($this->PlayerID())) return false;//Aayla Secura
    }
    //Upgrade damage prevention
    $subcards = $this->GetSubcards();
    for($i=0; $i<count($subcards); $i+=SubcardPieces()) {
      if($subcards[$i] == "8752877738") {
        //Shield Token
        unset($subcards[$i+1]);
        unset($subcards[$i]);
        $subcards = array_values($subcards);
        $this->allies[$this->index+4] = count($subcards) > 0 ? implode(",", $subcards) : "-";
        AddEvent("SHIELDDESTROYED", $this->UniqueID());
        if(!$bypassShield) return false;//Cancel the damage if shield prevented it
      }
      switch($subcards[$i]) {
        case "5738033724"://Boba Fett's Armor
          if(CardTitle($this->CardID()) == "Boba Fett") $amount -= 2;
          if($amount < 0) $amount = 0;
          break;
        default: break;
      }
    }
    //Current effect damage prevention
    for($i=0; $i<count($currentTurnEffects); $i+=CurrentTurnEffectPieces()) {
      if($currentTurnEffects[$i+1] != $this->PlayerID()) continue;
      if($currentTurnEffects[$i+2] != -1 && $currentTurnEffects[$i+2] != $this->UniqueID()) continue;
      switch($currentTurnEffects[$i]) {
        case "7244268162"://Finn
          $amount -= 1;
          if($amount < 0) $amount = 0;
          break;
        default: break;
      }
    }
    switch($this->CardID()) {
      case "8862896760"://Maul
        $preventUniqueID = SearchLimitedCurrentTurnEffects("8862896760", $this->PlayerID(), remove:true);
        if($preventUniqueID != -1) {
          $preventIndex = SearchAlliesForUniqueID($preventUniqueID, $this->PlayerID());
          if($preventIndex > -1) {
            $preventAlly = new Ally("MYALLY-" . $preventIndex, $this->PlayerID());
            $preventAlly->DealDamage($amount, $bypassShield, $fromCombat, $damageDealt);
            return false;
          }
        }
        break;
      default: break;
    }
    if($damageDealt != NULL) $damageDealt = $amount;
    $this->AddDamage($amount);
    AddEvent("DAMAGE", $this->UniqueID() . "!" . $amount);
    if($this->Health() <= 0 && ($this->CardID() != "d1a7b76ae7" || $this->LostAbilities())//Chirrut Imwe
        && (!AllyIsMultiAttacker($this->CardID()) || !IsMultiTargetAttackActive())) {
      DestroyAlly($this->playerID, $this->index, fromCombat:$fromCombat);
      return true;
    }
    AllyDamageTakenAbilities($this->playerID, $this->index, survived:true,
      damage:$amount, fromCombat:$fromCombat, enemyDamage:$enemyDamage, fromUnitEffect:$fromUnitEffect);
    switch($this->CardID())
    {
      case "4843225228"://Phase-III Dark Trooper
        if($fromCombat) $this->Attach("2007868442");//Experience token
        break;
      default: break;
    }
    return false;
  }

  function AddRoundHealthModifier($amount) {
    if($this->index == -1) return;
    $this->allies[$this->index+9] += $amount;
    $this->DefeatIfNoRemainingHP();
  }

  function NumAttacks() {
    return $this->allies[$this->index+10];
  }

  function IncrementTimesAttacked() {
    ++$this->allies[$this->index+10];
  }

  function CurrentPower() {
    global $currentTurnEffects;
    $power = ((int) (AttackValue($this->CardID() ?? 0))) + ((int) $this->allies[$this->index+7]);
    $power += AttackModifier($this->CardID(), $this->playerID, $this->index);
    $upgrades = $this->GetUpgrades();

    // Grit buff
    if(HasGrit($this->CardID(), $this->playerID, $this->index)) {
      $damage = $this->Damage();
      if($damage > 0) $power += $damage;
    }

    // Upgrades buffs
    for ($i=0; $i<count($upgrades); ++$i) {
      if ($upgrades[$i] != "-") {
        $power += AttackValue($upgrades[$i]);
      }

      switch ($upgrades[$i]) {
        case "3292172753"://Squad Support
          $power += SearchCount(SearchAlliesUniqueIDForTrait($this->Controller(), "Trooper"));
          break;
        default:
          break;
      }
    }

    //Other ally buffs
    $otherAllies = &GetAllies($this->playerID);
    for($i=0; $i<count($otherAllies); $i+=AllyPieces()) {
      switch($otherAllies[$i]) {
        case "6097248635"://4-LOM
          if(CardTitle($this->CardID()) == "Zuckuss") $power += 1;
          break;
        case "1690726274"://Zuckuss
          if(CardTitle($this->CardID()) == "4-LOM") $power += 1;
          break;
        case "e2c6231b35"://Director Krennic Leader Unit
          if($this->IsDamaged() && !LeaderAbilitiesIgnored()) $power += 1;
          break;
        case "1557302740"://General Veers
          if($i != $this->index && TraitContains($this->CardID(), "Imperial", $this->PlayerID())) $power += 1;
          break;
        case "9799982630"://General Dodonna
          if($i != $this->index && TraitContains($this->CardID(), "Rebel", $this->PlayerID())) $power += 1;
          break;
        case "4339330745"://Wedge Antilles
          if(TraitContains($this->CardID(), "Vehicle", $this->PlayerID())) $power += 1;
          break;
        case "4484318969"://Moff Gideon Leader
          global $mainPlayer;
          //As defined on NetworkingLibraries.Attack, $mainPlayer is always the attacker
          if(CardCost($this->CardID()) <= 3 && $mainPlayer == $this->playerID && AttackIndex() == $this->index && IsAllyAttackTarget()) {
            $power += 1;
          }
          break;
        case "3feee05e13"://Gar Saxon Leader Unit
          if($this->IsUpgraded() && !LeaderAbilitiesIgnored()) $power += 1;
          break;
        case "919facb76d"://Boba Fett Green Leader
          if($i != $this->index && HasKeyword($this->CardID(), "Any", $this->playerID, $this->index)) $power += 1;
          break;
        case "1314547987"://Shaak Ti
          if($i != $this->index && IsToken($this->CardID())) $power += 1;
          break;
        case "9017877021"://Clone Commander Cody
          if($i != $this->index && IsCoordinateActive($this->playerID)) $power += 1;
          break;
        case "7924172103"://Bariss Offee
          if($this->WasHealed()) $power += 1;
          break;
        default: break;
      }
    }
    //Their ally modifiers
    $theirAllies = &GetAllies($this->playerID == 1 ? 2 : 1);
    for($i=0; $i<count($theirAllies); $i+=AllyPieces()) {
      switch($theirAllies[$i]) {
        case "3731235174"://Supreme Leader Snoke
          if (!$this->IsLeader()) {
            $power -= 2;
          }
          break;
        default: break;
      }
    }
    //Character buffs
    $myChar = &GetPlayerCharacter($this->playerID);
    for($i=0; $i<count($myChar); $i+=CharacterPieces()) {
      switch($myChar[$i]) {
        case "8560666697"://Director Krennic Leader
          if($this->IsDamaged() && !LeaderAbilitiesIgnored()) $power += 1;
          break;
        case "9794215464"://Gar Saxon Leader
          if($this->IsUpgraded() && !LeaderAbilitiesIgnored()) $power += 1;
          break;
        default: break;
      }
    }
    //Current effect buffs
    for($i=0; $i<count($currentTurnEffects); $i+=CurrentTurnEffectPieces()) {
      if($currentTurnEffects[$i+1] != $this->playerID) continue;
      if($currentTurnEffects[$i+2] != -1 && $currentTurnEffects[$i+2] != $this->UniqueID()) continue;
      $power += EffectAttackModifier($currentTurnEffects[$i], $this->PlayerID());
    }
    if($power < 0) $power = 0;
    return $power;
  }

  //All the things that should happen at the end of a round
  function EndRound() {
    if($this->index == -1) return;
    $roundHealthModifier = $this->allies[$this->index+9];
    if(is_int($roundHealthModifier)) $this->allies[$this->index+2] -= $roundHealthModifier;
    $this->allies[$this->index+9] = 0;
    $this->DefeatIfNoRemainingHP();
  }

  function Ready() {
    $upgrades = $this->GetUpgrades();
    for($i=0; $i<count($upgrades); ++$i) {
      switch($upgrades[$i]) {
        case "7718080954"://Frozen in Carbonite
          return false;
        default: break;
      }
    }
    if($this->allies[$this->index+3] == 1) return false;
    $this->allies[$this->index+1] = 2;
    return true;
  }

  function Exhaust() {
    if($this->index == -1) return;
    AddEvent("EXHAUST", $this->UniqueID());
    $this->allies[$this->index+1] = 1;
  }

  function AddSubcard($cardID, $ownerID = null) {
    $ownerID = $ownerID ?? $this->playerID;
    if($this->allies[$this->index+4] == "-") $this->allies[$this->index+4] = $cardID . "," . $ownerID;
    else $this->allies[$this->index+4] = $this->allies[$this->index+4] . "," . $cardID . "," . $ownerID;
  }

  function RemoveSubcard($subcardID) {
    if($this->index == -1) return false;
    $subcards = $this->GetSubcards();
    for($i=0; $i<count($subcards); $i+=SubcardPieces()) {
      if($subcards[$i] == $subcardID) {
        $ownerId = $subcards[$i+1];
        unset($subcards[$i+1]);
        unset($subcards[$i]);
        $subcards = array_values($subcards);
        $this->allies[$this->index + 4] = count($subcards) > 0 ? implode(",", $subcards) : "-";
        if(DefinedTypesContains($subcardID, "Upgrade")) UpgradeDetached($subcardID, $this->playerID, "MYALLY-" . $this->index);
        return $ownerId;
      }
    }
    return -1;
  }

  function AddEffect($effectID, $from="") {
    AddCurrentTurnEffect($effectID, $this->PlayerID(), from:$from, uniqueID:$this->UniqueID());
  }

  function Attach($cardID, $ownerID = null) {
    $this->AddSubcard($cardID, $ownerID);
    if (CardIsUnique($cardID)) {
      $this->CheckUniqueUpgrade($cardID);
    }
  }

  function GetSubcards() {
    $subcards = $this->allies[$this->index + 4];
    if($subcards == null || $subcards == "" || $subcards == "-") return [];
    return explode(",", $subcards);
  }

  function GetUpgrades($withOwnerData = false) {
    if($this->allies[$this->index + 4] == "-") return [];
    $subcards = $this->GetSubcards();
    $upgrades = [];
    for($i=0; $i<count($subcards); $i+=SubcardPieces()) {
      if(DefinedTypesContains($subcards[$i], "Upgrade", $this->PlayerID()) || DefinedTypesContains($subcards[$i], "Token Upgrade", $this->PlayerID())) {
        if($withOwnerData) array_push($upgrades, $subcards[$i], $subcards[$i+1]);
        else $upgrades[] = $subcards[$i];
      }
    }
    return $upgrades;
  }

  function GetCaptives($withOwnerData = false) {
    if($this->allies[$this->index + 4] == "-") return [];
    $subcards = $this->GetSubcards();
    $capturedUnits = [];
    for($i=0; $i<count($subcards); $i+=SubcardPieces()) {
      if(DefinedTypesContains($subcards[$i], "Unit", $this->PlayerID())) {
        if($withOwnerData) array_push($capturedUnits, $subcards[$i], $subcards[$i+1]);
        else $capturedUnits[] = $subcards[$i];
      }
    }
    return $capturedUnits;
  }

  function IsCloned() {
    return $this->allies[$this->index + 13] == 1;
  }

  function ClearSubcards() {
    $this->allies[$this->index + 4] = "-";
  }

  function CheckUniqueUpgrade($cardID) {
    $firstCopy = "";
    $secondCopy = "";
    for($i=0; $i<count($this->allies); $i+=AllyPieces()) {
      $subcards = explode(",", $this->allies[$i + 4]);
      for($j=0; $j<count($subcards); ++$j) {
        if($subcards[$j] == $cardID) {
          if($firstCopy == "") $firstCopy = $i;
          else $secondCopy = $i;
        }
      }
    }

    if($firstCopy != "" && $firstCopy == $secondCopy && $this->index == $firstCopy) {
      $ownerId = $this->DefeatUpgrade($cardID);
      AddGraveyard($cardID, $ownerId, "PLAY");
      WriteLog("Existing copy of upgrade defeated due to unique rule.");
    } elseif ($firstCopy != "" && $secondCopy != "" && $firstCopy != $secondCopy) {
      $otherIndex = $this->index == $firstCopy ? $secondCopy : $firstCopy;
      $otherAlly = new Ally("MYALLY-" . $otherIndex);
      $ownerId = $otherAlly->DefeatUpgrade($cardID);
      AddGraveyard($cardID, $ownerId, "PLAY");
      WriteLog("Existing copy of upgrade defeated due to unique rule.");
    }
  }

  function HasUpgrade($upgradeID) {
    if($this->index == -1) return false;
    $subcards = $this->GetSubcards();
    for($i=0; $i<count($subcards); $i+=SubcardPieces()) {
      if($subcards[$i] == $upgradeID) {
        return true;
      }
    }
    return false;
  }

  function DefeatUpgrade($upgradeID) {
    $uniqueID = $this->UniqueID();
    $ownerId = $this->RemoveSubcard($upgradeID);
    $updatedAlly = new Ally($uniqueID); // Refresh the ally, as the index or controller may have changed
    $updatedAlly->DefeatIfNoRemainingHP();
    return $ownerId;
  }

  function RescueCaptive($captiveID, $newController=-1) {
    $ownerId = $this->RemoveSubcard($captiveID);
    if($ownerId != -1) {
      if($newController == -1) $newController = $ownerId;
      PlayAlly($captiveID, $newController, from:"CAPTIVE", owner:$ownerId);
    }
  }

  function DiscardCaptive($captiveID) {
    $ownerId = $this->RemoveSubcard($captiveID);
    if($ownerId != -1) {
      AddGraveyard($captiveID, $ownerId, "CAPTIVE");
      return true;
    }
    else return false;
  }

  function NumUses() {
    return $this->allies[$this->index + 8];
  }

  function ModifyUses($amount): void {
    $this->allies[$this->index + 8] += $amount;
  }

  function LostAbilities($ignoreFirstCardId = ""): bool {
    global $currentTurnEffects;
    for($i=0; $i<count($currentTurnEffects); $i+=CurrentTurnEffectPieces()) {
      if($currentTurnEffects[$i+1] != $this->PlayerID()) continue;
      if($currentTurnEffects[$i+2] != -1 && $currentTurnEffects[$i+2] != $this->UniqueID()) continue;
      if($currentTurnEffects[$i] == "2639435822") return true;
    }
    $upgrades = $this->GetUpgrades();
    $ignoredUpgrade = 0;
    for($i=0; $i<count($upgrades); ++$i) {
      //in case of imprisoned, upgrade are added before all triggers, we need to ignore it for krayt
      if($ignoreFirstCardId != "" && $upgrades[$i] == $ignoreFirstCardId && $ignoredUpgrade == 0) {
        $ignoredUpgrade++;
        continue;
      }
      switch($upgrades[$i]) {
        case "1368144544"://Imprisoned
          return true;
        default: break;
      }
    }
    if ($this->IsLeader() && LeaderAbilitiesIgnored()) {
      return true;
    }

    return false;
  }

  function IsLeader() {
    return IsLeader($this->CardID());
  }

  function IsUpgraded(): bool {
    return $this->NumUpgrades() > 0;
  }

  function NumUpgrades(): int {
    $upgrades = $this->GetUpgrades();
    return count($upgrades);
  }

  function HasBounty(): bool {
    return CollectBounties($this->PlayerID(), $this->CardID(), $this->UniqueID(), $this->IsExhausted(), $this->Owner(), $this->GetUpgrades(), reportMode:true) > 0;
  }

  function Serialize() {
    $builder = [];
    for($i=0; $i<AllyPieces();++$i) {
      $builder[$i] = $this->allies[$this->index+$i];
    }
    return implode(";", $builder);
  }

  function AvoidsDestroyByEnemyEffects() {
    global $mainPlayer;
    return $mainPlayer != $this->playerID
      && !$this->LostAbilities()
      && ($this->CardID() == "1810342362"//Lurking TIE Phantom
        || $this->HasUpgrade("9003830954"))//Shadowed Intentions
    ;
  }

  function AvoidsCapture() {
    return !$this->LostAbilities()
      && ($this->CardID() == "1810342362"//Lurking TIE Phantom
        || $this->HasUpgrade("9003830954"))//Shadowed Intentions
    ;
  }

  function AvoidsDamage($enemyDamage) {
    global $mainPlayer;
    return ($mainPlayer != $this->playerID || $enemyDamage)
      && !$this->LostAbilities()
      && $this->CardID() == "1810342362"//Lurking TIE Phantom
    ;
  }

  function AvoidsBounce() {
    global $mainPlayer;
    return $mainPlayer != $this->playerID
      && !$this->LostAbilities()
      && $this->HasUpgrade("9003830954")//Shadowed Intentions
    ;
  }
}

function LastAllyIndex($player): int {
  $allies = &GetAllies($player);
  return count($allies) - AllyPieces();
}

?>
