<?php

declare(strict_types=1);

namespace RoMo\LinkCore;

use pocketmine\thread\log\ThreadSafeLogger;
use pocketmine\thread\Thread;
use pmmp\thread\ThreadSafeArray;
use Socket;

class LinkConnection extends Thread{

    const STATE_CONNECTED = 0;
    const STATE_DISCONNECTED = 1;

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
                $this->communicate();
            }catch(\Throwable $e){
                continue;
            }
        }
    }

    private function communicate() : void{
        $this->changeState(self::STATE_CONNECTED);
        $socket = $this->socket;

        while(true){
            $error = socket_last_error();
            socket_clear_error($socket);

            if(is_null($socket) || $error === 10057 || $error === 10054 || $error === 10053){
                $this->close();
                return;
            }
            //READ
            $buffer = @socket_read($socket, 65536);
            if($buffer !== ""){
                //TODO: READ
            }
            //WRITE
            while(!is_null(($buffer = $this->output->shift())) && $buffer !== ""){
                if(@socket_write($socket, $buffer) === false){
                    $this->close();
                }
            }
        }
    }

    private function close() : void{
        if(!is_null($this->socket)){
            socket_close($this->socket);
        }
        $this->changeState(self::STATE_DISCONNECTED);
        $this->logger->notice("link has disconnected");
        $this->tryConnect();
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


}