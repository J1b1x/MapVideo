<?php
namespace Jibix\MapVideo\util;
use pocketmine\color\Color;
use pocketmine\network\mcpe\protocol\ClientboundPacket;
use pocketmine\network\mcpe\protocol\DataPacket;
use pocketmine\network\mcpe\protocol\PacketDecodeException;
use pocketmine\network\mcpe\protocol\PacketHandlerInterface;
use pocketmine\network\mcpe\protocol\ProtocolInfo;
use pocketmine\network\mcpe\protocol\serializer\PacketSerializer;
use pocketmine\network\mcpe\protocol\types\BlockPosition;
use pocketmine\network\mcpe\protocol\types\DimensionIds;
use pocketmine\network\mcpe\protocol\types\MapDecoration;
use pocketmine\network\mcpe\protocol\types\MapTrackedObject;
use pocketmine\utils\Binary;


/**
 * Class CustomMapItemDataPacket
 * @package Jibix\MapVideo\util
 * @author Jibix
 * @date 04.12.2023 - 16:47
 * @project MapVideo
 */
class CustomMapItemDataPacket extends DataPacket implements ClientboundPacket{

    //We use a custom packet, so we have no deserialization overhead (caused massive performance issues to serialize/deserialize MapImage objects, so we do this instead)

    public const NETWORK_ID = ProtocolInfo::CLIENTBOUND_MAP_ITEM_DATA_PACKET;

    public const BITFLAG_TEXTURE_UPDATE = 0x02;
    public const BITFLAG_DECORATION_UPDATE = 0x04;
    public const BITFLAG_MAP_CREATION = 0x08;

    private int $mapId;
    private int $dimensionId = DimensionIds::OVERWORLD;
    private bool $isLocked = false;
    private BlockPosition $origin;

    /** @var int[] */
    private array $parentMapIds = [];
    private int $scale;

    /** @var MapTrackedObject[] */
    private array $trackedEntities = [];
    /** @var MapDecoration[] */
    private array $decorations = [];

    private int $xOffset = 0;
    private int $yOffset = 0;
    private ?string $colors = null;

    public static function create(int $mapId, ?string $colors): self{
        $packet = new self();
        $packet->mapId = $mapId;
        $packet->dimensionId = DimensionIds::OVERWORLD;
        $packet->isLocked = false;
        $packet->scale = 1;
        $packet->xOffset = $packet->yOffset = 0;
        $packet->colors = $colors;
        $packet->origin = new BlockPosition(0, 0, 0);
        $packet->parentMapIds[] = $mapId;
        return $packet;
    }

    protected function decodePayload(PacketSerializer $in): void{
        $this->mapId = $in->getActorUniqueId();
        $type = $in->getUnsignedVarInt();
        $this->dimensionId = $in->getByte();
        $this->isLocked = $in->getBool();
        $this->origin = $in->getSignedBlockPosition();

        if(($type & self::BITFLAG_MAP_CREATION) !== 0){
            $count = $in->getUnsignedVarInt();
            for($i = 0; $i < $count; ++$i){
                $this->parentMapIds[] = $in->getActorUniqueId();
            }
        }

        if(($type & (self::BITFLAG_MAP_CREATION | self::BITFLAG_DECORATION_UPDATE | self::BITFLAG_TEXTURE_UPDATE)) !== 0){ //Decoration bitflag or colour bitflag
            $this->scale = $in->getByte();
        }

        if(($type & self::BITFLAG_DECORATION_UPDATE) !== 0){
            for($i = 0, $count = $in->getUnsignedVarInt(); $i < $count; ++$i){
                $object = new MapTrackedObject();
                $object->type = $in->getLInt();
                if($object->type === MapTrackedObject::TYPE_BLOCK){
                    $object->blockPosition = $in->getBlockPosition();
                }elseif($object->type === MapTrackedObject::TYPE_ENTITY){
                    $object->actorUniqueId = $in->getActorUniqueId();
                }else{
                    throw new PacketDecodeException("Unknown map object type $object->type");
                }
                $this->trackedEntities[] = $object;
            }

            for($i = 0, $count = $in->getUnsignedVarInt(); $i < $count; ++$i){
                $icon = $in->getByte();
                $rotation = $in->getByte();
                $xOffset = $in->getByte();
                $yOffset = $in->getByte();
                $label = $in->getString();
                $color = Color::fromRGBA(Binary::flipIntEndianness($in->getUnsignedVarInt()));
                $this->decorations[] = new MapDecoration($icon, $rotation, $xOffset, $yOffset, $label, $color);
            }
        }

        if(($type & self::BITFLAG_TEXTURE_UPDATE) !== 0){
            $width = $in->getVarInt();
            $height = $in->getVarInt();
            $this->xOffset = $in->getVarInt();
            $this->yOffset = $in->getVarInt();

            $count = $in->getUnsignedVarInt();
            if ($count !== $width * $height){
                throw new PacketDecodeException("Expected colour count of " . ($height * $width) . " (height $height * width $width), got $count");
            }

            $this->colors = $in->getRemaining();
        }
    }

    protected function encodePayload(PacketSerializer $out): void{
        $out->putActorUniqueId($this->mapId);

        $type = 0;
        if(($parentMapIdsCount = count($this->parentMapIds)) > 0){
            $type |= self::BITFLAG_MAP_CREATION;
        }
        if(($decorationCount = count($this->decorations)) > 0){
            $type |= self::BITFLAG_DECORATION_UPDATE;
        }
        if($this->colors !== null){
            $type |= self::BITFLAG_TEXTURE_UPDATE;
        }

        $out->putUnsignedVarInt($type);
        $out->putByte($this->dimensionId);
        $out->putBool($this->isLocked);
        $out->putSignedBlockPosition($this->origin);

        if(($type & self::BITFLAG_MAP_CREATION) !== 0){
            $out->putUnsignedVarInt($parentMapIdsCount);
            foreach($this->parentMapIds as $parentMapId){
                $out->putActorUniqueId($parentMapId);
            }
        }

        if (($type & (self::BITFLAG_MAP_CREATION | self::BITFLAG_TEXTURE_UPDATE | self::BITFLAG_DECORATION_UPDATE)) !== 0){
            $out->putByte($this->scale);
        }

        if (($type & self::BITFLAG_DECORATION_UPDATE) !== 0) {
            $out->putUnsignedVarInt(count($this->trackedEntities));
            foreach($this->trackedEntities as $object){
                $out->putLInt($object->type);
                if ($object->type === MapTrackedObject::TYPE_BLOCK) {
                    $out->putBlockPosition($object->blockPosition);
                } elseif ($object->type === MapTrackedObject::TYPE_ENTITY) {
                    $out->putActorUniqueId($object->actorUniqueId);
                } else {
                    throw new \InvalidArgumentException("Unknown map object type $object->type");
                }
            }

            $out->putUnsignedVarInt($decorationCount);
            foreach ($this->decorations as $decoration) {
                $out->putByte($decoration->getIcon());
                $out->putByte($decoration->getRotation());
                $out->putByte($decoration->getXOffset());
                $out->putByte($decoration->getYOffset());
                $out->putString($decoration->getLabel());
                $out->putUnsignedVarInt(Binary::flipIntEndianness($decoration->getColor()->toRGBA()));
            }
        }

        if($this->colors !== null){
            $out->putVarInt(128);
            $out->putVarInt(128);
            $out->putVarInt($this->xOffset);
            $out->putVarInt($this->yOffset);

            $out->putUnsignedVarInt(128 * 128); //list count, but we handle it as a 2D array... thanks for the confusion mojang

            $out->put($this->colors);
        }
    }

    public function handle(PacketHandlerInterface $handler): bool{
        return false;
    }
}