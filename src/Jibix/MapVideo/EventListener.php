<?php
namespace Jibix\MapVideo;
use Jibix\MapVideo\item\FilledMap;
use Jibix\MapVideo\util\Utils;
use pocketmine\event\Listener;
use pocketmine\event\server\DataPacketReceiveEvent;
use pocketmine\network\mcpe\protocol\MapInfoRequestPacket;
use pocketmine\player\Player;


/**
 * Class EventListener
 * @package Jibix\MapVideo
 * @author Jibix
 * @date 05.12.2023 - 23:54
 * @project MapVideo
 */
final class EventListener implements Listener{

    public function onPacketReceive(DataPacketReceiveEvent $event): void{
        $packet = $event->getPacket();
        if (!$packet instanceof MapInfoRequestPacket) return;
        $origin = $event->getOrigin();
        if ($origin->getPlayer() !== null && $packet->mapId == FilledMap::BLANK_MAP_ID) $origin->sendDataPacket(Utils::getBlankImagePacket());
    }
}
