<?php
/**
 * Author: MagicDroidX
 * Date: 2015/5/2
 * Time: 13:10
 */

namespace MagicDroidX;


use ALLVIP\main;
use onebone\economyapi\EconomyAPI;
use pocketmine\scheduler\PluginTask;
use pocketmine\utils\TextFormat;

class sendTipTask extends PluginTask {
    private $plugin;

    public function __construct(Exp $plugin) {
        $this->plugin = $plugin;
        parent::__construct($plugin);
    }

    public function onRun($CT) {
        $online = count($this->plugin->getServer()->getOnlinePlayers());
        $moneys = EconomyAPI::getInstance()->getAllMoney();
        foreach ($this->plugin->getServer()->getOnlinePlayers() as $player) {
            if (isset($moneys["money"][strtolower($player->getName())])) {
                $cc = $this->plugin->getPlayerConfigCache($player->getName());
				$skills = $cc->skills;
                $level = $cc->level;
                $popup = TextFormat::GREEN . "LV." . $level;
                $popup .= "   " . TextFormat::GOLD . "经验值 " . $cc->exp . "/" . $this->plugin->getNextLevelExp($level);
                $popup .= "   " . $this->plugin->getKnightString($level);
                $popup .= TextFormat::AQUA . "   光荣:" . $cc->glory . "\n";
                $popup .= TextFormat::WHITE . "在线玩家:" . $online;
                $popup .= TextFormat::LIGHT_PURPLE . "   金币: $" . $moneys["money"][strtolower($player->getName())];
				$popup .= TextFormat::LIGHT_PURPLE . "  技能点: *" . $skills;
                $quanxian = "玩家";
                if ($player->isOp()) {
                    $quanxian = "OP";
                } else {
                    if (main::getInstance()->isVip($player->getName())) {
                        $quanxian = "VIP";
                    }
                    if (main::getInstance()->isSvip($player->getName())) {
                        $quanxian = "SVIP";
                    }
                }
                $popup .= TextFormat::GOLD . "   职位:" . $quanxian;
                $player->sendPopup($popup);
            }
        }
    }
}