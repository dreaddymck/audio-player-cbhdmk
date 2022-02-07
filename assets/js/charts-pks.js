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
        let ctx, id;
        id = "canvas_" + _dmck_functions.string_to_slug(selector);
        let thecanvas = jQuery("<div />", {
                            class: "chart-container",
                        })
                        .css({
                            "position":"relative", 
                            "height":"auto", 
                            "width":"auto"
                        })
                        .append(`<canvas id="` + id + `"></canvas>`)

        jQuery("#" + selector).append(thecanvas);
        ctx = jQuery("#" + id);
        /*
            Append total count value to each label in the datasets here.               
        */
        let count = 0
        dmck_chart_object[selector].datasets.forEach(element => {
            count = element.dmck_request_count ? element.dmck_request_count[Object.keys(element.dmck_request_count)[0]] : 0;
            if(count){ element.label = element.label + " (" + count + ")"; }                       
        });
        
        const data = {
            labels: dmck_chart_object[selector].labels,
            datasets: dmck_chart_object[selector].datasets
        };
        let title_text = (dmck_chart_object[selector].options && dmck_chart_object[selector].options.plugins.title.text)  ? 
                            dmck_chart_object[selector].options.plugins.title.text : 
                            'Request history';
        new Chart(ctx, {
            type: 'line',
            data: data,
            options: {
                responsive: true,
                plugins: {
                    legend: {
                        position: 'left',
                        labels: {
                            // This more specific font property overrides the global property
                            font: {
                                size: 12,
                            },
                            boxWidth: 10
                        }                       
                    },
                    title: {
                        text: title_text,
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
                    }
                },
            },
        });
    }
}