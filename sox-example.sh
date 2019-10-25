#!/bin/bash                                                                                                                                                                 
echo "*******************************"                                                                                                                                      
echo "Begin render wavform";                                                                                                                                                
echo "*******************************"                                                                                                                                                                                                                                                                                                                  
cd /home/dreaddy/Dropbox/Public/MUSIC/FEATURING/                                                                                                                                                                                                                                                                                                        
for i in *.mp3; do                                                                                                                                                                  
    ffmpeg -y -i "$i" -filter_complex "compand,showwavespic=s=1200x600:colors=b2b2b2ff:" -frames:v 1  "${i%.mp3}.wavform.png";                                                  
    sleep 1;                                                                                                                                                            
done 


for i in *.wav; do $which(sox) $i -n spectrogram -y 130 -l -r -o ${i%%.wav}.png; done