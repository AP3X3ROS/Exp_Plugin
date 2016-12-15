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
            "Wood" => 1,
            "Leaf" => 0,
            "Stone" => 1,
            "Coal" => 2,
            "Iron" => 3,
            "Gold" => 5,
            "Diamond" => 10,
            "Sand" => 1,
            "Dirt" => 1,
            "Gravel" => 1,
            "Redstone" => 1
        ));
    }

    public function onPlayerLogin(PlayerLoginEvent $event) {
        $cc = $this->plugin->getPlayerConfigCache($event->getPlayer()->getName());
        $event->getPlayer()->setNameTag(TextFormat::GREEN . "LV." . $cc->level . "  " . $this->plugin->getKnightString($cc->level) . "  " . TextFormat::WHITE . $event->getPlayer()->getName());
    }

    public function onPlayerJoin(PlayerJoinEvent $event) {
        $event->setJoinMessage(null);
        $cc = $this->plugin->getPlayerConfigCache($event->getPlayer()->getName());
        $this->plugin->getServer()->broadcastMessage(TextFormat::YELLOW . "Plyer " . $this->plugin->getKnightString($cc->level) . TextFormat::YELLOW . " " . $event->getPlayer()->getName() . " Join the game");
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
                        $this->plugin->getServer()->broadcastMessage(TextFormat::YELLOW . "Oh my god！" . TextFormat::GREEN . "LV." . $kcc->level . " " . $killer->getName() . " KIll LV." . $bkcc->level . " " . $bkiller->getName() . " get an extra 50 Exp");
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
                $this->plugin->addExp($player->getName(), $this->config->get("Wood"));
				$player->sendMessage(TextFormat::RED ."Lovely VIP，You get an extra Exp");
				}
				if (main::getInstance()->isSvip($player->getName())){
				$this->plugin->addExp($player->getName(), $this->config->get("Wood"));
				$money = $this->config->get("Wood");
				$player->sendMessage(TextFormat::RED ."Lovely SVIP，You get an extra Exp and $money coins");
				EconomyAPI::getInstance()->addMoney($player, $money);
				}
                break;
            case ItemBlock::LEAVE:
			    if (main::getInstance()->isVip($player->getName())){
                $this->plugin->addExp($player->getName(), $this->config->get("Leaf"));
				$player->sendMessage(TextFormat::RED ."Lovely VIP，You get an extra Exp");
				}
				if (main::getInstance()->isSvip($player->getName())){
				$this->plugin->addExp($player->getName(), $this->config->get("Leaf"));
				$money = $this->config->get("Leaf");
				$player->sendMessage(TextFormat::RED ."Lovely SVIP，You get an extra Exp and $money coins");
				EconomyAPI::getInstance()->addMoney($player, $money);
				}
                break;
            case ItemBlock::STONE:
                if (main::getInstance()->isVip($player->getName())){
                $this->plugin->addExp($player->getName(), $this->config->get("Stone"));
				$player->sendMessage(TextFormat::RED ."Lovely VIP，You get an extra Exp.");
				}
				if (main::getInstance()->isSvip($player->getName())){
				$this->plugin->addExp($player->getName(), $this->config->get("Stone"));
				$money = $this->config->get("Stone");
				$player->sendMessage(TextFormat::RED ."Lovely SVIP，You get an extra Exp and $money coins");
				EconomyAPI::getInstance()->addMoney($player, $money);
				}
                break;
            case ItemBlock::COAL_ORE:
                $this->plugin->addExp($player->getName(), $this->config->get("Coal"));
				if (main::getInstance()->isVip($player->getName())){
				$this->plugin->addExp($player->getName(), $this->config->get("Coal"));
				$money = $this->config->get("Coal");
				$player->sendMessage(TextFormat::RED ."Lovelt VIP，You get $money coins");
				EconomyAPI::getInstance()->addMoney($player, $money);
				}
				if (main::getInstance()->isSvip($player->getName())){
				$EXP = $this->config->get("coal");
				$EXP = $EXP * 2;
				$this->plugin->addExp($player->getName(), $EXP);
				$money = $this->config->get("coal");
				$player->sendMessage(TextFormat::RED ."Lovely SVIP，You get" . TextFormat::YELLOW . "double Exp" . TextFormat::RED . "and $money coins");
				EconomyAPI::getInstance()->addMoney($player, $money);
				}
                break;
            case ItemBlock::IRON_ORE:
                $this->plugin->addExp($player->getName(), $this->config->get("Iron"));
				if (main::getInstance()->isVip($player->getName())){
				$this->plugin->addExp($player->getName(), $this->config->get("Iron"));
				$money = $this->config->get("Iron");
				$player->sendMessage(TextFormat::RED ."Lovely VIP，You get $money coins");
				EconomyAPI::getInstance()->addMoney($player, $money);
				}
				if (main::getInstance()->isSvip($player->getName())){
				$EXP = $this->config->get("Iron");
				$EXP = $EXP * 2;
				$this->plugin->addExp($player->getName(), $EXP);
				$money = $this->config->get("Iron");
				$player->sendMessage(TextFormat::RED ."Lovely SVIP，You get" . TextFormat::YELLOW . "double Exp" . TextFormat::RED . "and $money coins");
				EconomyAPI::getInstance()->addMoney($player, $money);
				}
                break;
            case ItemBlock::GOLD_ORE:
                $this->plugin->addExp($player->getName(), $this->config->get("Gold"));
				if (main::getInstance()->isVip($player->getName())){
				$this->plugin->addExp($player->getName(), $this->config->get("Gold"));
				$money = $this->config->get("Gold");
				$player->sendMessage(TextFormat::RED ."Lovely VIP，You get $money coins");
				EconomyAPI::getInstance()->addMoney($player, $money);
				}
				if (main::getInstance()->isSvip($player->getName())){
				$EXP = $this->config->get("Gold");
				$EXP = $EXP * 2;
				$this->plugin->addExp($player->getName(), $EXP);
				$money = $this->config->get("Gold");
				$player->sendMessage(TextFormat::RED ."Lovely SVIP，You get" . TextFormat::YELLOW . "double Exp" . TextFormat::RED . "and $money coins");
				EconomyAPI::getInstance()->addMoney($player, $money);
				}
                break;
            case ItemBlock::DIAMOND_ORE:
                $this->plugin->addExp($player->getName(), $this->config->get("Diamond"));
				if (main::getInstance()->isVip($player->getName())){
				$this->plugin->addExp($player->getName(), $this->config->get("Diamond"));
				$money = $this->config->get("Diamond");
				$player->sendMessage(TextFormat::RED ."Lovely VIP，You get $money coins");
				EconomyAPI::getInstance()->addMoney($player, $money);
				}
				if (main::getInstance()->isSvip($player->getName())){
				$EXP = $this->config->get("Diamond");
				$EXP = $EXP * 2;
				$this->plugin->addExp($player->getName(), $EXP);
				$money = $this->config->get("Diamond");
				$player->sendMessage(TextFormat::RED ."Lovely SVIP，You get" . TextFormat::YELLOW . "double Exp" . TextFormat::RED . "and $money coins");
				EconomyAPI::getInstance()->addMoney($player, $money);
				}
                break;
            case ItemBlock::SAND:
                if (main::getInstance()->isVip($player->getName())){
                $this->plugin->addExp($player->getName(), $this->config->get("Sand"));
				$player->sendMessage(TextFormat::RED ."Lovely VIP，You get an extra Exp");
				}
				if (main::getInstance()->isSvip($player->getName())){
				$this->plugin->addExp($player->getName(), $this->config->get("Sand"));
				$money = $this->config->get("Sand");
				$player->sendMessage(TextFormat::RED ."Lovely SVIP，You get an extra Exp and $money coins");
				EconomyAPI::getInstance()->addMoney($player, $money);
				}
                break;
            case ItemBlock::GRASS:
            case ItemBlock::DIRT:
                if (main::getInstance()->isVip($player->getName())){
                $this->plugin->addExp($player->getName(), $this->config->get("Dirt"));
				$player->sendMessage(TextFormat::RED ."Lovely VIP，You get an extra Exp");
				}
				if (main::getInstance()->isSvip($player->getName())){
				$this->plugin->addExp($player->getName(), $this->config->get("Dirt"));
				$money = $this->config->get("泥土");
				$player->sendMessage(TextFormat::RED ."Lovely SVIP，You get an extra Exp and $money coins");
				EconomyAPI::getInstance()->addMoney($player, $money);
				}
                break;
            case ItemBlock::GRAVEL:
                if (main::getInstance()->isVip($player->getName())){
                $this->plugin->addExp($player->getName(), $this->config->get("Gravel"));
				$player->sendMessage(TextFormat::RED ."Lovely VIP，You have got an extra Exp");
				}
				if (main::getInstance()->isSvip($player->getName())){
				$this->plugin->addExp($player->getName(), $this->config->get("Gravel"));
				$money = $this->config->get("Gravel");
				$player->sendMessage(TextFormat::RED ."Lovely SVIP，You get an extra Exp and $money coins");
				EconomyAPI::getInstance()->addMoney($player, $money);
				}
                break;
            case ItemBlock::REDSTONE_ORE:
                $this->plugin->addExp($player->getName(), $this->config->get("Redstone"));
				if (main::getInstance()->isVip($player->getName())){
				$this->plugin->addExp($player->getName(), $this->config->get("Redstone"));
				$money = $this->config->get("Redstone");
				$player->sendMessage(TextFormat::RED ."Lovely VIP，You get $money coins");
				EconomyAPI::getInstance()->addMoney($player, $money);
				}
				if (main::getInstance()->isSvip($player->getName())){
				$EXP = $this->config->get("Redstone");
				$EXP = $EXP * 2;
				$this->plugin->addExp($player->getName(), $EXP);
				$money = $this->config->get("Redstone");
				$player->sendMessage(TextFormat::RED ."Lovely SVIP，You got" . TextFormat::YELLOW . "double Exp" . TextFormat::RED . "and $money coins");
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
		$qvanxian = TextFormat::GREEN . "[Player]";
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
        $this->plugin->getServer()->broadcastMessage(TextFormat::GOLD . "§e[Server] "
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