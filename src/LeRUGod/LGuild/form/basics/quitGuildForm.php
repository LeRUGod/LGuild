<?php


namespace LeRUGod\LGuild\form\basics;


use LeRUGod\LGuild\LGuild;
use pocketmine\form\Form;
use pocketmine\Player;

class quitGuildForm implements Form
{

    public function jsonSerialize()
    {
        return [
            'type' => 'custom_form',
            'title' => '§l§cQUITGUILD',
            'content' => "\n정말 탈퇴하시겠습니까?\n",
            'button' => [
                [
                    'text' => "§l§c예\n§r§c한번 나간 길드는 다시 복구가 불가능합니다!"
                ],
                [
                    'text' => '§l§f아니오'
                ]
            ]
        ];
    }

    public function handleResponse(Player $player, $data): void
    {
        if ($data === null)return;
        if ($data === 0){
            $a = LGuild::getInstance()->quitGuild(strtolower($player->getName()));
            if ($a === LGuild::BECAUSE_DONT_HAVE_GUILD){
                $player->sendMessage(LGuild::getInstance()->sy."§l§f탈퇴할 길드가 존재하지 않습니다!");
                return;
            }elseif ($a === LGuild::BECAUSE_IS_ADMIN){
                $player->sendMessage(LGuild::getInstance()->sy."§l§f길드의 어드민은 길드를 탈퇴할 수 없습니다!");
                return;
            }elseif ($a === LGuild::SUCCESS){
                $player->sendMessage(LGuild::getInstance()->sy."§l§f성공적으로 길드를 탈퇴하였습니다!");
                return;
            }
        }elseif ($data === 1){
            return;
        }else return;
    }

}