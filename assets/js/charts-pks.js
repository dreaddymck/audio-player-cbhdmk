"use strict";

//TODO: chart color variation script

window._dmck_charts_pkg = {
    time_scale: function (selector) {
        if (typeof chart_json === 'undefined') {
            return;
        }
        let ctx, id;
        const data = {
            labels: chart_json.labels,
            datasets: chart_json.datasets
        };
        id = "time_scale_canvas";
        jQuery(selector).append(`<canvas id="` + id + `" width="auto" height="auto"></canvas>`);
        ctx = jQuery("#" + id);
        let borderColor = _dmck_functions.computed["color"];
        let backgroundColor = _dmck_functions.computed["background-color"];
        let fontColor = _dmck_functions.computed["color"];
        if (dmck_audioplayer && dmck_audioplayer.chart_rgb_enabled) {
            borderColor = dmck_audioplayer.chart_rgb
            backgroundColor = dmck_audioplayer.chart_rgb_init;
            fontColor = dmck_audioplayer.chart_rgb;
        }
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