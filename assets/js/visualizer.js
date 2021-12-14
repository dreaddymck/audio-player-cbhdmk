"use strict"

window.dmck_visualizer = {

    context:null,
    audio_node:null,
    MEDIA_ELEMENT_NODES: new WeakMap(),
  
    init: function(audio,id) {

        if(!dmck_audioplayer.visualizer_enabled){ return; }
       
        dmck_visualizer.context = new (window.AudioContext || window.webkitAudioContext);
        if(dmck_visualizer.MEDIA_ELEMENT_NODES.has(audio)){
            dmck_visualizer.audio_node = dmck_visualizer.MEDIA_ELEMENT_NODES.get(audio)
        }else{
            dmck_visualizer.audio_node = dmck_visualizer.context.createMediaElementSource(audio);
            dmck_visualizer.MEDIA_ELEMENT_NODES.set(audio, dmck_visualizer.audio_node);
        }        
        let analyser = dmck_visualizer.context.createAnalyser();
        analyser.connect(dmck_visualizer.context.destination);
        /* Failed to execute 'connect' on 'AudioNode': cannot connect to a destination belonging to a different audio context.*/
        dmck_visualizer.audio_node.connect(analyser);        

        analyser.fftSize = (dmck_audioplayer && dmck_audioplayer.visualizer_samples) ? dmck_audioplayer.visualizer_samples : 32;

        let canvas = document.getElementById(id);
        canvas.width = window.innerWidth;
        canvas.height = window.innerHeight;

        let bufferLength = analyser.frequencyBinCount;
        let dataArray = new Uint8Array(bufferLength);
        // let WIDTH = canvas.width;
        // let HEIGHT = canvas.height;
        let barWidth = (canvas.width / bufferLength) * 1.0;
        let barHeight;
        let x = 0;
        let visualizer_rgb_init = _dmck_functions.computed["background-color"];
        let visualizer_rgb = _dmck_functions.computed["color"];
        if(dmck_audioplayer && dmck_audioplayer.visualizer_rgb_enabled){
            if(dmck_audioplayer.visualizer_rgb_init){ visualizer_rgb_init = dmck_audioplayer.visualizer_rgb_init; }
            if(dmck_audioplayer.visualizer_rgb){ visualizer_rgb = dmck_audioplayer.visualizer_rgb; }
        }
        let ctx = canvas.getContext("2d");  
         

        function renderFrame() {
            requestAnimationFrame(renderFrame);
            x = 0;
            analyser.getByteFrequencyData(dataArray);

            ctx.globalCompositeOperation = 'source-in'
            ctx.fillStyle = visualizer_rgb_init;
            ctx.fillRect(0, 0, canvas.width, canvas.height);

            for (let i = 0; i < bufferLength; i++) {
     
                barHeight = dataArray[i];
                ctx.globalCompositeOperation = 'source-over'
                ctx.fillStyle = visualizer_rgb
                ctx.fillRect(x, canvas.height - barHeight, barWidth, barHeight);
                x += barWidth + 1;
            }
        }
        renderFrame();
        return canvas;
    } 
};