# (DMCK) audio presenter

## Always a work in progress.

Another audio thingy. Can be used to generate playlists and simple charts. 
Default action is to grab the first mp3 located in published posts, then render a tabbed playlist.
Playlists rendered are currently defined by a json structure, and If the sited access log is available, a top 10 playlist and couple of charts can be rendered.

Based on this html5 audio playlist tutorial:
https://www.script-tutorials.com/html5-audio-player-with-playlist/

shortcode for tabbed playlist:
[dmck-audioplayer]

## Installation

1. Upload plugin folder to `/wp-content/plugins/` directory
2. Activate the plugin through the 'Plugins' menu in WordPress
3. Configure plugin admin settings
4. Add shortcode to pages or posts as needed.
5. something something work in progress, idk might delete later.


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

## Notes

**Regarding all version before 1.0.42:**

Changes require plugin deactivate/reactivate or attempt remove duplicates in the custom table using example SQL query below
*(The latter option has not been optimized and takes a long long time ).*

```sql

DELETE t1 FROM dmck_audio_log_reports t1 
INNER JOIN dmck_audio_log_reports t2 
WHERE t1.id < t2.id AND DATE_FORMAT(t1.updated, '%m-%d-%Y') = DATE_FORMAT(t2.updated, '%m-%d-%Y')
```

Creates custom table dmck_audio_log_reports when activated.
Drops table dmck_audio_log_reports when deactivated AND the admin option to drop custom table is checked.
dmck_audio_log_reports stores data used to render top 10 list and charts.

The script below is used to manually create wavforms (Requires an ffmpeg Installation on server).
_Parameters:_

1. wavform - _action flag, required_
2. path - _required_
3. name - _optional, specific file to generate wavform from_

```bash

$(which php) /home/user/site.com/wp-content/plugins/audio-player-cbhdmk/lib/reports.php wavform "/path/to/folder"
# or
$(which php) /home/user/site.com/wp-content/plugins/audio-player-cbhdmk/lib/reports.php wavform "/path/to/folder" "file-name.mp3"
```

## Misc

The following bash script can also be used to generate wavform (Requires ffmpeg Installation on server and some tweaking).

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

## Screenshot

![alt tag](https://github.com/dreaddymck/audio-player-cbhdmk/blob/master/screenshot.png?raw=true)
