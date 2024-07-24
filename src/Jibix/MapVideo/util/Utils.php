<?php
namespace Jibix\MapVideo\util;
use GdImage;
use GifFrameExtractor\GifFrameExtractor;
use InvalidArgumentException;
use Jibix\MapVideo\item\FilledMap;
use pocketmine\color\Color;
use pocketmine\utils\AssumptionFailedError;
use pocketmine\utils\Binary;
use pocketmine\utils\BinaryStream;


/**
 * Class Utils
 * @package Jibix\MapVideo\util
 * @author Jibix
 * @date 01.12.2023 - 15:24
 * @project MapVideo
 */
final class Utils{

    private static CustomMapItemDataPacket $blankImage;

    private function __construct(){}

    public static function videoToFrames(string $file): array{
        return array_map(fn (array $frame): GdImage => $frame['image'], (new GifFrameExtractor())->extract($file));
    }

    public static function frameToImage(GdImage $image, int $xChunkCount = 1, int $yChunkCount = 1, int $xOffset = 0, int $yOffset = 0): GdImage{
        $image = imagescale($image, 128 * $xChunkCount, 128 * $yChunkCount);
        if (!$image) throw new InvalidArgumentException("Could not rescale the image");
        $image = imagecrop($image, [
            "x" => 128 * $xOffset,
            "y" => 128 * $yOffset,
            "width" => 128,
            "height" => 128
        ]);
        if (!$image) throw new InvalidArgumentException("Could not crop the image");
        return $image;
    }

    public static function frameToColors(GdImage $image): string{
        $serializer = new BinaryStream();
        for ($y = 0; $y < 128; ++$y) {
            for ($x = 0; $x < 128; ++$x) {
                $color = imagecolorat($image, $x, $y);
                if ($color === false) throw new AssumptionFailedError("Could not read image pixel at $x:$y");
                $color |= (0xff << 24);
                $serializer->putUnsignedVarInt(Binary::flipIntEndianness(Color::fromARGB($color)->toRGBA()));
            }
        }
        return $serializer->getBuffer();
    }

    public static function getBlankImagePacket(): CustomMapItemDataPacket{
        if (!isset(self::$blankImage)) {
            $serializer = new BinaryStream();
            for ($y = 0; $y < 128; ++$y) {
                for ($x = 0; $x < 128; ++$x) {
                    $serializer->putUnsignedVarInt(Binary::flipIntEndianness(0));
                }
            }
            self::$blankImage = CustomMapItemDataPacket::create(FilledMap::BLANK_MAP_ID, $serializer->getBuffer());
        }
        return self::$blankImage;
    }
}
