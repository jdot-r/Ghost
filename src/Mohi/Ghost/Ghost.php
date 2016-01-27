namespace Mohi\Ghost;

use pocketmine\plugin\PluginBase;
use pocketmine\event\Listener;
use pocketmine\Player;
use pocketmine\event\player\PlayerDeathEvent;
use pocketmine\command\Command;
use pocketmine\command\CommandSender;

class Ghost extends PluginBase implements Listener {
	private $ghost, $config;
	public function onEnable() {
		$this->config = $this->loadDB();
		if(! isset($this->config[sec]))
			$this->config["sec"] = 120;
			$this->config["Enable"] = "true";
		$this->getServer()->getPluginManager()->registerEvent($this, $this);
	}
	public function onDisable() {
		$this->save("config.json", $this->config);
	}
	public function onCommand(CommandSender $sender, Command $command, $label, Array $args) {
		if(strlower($args[0]) == ghost) {
			if(! isset($args[1])) {
				$this->alert($sender, "/ghost <on|off|sec>");
				return;
			}
			switch(strlower($args[1])) {
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
					if($this->config["sec"] === $args[2])
						$this->config["sec"] = $args[2];
					else
						$this->alert($sender, "숫자를 적어주세요");
			}
		}
	}
	public function onDeath(PlayerDeathEvent $event) {
		if($this->ghost[$event->getPlayer()->getName()] == true && $this->config["Enable"] == "true") {
			$event->getEntity()->setHealth(20);
			$event->getPlayer()->setGamemode(3);
			Server::getInstance()->getScheduler()-	>scheduleDelayTask(new GhostTask($this, $event->getPlayer(), $this->ghost), $this->ghostsec * 30);
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
		$dbsave = (new Config ($this-getDataFolder().$db, Config::JSON));
		$dbsave->setAll($param);
		$dbsave->save();
		return;
	}
}