<?php
namespace Jibix\MapVideo\video;
use Closure;
use Exception;
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

    public function loadVideo(int $id, string $file, ?Closure $onComplete, bool $cache = self::CACHE_VIDEOS): void{
        if ($onComplete !== null) Utils::validateCallableSignature(static function (Video $video): void{}, $onComplete);
        if (isset($this->videos[$id])) {
            ($onComplete)($this->videos[$id]);
            return;
        }
        if ($onComplete === null && !$cache) throw new Exception("No result handling provided");
        Server::getInstance()->getAsyncPool()->submitTask(new LoadVideoAsyncTask($id, $file, $cache, $onComplete));
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