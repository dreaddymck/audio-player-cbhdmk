# (DMCK) audio player

== Work In Progress ==

Just another html5 mp3 audio player based on html5 audio playlist tutorial:
https://www.script-tutorials.com/html5-audio-player-with-playlist/

This plugin will generate a playlist from the the first mp3 link found in public posts.
Filter by tags availble.

shortcode example:
[dmck-audioplayer]

playlists will be generated from the json structure created in the admin playlist tab.

== Installation ==

1. Upload plugin folder to `/wp-content/plugins/` directory
2. Activate the plugin through the 'Plugins' menu in WordPress
3. Configure plugin admin settings
4. Add shortcode to pages or posts as needed.
5. something something work in progress

== Public resources included  ==

```bash

MIT License
Copyright (c) 2016 MaximAL
```

```bash

* H3K | Tiny File Manager V2.4.1
* CCP Programmers | ccpprogrammers@gmail.com
* https://tinyfilemanager.github.io
```

== Notes ==

This plugin creates table dmck_audio_log_reports upon activation.
This plugin drops table dmck_audio_log_reports upon deactivation.
table dmck_audio_log_reports stores the top 10 data extracted from the system access_log.
A system cron task will be suggested in admin for manual setup.
Please remember to remove the associated system cron task if enabled.

Native create wavforms script (Requires ffmpeg Installation on server).

```bash

 $(which php) /home/user/site.com/wp-content/plugins/audio-player-cbhdmk/lib/reports.php wavform "" "filename.mp3"
```

The following bash script generates wavform (Requires ffmpeg Installation on server).

```bash

#!/bin/bash
echo "*******************************"
echo "Begin render wavform";
echo "*******************************"
cd /home/user/path/to/mp3/
for i in *.mp3; do
 ffmpeg -y -i "$i" -filter_complex "compand,showwavespic=s=1200x120:colors=b2b2b2ff:" -frames:v 1  "${i%.mp3}.wavform.png";
 sleep 1;
done
echo "end"
exit
```

== screenshot ==

![alt tag](https://github.com/dreaddymck/audio-player-cbhdmk/blob/master/screenshot.png?raw=true)
