"use strict";

const charts_pkg = {
    top_requests_chart: function(){
        /**
         * top_10_json is currently embeded in html - playlist-layout.php
         */        
        if(typeof top_10_json === 'undefined'){return;}
        if(!top_10_json.length){return;}
        let arr = top_10_json;
        let labels = [];
        let data = [];
        let colors = dmck_audioplayer.chart_colors ? JSON.parse(dmck_audioplayer.chart_colors) : [];
        for( let x in arr ){
            labels.push( arr[x].title ? unescape(arr[x].title.toUpperCase()) : unescape(arr[x].name) );
            data.push(arr[x].count)                
        }
        jQuery("#top-10").append(`<canvas id="top-requests-chart" width="auto" height="auto"></canvas>`);            
        let ctx = jQuery("#top-requests-chart");
        new Chart(ctx, {
            type: 'horizontalBar',
            data: {
                labels: labels,
                datasets: [{
                    label: '# of Requests',
                    data: data,
                    borderWidth: 1,
                    backgroundColor: colors,
                }]
            },
            options: {
                responsive: true,
                legend: {
                    labels: {
                        fontColor: "#ffffff",
                    }
                },                    
                scales: {
                    yAxes: [{
                        ticks: {
                            beginAtZero:true,
                            fontColor: "#ffffff",
                        }
                    }],                      
                }
            }
        }); 
    },
    post_chart: function(){
        /**
         * top_10_json is currently embeded in html - playlist-layout.php
         */        
        if(typeof post_chart_json === 'undefined'){return;}
        if(!post_chart_json.length){return;}
        let chart_data = {};
        let colors = dmck_audioplayer.chart_colors ? JSON.parse(dmck_audioplayer.chart_colors) : [];
        let ctx,id,date;
        for( let x in post_chart_json ){
            if(typeof chart_data[post_chart_json[x].target] === "undefined" ){ 
                chart_data[post_chart_json[x].target] = { labels : [], data: [], filename: ""}
            }
            date = new Date(post_chart_json[x].time*1000 ).toLocaleString("en",{
                // weekday: "numeric",
                year: "numeric",
                month: "2-digit",
                day: "numeric"
            });
            chart_data[post_chart_json[x].target].labels.push( date ); //
            chart_data[post_chart_json[x].target].data.push(post_chart_json[x].count); 
            chart_data[post_chart_json[x].target].filename = post_chart_json[x].filename; 
        }        
        for( let c in chart_data ){
            id = c + "_canvas";
            jQuery(".post_chart_section, ." + c + "_chart").append(`<canvas id="` + id + `" width="auto" height="auto"></canvas>`);
            ctx = jQuery("#" + id);
            new Chart(ctx, {
                type: 'line',
                data: {
                    labels: chart_data[c].labels,
                    datasets: [{
                        label: chart_data[c].filename + ' year history',
                        data: chart_data[c].data,
                        backgroundColor: colors,
                    }]
                },
                options: {
                    responsive: true,
                    legend: {
                        labels: {
                            fontColor: "#ffffff",
                        }
                    },
                    scales: {
                        yAxes: [{
                            ticks: {
                                beginAtZero:true,
                                fontColor: "#ffffff",
                            }
                        }],                      
                    }                    
                }
            });            
        }
    }
}