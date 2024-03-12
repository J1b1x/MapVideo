# MapVideo

![php](https://img.shields.io/badge/php-8.1-informational)
![api](https://img.shields.io/badge/pocketmine-5.0-informational)

A PocketMine-MP library to play videos on maps.
You can find an example of how to use this library in a plugin [here](https://github.com/J1b1x/MapVideoExample).

![MapVideo](https://github.com/J1b1x/MapVideo/assets/64813399/b2cb44cd-cdcf-4945-b8a8-f6584377d5a6)

## Registration
First you need to register the library. Simply do:
```php
\Jibix\MapVideo\MapVideo::initialize($plugin);
```

## Video loading
Load a video:
```php
VideoManager::getInstance()->loadVideo(
    Video::id("my_video_name"),
    "/path/to/video.gif", //Only .gif files are supported at the moment
    static function (Video $video): void{
        //Do something (you could play the video for example)
    },
    static function (int $totalFrames, int $loadedFrames): void{
        $percentage = round($loadedFrames / $loadedFrames * 100);
        //Do something (you could send a progress bar to the player for example, since this is called in the main thread)
    },
    true //Set to false if you don't want to cache the video
);
```
Get a cached video:
```php
VideoManager::getInstance()->getCachedVideo($videoId);
```
Get all cached videos:
```php
$videos = VideoManager::getInstance()->getCachedVideos();
```

## Video playing
Play a video:
```php
$videoSettings = new VideoPlaySettings(
    repeat: true, //Automatically restarts when the video ends
    offHand: false //Set to true if you want to play the video in the off-hand
    //Ideas for more options? Just make an issue!
);
VideoSession::get($player)->play($video, $videoSettings);
```
Stop a video:
```php
VideoSession::get($player)->stop();
```
Get the currently playing video:
```php
$video = VideoSession::get($player)->getVideo();
```
