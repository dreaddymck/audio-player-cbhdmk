# (DMCK) audio player

Work In Progress.

Just another html5 mp3 audio player based on html5 audio playlist tutorial:
https://www.script-tutorials.com/html5-audio-player-with-playlist/

This plugin will extract the first mp3 link in each post then add to playlist.

shortcode example: 
[dmck-audioplayer tag="in-playlist"]

== Installation ==

1. Upload plugin folder to `/wp-content/plugins/` directory
2. Activate the plugin through the 'Plugins' menu in WordPress
3. Add shortcode to pages or posts as needed.
4. something something work in progress

== screenshot ==

![alt tag](https://github.com/dreaddymck/audio-player-cbhdmk/blob/master/screenshot.png)

== notes ==

The following bash script can be used with ffmpeg to generate wav images:

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
