# (DMCK) audio

## Always a work in progress

SHORTCODE: [dmck-audioplayer]

Another media thingy. Can be used to generate a tabbed playlists and simple charts from mp3 urls embeded in posts.

If an access log is available this applicaton can generate a rudimentary top 10 playlist.

Based originally on this html5 audio playlist tutorial:
https://www.script-tutorials.com/html5-audio-player-with-playlist/

## Installation

1. Upload plugin folder to `/wp-content/plugins/` directory
2. Activate the plugin through the 'Plugins' menu in WordPress
3. Configure the playlist json object and other options in the admin menu in the admin section.
4. Add shortcode to pages or posts as needed.
5. Something something...

### Wavform

Add wavform image url to the available "DMCK Audio meta options" post meta box section.

Default location for the wav form image is the same directory as the mp3. 
The default can be overriden with a custom field in post document name *dmck_wavformpng*, value is the url to the wav form image.

The script below may be used to manually create wavforms (Requires an ffmpeg Installation on server).
_Parameters:_


```bash

#process everything in folder. subfolders not supported
$(which php) /home/user/site.com/wp-content/plugins/audio-player-cbhdmk/lib/reports.php wavform "/path/to/folder"
#individual mp3 files
$(which php) /home/user/site.com/wp-content/plugins/audio-player-cbhdmk/lib/reports.php wavform "/path/to/folder" "file-name.mp3"
```

1. wavform - _action flag, required_
2. path - _required_
3. name - _optional, specific file to generate wavform from_



### Filter access logs and debugging

The script below is used to manually test the filter used when parsing the access log.
_Parameters:_

```bash

$(which php) /home/user/site.com/wp-content/plugins/audio-player-cbhdmk/lib/reports.php put "/path/to/accesslog" "/.mp3/i" true
```
1. put - _action flag, required_
2. path - _optional, overrides admin settings_
3. regex - _optional, override admin settings_
4. true - _optional, show results in error log_

## Screenshot

![alt tag](https://github.com/dreaddymck/audio-player-cbhdmk/blob/master/screenshot.png?raw=true)
