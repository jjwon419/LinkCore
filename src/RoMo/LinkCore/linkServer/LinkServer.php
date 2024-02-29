<?php

declare(strict_types=1);

namespace RoMo\LinkCore\linkServer;

class LinkServer{

    /** @var string */
    private string $name;

    public function __construct(string $name){
        $this->name = $name;
    }

    /**
     * @return string
     */
    public function getName() : string{
        return $this->name;
    }
}