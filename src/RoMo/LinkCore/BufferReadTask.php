<?php

declare(strict_types=1);

namespace RoMo\LinkCore;

use pocketmine\scheduler\Task;
use pocketmine\utils\Binary;
use RoMo\LinkCore\protocol\LinkPacketSerializer;
use RoMo\LinkCore\protocol\PacketFactory;

class BufferReadTask extends Task{

    /** @var LinkConnection */
    private LinkConnection $connection;

    public function __construct(LinkConnection $connection){
        $this->connection = $connection;
    }

    public function onRun() : void{
        if($this->connection->getState() !== LinkConnection::STATE_CONNECTED){
            return;
        }
        while(($payload = $this->connection->inBufferShift()) !== null && !empty($payload)){
            $packetId = Binary::readByte($payload);
            $offset = 1;

            $packet = PacketFactory::getInstance()->getPacketById($packetId);
            if(is_null($packet)){
                LinkCore::getInstance()->getLogger()->error("unknown packet(ID: {$packetId})");
                continue;
            }

            $serializer = new LinkPacketSerializer($payload);
            $packet->encodePayload($serializer);

            if($packet->handle()){
                LinkCore::getInstance()->getLogger()->notice("unhandled packet(ID: {$packet->getPacketId()})");
            }
        }
    }
}