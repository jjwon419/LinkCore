<?php

declare(strict_types=1);

namespace RoMo\LinkCore\protocol;

use pocketmine\utils\SingletonTrait;
use RoMo\LinkCore\protocol\default\HandShakePacket;
use RoMo\LinkCore\protocol\exception\AlreadyExistPacketException;

class PacketFactory{

    use SingletonTrait;

    /** @var LinkPacket[] */
    private array $packets = [];

    public static function init() : void{
        self::$instance = new self();
    }

    private function __construct(){
        $this->register(new HandShakePacket());
    }

    /**
     * @throws AlreadyExistPacketException
     */
    public function register(LinkPacket $packet) : void{
        if(isset($this->packets[$packet->getPacketId()])){
            throw new AlreadyExistPacketException("Another packet exist(ID: {$packet->getPacketId()}}");
        }
        $this->packets[$packet->getPacketId()] = $packet;
    }

    public function getPacketById(int $id) : ?LinkPacket{
        return isset($this->packets[$id]) ? clone $this->packets[$id] : null;
    }

}