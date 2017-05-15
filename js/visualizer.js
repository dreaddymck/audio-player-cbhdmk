/*
 * Source: https://w-labs.at/experiments/audioviz/
 * Authors: Patrick Wied, dreaddymck
 * 
 */


window.AudioContext = window.AudioContext || window.webkitAudioContext || window.mozAudioContext;
var renderers = {
    'r0': (function() {
        var barsArr = [],
            initialized = false,
            barsEl;
        var height = 0;
        var init = function(config) {
            var count = config.count;
            var width = config.width;
            var barWidth = (width / count) >> 0;
            height = config.height;
            barsEl = document.getElementById('bars');
            for (var i = 0; i < count; i++) {
                var nunode = document.createElement('div');
                nunode.classList.add('bar');
                nunode.style.width = barWidth + 'px';
                nunode.style.left = (barWidth * i) + 'px';
                barsArr.push(nunode);
                barsEl.appendChild(nunode);
            }
            initialized = true;
        };
        var max = 256;
        var renderFrame = function(frequencyData) {
            for (var i = 0; i < barsArr.length; i++) {
                var bar = barsArr[i];
                bar.style.height = ((frequencyData[i] / max) * height + 'px');
            }
        };
        return {
            init: init,
            isInitialized: function() {
                return initialized;
            },
            renderFrame: renderFrame
        }
    })(),
    'r1': (function() {
        var barsArr = [],
            initialized = false,
            barsEl;
        var height = 0;
        var init = function(config) {
            var count = config.count;
            var width = config.width;
            var barWidth = (width / count) >> 0;
            height = config.height;
            barsEl = document.getElementById('dots');
            for (var i = 0; i < count; i++) {
                var nunode = document.createElement('div');
                nunode.classList.add('dot');
                nunode.style.width = barWidth + 'px';
                nunode.style.height = barWidth + 'px';
                nunode.style.borderRadius = (barWidth / 2) + 'px';
                nunode.style.left = (barWidth * i) + 'px';
                barsArr.push(nunode);
                barsEl.appendChild(nunode);
            }
            initialized = true;
        };
        var max = 256;
        var renderFrame = function(frequencyData) {
            for (var i = 0; i < barsArr.length; i++) {
                var bar = barsArr[i];
                bar.style.bottom = ((frequencyData[i] / max) * height + 'px');
            }
        };
        return {
            init: init,
            isInitialized: function() {
                return initialized;
            },
            renderFrame: renderFrame
        }
    })(),
    'r2': (function() {
        var barsArrLeft = [],
            barsArrRight = [],
            initialized = false,
            barsEl;
        var height = 0;
        var width = 0;
        var init = function(config) {
            var count = config.count;
            width = config.width;
            height = config.height;
            var barHeight = (height / count) >> 0;
            barsEl = document.getElementById('symm');
            for (var i = 0; i < count; i++) {
                var nunode = document.createElement('div');
                var symnode = document.createElement('div');
                nunode.classList.add('symbar_left');
                symnode.classList.add('symbar_right');
                nunode.style.height = symnode.style.height = barHeight + 'px';
                nunode.style.bottom = symnode.style.bottom = (barHeight * i) + 'px';
                barsArrLeft.push(nunode);
                barsArrRight.push(symnode);
                barsEl.appendChild(nunode);
                barsEl.appendChild(symnode);
            }
            initialized = true;
        };
        var max = 256;
        var renderFrame = function(frequencyData) {
            for (var i = 0; i < barsArrLeft.length; i++) {
                var bar = barsArrLeft[i];
                var symbar = barsArrRight[i];
                var val = ((frequencyData[i] / max) * width / 2 + 'px');
                bar.style.width = symbar.style.width = val;
            }
        };
        return {
            init: init,
            isInitialized: function() {
                return initialized;
            },
            renderFrame: renderFrame
        }
    })(),
    'r3': (function() {
        var circles = [];
        var initialized = false;
        var height = 0;
        var width = 0;
        var init = function(config) {
            var count = config.count;
            width = config.width;
            height = config.height;
            var circleMaxWidth = (width * 0.66) >> 0;
            circlesEl = document.getElementById('circles');
            for (var i = 0; i < count; i++) {
                var node = document.createElement('div');
                node.style.width = node.style.height = (i / count * circleMaxWidth) + 'px';
                node.classList.add('circle');
                circles.push(node);
                circlesEl.appendChild(node);
            }
            initialized = true;
        };
        var max = 256;
        var renderFrame = function(frequencyData) {
            for (var i = 0; i < circles.length; i++) {
                var circle = circles[i];
                circle.style.cssText = '-webkit-transform:scale(' + ((frequencyData[i] / max)) + ')';
            }
        };
        return {
            init: init,
            isInitialized: function() {
                return initialized;
            },
            renderFrame: renderFrame
        }
    })(),
    'r4': (function() {
        var bars = [];
        var initialized = false;
        var height = 0;
        var width = 0;
        var init = function(config) {
            var count = config.count;
            width = config.width;
            height = config.height;
            var center = width / 2;
            var circleMaxWidth = (width * 0.5) >> 0;
            var radius = circleMaxWidth * 0.2;
            var twopi = 2 * Math.PI;
            var change = twopi / count;
            circlesEl = document.getElementById('circular');
            for (var i = 0; i < twopi; i += change) {
                var node = document.createElement('div');
                node.style.left = (center + radius * Math.cos(i)) + 'px';
                node.style.top = (center + radius * Math.sin(i)) + 'px';
                node.style.webkitTransform = node.style.mozTransform = node.style.transform = 'rotate(' + (i - (Math.PI / 2)) + 'rad)';
                node.style.webkitTransformOrigin = node.style.mozTransformOrigin = node.style.transformOrigin = '0px 0px';
                node.classList.add('circularBar');
                bars.push(node);
                circlesEl.appendChild(node);
            }
            var center = document.createElement('div');
            center.id = 'circularCenter';
            circlesEl.appendChild(center);
            initialized = true;
        };
        var max = 256;
        var renderFrame = function(frequencyData) {
            for (var i = 0; i < bars.length; i++) {
                var bar = bars[i];
                bar.style.height = ((frequencyData[i] / max) * 150) + 'px';
            }
        };
        return {
            init: init,
            isInitialized: function() {
                return initialized;
            },
            renderFrame: renderFrame
        }
    })(),
    'r5': (function() {
        var barsRight = [];
        var barsLeft = [];
        var initialized = false;
        var height = 0;
        var width = 0;
        var init = function(config) {
            var count = config.count;
            width = config.width;
            height = config.height;
            var center = width / 2;
            var circleMaxWidth = (width * 0.5) >> 0;
            var radius = circleMaxWidth * 0.3;
            var twopi = 2 * Math.PI
            var change = (twopi / count) / 2;
            var cos = Math.cos;
            var sin = Math.sin;
            circlesEl = document.getElementById('curve');
            for (var i = 0; i < twopi / 2; i += change) {
                var node = document.createElement('div');
                var x = (center + radius * (4 * cos(i / 2) * Math.pow(sin(i / 2), 4)));
                var y = (center + radius * (2 * cos(i + Math.PI)));
                node.style.left = x + 'px';
                node.style.top = y + 'px';
                var symnode = document.createElement('div');
                var symx = width - x;
                var symy = y;
                symnode.style.left = symx + 'px';
                symnode.style.top = symy + 'px';
                var rotation = Math.atan(Math.abs(x - center) / Math.abs(y - center));
                node.style.webkitTransform = node.style.mozTransform = node.style.transform = 'rotate(' + (-Math.PI / 3) + 'rad)';
                node.style.webkitTransformOrigin = node.style.mozTransformOrigin = node.style.transformOrigin = '0px 0px';
                symnode.style.webkitTransform = symnode.style.mozTransform = symnode.style.transform = 'rotate(' + (Math.PI / 3) + 'rad)';
                symnode.style.webkitTransformOrigin = symnode.style.mozTransformOrigin = symnode.style.transformOrigin = '0px 0px';
                node.classList.add('curveBar');
                symnode.classList.add('curveBar');
                barsLeft.push(symnode);
                barsRight.push(node);
                circlesEl.appendChild(node);
                circlesEl.appendChild(symnode);
            }
            initialized = true;
        };
        var max = 256;
        var renderFrame = function(frequencyData) {
            for (var i = 0; i < barsRight.length; i++) {
                var bar = barsRight[i];
                var left = barsLeft[i];
                bar.style.height = left.style.height = ((frequencyData[barsRight.length - i] / max) * 150) + 'px';
            }
        };
        return {
            init: init,
            isInitialized: function() {
                return initialized;
            },
            renderFrame: renderFrame
        }
    })()
};
window.onload = function() {
    function Visualization(config) {
        var audio, audioStream, analyser, source, audioCtx, canvasCtx, frequencyData, running = false,
            renderer = config.renderer,
            width = config.width || 360,
            height = config.height || 360;
        var init = function() {
            audio = document.getElementById('r0audio');
            audioCtx = new AudioContext();
            analyser = audioCtx.createAnalyser();
            source = audioCtx.createMediaElementSource(audio);
            source.connect(analyser);
            analyser.connect(audioCtx.destination);
            analyser.fftSize = 64;
            frequencyData = new Uint8Array(analyser.frequencyBinCount);
            renderer.init({
                count: analyser.frequencyBinCount,
                width: width,
                height: height
            });
        };
        this.start = function() {
            audio.play();
            running = true;
            renderFrame();
        };
        this.stop = function() {
            running = false;
            audio.pause();
        };
        this.setRenderer = function(r) {
            if (!r.isInitialized()) {
                r.init({
                    count: analyser.frequencyBinCount,
                    width: width,
                    height: height
                });
            }
            renderer = r;
        };
        this.isPlaying = function() {
            return running;
        }
        var renderFrame = function() {
            analyser.getByteFrequencyData(frequencyData);
            renderer.renderFrame(frequencyData);
            if (running) {
                requestAnimationFrame(renderFrame);
            }
        };
        init();
    };
    var vis = document.querySelectorAll('.initiator');
    var v = null;
    var lastEl;
    var lastElparentId;
    for (var i = 0; i < vis.length; i++) {
        vis[i].onclick = (function() {
            return function() {
                var el = this;
                var id = el.parentNode.id;
                if (!v) {
                    v = new Visualization({
                        renderer: renderers[id]
                    });
                }
                v.setRenderer(renderers[id]);
                if (v.isPlaying()) {
                    if (lastElparentId === id) {
                        v.stop();
                        el.style.backgroundColor = 'rgba(0,0,0,0.5)';
                    } else {
                        lastEl.style.backgroundColor = 'rgba(0,0,0,0.5)';
                        el.style.backgroundColor = 'rgba(0,0,0,0)';
                    }
                } else {
                    v.start();
                    el.style.backgroundColor = 'rgba(0,0,0,0)';
                }
                lastElparentId = id;
                lastEl = el;
            };
        })();
    }
};