# (DMCK) audio presenter

## Work In Progress

Just another audio player, playlist creation tool. Designed to grab the first mp3 link in published posts then render a tabbed playlist.
Playlists are currently defined by json structure and If the access_log is available, a top 10 playlist can be parsed the tab also.

based on this html5 audio playlist tutorial:
https://www.script-tutorials.com/html5-audio-player-with-playlist/

shortcode example:
[dmck-audioplayer]

## Installation

1. Upload plugin folder to `/wp-content/plugins/` directory
2. Activate the plugin through the 'Plugins' menu in WordPress
3. Configure plugin admin settings
4. Add shortcode to pages or posts as needed.
5. something something work in progress

## Notes

**Regarding all version before 1.0.42:**

Changes require plugin deactivate/reactivate or attempt remove duplicates in the custom table using example SQL query below
*(The latter option has not been optimized and takes a long time ).*

```sql

DELETE t1 FROM dmck_audio_log_reports t1 
INNER JOIN dmck_audio_log_reports t2 
WHERE t1.id < t2.id AND DATE_FORMAT(t1.updated, '%m-%d-%Y') = DATE_FORMAT(t2.updated, '%m-%d-%Y')
```

Creates custom table dmck_audio_log_reports when activated.
Drops table dmck_audio_log_reports when deactivated AND the admin option to drop custom table is checked.
dmck_audio_log_reports stores used to render top 10 and charts.
A cron string will be used to create data. See admin panel during setup. Please remember to remove the cron task when deactivated.

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

## Dependencies

```javascript

"bootstrap": "^4.4.1",
"font-awesome": "^4.7.0",
"jquery": "^3.4.1",
"jquery.cookie": "^1.4.1",
"popper.js": "^1.16.1"

"maximal/audio-waveform": "~1.0"
```

```bash

* H3K | Tiny File Manager V2.4.1
* CCP Programmers | ccpprogrammers@gmail.com
* https://tinyfilemanager.github.io
```

## Screenshot

![alt tag](https://github.com/dreaddymck/audio-player-cbhdmk/blob/master/screenshot.png?raw=true)
