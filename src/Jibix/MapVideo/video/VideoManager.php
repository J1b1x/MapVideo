<?php
namespace Jibix\MapVideo\video;
use Closure;
use LogicException;
use Jibix\MapVideo\task\LoadVideoAsyncTask;
use pocketmine\Server;
use pocketmine\utils\SingletonTrait;
use pocketmine\utils\Utils;


/**
 * Class VideoManager
 * @package Jibix\MapVideo\video
 * @author Jibix
 * @date 01.12.2023 - 14:51
 * @project MapVideo
 */
final class VideoManager{
    use SingletonTrait{
        setInstance as private;
        reset as private;
    }

    private const CACHE_VIDEOS = true;

    /** @var Video[] */
    private array $videos = [];

    private function __construct(){}

    /**
     * Function loadVideo
     * @param int $id
     * @param string $file
     * @param Closure|null $onComplete (static function (Video $video): void{})
     * @param Closure|null $progressNotifier (static function (int $totalFrames, int $loadedFrames): void{})
     * @param bool $cache
     * @return void
     * @throws LogicException
     */
    public function loadVideo(int $id, string $file, ?Closure $onComplete, ?Closure $progressNotifier = null, bool $cache = self::CACHE_VIDEOS): void{
        if ($onComplete !== null) Utils::validateCallableSignature(static function (Video $video): void{}, $onComplete);
        if ($progressNotifier !== null) Utils::validateCallableSignature(static function (int $totalFrames, int $loadedFrames): void{}, $progressNotifier);
        if (isset($this->videos[$id])) {
            ($onComplete)($this->videos[$id]);
            return;
        }
        if (!is_file($file)) throw new Exception("Video file could not be found");
        if ($onComplete === null && !$cache) throw new LogicException("No result handling provided");
        Server::getInstance()->getAsyncPool()->submitTask(new LoadVideoAsyncTask($id, $file, $cache, $onComplete, $progressNotifier));
    }

    public function cacheVideo(Video $video): void{
        $this->videos[$video->getId()] = $video;
    }

    public function getCachedVideo(int $id): ?Video{
        return $this->videos[$id] ?? null;
    }

    public function getCachedVideos(): array{
        return $this->videos;
    }
}