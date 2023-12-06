<?php
namespace Jibix\MapVideo\item;
use pocketmine\item\Item;
use pocketmine\item\ItemIdentifier;
use pocketmine\item\ItemTypeIds;
use pocketmine\utils\CloningRegistryTrait;


/**
 * Class CustomItemRegistry
 * @package Jibix\MapVideo\item
 * @author Jibix
 * @date 05.12.2023 - 22:42
 * @project MapVideo
 * @generate-registry-docblock
 *
 * @method static FilledMap FILLED_MAP()
 */
final class CustomItemRegistry{
    use CloningRegistryTrait;

    protected static function setup(): void {
        self::register("filled_map", new FilledMap(new ItemIdentifier(ItemTypeIds::newId())));
    }

    protected static function register(string $name, Item $item): void{
        self::_registryRegister($name, $item);
    }

    private function __construct(){}
}