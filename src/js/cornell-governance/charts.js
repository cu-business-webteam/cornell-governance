import {Chart} from "chart.js/auto";

chartConfig = chartConfig || {};
let governanceCharts = {};

Object.keys(chartConfig).forEach((key) => {
    if ( chartConfig.hasOwnProperty(key) ) {
        let el = document.getElementById(chartConfig[key].canvasID);

        let userConfig = {
            type: chartConfig[key].type,
            data: {
                datasets: chartConfig[key].datasets,
                labels: chartConfig[key].labels
            },
            parsing: false
        };

        if ( chartConfig[key].hasOwnProperty( 'options' ) ) {
            console.log( 'Adding additional options' );
            userConfig.options = chartConfig[key].options;
        }

        governanceCharts[key] = new Chart(
            el,
            userConfig
        );
    }
});