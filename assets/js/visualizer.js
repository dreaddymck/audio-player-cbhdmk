"use strict"

const dmck_visualizer = {

    init: function(audio,id) {

        var context = new AudioContext();
        var src = context.createMediaElementSource(audio);
        var analyser = context.createAnalyser();
        var canvas = document.getElementById(id);
        console.log(id);
        // var canvas = document.querySelector('[id^="canvas_visualizer"]');
        canvas.width = window.innerWidth;
        canvas.height = window.innerHeight;
        var ctx = canvas.getContext("2d");

        src.connect(analyser);
        analyser.connect(context.destination);

        analyser.fftSize = 512;

        var bufferLength = analyser.frequencyBinCount;
        var dataArray = new Uint8Array(bufferLength);
        var WIDTH = canvas.width;
        var HEIGHT = canvas.height;
        var barWidth = (WIDTH / bufferLength) * 1.0;
        var barHeight;
        var x = 0;
        var visualizer_rgb_init = "rgba(0, 0, 0, 1.0)";
        var visualizer_rgb = "rgba(255, 255, 255, 1.0)";

        if(dmck_audioplayer){
            if(dmck_audioplayer.visualizer_rgb_init){ visualizer_rgb_init = dmck_audioplayer.visualizer_rgb_init; }
            if(dmck_audioplayer.visualizer_rgb){ visualizer_rgb = dmck_audioplayer.visualizer_rgb; }
        }         

        function renderFrame() {
            requestAnimationFrame(renderFrame);
            x = 0;
            analyser.getByteFrequencyData(dataArray);
            ctx.fillStyle = visualizer_rgb_init;
            ctx.fillRect(0, 0, WIDTH, HEIGHT);

            for (var i = 0; i < bufferLength; i++) {
     
                barHeight = dataArray[i];
                ctx.fillStyle = visualizer_rgb
                ctx.fillRect(x, HEIGHT - barHeight, barWidth, barHeight);
                x += barWidth + 1;
            }
        }
        renderFrame();
        return canvas;
    } 
};