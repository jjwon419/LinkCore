<?php

declare(strict_types=1);

namespace RoMo\LinkCore\protocol\default;

use RoMo\LinkCore\protocol\LinkPacket;
use RoMo\LinkCore\protocol\LinkPacketSerializer;

class HandShakeResultPacket extends LinkPacket{

    private int $code;

    public function getPacketId() : int{
        return DefaultLinkPacketId::HAND_SHAKE_RESULT_PACKET->value;
    }

    public function encodePayload(LinkPacketSerializer $binaryStream) : void{
        $this->code = $binaryStream->getByte();
    }

    public function decodePayload(LinkPacketSerializer $binaryStream) : void{
        return;
    }

    public function handle() : bool{
        var_dump($this->code);
        return true;
    }

}