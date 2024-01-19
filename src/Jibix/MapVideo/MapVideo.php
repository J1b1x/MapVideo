<?php
namespace Jibix\MapVideo;
use Jibix\MapVideo\item\CustomItemRegistry;
use Jibix\MapVideo\util\CustomMapItemDataPacket;
use pocketmine\data\bedrock\item\ItemTypeNames;
use pocketmine\data\bedrock\item\SavedItemData;
use pocketmine\item\StringToItemParser;
use pocketmine\network\mcpe\protocol\PacketPool;
use pocketmine\plugin\Plugin;
use pocketmine\scheduler\AsyncTask;
use pocketmine\Server;
use pocketmine\world\format\io\GlobalItemDataHandlers;


/**
 * Class MapVideo
 * @package Jibix\MapVideo
 * @author Jibix
 * @date 01.12.2023 - 15:05
 * @project MapVideo
 */
final class MapVideo{

    private static Plugin $plugin;

    private function __construct(){}

    public static function initialize(Plugin $plugin): void{
        if (isset(self::$plugin)) return;
        self::$plugin = $plugin;
        PacketPool::getInstance()->registerPacket(new CustomMapItemDataPacket());
        self::registerItems();
        $plugin->getServer()->getPluginManager()->registerEvents(new EventListener(), $plugin);
        $plugin->getServer()->getAsyncPool()->addWorkerStartHook(function (int $worker): void{
            Server::getInstance()->getAsyncPool()->submitTaskToWorker(new class extends AsyncTask{
                public function onRun(): void{
                    MapVideo::registerItems();
                }
            }, $worker);
        });
    }

    public static function getPlugin(): Plugin{
        return self::$plugin;
    }

    /** @internal */
    public static function registerItems(): void{
        $item = CustomItemRegistry::FILLED_MAP();
        GlobalItemDataHandlers::getDeserializer()->map(ItemTypeNames::FILLED_MAP, fn() => clone $item);
        GlobalItemDataHandlers::getSerializer()->map($item, fn() => new SavedItemData(ItemTypeNames::FILLED_MAP));
        StringToItemParser::getInstance()->register("filled_map", fn() => clone $item);
    }
}