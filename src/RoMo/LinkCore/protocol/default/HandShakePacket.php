<?php

declare(strict_types=1);

namespace RoMo\LinkCore\protocol\default;

use pocketmine\Server;
use RoMo\LinkCore\LinkCore;
use RoMo\LinkCore\protocol\LinkPacket;
use RoMo\LinkCore\protocol\LinkPacketSerializer;

class HandShakePacket extends LinkPacket{

    public function getPacketId() : int{
        return DefaultLinkPacketId::HAND_SHAKE_PACKET->value;
    }

    public function encodePayload(LinkPacketSerializer $binaryStream) : void{
        $binaryStream->putInt(LinkCore::PROTOCOL_VERSION);
        $binaryStream->putString(LinkCore::getInstance()->getPassword());
        $binaryStream->putString(Server::getInstance()->getIp());
        $binaryStream->putInt(Server::getInstance()->getPort());
        $binaryStream->putString(LinkCore::getInstance()->getServerName());
    }
    public function decodePayload(LinkPacketSerializer $binaryStream) : void{
        //NOTHING: THIS PACKET IS NOT OUT BOUND.
        return;
    }

    public function isDefaultPacket() : bool{
        return true;
    }

    public function handle() : bool{
        return false;
    }
}