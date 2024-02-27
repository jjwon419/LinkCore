<?php

declare(strict_types=1);

namespace RoMo\LinkCore\protocol;

use pocketmine\utils\BinaryStream;

class LinkPacketSerializer extends BinaryStream{
    public function putString(string $string) : void{
        $this->putInt(strlen($string));
        $this->put($string);
    }

    public function getString() : string{
        return $this->get($this->getInt());
    }
}