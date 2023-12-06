# MapVideo

![php](https://img.shields.io/badge/php-8.1-informational)
![api](https://img.shields.io/badge/pocketmine-5.0-informational)

A PocketMine-MP library to play videos on maps.

![MapVideo](https://github.com/J1b1x/MapVideo/assets/64813399/b2cb44cd-cdcf-4945-b8a8-f6584377d5a6)

## Registration
First you need to register the library. Simply do:
```php
\Jibix\MapVideo\MapVideo::initialize($plugin);
```

## Video loading
To load a video, all you need to do is:
```php
VideoManager::getInstance()->loadVideo(
    Video::id("my_video_name"),
    "/path/to/video.gif", //Only .gif files are supported at the moment
    static function (Video $video): void{
        //Do something (you could play the video for example)
    },
    static function (int $totalFrames, int $loadedFrames): void{
        $percentage = round($loadedFrames / $loadedFrames * 100);
        //Do something (you could send a progress bar for example)
    }
    true //Set to false if you don't want to cache the video
);
```
To get a cached video you can do:
```php
VideoManager::getInstance()->getCachedVideo($videoId);
```
You can also get all cached videos by doing
```php
$videos = VideoManager::getInstance()->getCachedVideos();
```

## Video playing
To play a video, all you need to do is:
```php
$videoSettings = new VideoPlaySettings(
    repeat: true, //Automatically restarts when the video ends
    offHand: false //Set to true if you want to play the video in the off-hand
    //Ideas for more options? Just make an issue!
);
VideoSession::get($player)->play($video, $videoSettings);
```
If you want to stop a video you can just do:
```php
VideoSession::get($player)->stop();
```
You can also get the current video by doing this:
```php
$video = VideoSession::get($player)->getVideo();
```
