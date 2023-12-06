<?php
namespace Jibix\MapVideo\video;
use Jibix\MapVideo\util\CustomMapItemDataPacket;


/**
 * Class Video
 * @package Jibix\MapVideo\video
 * @author Jibix
 * @date 01.12.2023 - 14:51
 * @project MapVideo
 */
class Video{

    public static function id(string $name): int{
        return crc32(md5($name));
    }

    /**
     * Video constructor.
     * @param int $id
     * @param CustomMapItemDataPacket[] $frames
     */
    public function __construct(protected int $id, protected array $frames){}

    public function getId(): int{
        return $this->id;
    }

    public function getFrames(): array{
        return $this->frames;
    }

    public function getFrame(int $index): ?CustomMapItemDataPacket{
        return $this->frames[$index] ?? null;
    }
}