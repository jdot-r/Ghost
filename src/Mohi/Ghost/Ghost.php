<?php
namespace Mohi\Ghost;

use pocketmine\plugin\PluginBase;
use pocketmine\event\Listener;
use pocketmine\Player;
use pocketmine\Server;
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
	public $ghost, $config;
	public function onEnable() {
		@mkdir($this->getDataFolder());
		$this->config = $this->loadDB();
		if(! isset($this->config["sec"]))
			$this->config["sec"] = 30;
		if(! isset($this->config["Enable"]))
			$this->config["Enable"] = "true";
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
		if(strtolower($command) == "ghost") {
			if(! isset($args[0])) {
				$this->alert($sender, "/ghost <on|off|sec>");
				return true;
			}
			switch(strlower($args[0])) {
				case on:
					$this->config["Enable"] = "true";
					$this->save("config.json", $this->config);
					break;
				case off:
					$this->config["Enable"] = "false";
					$this->save("config.json", $this->config);
					break;
				case sec:
					if(! isset($args[2])){
						$this->alert($sender, "초(sec)를 적어주세요");
						break;
					}
					$this->config["sec"] = $args[2];
					break;
			}
			return true;
		}
	}
	public function onDeath(PlayerDeathEvent $event) {
		if($this->ghost[$event->getEntity()->getName()] == true && $this->config["Enable"] == "true") {
			$this->alert($event->getEntity(), "당신은 유령이 되었습니다.");
		 $this->alert($event->getEntity(), $this->config["sec"]."초 후 리스폰합니다.");
			$event->getEntity()->setHealth(20);
			$event->getEntity()->setGamemode(3);
			Server::getInstance()->getScheduler()->scheduleDelayedTask(new GhostTask($this, $event->getEntity()), $this->config["sec"] * 20);
 		}
	}
	public function onJoin(PlayerJoinEvent $event) {
		if($event->getPlayer()->getGamemode() == 3)
			$event->getPlayer()->setGamemode(0);
		$this->ghost[$event->getPlayer()->getName()] = true;
		return;
	}
	public function onQuit(PlayerQuitEvent $event) {
		unset($this->ghost[$event->getPlayer()->getName()]);
		return;
	}
	public function setGhost(Player $player, $bool) {
		$this->ghost[$player->getName()] = $bool;
	}
	public function alert(Player $player, $message, $prefix = NULL){
		if($prefix==NULL){
			$prefix = "[Ghost]";
		}
		$player->sendMessage(TextFormat::RED.$prefix." $message");
		return;
	}
	
	public function message(Player $player, $message, $prefix = NULL){
		if($prefix==NULL){
			$prefix = "[Ghost]";
		}
		$player->sendMessage(TextFormat::DARK_AQUA.$prefix." $message");
		return;
	}
	 public function loadDB() {
		$this->config = (new Config($this->getDataFolder()."config.json", Config::JSON))->getAll();
		return;
	}
	public function save($db, $param) {
		$dbsave = (new Config ($this->getDataFolder().$db, Config::JSON));
		$dbsave->setAll($param);
		$dbsave->save();
		return;
	}
}