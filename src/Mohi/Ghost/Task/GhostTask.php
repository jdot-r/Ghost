<?php
namespace Mohi\Ghost\Task;

use pocketmine\scheduler\Task;

class GhostTask extends Task {
	public $player, $ghost;
	public function __construct(Plugin $plugin, Player $player, &$ghost) {
		parent::__construct($plugin);
		$player = $this->player;
		$ghost = $this->ghost;
	}
	public function onRun() {
		$this->player->setGamemode(0);
		$this->ghost[$this->player->getName()] = false;
	}
}
?>
