<?php

declare(strict_types=1);
/*
 *  _          _____  _    _  _____           _
 * | |        |  __ \| |  | |/ ____|         | |
 * | |     ___| |__) | |  | | |  __  ___   __| |
 * | |    / _ \  _  /| |  | | | |_ |/ _ \ / _` |
 * | |___|  __/ | \ \| |__| | |__| | (_) | (_| |
 * |______\___|_|  \_\\____/ \_____|\___/ \__,_|
 *
 * @author : LeRUGod
 * @api : 3.x.x
 * @github : github.com/LeRUGod
 */

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
    public $sy = "§b§l[ §f길드 §b]§r ";

    /**@var self*/
    private static $instance;

    public const SUCCESS = 1;

    public const FAIL = 0;

    public const BECAUSE_DONT_HAVE_GUILD = -1;

    public const BECAUSE_HAVE_GUILD = -2;

    public const BECAUSE_NOT_EXIST_GUILD = -3;

    public const BECAUSE_IS_ADMIN = -4;

    public const BECAUSE_IS_MEMBER = -5;

    public const BECAUSE_MAX_GUILD = -6;

    public const BECAUSE_DONT_HAVE_POINTS = -7;

    public const BECAUSE_EXIST_SAME_GUILD = -8;

    public const BECAUSE_EXIST_SAME_ROLE = -9;

    public const BECAUSE_NOT_EXIST_ROLE = -10;

    public const BECAUSE_NOT_EXIST_PLAYER = -11;

    public const BECAUSE_EXIST_SAME_PLAYER = -12;

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
            return self::BECAUSE_HAVE_GUILD;
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
     * @param string $guildName
     * @return int
     */

    public function upgradeGuild(string $guildName) : int {
        if (!isset($this->db['guilds'][$guildName])){
            return self::BECAUSE_NOT_EXIST_GUILD;
        }else{
            $this->db['guilds'][$guildName]['level'] += 1;
            return self::SUCCESS;
        }
    }

    /**
     * @param string $guildName
     * @return int|null
     */

    public function getGuildLevel(string $guildName) : ?int {
        return isset($this->db['guilds'][$guildName]['level']) ? $this->db['guilds'][$guildName]['level'] : null;
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
     * @param string $guildName
     * @return array|null
     */

    public function getOnlineGuildMembers(string $guildName) : ?array {
        if ($this->getGuildMembers($guildName) === null){
            return null;
        }else{
            $guildMembers = $this->getGuildMembers($guildName);
            $online = [];
            foreach ($guildMembers as $member){
                if ($this->getServer()->getPlayer($member) !== null){
                    array_push($online,$member);
                }
            }
            return $online;
        }
    }

    /**
     * @param Player $player
     * @return string|null
     */

    public function getGuildByPlayer(Player $player) : ?string {
        return $this->db['players'][strtolower($player->getName())]['guild'];
    }

    /**
     * @param string $name
     * @return string|null
     */

    public function getGuildByName(string $name) : ?string {
        return $this->db['players'][strtolower($name)]['guild'];
    }

    /**
     * @param string $guildName
     * @param int $amount
     * @return int
     */

    public function addGuildPoint(string $guildName, int $amount) : int {
        if (!isset($this->db['guilds'][$guildName])){
            return self::BECAUSE_NOT_EXIST_GUILD;
        }else{
            $this->db['guilds'][$guildName]['points']+=$amount;
            $this->onSave();
            return self::SUCCESS;
        }
    }

    /**
     * @param string $guildName
     * @param int $amount
     * @return int
     */

    public function removeGuildPoint(string $guildName, int $amount) : int {
        if (!isset($this->db['guilds'][$guildName])){
            return self::BECAUSE_NOT_EXIST_GUILD;
        }else{
            $this->db['guilds'][$guildName]['points']-=$amount;
            $this->onSave();
            return self::SUCCESS;
        }
    }

    /**
     * @param string $guildName
     * @return int|null
     */

    public function getGuildPoint(string $guildName) : ?int {
        return isset($this->db['guilds'][$guildName]['points']) ? $this->db['guilds'][$guildName]['points'] : null;
    }

    /**
     * @param string $guildName
     * @param string $roleName
     * @param bool $isAdmin
     * @param bool $canAccept
     * @param bool $canKick
     * @return int
     */

    public function addGuildRole(string $guildName,string $roleName,bool $isAdmin,bool $canAccept,bool $canKick) : int {
        if (!isset($this->db['guilds'][$guildName])){
            return self::BECAUSE_NOT_EXIST_GUILD;
        }elseif (isset($this->db['guilds'][$guildName]['roles'][$roleName])){
            return self::BECAUSE_EXIST_SAME_ROLE;
        }else{

            array_push($this->db['guilds'][$guildName]['roles'],$roleName);

            $args = ['members' => [],'isAdmin' => $isAdmin,'canAccept' => $canAccept,'canKick' => $canKick];

            foreach ($args as $key => $value){
                $this->db['guilds'][$guildName]['roles'][$roleName][$key] = $value;
            }

            $this->onSave();

            return self::SUCCESS;
        }
    }

    /**
     * @param string $guildName
     * @param string $roleName
     * @return int
     */

    public function removeGuildRole(string $guildName,string $roleName) : int {
        if (!isset($this->db['guilds'][$guildName])){
            return self::BECAUSE_NOT_EXIST_GUILD;
        }elseif (!isset($this->db['guilds'][$guildName]['roles'][$roleName])){
            return self::BECAUSE_NOT_EXIST_ROLE;
        }else{

            foreach($this->db['guilds'][$guildName]['roles'][$roleName]['members'] as $member){
                $this->db['players'][$member]['role'] = null;
            }

            $this->onSave();

            return self::SUCCESS;

        }
    }

    /**
     * @param string $guildName
     * @return array|null
     */

    public function getGuildRoles(string $guildName) : ?array {
        return isset($this->db['guilds'][$guildName]['roles']) ? $this->db['guilds'][$guildName]['roles'] : null;
    }

    /**
     * @param string $name
     * @return string|null
     */

    public function getRole(string $name) : ?string {

        $name = strtolower($name);

        return isset($this->db['players'][$name]['role']) ? $this->db['players'][$name]['role'] : null;

    }

    /**
     * @param string $name
     * @param string $roleName
     * @return int
     */

    public function addRoleToPlayer(string $name,string $roleName) : int {

        if ($this->getGuildByName(strtolower($name)) === null){
            return self::BECAUSE_DONT_HAVE_GUILD;
        }elseif (!in_array($roleName,$this->getGuildRoles($this->getGuildByName(strtolower($name))))){
            return self::BECAUSE_NOT_EXIST_ROLE;
        }elseif (in_array(strtolower($name),$this->db['guilds'][$this->getGuildByName(strtolower($name))]['roles'][$roleName]['members'])) {
            return self::BECAUSE_EXIST_SAME_PLAYER;
        }else{

            array_push($this->db['guilds'][$this->getGuildByName(strtolower($name))]['roles'][$roleName]['members'],strtolower($name));
            $this->db['players'][strtolower($name)]['role'] = $roleName;

            $this->onSave();

            return self::SUCCESS;
        }

    }

    /**
     * @param string $name
     * @return int
     */

    public function removeRoleToPlayer(string $name) : int {

        if ($this->getGuildByName(strtolower($name)) === null){
            return self::BECAUSE_DONT_HAVE_GUILD;
        }elseif ($this->getRole(strtolower($name))){
            return self::BECAUSE_NOT_EXIST_ROLE;
        }else{

            $guildName = $this->getGuildByName(strtolower($name));

            foreach ($this->db['guilds'][$guildName]['roles'] as $role){
                foreach ($role['members'] as $member){
                    if ($member === strtolower($name)){

                        unset($this->db['guilds'][$guildName]['roles'][$role]['members'][array_search(strtolower($name),$this->db['guilds'][$guildName]['roles'][$role]['members'])]);
                        break;

                    }
                }
            }

            $this->onSave();

            return self::SUCCESS;

        }

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
     * @param string $guildName
     * @return int
     *
     * Function to accept guild subscription application
     */

    public function acceptGuildRequest(string $name,string $guildName) : int {
        if (!isset($this->db['guilds'][$guildName])){
            return self::BECAUSE_NOT_EXIST_GUILD;
        }elseif($this->db['players'][strtolower($name)]['guild'] !== null){
            return self::BECAUSE_HAVE_GUILD;
        }elseif (!array_key_exists(strtolower($name),$this->db['guilds'][$guildName]['requests'])){
            return self::BECAUSE_NOT_EXIST_PLAYER;
        }else{
            $i = $this->joinGuild($name,$guildName);
            if ($i === self::BECAUSE_MAX_GUILD){
                return self::BECAUSE_MAX_GUILD;
            }
            $array = array_keys([strtolower($name)],$this->db['guilds'][$guildName]['requests']);
            foreach ($array as $key){
                unset($this->db['guilds'][$guildName]['requests'][$key]);
            }

            $this->onSave();
            return self::SUCCESS;
        }
    }

    /**
     * @param string $name
     * @param string $guildName
     * @return int
     *
     * Function to reject guild subscription application
     */

    public function rejectGuildRequest(string $name,string $guildName) : int {
        if (!isset($this->db['guilds'][$guildName])){
            return self::BECAUSE_NOT_EXIST_GUILD;
        }elseif($this->db['players'][strtolower($name)]['guild'] !== null){
            return self::BECAUSE_HAVE_GUILD;
        }elseif (!array_key_exists(strtolower($name),$this->db['guilds'][$guildName]['requests'])){
            return self::BECAUSE_NOT_EXIST_PLAYER;
        }else{
            $array = array_keys([strtolower($name)], $this->db['guilds'][$guildName]['requests']);
            foreach ($array as $key) {
                unset($this->db['guilds'][$guildName]['requests'][$key]);
            }
            $this->db['players'][strtolower($name)]['guildRequest'] = null;
            $this->onSave();
            return self::SUCCESS;
        }
    }

    /**
     * @param string $name
     * @param string $guildName
     * @return int
     *
     * Function to send guild subscription application
     */

    public function sendGuildRequest(string $name, string $guildName) : int {

        if (!isset($this->db['guilds'][$guildName])){
            return self::BECAUSE_NOT_EXIST_GUILD;
        }elseif($this->db['players'][strtolower($name)]['guild'] !== null){
            return self::BECAUSE_HAVE_GUILD;
        }else{
            $this->db['players'][strtolower($name)]['guildRequest'] = $guildName;
            array_push($this->db['guilds'][$guildName]['requests'],strtolower($name));
            $this->onSave();

            return self::SUCCESS;
        }

    }

    /**
     * @param string $name
     * @param string $victim
     * @return int
     *
     * Function to kick guild members
     */

    public function kickGuildMember(string $name, string $victim){

        $role = $this->getRole(strtolower($name));
        $guild1 = $this->getGuildByName(strtolower($name));
        $guild2 = $this->getGuildByName(strtolower($victim));

        if (!$this->isAdminRole($role,$guild1)){
            return self::BECAUSE_IS_MEMBER;
        }elseif (!$guild1 === $guild2){
            return self::BECAUSE_NOT_EXIST_PLAYER;
        }else{
            $this->quitGuild(strtolower($victim));
            return self::SUCCESS;
        }

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

        if (isset($this->db['players'][$name]['guild'])){

            foreach ($this->getOnlineGuildMembers($this->db['players'][$name]['guild']) as $member){
                if ($member instanceof Player){
                    $member->sendMessage($this->sy."§l§f".$event->getPlayer()->getName()." 님이 접속하셨습니다!");
                }
            }

        }

    }

    /**
     * @param PlayerQuitEvent $event
     */

    public function onQuit(PlayerQuitEvent $event){

        $name = strtolower($event->getPlayer()->getName());

        if (isset($this->db['players'][$name]['guild'])){

            foreach ($this->getOnlineGuildMembers($this->db['players'][$name]['guild']) as $member){
                if ($member instanceof Player){
                    $member->sendMessage($this->sy."§l§f".$event->getPlayer()->getName()." 님이 접속하셨습니다!");
                }
            }

        }

    }
}