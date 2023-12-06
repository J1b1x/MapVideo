<?php
namespace Jibix\MapVideo\item;
use pocketmine\item\Item;
use pocketmine\nbt\tag\CompoundTag;


/**
 * Class FilledMap
 * @package Jibix\MapVideo\item
 * @author Jibix
 * @date 05.12.2023 - 22:41
 * @project MapVideo
 */
class FilledMap extends Item{

    public const BLANK_MAP_ID = 0;
    public const MAP_ID_TAG = "map_uuid";

    private int $uuid = self::BLANK_MAP_ID;

    public function setMapId(int $uuid): self{
        $this->uuid = $uuid;
        return $this;
    }

    public function getMapId(): int{
        return $this->uuid;
    }

    protected function serializeCompoundTag(CompoundTag $tag): void{
        parent::serializeCompoundTag($tag);
        $tag->setLong(self::MAP_ID_TAG, $this->uuid);
    }

    protected function deserializeCompoundTag(CompoundTag $tag): void{
        parent::deserializeCompoundTag($tag);
        $this->uuid = $tag->getLong(self::MAP_ID_TAG);
    }
}