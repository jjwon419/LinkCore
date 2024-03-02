<?php

declare(strict_types=1);

namespace RoMo\LinkCore;

use pocketmine\plugin\PluginBase;
use pocketmine\Server;
use pocketmine\utils\SingletonTrait;
use RoMo\LinkCore\linkServer\LinkServer;
use RoMo\LinkCore\protocol\default\HandShakePacket;
use RoMo\LinkCore\protocol\LinkPacket;
use RoMo\LinkCore\protocol\LinkPacketSerializer;
use pocketmine\utils\Binary;

class LinkCore extends PluginBase{

    use SingletonTrait;

    const PROTOCOL_VERSION = 0;

    private LinkConnection $connection;

    protected function onLoad() : void{
        self::$instance = $this;
    }

    protected function onEnable() : void{
        $this->saveDefaultConfig();
        $config = $this->getConfig();
        $this->connection = new LinkConnection(Server::getInstance()->getLogger(),
            $config->get("ip"),
            $config->get("port"));

        $this->sendPacket(new HandShakePacket());

        $this->getScheduler()->scheduleRepeatingTask(new BufferReadTask($this->connection), 5);
    }

    public function sendPacket(LinkPacket $packet, ?LinkServer $linkServer = null) : void{
        $serializer = new LinkPacketSerializer();

        $serializer->rewind();

        //IS DEFAULT PACKET
        $serializer->putBool($packet->isDefaultPacket());

        //TARGET SERVER
        if(!$packet->isDefaultPacket()){
            $serializer->putString(is_null($linkServer) ? "test" : $linkServer->getName());
        }

        //PACKET ID
        $serializer->putByte($packet->getPacketId());

        $packet->encodePayload($serializer);

        $return = Binary::writeInt(strlen($serializer->getBuffer()));
        $return .= $serializer->getBuffer();

        $this->connection->writeOutBuffer($return);
    }

    protected function onDisable() : void{
        $this->connection->close(true);
    }

    /**
     * @return LinkConnection
     */
    public function getConnection() : LinkConnection{
        return $this->connection;
    }

    public function getPassword() : string{
        return (string) $this->getConfig()->get("password");
    }
}