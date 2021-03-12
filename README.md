# (DMCK) audio

## Always a work in progress

SHORTCODE: [dmck-audioplayer]

Another media thingy. Can be used to generate playlists embeded in posts and simple charts. Application will grab the first mp3 located in published posts then render a tabbed playlist.

If the access log is available, a top 10 playlist and some charts can be rendered.

Based on this html5 audio playlist tutorial:
https://www.script-tutorials.com/html5-audio-player-with-playlist/

## Installation

1. Upload plugin folder to `/wp-content/plugins/` directory
2. Activate the plugin through the 'Plugins' menu in WordPress
3. Configure the playlist json object and other options in the admin menu in the admin section.
4. Add shortcode to pages or posts as needed.
5. Something something...

### NOTICE: 

Meta data used with Charts and top ten display has been migrated to a new table. The migration process requires command line access.
Migration command

0. command = lib/reports_migrate.php
1. flag = "migrate" (required).
2. Int = Number of months (defaults to 1).
3. Bool = optional, display debugs (defaults to false).

```bash

# example: 
php lib/reports_migrate.php migrate 12
```

### Wavform

Add wavform image url to the available "DMCK Audio meta options" post meta box section..

Default location for the wav form image is the same directory as the mp3. 
The default can be overriden by creating a custom field for the post document called *dmck_wavformpng*. Value is the url to the alternate wav form image location.

The script below may be used to manually create wavforms (Requires an ffmpeg Installation on server).
_Parameters:_

1. wavform - _action flag, required_
2. path - _required_
3. name - _optional, specific file to generate wavform from_

```bash

$(which php) /home/user/site.com/wp-content/plugins/audio-player-cbhdmk/lib/reports.php wavform "/path/to/folder"
# or
$(which php) /home/user/site.com/wp-content/plugins/audio-player-cbhdmk/lib/reports.php wavform "/path/to/folder" "file-name.mp3"
```

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

## Screenshot

![alt tag](https://github.com/dreaddymck/audio-player-cbhdmk/blob/master/screenshot.png?raw=true)
