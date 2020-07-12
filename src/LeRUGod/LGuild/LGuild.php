<?php

declare(strict_types=1);

namespace LeRUGod\LGuild;

use pocketmine\event\player\PlayerJoinEvent;
use pocketmine\event\player\PlayerQuitEvent;
use pocketmine\Player;
use pocketmine\plugin\PluginBase;
use pocketmine\event\Listener;
use pocketmine\utils\Config;

/**
 * Class LGuild
 * @package LeRUGod\LGuild
 */

class LGuild extends PluginBase implements Listener
{

    /**@var Config*/
    public $data;

    /**@var array*/
    public $db;

    /**@var string*/
    public $sy = "§b§l[ §f시스템 §b]§r ";

    /**@var self*/
    private static $instance;

    /*
     * PluginBase Part
     */

    public function onEnable() : void {
        @mkdir($this->getDataFolder());
        $this->data = new Config($this->getDataFolder().'guild.yml',Config::YAML);
        $this->db = $this->data->getAll();

        if (empty($this->db)){
            $this->db['guilds'] = [];
            $this->db['players'] = [];
            $this->onSave();
        }

        $this->getServer()->getPluginManager()->registerEvents($this,$this);
    }

    public function onLoad() : void {
        self::$instance = $this;
    }

    public function onSave() : void {
        $this->data->setAll($this->db);
        $this->data->save();
    }

    /**
     * @return self
     */

    public static function getInstance() : self {
        return self::$instance;
    }

    /*
     * Main Functions
     */

    /**
     * @param Player $player
     * @param string $guildName
     */

    public function makeGuild(Player $player, string $guildName) : void {

        if (isset($this->db[strtolower($player->getName())]['guild'])){

            $player->sendMessage($this->sy."§l§f현재 가입한 길드를 나온 뒤에 길드를 만들어주세요!");
            return;

        }

        $array = ['§0','§1','§2','§3','§4','§5','§6','§7','§8','§9','§a','§b','§c','§d','§e','§f','§l','§m','§n','§o'];

        $realGuildName = str_replace($array,"",$guildName);

        if (isset($this->db['guilds'][$realGuildName])){
            $player->sendMessage($this->sy."§l§f이미 있는 길드이름 입니다!");
            return;
        }

        $arr = ['admins','members','roles','requests'];

        foreach ($arr as $item){

            $this->db['guilds'][$realGuildName][$item] = [];

        }

        $this->db['guilds'][$realGuildName]['admins'] = strtolower($player->getName());
        array_push($this->db['guilds'][$realGuildName]['members'],strtolower($player->getName()));
        array_push($this->db['guilds'][$realGuildName]['roles'],'admin','member');

        $args = ['members' => [],'isAdmin' => true,'canAccept' => true,'canKick' => true];

        foreach ($args as $key => $value){
            $this->db['guilds'][$realGuildName]['roles']['admin'][$key] = $value;
        }

        array_push($this->db['guilds'][$realGuildName]['roles']['admin']['members'],strtolower($player->getName()));

        $args = ['members' => [],'isAdmin' => false,'canAccept' => false,'canKick' => false];

        foreach ($args as $key => $value){
            $this->db['guilds'][$realGuildName]['roles']['member'][$key] = $value;
        }

        $this->db['players'][strtolower($player->getName())]['guild'] = $realGuildName;
        $this->db['players'][strtolower($player->getName())]['role'] = 'admin';

        $this->onSave();

        $player->sendMessage($this->sy."§l§f성공적으로 길드가 생성되었습니다!");
        $player->sendMessage($this->sy."§l§f생성한 길드의 이름은 {$realGuildName} 입니다!");

    }

    /**
     * @param Player $player
     */

    public function deleteGuild(Player $player) : void {

        $name = strtolower($player->getName());

        if (!isset($this->db['players'][$name]['guild'])){

            $player->sendMessage($this->sy."§l§f길드에 가입되어있지 않습니다!");
            return;

        }

        $guild = $this->db['players'][$name]['guild'];

        if ($this->getGuildAdmin($guild) === $name){

            unset($this->db['guilds'][$guild]);

            $array = ['guild','role','guildRequest'];

            foreach ($array as $item){
                $this->db['players'][$name][$item] = null;
            }

            $this->onSave();

            $player->sendMessage($this->sy."§l§f길드가 성공적으로 삭제되었습니다!");

        }else{

            $player->sendMessage($this->sy."§l§f관리자 권한을 가진 사람만 길드를 삭제할 수 있습니다!");
            return;

        }

    }

    /**
     * @param Player $player
     */

    public function joinGuild(Player $player) : void {

    }

    /**
     * @param Player $player
     */

    public function quitGuild(Player $player) : void {

    }

    /**
     * @param Player $player
     */

    public function upgradeGuild(Player $player) : void {

    }

    /**
     * @param string $guildName
     * @return string|null
     */

    public function getGuildAdmin(string $guildName) : ?string {
        return isset($this->db['guilds'][$guildName]['admins']) ? $this->db['guilds'][$guildName]['admins'] : null;
    }

    /**
     * @param string $guildName
     * @return array
     */

    public function getGuildMembers(string $guildName) : array {

    }

    /**
     * @param Player $player
     * @return string
     */

    public function getGuildByPlayer(Player $player) : string {

    }

    /**
     * @param string $name
     * @return string
     */

    public function getGuildByName(string $name) : string {

    }

    /**
     * @param string $guildName
     */

    public function addGuildPoint(string $guildName) : void {

    }

    /**
     * @param string $guildName
     */

    public function removeGuildPoint(string $guildName) : void {

    }

    /**
     * @param string $guildName
     * @return int
     */

    public function getGuildPoint(string $guildName) : int {

    }

    /**
     * @param Player $player
     * @param string $roleName
     * @param bool $isAdmin
     * @param bool $canAccept
     * @param bool $canKick
     */

    public function addGuildRole(Player $player,string $roleName,bool $isAdmin,bool $canAccept,bool $canKick) : void {

    }

    /**
     * @param Player $player
     * @param string $roleName
     */

    public function removeGuildRole(Player $player,string $roleName) : void {

    }

    /**
     * @param string $guildName
     * @return array
     */

    public function getGuildRoles(string $guildName) : array {

    }

    /**
     * @param string $name
     * @return string|null
     */

    public function getRole(string $name) : ?string {

        $name = strtolower($name);

        $role = $this->db['players'][$name]['role'];

        return is_string($role) ? $role : null;

    }

    /**
     * @param string $guildName
     * @param string $roleName
     * @return bool|null
     */

    public function isAdminRole(string $guildName,string $roleName) : ?bool {
        return isset($this->db['guilds'][$guildName]['roles'][$roleName]['isAdmin']) ? $this->db['guilds'][$guildName]['roles'][$roleName]['isAdmin'] : null;
    }

    /**
     * @param string $guildName
     * @param string $roleName
     * @return bool|null
     */

    public function isCanAcceptRole(string $guildName,string $roleName) : ?bool {
        return isset($this->db['guilds'][$guildName]['roles'][$roleName]['canAccept']) ? $this->db['guilds'][$guildName]['roles'][$roleName]['canAccept'] : null;
    }

    /**
     * @param string $guildName
     * @param string $roleName
     * @return bool|null
     */

    public function isCanKickRole(string $guildName,string $roleName) : ?bool {
        return isset($this->db['guilds'][$guildName]['roles'][$roleName]['canKick']) ? $this->db['guilds'][$guildName]['roles'][$roleName]['canKick'] : null;
    }

    /**
     * @param string $name
     */

    public function acceptGuildRequest(string $name) : void {

    }

    /**
     * @param string $name
     */

    public function rejectGuildRequest(string $name) : void {

    }

    /**
     * @param Player $player
     * @param string $victim
     */

    public function kickGuildMember(Player $player, string $victim){

    }

    /*
     * Using Events
     */

    /**
     * @param PlayerJoinEvent $event
     */

    public function onJoin(PlayerJoinEvent $event){

        $name = strtolower($event->getPlayer()->getName());

        if (!isset($this->db[$name])){

            $array = ['guild','role','guildRequest'];

            foreach ($array as $item){
                $this->db['players'][$name][$item] = null;
            }

            $this->onSave();

        }

        /*
         * TODO : MAKE JOIN MESSAGE
         */

    }

    /**
     * @param PlayerQuitEvent $event
     */

    public function onQuit(PlayerQuitEvent $event){

        /*
         * TODO : MAKE QUIT MESSAGE
         */

    }
}