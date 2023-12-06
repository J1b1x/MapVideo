<?php
namespace Jibix\MapVideo\video;


/**
 * Class VideoPlaySettings
 * @package Jibix\MapVideo\video
 * @author Jibix
 * @date 05.12.2023 - 23:12
 * @project MapVideo
 */
class VideoPlaySettings{

    public function __construct(
        private bool $repeat = true,
        private bool $offHand = false
    ){}

    public function playOnRepeat(): bool{
        return $this->repeat;
    }

    public function playInOffHand(): bool{
        return $this->offHand;
    }
}