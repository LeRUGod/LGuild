<?php


namespace LeRUGod\LGuild\form\requests;


use LeRUGod\LGuild\LGuild;
use pocketmine\form\Form;
use pocketmine\Player;

class sendRequestForm implements Form
{

    public function jsonSerialize()
    {
        $content = [
            [
                'type' => 'input',
                'text' => '가입신청할 길드의 이름을 입력해주세요!',
                'placeholder' => '정확히 입력해주세요!'
            ]
        ];

        return [
            'type' => 'custom_form',
            'title' => '§l§fSEND REQUEST',
            'content' => $content
        ];
    }

    public function handleResponse(Player $player, $data): void
    {
        if ($data === null)return;
        if ($data[0] === null){
            $player->sendMessage(LGuild::getInstance()->sy."§l§f신청을 보낼 길드 이름을 써주세요!");
            return;
        }else{
            $a = LGuild::getInstance()->sendGuildRequest(strtolower($player->getName()),$data[0]);
            if ($a === LGuild::BECAUSE_NOT_EXIST_GUILD){
                $player->sendMessage(LGuild::getInstance()->sy."§l§f존재하지 않는 길드입니다!");
            }elseif ($a === LGuild::BECAUSE_HAVE_GUILD){
                $player->sendMessage(LGuild::getInstance()->sy."§l§f이미 길드에 가입되어있습니다!");
            }elseif ($a === LGuild::SUCCESS){
                $player->sendMessage(LGuild::getInstance()->sy."§l§f{$data[0]} 길드에 성공적으로 가입신청을 보냈습니다!");
                return;
            }else{
                return;
            }
        }
    }

}