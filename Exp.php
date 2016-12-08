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
        $this->getLogger()->info(TextFormat::GREEN . "Aery123这版等级插件QQ3308750959");
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
                    $sender->sendMessage(TextFormat::RED . "你不是一个玩家，不能兑换光荣");
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
                    $sender->sendMessage(TextFormat::RED . "你不是一个玩家，不能学习技能");
                    return true;
                }
            case "setgrade":
                if (!$sender->isOp()) {
                    $sender->sendMessage(TextFormat::RED . "你没有权限使用这个指令");
                    return true;
                } else {
                    if (count($args) != 2) {
                        return false;
                    }
                    $cc = $this->getPlayerConfigCache($args[0]);
                    if ($args[1] > 0 and $args[1] <= 200) {
                        $cc->level = $args[1] + 0;
                        $sender->sendMessage(TextFormat::GREEN . "设置玩家$args[0] 的职位为 $args[1]");
                        return true;
                    } else {
                        $sender->sendMessage(TextFormat::RED . "等级应在1到200之内");
                        return true;
                    }
				}
			case "setskills":
			    if (!$sender->isOp()) {
                    $sender->sendMessage(TextFormat::RED . "你没有权限使用这个指令");
                    return true;
                } else {
                    if (count($args) != 2) {
                        return false;
                    }
                    $cc = $this->getPlayerConfigCache($args[0]);
                    if ($args[1] > 0 and $args[1] <=5000) {
                        $cc->skills = $args[1] + 0;
                        $sender->sendMessage(TextFormat::GREEN . "设置玩家 $args[0] 的技能点为 $args[1]");
                        return true;
                    } else {
                        $sender->sendMessage(TextFormat::RED . "技能点应在1到5000之内");
                        return true;
                    }
                }
            case "giveglory":
                if (!$sender->isOp()) {
                    $sender->sendMessage(TextFormat::RED . "你没有权限使用这个指令");
                    return true;
                } else {
                    if (count($args) != 2) {
                        return false;
                    }
                    $cc = $this->getPlayerConfigCache($args[0]);
                    $cc->glory += $args[1];
                    $sender->sendMessage(TextFormat::GREEN . "赠送玩家$args[0] $args[1] 技能点");
                    return true;
                }
            case "health":
                if (!$sender->isOp()) {
                    $sender->sendMessage(TextFormat::RED . "你没有权限使用这个指令");
                    return true;
                } else {
                    if (count($args) != 2) {
                        return false;
                    }
                    $player = $this->getServer()->getPlayerExact($args[0]);
                    if ($player == null) {
                        $sender->sendMessage(TextFormat::RED . "玩家不在线！");
                        return true;
                    }
                    $player->setMaxHealth($args[1]);
                    $player->setHealth($player->getMaxHealth());
                    $cc = $this->getPlayerConfigCache($args[0]);
                    $cc->max_health = $args[1] + 0;
                    $sender->sendMessage(TextFormat::GREEN . "设置血量成功");
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
                        $sender->sendMessage(TextFormat::GREEN . "[每日领取] 你获得了 $money 金币");
                        EconomyAPI::getInstance()->addMoney($sender, $money);
                        /*$config->set("daily-money", date("Y-m-d", time()));
                        $config->save();*/
                        $cc->daily_money = date("Y-m-d", time());
                        return true;
                    }
                } else {
                    $sender->sendMessage(TextFormat::RED . "你不是玩家");
                    return false;
                }
                return true;
			case "qdskills":
			    if (main::getInstance()->isSvip($sender->getName())) {
                    $cc = $this->getPlayerConfigCache($args[0]);
                    if (date("Y-m-d", time()) != $day) {
						$player = $sender->getName();
                        $sender->sendMessage(TextFormat::GREEN . "[每日领取] 尊敬的SVIP[",$player,"]你获得了 ",$args[2]," 技能点");
						$cc->skills = $args[0] + $args[2];
                        $cc->daily_money = date("Y-m-d", time());
                        return true;
                    }
                } else {
                    $sender->sendMessage(TextFormat::RED . "你不是SVIP");
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
        $jiangli = "光荣 60";
		$cc->skills += 3;
		$jiangli = "技能点 3";
        $player = $this->getServer()->getPlayerExact($name);
        if ($player != null) {
            if ($player->getMaxHealth() < 160) {
                $player->setMaxHealth($player->getMaxHealth() + 1);
                $player->setHealth($player->getMaxHealth());
                $cc->max_health++;
                $jiangli .= ",半格血量上限";
            }
            if ($cc->level / 10 ==1) {
                $jiangli .= ",0颗钻石";
                $player->getInventory()->addItem(Item::get(ItemBlock::DIAMOND, 0, 0));
            }
			if ($cc->level / 10 == 1) {
                $jiangli .= ",超级效果";
                $player->addEffect(Effect::getEffect(Effect::HEALTH_BOOST)->setAmplifier(0)->setDuration(300 * 20));
				$player->addEffect(Effect::getEffect(Effect::INVISIBILITY)->setAmplifier(3)->setDuration(300 * 20));
				$player->addEffect(Effect::getEffect(Effect::INVISIBILITY)->setAmplifier(3)->setDuration(300 * 20));
				$player->addEffect(Effect::getEffect(Effect::SWIFTNESS)->setAmplifier(1)->setDuration(300 * 20));
				$player->addEffect(Effect::getEffect(Effect::STRENGTH)->setAmplifier(1)->setDuration(300 * 20));
            }
			if($cc->level / 50 == 1){
				$jiangli .= ",金币奖励[突破50!]此玩家已经成为常驻玩家";
				$moneys = $cc->daily-money;
				$money = $moneys * 3;
				EconomyAPI::getInstance()->addMoney($player, $money);
			}
			if($cc->level / 100 == 1){
				$jiangli .= ",100秒效果奖励[突破100!]此玩家已成为元老级玩家了";
				$player->addEffect(Effect::getEffect(Effect::STRENGTH)->setAmplifier(2)->setDuration(100 * 20));
				$player->addEffect(Effect::getEffect(Effect::INVISIBILITY)->setAmplifier(1)->setDuration(100 * 20));
				$player->sendMessage("获得隐身效果100秒，力量效果100秒 ");
			}
			if($cc->level / 150 == 1){
				$jiangli .= ",超级仙术奖励[突破150!]此玩家已经接近神了";
			}
			if($cc->level / 200 == 1){
				$jiangli .= ",超级钻石甲奖励奖励[达到200!]此玩家已经超神";
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
        $this->getServer()->broadcastMessage(TextFormat::GREEN . "恭喜玩家$name 升级到 $level 阶位\n获得天道恩赐：$jiangli");
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
                $player->sendMessage(TextFormat::RED . "请正确输入编号：\n" . TextFormat::WHITE . "1 - 钻石 需要:10000光荣\n2 - 金锭\n3 - 铁锭\n 4 - 钻石套\n 5 - 锁链套");
                return;
        }
        $glory = $cc->glory;
        if ($glory < $price) {
            $player->sendMessage(TextFormat::RED . "需要 $price 光荣，你拥有 $glory 光荣");
            return;
        }
        $cc->glory = $glory - $price;
        $player->sendMessage(TextFormat::GREEN . "兑换成功！扣除 $price 光荣");
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
                $player->sendMessage(TextFormat::RED . "请正确输入编号：\n" . TextFormat::WHITE . "1 - 火焰仙术 需要: 100技能点\n2 - 隐身仙术 需要: 150技能点\n3 - 跳跃仙术 需要: 技能点60\n 4 - 超级效果 需要: 技能点250\n 5 - 中毒仙术 需要: 200技能点， \n 6 - 缓慢仙术 需要: 200技能点 \n 7 - 冰冻仙术 需要: 230技能点");
                return;
        }
        $skills = $cc->skills;
		$qvanxian = "仙人";
		$quanxian = "不打折";
			if ($player->isOp()) {
                    $qvanxian = "OP";
					$quanxian = "免费";
                } else {
                    if (main::getInstance()->isVip($player->getName())) {
                        $qvanxian = "VIP";
						$quanxian = "9折";
                    }
                    if (main::getInstance()->isSvip($player->getName())) {
                        $qvanxian = "SVIP";
						$quanxian = "7折";
						
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
            $player->sendMessage(TextFormat::RED . "尊敬的$qvanxian 需要 $price 技能点，你拥有 $skills 技能点 \n 打折详情: \n 您的身份是 $qvanxian 可以打 $quanxian \n 尊敬的 $qvanxian 祝您天天渡劫");
            return;
        }
        $cc->skills = $skills - $price;
		$skillsd = $cc->skills;
        $player->sendMessage(TextFormat::GREEN . "兑换成功！扣除 $price 技能点！还剩 $skillsd \n 打折详情: \n 您的身份是 $qvanxian 打了 $quanxian \n 尊敬的 $qvanxian 欢迎下次再来");
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
            return TextFormat::GREEN . "新手";
        }
        if ($Level > 10 and $Level < 21) {
            return TextFormat::GREEN . "普通玩家";
        }
        if ($Level > 20 and $Level < 31) {
            return TextFormat::GREEN . "高手";
        }
        if ($Level > 30 and $Level < 41) {
            return TextFormat::AQUA . "老手";
        }
        if ($Level > 40 and $Level < 51) {
            return TextFormat::AQUA . "常驻玩家";
        }
        if ($Level > 50 and $Level < 61) {
            return TextFormat::AQUA . "常驻商人";
        }
        if ($Level > 60 and $Level < 71) {
            return TextFormat::LIGHT_PURPLE . "常驻高手";
        }
        if ($Level > 70 and $Level < 81) {
            return TextFormat::LIGHT_PURPLE . "常驻老手";
        }
        if ($Level > 80 and $Level < 91) {
            return TextFormat::DARK_PURPLE . "小元老";
        }
        if ($Level > 90 and $Level < 101) {
            return TextFormat::RED . "中元老";
        }
		if ($Level > 100 and $Level < 111) {
            return TextFormat::RED . "元老";
        }
		if ($Level > 110 and $Level < 121) {
            return TextFormat::RED . "仙人";
        }
		if ($Level > 120 and $Level < 131) {
            return TextFormat::RED . "仙子";
        }
		if ($Level > 130 and $Level < 141) {
            return TextFormat::RED . "仙侠";
        }
		if ($Level > 140 and $Level < 151) {
            return TextFormat::RED . "仙帝";
        }
		if ($Level > 150 and $Level < 161) {
            return TextFormat::RED . "仙皇";
        }
		if ($Level > 160 and $Level < 171) {
            return TextFormat::RED . "神人";
        }
		if ($Level > 170 and $Level < 181) {
            return TextFormat::RED . "神子";
        }
		if ($Level > 180 and $Level < 191) {
            return TextFormat::RED . "造物神";
        }
		if ($Level > 190 and $Level < 201) {
            return TextFormat::RED . "超神";
        }
        return TextFormat::RED . "神罗皇";
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