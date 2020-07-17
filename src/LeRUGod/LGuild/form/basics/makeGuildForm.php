<?php


namespace LeRUGod\LGuild\form\basics;


use LeRUGod\LGuild\LGuild;
use pocketmine\form\Form;
use pocketmine\Player;

class makeGuildForm implements Form
{

    public function jsonSerialize() {
        $content = [
            [
                'type' => 'input',
                'text' => '생성할 길드의 이름을 적어주세요!',
                'placeholder' => '색코드 적용은 불가능합니다!'
            ],
            [
                'type' => 'input',
                'text' => '§c주의 : 한번 생성한 길드의 이름은 다시 바꿀 수 없습니다! 정말 생성하시겠습니까?',
                'placeholder' => '동의합니다'
            ]
        ];

        return [
            'type' => 'custom_form',
            'title' => '§l§fMAKE GUILD',
            'content' => $content
        ];
    }

    public function handleResponse(Player $player, $data): void {
        if ($data === null)return;
        if ($data[0] === null){
            $player->sendMessage(LGuild::getInstance()->sy."§l§f길드의 이름을 적어주세요!");
            return;
        }elseif (mb_strlen($data[0])>=15){
            $player->sendMessage(LGuild::getInstance()->sy."§l§f길드의 이름이 너무 깁니다!");
            return;
        }elseif ($data[1] !== "동의합니다"){
            $player->sendMessage(LGuild::getInstance()->sy."§l§f확인문자가 입력되지 않았습니다!");
            return;
        }else{
            $a = LGuild::getInstance()->makeGuild(strtolower($player->getName()),$data[0]);
            if ($a === LGuild::BECAUSE_EXIST_SAME_GUILD){
                $player->sendMessage(LGuild::getInstance()->sy."§l§f이미 같은 이름의 길드가 존재합니다!");
                return;
            }elseif ($a === LGuild::BECAUSE_HAVE_GUILD){
                $player->sendMessage(LGuild::getInstance()->sy."§l§f이미 길드가 존재합니다!");
                return;
            }elseif ($a === LGuild::SUCCESS){
                LGuild::getInstance()->getServer()->broadcastMessage(LGuild::getInstance()->sy."§l§f새로운 길드 ".$data[0]." 길드가 생성되었습니다!");
            }else return;
        }
    }

}