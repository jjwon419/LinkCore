<?php

declare(strict_types=1);

namespace RoMo\LinkCore\protocol;

use pocketmine\utils\BinaryStream;

abstract class LinkPacket{
    public abstract function getPacketId() : int;
    public abstract function encodePayload(LinkPacketSerializer $binaryStream);
    public abstract function decodePayload(LinkPacketSerializer $binaryStream);
}