<?php
namespace Mohi\Ghost;

use pocketmine\plugin\PluginBase;
use pocketmine\event\Listener;
use pocketmine\Player;
use pocketmine\event\player\PlayerDeathEvent;
use pocketmine\command\Command;
use pocketmine\command\CommandSender;
use pocketmine\event\player\PlayerJoinEvent;
use pocketmine\event\player\PlayerQuitEvent;
use Mohi\Ghost\Task\GhostTask;
use pocketmine\utils\Config;
use pocketmine\utils\TextFormat;
use pocketmine\command\PluginCommand;

class Ghost extends PluginBase implements Listener {
	public $ghost, $inventory, $config;
	public function onEnable() {
		@mkdir($this->getDataFolder());
		$this->loadDB();
		$this->registerCommand("ghost", "Ghost", "ghost.command.allow", "플레이어가 죽으면 유령이 됩니다.", "/ghost <on|off|sec>");
		$this->getServer()->getPluginManager()->registerEvents($this, $this);
	}
	public function onDisable() {
		$this->save("config.json", $this->config);
	}
	 public function registerCommand($name, $fallback, $permission, $description = "", $usage = "") {
	 	$commandMap = $this->getServer ()->getCommandMap ();	
	 	$command = new PluginCommand ( $name, $this );
	 	$command->setDescription ( $description );	
	 	$command->setPermission ( $permission );
	 	$command->setUsage ( $usage );
	 	$commandMap->register ( $fallback, $command );
	 	}
	public function onCommand(CommandSender $sender, Command $command, $label, Array $args) {
		if(! isset($args[0])){
			return false;
		}
		switch(strtolower($args[0])) {
			case "on":
				$this->config["Enable"] = true;
				$this->alert($sender, "Ghost가 켜졌습니다.");
				$this->save("config.json", $this->config, true);
				break;
			case "off":
				$this->config["Enable"] = false;
				$this->alert($sender, "Ghost가 꺼졌습니다.");
				$this->save("config.json", $this->config, true);
				break;
			case "sec":
				if(! isset($args[1])){
					$this->alert($sender, "초(sec)를 적어주세요");
					break;
				}
				$this->config["sec"] = $args[1];
				$this->alert($sender, $this->config["sec"]."초로 설정되었습니다." );
				break;
			default:
				return false;
		}
		return true;
	}
	public function onDeath(PlayerDeathEvent $event) {
		$player = $event->getEntity();
		if ($player->isOp()){
			return;
		}
		if($this->ghost[$player->getName()] == false && $this->config["Enable"] == true) {
			$player->setHealth(20);
			$this->inventory[$player->getName()] = $player->getInventory()->getContents();
			$player->setGamemode(3);
			$this->alert($player, "당신은 유령이 되었습니다.");
			$this->alert($player, $this->config["sec"]."초 후 리스폰합니다.");
			$this->setGhost($player, true);
			$this->getServer()->getScheduler()->scheduleDelayedTask(new GhostTask($this, $player), $this->config["sec"] * 20);
 		}
	}
	public function onJoin(PlayerJoinEvent $event) {
		$this->ghost[$event->getPlayer()->getName()] = false;
	}
	public function onQuit(PlayerQuitEvent $event) {
		$player = $event->getPlayer();
		if($player->isSpectator())
			$player->setGamemode(0);
		if (!$player->isOp() && $this->ghost[$player->getName()] == true) {
			$player->getInventory()->setContents($this->inventory[$player->getName()]);
			$this->getServer()->getNetwork()->blockAddress($player-> getAddress(), $this->config["sec"] * 20);
		}
		unset($this->ghost[$player->getName()]);
	}
	public function setGhost(Player $player, $bool) {
		$this->ghost[$player->getName()] = $bool;
	}
	public function alert(CommandSender $sender, $message, $prefix = "[Ghost]"){
		$sender->sendMessage(TextFormat::RED.$prefix." $message");
	}
	public function loadDB() {
	$this->config = (new Config($this->getDataFolder()."config.json", Config::JSON, ["sec" => 30, "Enable" => true]))->getAll();
	}
	public function save($db, $param, $async = false) {
		$dbsave = (new Config ($this->getDataFolder().$db, Config::JSON));
		$dbsave->setAll($param);
		$dbsave->save($async);
	}
}
?>