<?php
namespace Jibix\MapVideo\task;
use Closure;
use Jibix\MapVideo\util\CustomMapItemDataPacket;
use Jibix\MapVideo\util\Utils;
use Jibix\MapVideo\video\Video;
use Jibix\MapVideo\video\VideoManager;
use pmmp\thread\ThreadSafeArray;
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
        private Closure $progressNotifier,
    ){}

    public function onRun(): void{
        $frames = new ThreadSafeArray();
        $totalFrames = count($videoFrames = Utils::videoToFrames($this->file));
        foreach ($videoFrames as $i => $frame) {
            $frames[] = Utils::frameToColors(Utils::frameToImage($frame));
            if ($this->progressNotifier !== null) $this->publishProgress([$totalFrames, $i +1]);
        }
        $this->setResult($frames);
    }

    public function onCompletion(): void{
        $packets = [];
        foreach ($this->getResult() as $frame) {
            $packets[] = CustomMapItemDataPacket::create($this->id, $frame);
        }
        ($this->onComplete)($video = new Video($this->id, $packets));
        if ($this->cache) VideoManager::getInstance()->cacheVideo($video);
    }

    public function onProgressUpdate($progress): void{
        //progressNotifier can't be null at this point
        [$totalFrames, $loadedFrames] = $progress;
        ($this->progressNotifier)($totalFrames, $loadedFrames);
    }
}