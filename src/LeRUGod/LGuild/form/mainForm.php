<?php


namespace LeRUGod\LGuild\form\mains;


use LeRUGod\LGuild\form\requests\sendRequestForm;
use LeRUGod\LGuild\form\basics\quitGuildForm;
use LeRUGod\LGuild\form\basics\makeGuildForm;
use LeRUGod\LGuild\form\mains\changeGuildSettingForm;
use LeRUGod\LGuild\LGuild;
use pocketmine\form\Form;
use pocketmine\Player;

class mainForm implements Form
{

    private $name;

    public function __construct(string $name)
    {
        $this->name = strtolower($name);
    }

    public function jsonSerialize()
    {

        $guild = LGuild::getInstance()->getGuildByName($this->name);

        $buttons = [
            ['text' => "§l§e☆ §f길드 가입 §e☆§r\n길드에 가입해보세요!"],
            ['text' => "§l§e☆ §f길드 탈퇴 §e☆§r\n길드를 탈퇴할 수 있습니다!"],
            ['text' => "§l§e☆ §f길드 생성 §e☆§r\n길드를 생성해보세요!"],
            ['text' => "§l§e☆ §f길드 관리 §e☆§r\n자신의 길드를 관리할 수 있습니다!"]
        ];

        if ($guild === null){

            $return = [
                'type' => 'form',
                'title' => '§l§fGUILD MENU',
                'content' => "아직 길드에 가입하지 않으셨습니다!\n길드에 가입해보세요!\n\n",
                'buttons' => $buttons
            ];
        }else{

            $members = "";

            foreach (LGuild::getInstance()->getGuildMembers($guild) as $guildMember) {

                $members = $members.$guildMember."\n";

            }

            $return = [
                'type' => 'form',
                'title' => '§l§fGUILD MENU',
                'content' => "\n\n소속된 길드 이름 : ".$guild
                    ."\n길드 설립자 : ".LGuild::getInstance()->getGuildAdmin($guild)
                    ."\n길드 멤버 : ".$members
                    ."\n당신의 역할 : ".LGuild::getInstance()->getRole($this->name)
                    ."\n길드 포인트 : ".LGuild::getInstance()->getGuildPoint($guild)
                    ."\n길드 레벨 : ".LGuild::getInstance()->getGuildLevel($guild)
                    ."\n\n",
                'buttons' => $buttons
            ];

        }

        return $return;
    }

    /**
     * @inheritDoc
     */
    public function handleResponse(Player $player, $data): void
    {
        if ($data === null)return;
        if ($data === 0){
            $player->sendForm(new sendRequestForm());
        }elseif ($data === 1){
            $player->sendForm(new quitGuildForm());
        }elseif ($data === 2){
            $player->sendForm(new makeGuildForm());
        }elseif ($data === 3){
            if (LGuild::getInstance()->isGuildAdmin(strtolower($player->getName()),LGuild::getInstance()->getGuildByName(strtolower($player->getName())))){
                $player->sendForm(new changeGuildSettingForm());
            }else{
                $player->sendMessage(LGuild::getInstance()->sy."§l§f길드의 설립자만 길드 관리 메뉴에 접속할 수 있습니다!");
                return;
            }
        }else return;
    }

}