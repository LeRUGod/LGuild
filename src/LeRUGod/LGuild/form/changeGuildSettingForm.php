<?php


namespace LeRUGod\LGuild\form\mains;


use LeRUGod\LGuild\form\roles\addRoleForm;
use pocketmine\form\Form;
use pocketmine\Player;

class changeGuildSettingForm implements Form
{

    public function jsonSerialize()
    {
        $buttons = [
                ['text' => "§l§e☆ §f역할 추가 §e☆§r\n역할을 추가해보세요!"],
                ['text' => "§l§e☆ §f역할 삭제 §e☆§r\n역할을 삭제 할 수 있습니다!"],
                ['text' => "§l§e☆ §f역할 부여 §e☆§r\n역할을 부여해보세요!"],
                ['text' => "§l§e☆ §f멤버 추방 §e☆§r\n길드 멤버를 추방 할 수 있습니다!"],
                ['text' => "§l§e☆ §f가입신청 관리 §e☆§r\n가입신청 관리 메뉴에 들어갑니다!"],
                ['text' => "§l§e☆ §f길드 삭제 §e☆§r\n§c주의 : 한번 삭제한 길드는 복구할 수 없습니다!"],
        ];

        return [
            'type' => 'form',
            'title' => '§l§fSETTING',
            'content' => '',
            'buttons' => $buttons
        ];
    }

    public function handleResponse(Player $player, $data): void
    {
        if ($data === null)return;
        if ($data === 0){
            $player->sendForm(new addRoleForm());
        }
    }

}