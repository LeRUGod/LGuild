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

    public const SUCCESS = 1;
    public const BECAUSE_DONT_HAVE_GUILD = -1;
    public const BECAUSE_GUILD_EXIST = -2;
    public const BECAUSE_IS_ADMIN = -3;
    public const BECAUSE_IS_MEMBER = -4;
    public const BECAUSE_MAX_GUILD = -5;
    public const BECAUSE_DONT_HAVE_POINTS = -6;
    public const BECAUSE_EXIST_SAME_GUILD = -7;

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
     * @param string $nam
     * @param string $guildName
     * @return int
     */

    public function makeGuild(string $nam, string $guildName) : int {

        $name = strtolower($nam);

        if (isset($this->db[strtolower($name)]['guild'])){
            return self::BECAUSE_GUILD_EXIST;
        }

        $array = ['§0','§1','§2','§3','§4','§5','§6','§7','§8','§9','§a','§b','§c','§d','§e','§f','§l','§m','§n','§o'];

        $realGuildName = str_replace($array,"",$guildName);

        if (isset($this->db['guilds'][$realGuildName])){
            return self::BECAUSE_EXIST_SAME_GUILD;
        }

        $arr = ['admins','members','roles','requests'];

        foreach ($arr as $item){

            $this->db['guilds'][$realGuildName][$item] = [];

        }

        $this->db['guilds'][$realGuildName]['points'] = 0;
        $this->db['guilds'][$realGuildName]['level'] = 1;

        $this->db['guilds'][$realGuildName]['admins'] = strtolower($name);
        array_push($this->db['guilds'][$realGuildName]['members'],strtolower($name));
        array_push($this->db['guilds'][$realGuildName]['roles'],'admin','member');

        $args = ['members' => [],'isAdmin' => true,'canAccept' => true,'canKick' => true];

        foreach ($args as $key => $value){
            $this->db['guilds'][$realGuildName]['roles']['admin'][$key] = $value;
        }

        array_push($this->db['guilds'][$realGuildName]['roles']['admin']['members'],strtolower($name));

        $args = ['members' => [],'isAdmin' => false,'canAccept' => false,'canKick' => false];

        foreach ($args as $key => $value){
            $this->db['guilds'][$realGuildName]['roles']['member'][$key] = $value;
        }

        $this->db['players'][strtolower($name)]['guild'] = $realGuildName;
        $this->db['players'][strtolower($name)]['role'] = 'admin';

        $this->onSave();

        return self::SUCCESS;

    }

    /**
     * @param string $nam
     * @return int
     */

    public function deleteGuild(string $nam) : int {

        $name = strtolower($nam);

        if (!isset($this->db['players'][$name]['guild'])){
            return self::BECAUSE_DONT_HAVE_GUILD;
        }

        $guild = $this->db['players'][$name]['guild'];

        if ($this->getGuildAdmin($guild) === $name){

            foreach ($this->db['guilds'][$guild]['members'] as $member){

                $array = ['guild','role','guildRequest'];

                foreach ($array as $item){
                    $this->db['players'][$member][$item] = null;
                }

                unset($item);

            }

            unset($this->db['guilds'][$guild]);

            $array = ['guild','role','guildRequest'];

            foreach ($array as $item){
                $this->db['players'][$name][$item] = null;
            }

            unset($item);

            $this->onSave();

            return self::SUCCESS;

        }else{
            return self::BECAUSE_IS_MEMBER;
        }

    }

    /**
     * @param string $name
     * @param string $guildName
     * @return int
     */

    public function joinGuild(string $name,string $guildName) : int {

        $name1 = strtolower($name);

        if (count($this->db['guilds'][$guildName]['members']) >= $this->db['guilds'][$guildName]['level'] * 5){
            return self::BECAUSE_MAX_GUILD;
        }

        array_push($this->db['guilds'][$guildName]['members'],$name1);
        array_push($this->db['guilds'][$guildName]['roles']['member']['members'],$name1);

        $this->db['players'][$name1]['guild'] = $guildName;
        $this->db['players'][$name1]['role'] = 'member';
        $this->db['players'][$name1]['guildRequest'] = null;

        $this->onSave();

        return self::SUCCESS;

    }

    /**
     * @param string $nam
     * @return int
     */

    public function quitGuild(string $nam) : int {

        $name = strtolower($nam);

        $guildName = $this->db['players'][$name]['guild'];

        if ($guildName === null){
            return self::BECAUSE_DONT_HAVE_GUILD;
        }

        if ($this->isGuildAdmin($name,$guildName)){
            return self::BECAUSE_IS_ADMIN;
        }

        $array = ['guild','role','guildRequest'];

        foreach ($array as $item){
            $this->db['players'][$name][$item] = null;
        }

        foreach ($this->db['guilds'][$guildName]['roles'] as $role){

            foreach ($role['members'] as $member){

                if ($member === $name){

                    unset($this->db['guilds'][$guildName]['roles'][$role]['members'][array_search($name,$this->db['guilds'][$guildName]['roles'][$role]['members'])]);
                    break;

                }

            }

        }

        $this->onSave();

        return self::SUCCESS;

    }

    /**
     * @param Player $player
     */

    public function upgradeGuild(Player $player) : void {

    }

    /**
     * @param string $name
     * @param string $guildName
     * @return bool
     */

    public function isGuildAdmin(string $name, string $guildName) : bool {
        return strtolower($name) === $this->getGuildAdmin($guildName);
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
     * @return array|null
     */

    public function getGuildMembers(string $guildName) : ?array {
        return isset($this->db['guilds'][$guildName]) ? $this->db['guilds'][$guildName]['members'] : null;
    }

    /**
     * @param Player $player
     * @return string
     */

    public function getGuildByPlayer(Player $player) : ?string {
        return $this->db['players'][strtolower($player->getName())]['guild'];
    }

    /**
     * @param string $name
     * @return string
     */

    public function getGuildByName(string $name) : ?string {
        return $this->db['players'][strtolower($name)]['guild'];
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