<?php
/**
 * Author: MagicDroidX
 * Date: 2015/5/2
 * Time: 11:14
 */

namespace MagicDroidX;


use pocketmine\event\block\BlockBreakEvent;
use pocketmine\event\entity\EntityDamageByEntityEvent;
use pocketmine\event\entity\EntityDamageEvent;
 use pocketmine\event\Listener;
use pocketmine\event\player\PlayerChatEvent;
use pocketmine\event\player\PlayerDeathEvent;
use pocketmine\event\player\PlayerJoinEvent;
use pocketmine\event\player\PlayerLoginEvent;
use pocketmine\event\player\PlayerRespawnEvent;
use pocketmine\item\ItemBlock;
use pocketmine\Player;
use pocketmine\utils\Config;
use pocketmine\utils\TextFormat;
use ALLVIP\main;
use onebone\economyapi\EconomyAPI;

class EventListener implements Listener {
    protected $plugin;

    private $config;

    public function __construct(Exp $plugin) {
        $this->plugin = $plugin;
        $this->config = new Config($this->plugin->getDataFolder() . "//exp.yml", Config::YAML, array(
            "原木" => 1, 
            "树叶" => 0, 
            "石头" => 1,
            "煤矿" => 2,
            "铁矿" => 3,
            "金矿" => 5,
            "钻石矿" => 10,
            "沙子" => 1,
            "泥土" => 1,
            "砂砾" => 1,
            "红石" => 1
        ));
    }

    public function onPlayerLogin(PlayerLoginEvent $event) {
        $cc = $this->plugin->getPlayerConfigCache($event->getPlayer()->getName());
        $event->getPlayer()->setNameTag(TextFormat::GREEN . "LV." . $cc->level . "  " . $this->plugin->getKnightString($cc->level) . "  " . TextFormat::WHITE . $event->getPlayer()->getName());
    }

    public function onPlayerJoin(PlayerJoinEvent $event) {
        $event->setJoinMessage(null);
        $cc = $this->plugin->getPlayerConfigCache($event->getPlayer()->getName());
        $this->plugin->getServer()->broadcastMessage(TextFormat::YELLOW . "Player " . $this->plugin->getKnightString($cc->level) . TextFormat::YELLOW . " " . $event->getPlayer()->getName() . " Join the Game");
        $event->getPlayer()->setNameTag(TextFormat::GREEN . "LV." . $cc->level .
            "  " . $this->plugin->getKnightString($cc->level) . "  " . TextFormat::WHITE . $event->getPlayer()->getName());
        $player = $event->getPlayer();
        $cc = $this->plugin->getPlayerConfigCache($player->getName());
        $player->setMaxHealth($cc->max_health);
        $player->setHealth($cc->max_health);
    }

    public function onKill(EntityDamageEvent $event) {
        if ($event->isCancelled()) {
            return;
        }
        if ($event instanceof EntityDamageByEntityEvent) {
            $killer = $event->getDamager();
            $bkiller = $event->getEntity();
            if ($killer instanceof Player and $bkiller instanceof Player) {
                if ($bkiller->getHealth() - $event->getDamage() <= 0) {
                    $this->plugin->addExp($killer->getName(), 50);
                    $kcc = $this->plugin->getPlayerConfigCache($killer->getName());
                    $bkcc = $this->plugin->getPlayerConfigCache($bkiller->getName());
                    if ($kcc->level < $bkcc->level) {
                        $this->plugin->getServer()->broadcastMessage(TextFormat::YELLOW . "逆天了！" . TextFormat::GREEN . "LV." . $kcc->level . " " . $killer->getName() . " 越阶杀死玩家 LV." . $bkcc->level . " " . $bkiller->getName() . " 额外获得50经验");
                        $this->plugin->addExp($killer->getName(), 50);
                    }
                }
            }
        }
    }

    /**
     * @param BlockBreakEvent $event
     *
     * @priority LOWEST
     */
    public function onBreak(BlockBreakEvent $event) {
        if ($event->isCancelled()) {
            return;
        }
        $item = $event->getBlock();
        $player = $event->getPlayer();
        if ($player->getGamemode() == 1) {
            return;
        }
        switch ($item->getId()) {
            case ItemBlock::LOG:
			    if (main::getInstance()->isVip($player->getName())){
                $this->plugin->addExp($player->getName(), $this->config->get("原木"));
				$player->sendMessage(TextFormat::RED ."尊敬VIP，您获得了VIP专属经验");
				}
				if (main::getInstance()->isSvip($player->getName())){
				$this->plugin->addExp($player->getName(), $this->config->get("原木"));
				$money = $this->config->get("原木");
				$player->sendMessage(TextFormat::RED ."尊敬的SVIP玩家，您获得了SVIP专属经验和SVIP专属金币 $money 个");
				EconomyAPI::getInstance()->addMoney($player, $money);
				}
                break;
            case ItemBlock::LEAVE:
			    if (main::getInstance()->isVip($player->getName())){
                $this->plugin->addExp($player->getName(), $this->config->get("树叶"));
				$player->sendMessage(TextFormat::RED ."尊敬的VIP，您获得了VIP专属经验");
				}
				if (main::getInstance()->isSvip($player->getName())){
				$this->plugin->addExp($player->getName(), $this->config->get("树叶"));
				$money = $this->config->get("树叶");
				$player->sendMessage(TextFormat::RED ."尊敬的SVIP，您获得了SVIP专属经验和SVIP专属金币 $money 个");
				EconomyAPI::getInstance()->addMoney($player, $money);
				}
                break;
            case ItemBlock::STONE:
                if (main::getInstance()->isVip($player->getName())){
                $this->plugin->addExp($player->getName(), $this->config->get("石头"));
				$player->sendMessage(TextFormat::RED ."尊敬的VIP，您获得了VIP专属经验");
				}
				if (main::getInstance()->isSvip($player->getName())){
				$this->plugin->addExp($player->getName(), $this->config->get("石头"));
				$money = $this->config->get("石头");
				$player->sendMessage(TextFormat::RED ."尊敬的SVIP，您获得了SVIP专属经验和SVIP专属金币 $money 个");
				EconomyAPI::getInstance()->addMoney($player, $money);
				}
                break;
            case ItemBlock::COAL_ORE:
                $this->plugin->addExp($player->getName(), $this->config->get("煤矿"));
				if (main::getInstance()->isVip($player->getName())){
				$this->plugin->addExp($player->getName(), $this->config->get("煤矿"));
				$money = $this->config->get("煤矿");
				$player->sendMessage(TextFormat::RED ."尊敬的VIP，您获得了VIP专属金币 $money 个");
				EconomyAPI::getInstance()->addMoney($player, $money);
				}
				if (main::getInstance()->isSvip($player->getName())){
				$EXP = $this->config->get("煤矿");
				$EXP = $EXP * 2;
				$this->plugin->addExp($player->getName(), $EXP);
				$money = $this->config->get("煤矿");
				$player->sendMessage(TextFormat::RED ."尊敬的SVIP，您获得了SVIP专属" . TextFormat::YELLOW . "双倍经验" . TextFormat::RED . "和SVIP专属金币 $money 个");
				EconomyAPI::getInstance()->addMoney($player, $money);
				}
                break;
            case ItemBlock::IRON_ORE:
                $this->plugin->addExp($player->getName(), $this->config->get("铁矿"));
				if (main::getInstance()->isVip($player->getName())){
				$this->plugin->addExp($player->getName(), $this->config->get("铁矿"));
				$money = $this->config->get("铁矿");
				$player->sendMessage(TextFormat::RED ."尊敬的VIP，您获得了VIP专属金币 $money 个");
				EconomyAPI::getInstance()->addMoney($player, $money);
				}
				if (main::getInstance()->isSvip($player->getName())){
				$EXP = $this->config->get("铁矿");
				$EXP = $EXP * 2;
				$this->plugin->addExp($player->getName(), $EXP);
				$money = $this->config->get("铁矿");
				$player->sendMessage(TextFormat::RED ."尊敬的SVIP，您获得了SVIP专属" . TextFormat::YELLOW . "双倍经验" . TextFormat::RED . "和SVIP专属金币 $money 个");
				EconomyAPI::getInstance()->addMoney($player, $money);
				}
                break;
            case ItemBlock::GOLD_ORE:
                $this->plugin->addExp($player->getName(), $this->config->get("金矿"));
				if (main::getInstance()->isVip($player->getName())){
				$this->plugin->addExp($player->getName(), $this->config->get("金矿"));
				$money = $this->config->get("金矿");
				$player->sendMessage(TextFormat::RED ."尊敬的神VIP，您获得了VIP专属金币 $money 个");
				EconomyAPI::getInstance()->addMoney($player, $money);
				}
				if (main::getInstance()->isSvip($player->getName())){
				$EXP = $this->config->get("金矿");
				$EXP = $EXP * 2;
				$this->plugin->addExp($player->getName(), $EXP);
				$money = $this->config->get("金矿");
				$player->sendMessage(TextFormat::RED ."尊敬的SVIP，您获得了SVIP专属" . TextFormat::YELLOW . "双倍经验" . TextFormat::RED . "和SVIP专属金币 $money 个");
				EconomyAPI::getInstance()->addMoney($player, $money);
				}
                break;
            case ItemBlock::DIAMOND_ORE:
                $this->plugin->addExp($player->getName(), $this->config->get("钻石矿"));
				if (main::getInstance()->isVip($player->getName())){
				$this->plugin->addExp($player->getName(), $this->config->get("钻石矿"));
				$money = $this->config->get("钻石矿");
				$player->sendMessage(TextFormat::RED ."尊敬的VIP，您获得了VIP专属金币 $money 个");
				EconomyAPI::getInstance()->addMoney($player, $money);
				}
				if (main::getInstance()->isSvip($player->getName())){
				$EXP = $this->config->get("钻石矿");
				$EXP = $EXP * 2;
				$this->plugin->addExp($player->getName(), $EXP);
				$money = $this->config->get("钻石矿");
				$player->sendMessage(TextFormat::RED ."尊敬的SVIP，您获得了SVIP专属" . TextFormat::YELLOW . "双倍经验" . TextFormat::RED . "和SVIP专属金币 $money 个");
				EconomyAPI::getInstance()->addMoney($player, $money);
				}
                break;
            case ItemBlock::SAND:
                if (main::getInstance()->isVip($player->getName())){
                $this->plugin->addExp($player->getName(), $this->config->get("沙子"));
				$player->sendMessage(TextFormat::RED ."尊敬的VIP，您获得了VIP专属经验");
				}
				if (main::getInstance()->isSvip($player->getName())){
				$this->plugin->addExp($player->getName(), $this->config->get("沙子"));
				$money = $this->config->get("沙子");
				$player->sendMessage(TextFormat::RED ."尊敬的SVIP玩家，您获得了SVIP专属经验和SVIP专属金币 $money 个");
				EconomyAPI::getInstance()->addMoney($player, $money);
				}
                break;
            case ItemBlock::GRASS:
            case ItemBlock::DIRT:
                if (main::getInstance()->isVip($player->getName())){
                $this->plugin->addExp($player->getName(), $this->config->get("泥土"));
				$player->sendMessage(TextFormat::RED ."尊敬的VIP，您获得了VIP专属经验");
				}
				if (main::getInstance()->isSvip($player->getName())){
				$this->plugin->addExp($player->getName(), $this->config->get("泥土"));
				$money = $this->config->get("泥土");
				$player->sendMessage(TextFormat::RED ."尊敬的SVIP，您获得了SVIP专属经验和SVIP专属金币 $money 个");
				EconomyAPI::getInstance()->addMoney($player, $money);
				}
                break;
            case ItemBlock::GRAVEL:
                if (main::getInstance()->isVip($player->getName())){
                $this->plugin->addExp($player->getName(), $this->config->get("砂硕"));
				$player->sendMessage(TextFormat::RED ."尊敬的VIP，您获得了VIP专属经验");
				}
				if (main::getInstance()->isSvip($player->getName())){
				$this->plugin->addExp($player->getName(), $this->config->get("砂硕"));
				$money = $this->config->get("砂硕");
				$player->sendMessage(TextFormat::RED ."尊敬的SVIP，您获得了SVIP专属经验和SVIP专属金币 $money 个");
				EconomyAPI::getInstance()->addMoney($player, $money);
				}
                break;
            case ItemBlock::REDSTONE_ORE:
                $this->plugin->addExp($player->getName(), $this->config->get("红石"));
				if (main::getInstance()->isVip($player->getName())){
				$this->plugin->addExp($player->getName(), $this->config->get("红石"));
				$money = $this->config->get("红石");
				$player->sendMessage(TextFormat::RED ."尊敬的VIP，您获得了VIP专属金币 $money 个");
				EconomyAPI::getInstance()->addMoney($player, $money);
				}
				if (main::getInstance()->isSvip($player->getName())){
				$EXP = $this->config->get("红石");
				$EXP = $EXP * 2;
				$this->plugin->addExp($player->getName(), $EXP);
				$money = $this->config->get("红石");
				$player->sendMessage(TextFormat::RED ."尊敬的SVIP，您获得了SVIP专属" . TextFormat::YELLOW . "双倍经验" . TextFormat::RED . "和SVIP专属金币 $money 个");
				EconomyAPI::getInstance()->addMoney($player, $money);
				}
                break;
        }
    }

    public function onChat(PlayerChatEvent $event) {
        $event->setCancelled();
        $cc = $this->plugin->getPlayerConfigCache($event->getPlayer()->getName());
        $msg = $event->getMessage();

		$player = $event->getPlayer();
		$qvanxian = TextFormat::GREEN . "[玩家]";
		$clor = TextFormat::WHITE;
		$z = ":";
			if ($player->isOp()) {
                    $qvanxian = TextFormat::YELLOW . "[OP]";
					$clor = TextFormat::GREEN;
					$z = "§l";
                } else {
                    if (main::getInstance()->isVip($player->getName())) {
                        $qvanxian = TextFormat::RED . "[VIP]";
						$clor = TextFormat::RED;
						$z = ":";
                    }
                    if (main::getInstance()->isSvip($player->getName())) {
                        $qvanxian = TextFormat::RED . "§l[SVIP]";
						$clor = TextFormat::RED;
						$z = "§l";
                    }
                }
				$level = $player->getLevel();
        $this->plugin->getServer()->broadcastMessage(TextFormat::GOLD . "§e[服务器] "
            . TextFormat::GREEN . "[LV." . $cc->level . " " . $this->plugin->getKnightString($cc->level) . TextFormat::GREEN . "]" . " $qvanxian "
            . TextFormat::WHITE . "<" . $event->getPlayer()->getName() . "> " . $clor . $z .$msg);
    }

    public function onDead(PlayerDeathEvent $event) {
        $player = $event->getEntity();
        if ($player instanceof Player) {
            $cc = $this->plugin->getPlayerConfigCache($player->getName());
            $cc->max_health = $player->getMaxHealth();
        }
    }

    public function onRespawn(PlayerRespawnEvent $event) {
        $player = $event->getPlayer();
        $cc = $this->plugin->getPlayerConfigCache($player->getName());
        $player->setMaxHealth($cc->max_health);
        $player->setHealth($cc->max_health);
    }

}
