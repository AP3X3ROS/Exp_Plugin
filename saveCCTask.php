<?php
/**
 * Author: MagicDroidX
 * Date: 2015/5/2
 * Time: 20:21
 */

namespace MagicDroidX;


use pocketmine\scheduler\PluginTask;

class saveCCTask extends PluginTask {
    protected $plugin;

    public function __construct(Exp $plugin) {
        parent::__construct($plugin);
        $this->plugin = $plugin;
    }

    public function onRun($CT) {
        foreach (array_keys($this->plugin->cc) as $key) {
            $cc = $this->plugin->cc[$key];
            $cc->save();
            if ($this->plugin->getServer()->getPlayerExact($key) == null) {
                unset($this->plugin->cc[$key]);
            }
        }

    }
}