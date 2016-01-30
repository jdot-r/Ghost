<?php
namespace Mohi\Ghost\Task;

use pocketmine\scheduler\PluginTask;
use pocketmine\Player;

class GhostTask extends PluginTask {
	protected $owner;
	public $player;
	public function __construct($plugin, Player $player) {
		parent::__construct($plugin);
		$this->owner = $plugin;
		$this->player = $player;
	}
	public function onRun($currentTick) {
		$this->player->teleport($this->player->getLevel()-> getSpawnLocation());
		$this->player->setGamemode(0);
		$this->player->getInventory()->setContents($this->owner->inventory[$this->player->getName()]);
		$this->owner->alert($this->player, "리스폰되었습니다.");
		$this->owner->setGhost($this->player, false);
	}
}
?>