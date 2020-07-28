<?php


namespace LeRUGod\LGuild\form\requests;


use LeRUGod\LGuild\LGuild;
use pocketmine\form\Form;
use pocketmine\Player;

class receiveRequestForm implements Form
{

    /**@var string*/
    private $name;

    /**@var null|string*/
    private $guild;

    /**
     * receiveRequestForm constructor.
     * @param string $name
     */
    public function __construct(string $name)
    {
        $this->name = strtolower($name);
        $this->guild = LGuild::getInstance()->getGuildByName($this->name);
    }

    /**
     * @return mixed|void
     */
    public function jsonSerialize()
    {
        if ($this->guild === null){
            $content = "길드가 존재하지 않습니다!";
            $button = null;
        }else{
            $content = "터치할 시 자동으로 가입 수락됩니다!";
            $button = [];
            $requests = LGuild::getInstance()->getGuildRequests($this->guild);
            sort($requests);
            foreach ($requests as $request){
                array_push($button,[
                    'text' => $request."\n§r§a●§f 터치시 자동으로 가입 수락됩니다!"
                ]);
            }
            array_push($button,[
                'text' => "§l§c가입신청 모두 거절\n§r§f가입신청을 모두 거절합니다!"
            ]);
        }
        return [
            'type' => 'form',
            'title' => '§l§fACCEPT REQUEST',
            'content' => $content,
            'button' => $button
        ];
    }

    /**
     * @param Player $player
     * @param mixed $data
     */
    public function handleResponse(Player $player, $data): void
    {
        if ($data === null)return;
        $requests = LGuild::getInstance()->getGuildRequests($this->guild);
        sort($requests);
        if (isset($request[$data])){
            $a = LGuild::getInstance()->acceptGuildRequest($request[$data],$this->guild);
        }else{
            $i = 0;
            $c = 0;
            $f = 0;
            $z = 0;
            foreach ($requests as $request){
                $a = LGuild::getInstance()->rejectGuildRequest($request,$this->guild);
                if ($a === LGuild::BECAUSE_NOT_EXIST_GUILD){
                    $i++;
                }elseif ($a === LGuild::BECAUSE_HAVE_GUILD){
                    $c++;
                }elseif ($a === LGuild::BECAUSE_NOT_EXIST_PLAYER){
                    $f++;
                }elseif ($a === LGuild::SUCCESS){
                    $z++;
                    continue;
                }else continue;
            }
            $player->sendMessage(LGuild::getInstance()->sy."§l§f거절된 신청 수 : ".(string)$z." 실패한 횟수 : ".(string)($i + $c + $f));
        }
    }

}