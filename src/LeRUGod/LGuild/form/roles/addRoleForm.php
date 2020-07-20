<?php


namespace LeRUGod\LGuild\form\roles;


use LeRUGod\LGuild\LGuild;
use pocketmine\form\Form;
use pocketmine\Player;

class addRoleForm implements Form
{

    public function jsonSerialize() {
        $content = [
            [
                'type' => 'input',
                'text' => '역할의 이름을 써주세요!',
                'placeholder' => '색코드 사용은 안됩니다!'
            ],
            [
                'type' => 'toggle',
                'text' => '역할의 관리자 여부를 정해주세요!',
                'default' => 'false'
            ],
            [
                'type' => 'toggle',
                'text' => '역할의 멤버 가입신청 수락 가능 여부를 정해주세요!',
                'default' => 'false'
            ],
            [
                'type' => 'toggle',
                'text' => '역할의 멤버 추방 가능 여부를 정해주세요!',
                'default' => 'false'
            ]
        ];

        return [
            'type' => 'custom_form',
            'title' => '§l§fADD ROLE',
            'content' => $content
        ];
    }

    public function handleResponse(Player $player, $data): void {

        if ($data === null)return;

        if ($data[0] === null){
            $player->sendMessage(LGuild::getInstance()->sy."§l§f역할의 이름을 입력해주세요!");
            return;
        }elseif (LGuild::getInstance()->isAdminRole(LGuild::getInstance()->getGuildByName(strtolower($player->getName())),LGuild::getInstance()->getRole(strtolower($player->getName()))) or LGuild::getInstance()->isGuildAdmin(strtolower($player->getName()),LGuild::getInstance()->getGuildByName(strtolower($player->getName())))){
            $a = LGuild::getInstance()->addGuildRole(LGuild::getInstance()->getGuildByName(strtolower($player->getName())),$data[0],$data[1],$data[2],$data[3]);
            if ($a === LGuild::BECAUSE_NOT_EXIST_GUILD){
                $player->sendMessage(LGuild::getInstance()->sy."§l§f길드가 존재하지 않습니다!");
                return;
            }elseif ($a === LGuild::BECAUSE_EXIST_SAME_ROLE){
                $player->sendMessage(LGuild::getInstance()->sy."§l§f이미 같은 이름의 역할이 존재합니다!");
                return;
            }elseif ($a === LGuild::SUCCESS){
                $player->sendMessage(LGuild::getInstance()->sy."§l§f역할이 성공적으로 생성되었습니다!");
                return;
            }else return;
        }else{
            $player->sendMessage(LGuild::getInstance()->sy."§l§f길드의 관리자만 실행할 수 있는 작업입니다!");
            return;
        }

    }

}