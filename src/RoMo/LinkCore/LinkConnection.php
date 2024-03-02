<?php

declare(strict_types=1);

namespace RoMo\LinkCore;

use pocketmine\thread\log\ThreadSafeLogger;
use pocketmine\thread\Thread;
use pmmp\thread\ThreadSafeArray;
use pocketmine\utils\Binary;
use RoMo\LinkCore\protocol\default\HandShakePacket;
use Socket;

class LinkConnection extends Thread{

    const STATE_CONNECTED = 0;
    const STATE_DISCONNECTED = 1;
    const STATE_HAND_SHAKE = 2;

    /** @var int */
    private int $state = self::STATE_DISCONNECTED;

    /** @var string */
    private string $address;

    /** @var int */
    private int $port;


    /** @var Socket|null */
    private ?Socket $socket = null;

    /** @var ThreadSafeLogger */
    private ThreadSafeLogger $logger;


    /** @var ThreadSafeArray */
    private ThreadSafeArray $input;
    private ThreadSafeArray $output;

    /** @var string */
    private string $buffer = "";

    private bool $isShutdown = false;

    public function __construct(ThreadSafeLogger $logger, string $address, int $port){
        $this->logger = $logger;
        $this->address = $address;
        $this->port = $port;

        $this->input = new ThreadSafeArray();
        $this->output = new ThreadSafeArray();

        $this->start();
    }

    protected function onRun() : void{
        $this->tryConnect();
    }

    private function tryConnect() : void{
        if($this->isShutdown){
            return;
        }
        $this->logger->notice("trying to connect to link...");
        $this->logger->notice("ip: {$this->address}");
        $this->logger->notice("port: {$this->port}");
        while($this->state === self::STATE_DISCONNECTED){
            $socket = socket_create(AF_INET, SOCK_STREAM, SOL_TCP);
            try{
                if($socket === false){
                    continue;
                }
                if(!socket_connect($socket, $this->address, $this->port)){
                    continue;
                }

                socket_set_nonblock($socket);
                socket_set_option($socket, SOL_TCP, TCP_NODELAY, 1);

                $this->socket = $socket;
                break;
            }catch(\Throwable $e){
                continue;
            }
        }

        $this->communicate();
    }

    private function communicate() : void{
        $this->changeState(self::STATE_CONNECTED);
        $socket = $this->socket;

        while($this->state != self::STATE_DISCONNECTED){
            $error = socket_last_error();
            socket_clear_error($socket);

            if(is_null($socket) || $error === 10057 || $error === 10054 || $error === 10053){
                $this->close();
                return;
            }
            //READ
            $buffer = @socket_read($socket, 65536);
            if($buffer !== ""){
                $this->buffer .= $buffer;
            }

            //WRITE
            while(!is_null(($buffer = $this->output->shift())) && $buffer !== ""){
                if(@socket_write($socket, $buffer) === false){
                    $this->close();
                }
            }

            $this->readBuffer();
        }

        $this->tryConnect();
    }

    private function readBuffer() : void{
        if(empty($this->buffer)){
            return;
        }

        $offset = 0;
        $bufferLength = strlen($this->buffer);
        while($offset < $bufferLength){
            if($offset > ($bufferLength - 4)){
                break;
            }

            $length = Binary::readInt(substr($this->buffer, $offset, 4));
            $offset += 4;

            if(($offset + $length) > $bufferLength){
                break;
            }

            $payload = substr($this->buffer, $offset, $length);
            $offset += $length;
            $this->input[] = $payload;
        }

        if($offset < $bufferLength){
            $this->buffer = substr($this->buffer, $offset);
        }else{
            $this->buffer = "";
        }
    }

    public function close(bool $isShutdown = false) : void{
        if(!is_null($this->socket)){
            socket_close($this->socket);
        }
        $this->isShutdown = $isShutdown;
        $this->changeState( self::STATE_DISCONNECTED);
        $this->logger->notice("link has disconnected");
    }

    /**
     * @return int
     */
    public function getState() : int{
        return $this->state;
    }

    public function changeState(int $state) : void{
        $this->state = $state;
    }

    public function writeOutBuffer(string $payload) : void{
        $this->output[] = $payload;
    }

    public function getThreadName() : string{
        return "link-connection";
    }

    public function inBufferShift() : ?string{
        return $this->input->shift();
    }
}