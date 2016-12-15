<?php
/**
 * Author: MagicDroidX
 * Date: 2015/5/2
 * Time: 11:14
 */

namespace MagicDroidX;


use onebone\economyapi\EconomyAPI;
use pocketmine\command\Command;
use pocketmine\command\CommandSender;
use pocketmine\entity\Effect;
use pocketmine\item\Item;
use pocketmine\item\ItemBlock;
use pocketmine\Player;
use pocketmine\plugin\PluginBase;
use pocketmine\utils\TextFormat;
use ALLVIP\main;

class Exp extends PluginBase {
    protected $Listener;

    /**@var ConfigCache[] */
    public $cc = array();

    public function onEnable() {
        @mkdir($this->getDataFolder());
        @mkdir($this->getDataFolder() . "//players");
        $this->getLogger()->info(TextFormat::GREEN . "Exp Plugin:NPWS");
        $this->Listener = new EventListener($this);
        $this->getServer()->getPluginManager()->registerEvents($this->Listener, $this);
        $this->getServer()->getScheduler()->scheduleRepeatingTask(new sendTipTask($this), 10);
        $this->getServer()->getScheduler()->scheduleRepeatingTask(new saveCCTask($this), 10);
    }
    public function onCommand(CommandSender $sender, Command $command, $label, array $args) {
        switch ($command->getName()) {
            case "glory":
                if (count($args) != 2) {
                    return false;
                }
                if ($sender instanceof Player) {
                    $this->glory($sender, $args[0], $args[1]);
                    return true;
                } else {
                    $sender->sendMessage(TextFormat::RED . "You aren't a player，you can't use glory");
                    return true;
                }
			case "skills":
			    if (count($args) != 2) {
                    return false;
                }
				if ($sender instanceof Player) {
                    $this->skills($sender, $args[0], $args[1]);
                    return true;
                } else {
                    $sender->sendMessage(TextFormat::RED . "You aren't a Player，you cannot study skills");
                    return true;
                }
            case "setgrade":
                if (!$sender->isOp()) {
                    $sender->sendMessage(TextFormat::RED . "No Permission");
                    return true;
                } else {
                    if (count($args) != 2) {
                        return false;
                    }
                    $cc = $this->getPlayerConfigCache($args[0]);
                    if ($args[1] > 0 and $args[1] <= 200) {
                        $cc->level = $args[1] + 0;
                        $sender->sendMessage(TextFormat::GREEN . "Set Player $args[0] Level: $args[1]");
                        return true;
                    } else {
                        $sender->sendMessage(TextFormat::RED . "Level must be in 1-200");
                        return true;
                    }
				}
			case "setskills":
			    if (!$sender->isOp()) {
                    $sender->sendMessage(TextFormat::RED . "No Permission");
                    return true;
                } else {
                    if (count($args) != 2) {
                        return false;
                    }
                    $cc = $this->getPlayerConfigCache($args[0]);
                    if ($args[1] > 0 and $args[1] <=5000) {
                        $cc->skills = $args[1] + 0;
                        $sender->sendMessage(TextFormat::GREEN . "Set Player $args[0] skills $args[1]");
                        return true;
                    } else {
                        $sender->sendMessage(TextFormat::RED . "skills must be 1 -5000");
                        return true;
                    }
                }
            case "giveglory":
                if (!$sender->isOp()) {
                    $sender->sendMessage(TextFormat::RED . "No Permission");
                    return true;
                } else {
                    if (count($args) != 2) {
                        return false;
                    }
                    $cc = $this->getPlayerConfigCache($args[0]);
                    $cc->glory += $args[1];
                    $sender->sendMessage(TextFormat::GREEN . "Give Player $args[0] $args[1] skills");
                    return true;
                }
            case "health":
                if (!$sender->isOp()) {
                    $sender->sendMessage(TextFormat::RED . "No Permission");
                    return true;
                } else {
                    if (count($args) != 2) {
                        return false;
                    }
                    $player = $this->getServer()->getPlayerExact($args[0]);
                    if ($player == null) {
                        $sender->sendMessage(TextFormat::RED . "Player is now offline！");
                        return true;
                    }
                    $player->setMaxHealth($args[1]);
                    $player->setHealth($player->getMaxHealth());
                    $cc = $this->getPlayerConfigCache($args[0]);
                    $cc->max_health = $args[1] + 0;
                    $sender->sendMessage(TextFormat::GREEN . "set health successfully");
                    return true;
                }
            case "money":
                if ($sender instanceof Player) {
                    $cc = $this->getPlayerConfigCache($sender->getName());
                    /*$config = $this->getPlayerConfig($sender->getName());
                    $day = $config->get("daily-money");*/
                    $day = $cc->daily_money;
                    if (date("Y-m-d", time()) != $day) {
                        //$money = $this->getDailyMoney($config->get("level"));
                        $money = $this->getDailyMoney($cc->level);
                        $sender->sendMessage(TextFormat::GREEN . "[Daily] You get $money coins");
                        EconomyAPI::getInstance()->addMoney($sender, $money);
                        /*$config->set("daily-money", date("Y-m-d", time()));
                        $config->save();*/
                        $cc->daily_money = date("Y-m-d", time());
                        return true;
                    }
                } else {
                    $sender->sendMessage(TextFormat::RED . "You aren't a player");
                    return false;
                }
                return true;
			case "qdskills":
			    if (main::getInstance()->isSvip($sender->getName())) {
                    $cc = $this->getPlayerConfigCache($args[0]);
                    if (date("Y-m-d", time()) != $day) {
						$player = $sender->getName();
                        $sender->sendMessage(TextFormat::GREEN . "[Daily] Lovely SVIP[",$player,"]you get ",$args[2]," skills");
						$cc->skills = $args[0] + $args[2];
                        $cc->daily_money = date("Y-m-d", time());
                        return true;
                    }
                } else {
                    $sender->sendMessage(TextFormat::RED . "you aren'tSVIP");
                    return false;
                }
        }
        return true;
    }

    public function getNextLevelExp($level) {
        if ($level > 0 and $level < 200) {
            return $level * 200;
        } else {
            return 0;
        }
    }

    /**
     * @param $name
     * @return ConfigCache
     */
    public function getPlayerConfigCache($name) {
        if (!isset($this->cc[strtolower($name)])) {
            $this->cc[strtolower($name)] = new ConfigCache($this, strtolower($name));
        }
        return $this->cc[strtolower($name)];
    }

    public function addExp($name, $exp) {
        $cc = $this->getPlayerConfigCache($name);
        $xp = $cc->exp;

        if ($cc->level <= 0 or $cc->level >= 200) {
            return;
        }
        if ($xp + $exp >= $this->getNextLevelExp($cc->level)) {
            $xp = $xp + $exp - $this->getNextLevelExp($cc->level);
            $cc->exp = $xp;
            $this->LevelUp($name);
        } else {
            $cc->exp = $xp + $exp;
        }
    }

    public function LevelUp($name) {
        $cc = $this->getPlayerConfigCache($name);
        $cc->level++;
        $cc->glory += 60;
        $jiangli = "Glory 60";
		$cc->skills += 3;
		$jiangli = "Skills 3";
        $player = $this->getServer()->getPlayerExact($name);
        if ($player != null) {
            if ($player->getMaxHealth() < 160) {
                $player->setMaxHealth($player->getMaxHealth() + 1);
                $player->setHealth($player->getMaxHealth());
                $cc->max_health++;
                $jiangli .= ",+Half heart ";
            }
            if ($cc->level / 10 ==1) {
                $jiangli .= ",No diamond";
                $player->getInventory()->addItem(Item::get(ItemBlock::DIAMOND, 0, 0));
            }
			if ($cc->level / 10 == 1) {
                $jiangli .= ",Super effect";
                $player->addEffect(Effect::getEffect(Effect::HEALTH_BOOST)->setAmplifier(0)->setDuration(300 * 20));
				$player->addEffect(Effect::getEffect(Effect::INVISIBILITY)->setAmplifier(3)->setDuration(300 * 20));
				$player->addEffect(Effect::getEffect(Effect::INVISIBILITY)->setAmplifier(3)->setDuration(300 * 20));
				$player->addEffect(Effect::getEffect(Effect::HEALTH_BOOST)->setAmplifier(1)->setDuration(300 * 20));
				$player->addEffect(Effect::getEffect(Effect::STRENGTH)->setAmplifier(1)->setDuration(300 * 20));
            }
			if($cc->level / 50 == 1){
				$jiangli .= ",[Coins Reward]:This Player got a coins reward";
				$moneys = $cc->daily-money;
				$money = $moneys * 3;
				EconomyAPI::getInstance()->addMoney($player, $money);
			}
			if($cc->level / 100 == 1){
				$jiangli .= ",[100s effect],This player get a 100s effect reward";
				$player->addEffect(Effect::getEffect(Effect::STRENGTH)->setAmplifier(2)->setDuration(100 * 20));
				$player->addEffect(Effect::getEffect(Effect::INVISIBILITY)->setAmplifier(1)->setDuration(100 * 20));
				$player->sendMessage("You get INVISIBILITY;100s，STRENGTH;100s ");
			}
			if($cc->level / 150 == 1){
				$jiangli .= ",[up 150!]This player level up to 150";
			}
			if($cc->level / 200 == 1){
				$jiangli .= ",[Max Level!]This Player got to the Max level";
			}
            $player->removeAllEffects();
            $player->addEffect(Effect::getEffect(Effect::HEALTH_BOOST)->setAmplifier(0)->setDuration(3 * 20));
            $player->addEffect(Effect::getEffect(Effect::INVISIBILITY)->setAmplifier(0)->setDuration(3 * 20));
            $player->sendMessage(TextFormat::GREEN . "获得隐身，生命恢复，持续3秒");
			$quanxian = TextFormat::GREEN . "[玩家]";
			if ($player->isOp()) {
                    $quanxian = TextFormat::YELLOW . "[OP]";
                } else {
                    if (main::getInstance()->isVip($player->getName())) {
                        $quanxian = TextFormat::RED . "[VIP";
                    }
                    if (main::getInstance()->isSvip($player->getName())) {
                        $quanxian = TextFormat::RED . "§l[SVIP]";
                    }
                }
            $player->setNameTag(TextFormat::GREEN . "LV." . $cc->level .
                "  " . $quanxian . "  " . $this->getKnightString($cc->level) . "  " . TextFormat::WHITE . $player);
        }
        $level = $cc->level;
        $this->getServer()->broadcastMessage(TextFormat::GREEN . "Player:$name level up to $level \n Get Reward：$jiangli");
    }

    public function glory(Player $player, $id, $amount) {
        $cc = $this->getPlayerConfigCache($player->getName());
        switch ($id) {
            case 1111:
                $price = 10000 * $amount;
                $item = Item::get(ItemBlock::DIAMOND, 0, $amount);
                break;
            case 2222:
                $price = 7500 * $amount;
                $item = Item::get(ItemBlock::GOLD_INGOT, 0, $amount);
                break;
            case 3333:
                $price = 5000 * $amount;
                $item = Item::get(ItemBlock::IRON_INGOT, 0, $amount);
                break;
            case 4:
                $price = 50000 * $amount;
                $item[0] = Item::get(ItemBlock::IRON_HELMET, 0, $amount);
                $item[1] = Item::get(ItemBlock::IRON_CHESTPLATE, 0, $amount);
                $item[2] = Item::get(ItemBlock::IRON_LEGGINGS, 0, $amount);
                $item[3] = Item::get(ItemBlock::IRON_BOOTS, 0, $amount);
                break;
            case 5:
                $price = 99990 * $amount;
                $item[0] = Item::get(ItemBlock::CHAIN_HELMET, 0, $amount);
                $item[1] = Item::get(ItemBlock::CHAIN_CHESTPLATE, 0, $amount);
                $item[2] = Item::get(ItemBlock::CHAIN_LEGGINGS, 0, $amount);
                $item[3] = Item::get(ItemBlock::CHAIN_BOOTS, 0, $amount);
                break;
            default:
                $player->sendMessage(TextFormat::RED . "Please Enter the correct number：\n" . TextFormat::WHITE . "1 - diamond Needed:10000 glory\n2 - Gold Needed:7500 glory\n3 - Iron Needed:5000 glory\n 4 - Iron Armor Set Needed:50000 glory\n 5 - Chain Armor Set Needed:99990 glory");
                return;
        }
        $glory = $cc->glory;
        if ($glory < $price) {
            $player->sendMessage(TextFormat::RED . "Needed $price glory，You have $glory glory");
            return;
        }
        $cc->glory = $glory - $price;
        $player->sendMessage(TextFormat::GREEN . "Success！Minus $price glory");
        if (is_array($item)) {
            foreach ($item as $i) {
                $player->getInventory()->addItem($i);
            }
        } else {
            $player->getInventory()->addItem($item);
        }
    }
	public function skills(Player $player, $id, $amount) {
        $cc = $this->getPlayerConfigCache($player->getName());
        switch ($id) {
            case 1:
                $price = 100 * $amount;
                $item = Item::get(ItemBlock::SULPHUR, 0, $amount);
                break;
            case 2:
                $price = 150 * $amount;
                $item = Item::get(ItemBlock::PAPER, 0, $amount);
                break;
            case 3:
                $price = 60 * $amount;
                $item = Item::get(ItemBlock::SLIME_BALL, 0, $amount);
                break;
            case 5:
                $price = 200 * $amount;
                $item = Item::get(ItemBlock::BRICK, 0, $amount);
                break;
            case 4:
                $price = 250 * $amount;
                $item = Item::get(ItemBlock::CAKE, 0, $amount);
                break;
			case 6:
                $price = 200 * $amount;
                $item = Item::get(ItemBlock::SNOW, 0, $amount);
                break;
			case 7:
                $price = 230 * $amount;
                $item = Item::get(ItemBlock::ICE, 0, $amount);
                break;
            default:
                $player->sendMessage(TextFormat::RED . "Plaese enter the correct number：\n" . TextFormat::WHITE . "1 - FIre Needed: 100 Skills\n2 - INVISIBILITY Needed: 150 Skills\n3 - Jump Boast Needed: 60 Skills\n 4 - Super effect Needed: 250 Skills\n 5 - Posion Needed: 200 Skills， \n 6 - Slowness Needed: 200 Skills \n 7 - Ice Needed: 230 Skills");
                return;
        }
        $skills = $cc->skills;
		$qvanxian = "Player";
		$quanxian = "No discount";
			if ($player->isOp()) {
                    $qvanxian = "OP";
					$quanxian = "Free";
                } else {
                    if (main::getInstance()->isVip($player->getName())) {
                        $qvanxian = "VIP";
						$quanxian = "90%";
                    }
                    if (main::getInstance()->isSvip($player->getName())) {
                        $qvanxian = "SVIP";
						$quanxian = "70%";
						
                    }
                }
		if($qvanxian = "OP"){
			$price = 0;
		}
		if($qvanxian = "VIP"){
			$price = $price / 10 * 9;
		}
		if($qvanxian = "SVIP"){
			$price = $price / 10 * 7;
		}
		if($qvanxian = 0){
			$price = $price;
		}
        if ($skills < $price) {
            $player->sendMessage(TextFormat::RED . "Lovely $qvanxian You need $price Skills，you have $skills skills \n Discount: $quanxian \n Your Permission: $qvanxian.");
            return;
        }
        $cc->skills = $skills - $price;
		$skillsd = $cc->skills;
        $player->sendMessage(TextFormat::GREEN . "Success！Minus $price Skills！Sills left: $skillsd \n Discount: $quanxian \n Your Permission: $qvanxian.");
        if (is_array($item)) {
            foreach ($item as $i) {
                $player->getInventory()->addItem($i);
            }
        } else {
            $player->getInventory()->addItem($item);
        }

    }

    public function getKnightString($Level) {
        if ($Level > 0 and $Level < 11) {
            return TextFormat::GREEN . "Beginner";
        }
        if ($Level > 10 and $Level < 21) {
            return TextFormat::GREEN . "New Player";
        }
        if ($Level > 20 and $Level < 31) {
            return TextFormat::GREEN . "Player";
        }
        if ($Level > 30 and $Level < 41) {
            return TextFormat::AQUA . "Pro";
        }
        if ($Level > 40 and $Level < 51) {
            return TextFormat::AQUA . "Good";
        }
        if ($Level > 50 and $Level < 61) {
            return TextFormat::AQUA . "Very Good";
        }
        if ($Level > 60 and $Level < 71) {
            return TextFormat::LIGHT_PURPLE . "Good Pro";
        }
        if ($Level > 70 and $Level < 81) {
            return TextFormat::LIGHT_PURPLE . "Very Pro";
        }
        if ($Level > 80 and $Level < 91) {
            return TextFormat::DARK_PURPLE . "co-leader";
        }
        if ($Level > 90 and $Level < 101) {
            return TextFormat::RED . "co-leader";
        }
		if ($Level > 100 and $Level < 111) {
            return TextFormat::RED . "Lv.100";
        }
		if ($Level > 110 and $Level < 121) {
            return TextFormat::RED . "Lv.110";
        }
		if ($Level > 120 and $Level < 131) {
            return TextFormat::RED . "Lv.120";
        }
		if ($Level > 130 and $Level < 141) {
            return TextFormat::RED . "Lv.130";
        }
		if ($Level > 140 and $Level < 151) {
            return TextFormat::RED . "Lv.140";
        }
		if ($Level > 150 and $Level < 161) {
            return TextFormat::RED . "Lv.150";
        }
		if ($Level > 160 and $Level < 171) {
            return TextFormat::RED . "King";
        }
		if ($Level > 170 and $Level < 181) {
            return TextFormat::RED . "God son";
        }
		if ($Level > 180 and $Level < 191) {
            return TextFormat::RED . "God helper";
        }
		if ($Level > 190 and $Level < 201) {
            return TextFormat::RED . "God helper";
        }
        return TextFormat::RED . "God";
    }

    public function getDailyMoney($Level) {
        if ($Level > 0 and $Level < 11) {
            return 300;
        }
        if ($Level > 10 and $Level < 21) {
            return 400;
        }
        if ($Level > 20 and $Level < 31) {
            return 500;
        }
        if ($Level > 30 and $Level < 41) {
            return 600;
        }
        if ($Level > 40 and $Level < 51) {
            return 700;
        }
        if ($Level > 50 and $Level < 61) {
            return 800;
        }
        if ($Level > 60 and $Level < 71) {
            return 900;
        }
        if ($Level > 70 and $Level < 81) {
            return 1000;
        }
        if ($Level > 80 and $Level < 91) {
            return 1100;
        }
        if ($Level > 90 and $Level < 101) {
            return 1200;
        }
		if ($Level > 100 and $Level < 111) {
            return 1300;
        }
		if ($Level > 110 and $Level < 121) {
            return 1400;
        }
		if ($Level > 120 and $Level < 131) {
            return 1500;
        }
		if ($Level > 130 and $Level < 141) {
            return 1600;
        }
		if ($Level > 140 and $Level < 151) {
            return 1700;
        }
		if ($Level > 150 and $Level < 161) {
            return 1800;
        }
		if ($Level > 160 and $Level < 171) {
            return 1900;
        }
		if ($Level > 170 and $Level < 181) {
            return 2000;
        }
		if ($Level > 180 and $Level < 191) {
            return 2100;
        }
		if ($Level > 190 and $Level < 201) {
            return 2200;
        }
        return 0;
    }
}