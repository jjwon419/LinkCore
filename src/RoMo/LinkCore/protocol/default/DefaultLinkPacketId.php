<?php

declare(strict_types=1);

namespace RoMo\LinkCore\protocol\default;

enum DefaultLinkPacketId : int{
    case HAND_SHAKE_PACKET = 0x01;
    case HAND_SHAKE_RESULT_PACKET = 0x02;
    case ADD_LINK_SERVER_PACKET = 0x03;
    case REMOVE_LINK_SERVER_PACKET = 0x04;
}