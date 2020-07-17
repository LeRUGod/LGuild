<?php


namespace LeRUGod\LGuild\form\requests;


use pocketmine\form\Form;
use pocketmine\Player;

class receiveRequestForm implements Form
{

    public function jsonSerialize()
    {
        
    }

    public function handleResponse(Player $player, $data): void
    {
        // TODO: Implement handleResponse() method.
    }

}