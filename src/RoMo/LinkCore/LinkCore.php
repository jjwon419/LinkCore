<?php

declare(strict_types=1);

namespace RoMo\LinkCore;

use pocketmine\plugin\PluginBase;
use pocketmine\Server;
use RoMo\LinkCore\protocol\LinkPacket;
use RoMo\LinkCore\protocol\LinkPacketSerializer;

class LinkCore extends PluginBase{

    private LinkConnection $connection;

    protected function onEnable() : void{
        $this->saveDefaultConfig();
        $config = $this->getConfig();
        $this->connection = new LinkConnection(Server::getInstance()->getLogger(),
            $config->get("ip"),
            $config->get("port"));
    }

    public function sendPacket(LinkPacket $packet) : void{
        $serializer = new LinkPacketSerializer();

        //IS DEFAULT PACKET
        $serializer->putBool($packet->isDefaultPacket());

        //TARGET SERVER
        //TODO: GET TARGET SERVER NAME
        $serializer->putString("");

        //PACKET ID
        $serializer->putByte($packet->getPacketId());

        $packet->encodePayload($serializer);

        $this->connection->writeOutBuffer($serializer->getBuffer());
    }

    protected function onDisable() : void{
        $this->connection->close(true);
    }
}