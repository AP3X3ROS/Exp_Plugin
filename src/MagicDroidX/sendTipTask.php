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
                $popup .= "   " . TextFormat::GOLD . "Exp " . $cc->exp . "/" . $this->plugin->getNextLevelExp($level);
                $popup .= "   " . $this->plugin->getKnightString($level);
                $popup .= TextFormat::AQUA . "   Glory:" . $cc->glory . "\n";
                $popup .= TextFormat::WHITE . "Player:" . $online;
                $popup .= TextFormat::LIGHT_PURPLE . "   Coins: $" . $moneys["money"][strtolower($player->getName())];
				$popup .= TextFormat::LIGHT_PURPLE . "  skills: *" . $skills;
                $quanxian = "Player";
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
                $popup .= TextFormat::GOLD . "   Permisson:" . $quanxian;
                $player->sendPopup($popup);
            }
        }
    }
}
