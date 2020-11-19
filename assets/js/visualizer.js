"use strict"

const dmck_visualizer = {

    init: function(audio) {

        var context = new AudioContext();
        var src = context.createMediaElementSource(audio);
        var analyser = context.createAnalyser();
        var canvas = document.getElementById("canvas_visualizer");
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
        var r = 255;
        var g = 255;
        var b = 255;

        if(dmck_audioplayer && dmck_audioplayer.visualizer_rgb.r && dmck_audioplayer.visualizer_rgb.g && dmck_audioplayer.visualizer_rgb.b){
            r = dmck_audioplayer.visualizer_rgb.r;
            g = dmck_audioplayer.visualizer_rgb.g;
            b = dmck_audioplayer.visualizer_rgb.b;
        }

        function renderFrame() {
            requestAnimationFrame(renderFrame);
            x = 0;
            analyser.getByteFrequencyData(dataArray);
            ctx.fillStyle = "rgba(0, 0, 0, 0.9)";
            ctx.fillRect(0, 0, WIDTH, HEIGHT);

            for (var i = 0; i < bufferLength; i++) {
     
                barHeight = dataArray[i];
                // var r = 250; //barHeight + (25 * (i / bufferLength));
                // var g = 250; //250 * (i / bufferLength);
                // var b = 250; //50;
                ctx.fillStyle = "rgb(" + r + "," + g + "," + b + ")";
                ctx.fillRect(x, HEIGHT - barHeight, barWidth, barHeight);
                x += barWidth + 1;
            }
        }
        renderFrame();
        return canvas;
    } 
};