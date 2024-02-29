<?php

declare(strict_types=1);

namespace RoMo\LinkCore;

use pocketmine\plugin\PluginBase;
use pocketmine\Server;
use RoMo\LinkCore\linkServer\LinkServer;
use RoMo\LinkCore\protocol\LinkPacket;
use RoMo\LinkCore\protocol\LinkPacketSerializer;
use pocketmine\utils\Binary;

class LinkCore extends PluginBase{

    private LinkConnection $connection;

    protected function onEnable() : void{
        $this->saveDefaultConfig();
        $config = $this->getConfig();
        $this->connection = new LinkConnection(Server::getInstance()->getLogger(),
            $config->get("ip"),
            $config->get("port"));
    }

    public function sendPacket(LinkPacket $packet, ?LinkServer $linkServer = null) : void{
        $serializer = new LinkPacketSerializer();

        //PACKET ID
        $serializer->putByte($packet->getPacketId());

        //TARGET SERVER
        $serializer->putString(is_null($linkServer) ? "" : $linkServer->getName());

        $packet->encodePayload($serializer);

        $return = Binary::writeInt(strlen($serializer->getBuffer()));
        $return .= $serializer->getBuffer();

        $this->connection->writeOutBuffer($return);
    }

    protected function onDisable() : void{
        $this->connection->close(true);
    }
}