<?php
/**
 * Author: MagicDroidX
 * Date: 2015/5/2
 * Time: 19:54
 */

namespace MagicDroidX;


use pocketmine\utils\Config;

class ConfigCache {

    protected $plugin;

    private $name;

    public $level, $exp, $glory, $daily_money, $max_health;

    public function __construct(Exp $plugin, $name) {
        $this->plugin = $plugin;
        $this->name = $name;
        $config = new Config($this->plugin->getDataFolder() . "//players//" . strtolower($name) . ".yml", Config::YAML, array(
            "level" => 1,
            "exp" => 0,
            "glory" => 0,
            "daily-money" => "",
            "max-health" => 20,
			"skills" => 0
        ));
        $this->level = $config->get("level");
        $this->exp = $config->get("exp");
        $this->glory = $config->get("glory");
        $this->daily_money = $config->get("daily-money");
        $this->max_health = $config->get("max-health");
		$this->skills = $config->get("skills");
    }

    public function save() {
        $config = new Config($this->plugin->getDataFolder() . "//players//" . strtolower($this->name) . ".yml", Config::YAML, array(
            "level" => 1,
            "exp" => 0,
            "glory" => 0,
            "daily-money" => "",
            "max-health" => 20,
			"skills" => 0
        ));
        $config->set("level", $this->level);
        $config->set("exp", $this->exp + 0);
        $config->set("glory", $this->glory + 0);
        $config->set("daily-money", $this->daily_money);
        $config->set("max-health", $this->max_health + 0);
		$config->set("skills", $this->skills + 0);
        $config->save();
    }
}