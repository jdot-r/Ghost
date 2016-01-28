<?php
namespace Mohi\Ghost\Task;

use pocketmine\scheduler\PluginTask;
use pocketmine\Player;

class GhostTask extends PluginTask {
	public $player, $ghost;
	public function __construct($plugin, Player $player) {
		parent::__construct($plugin);
		$this->player = $player;
	}
	public function onRun($currentTick) {
		$this->player->setGamemode(0);
		$this->owner->setGhost($this->player, false);
		$this->player->setHealth(0);
		$this->owner->setGhost($this->player, true);
	}
}
?>