<?php
namespace Jibix\MapVideo\task;
use Closure;
use Jibix\MapVideo\util\CustomMapItemDataPacket;
use Jibix\MapVideo\util\Utils;
use Jibix\MapVideo\video\Video;
use Jibix\MapVideo\video\VideoManager;
use pocketmine\scheduler\AsyncTask;


/**
 * Class LoadVideoAsyncTask
 * @package Jibix\MapVideo\task
 * @author Jibix
 * @date 01.12.2023 - 14:54
 * @project MapVideo
 */
class LoadVideoAsyncTask extends AsyncTask{

    public function __construct(
        private int $id,
        private string $file,
        private bool $cache,
        private Closure $onComplete,
    ){}

    public function onRun(): void{
        $frames = [];
        foreach (Utils::videoToFrames($this->file) as $frame) {
            $frames[] = Utils::frameToColors(Utils::frameToImage($frame));
        }
        $this->setResult($frames);
    }

    public function onCompletion(): void{
        ($this->onComplete)($video = new Video(
            $this->id,
            array_map(fn (string $colors): CustomMapItemDataPacket => CustomMapItemDataPacket::create($this->id, $colors), $this->getResult())
        ));
        if ($this->cache) VideoManager::getInstance()->cacheVideo($video);
    }
}