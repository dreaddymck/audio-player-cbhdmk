# (DMCK) audio presenter

## Always a work in progress.

Another audio thingy. Can be used to generate playlists and simple charts. 
Default action is to grab the first mp3 located in published posts, then render a tabbed playlist.
Playlists rendered are currently defined by a json structure, and If the website access log is available, a top 10 playlist and some charts can be rendered.

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

## Notes

### Filter access logs. Debugging

The script below is used to manually test the filter used when parsing the access log.
_Parameters:_

1. put - _action flag, required_
2. path - _optional, overrides admin settings_
3. regex - _optional, override admin settings_
4. true - _optional, show results in error log_

```bash

$(which php) /home/user/site.com/wp-content/plugins/audio-player-cbhdmk/lib/reports.php put "/path/to/accesslog" "/.mp3/i" true
```

### Wavform

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
