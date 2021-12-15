"use strict";

//TODO: chart color variation script

window._dmck_charts_pkg = {
    defaults: function(){
        let response = {
            borderColor : _dmck_functions.computed["color"],
            backgroundColor : _dmck_functions.computed["background-color"],
            fontColor : _dmck_functions.computed["color"],
        }
        if (dmck_audioplayer && dmck_audioplayer.chart_rgb_enabled) {
            response.borderColor = dmck_audioplayer.chart_rgb
            response.backgroundColor = dmck_audioplayer.chart_rgb_init;
            response.fontColor = dmck_audioplayer.chart_rgb;
        }        
        return response;
    },
    time_scale: function (selector) {
        if (typeof chart_json === 'undefined') { return; }
        let ctx, id;
        const data = {
            labels: chart_json.labels,
            datasets: chart_json.datasets
        };
        id = "timescale_canvas_" + _dmck_functions.string_to_slug(selector);
        jQuery(selector).append(`<canvas id="` + id + `" width="auto" height="auto"></canvas>`);
        ctx = jQuery("#" + id);
        let options = _dmck_charts_pkg.defaults();
        new Chart(ctx, {
            type: 'line',
            data: data,
            options: {
                plugins: {
                    title: {
                        text: 'Request history',
                        display: true
                    }
                },
                scales: {
                    x: {
                        title: {
                            display: true,
                            text: 'Date'
                        },
                    },
                    y: {
                        title: {
                            display: true,
                            text: 'Requests'
                        },
                        type: 'logarithmic',
                    }
                },
            },
        });
    }
}