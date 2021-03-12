import * as actions from './actions';
import * as getters from './getters';
import * as mutations from './mutations';

const state = {
    summary: {},
    taxonomiesList: {},
    colletionsList: {},
    taxonomyTerms: {},
    metadata: {},
    metadataList: {},
    activities: {},
    stackedBarChartOptions: {
        chart: {
          type: 'bar',
          height: 350,
          stacked: true,
          toolbar: {
              show: true
          },
          zoom: {
              enabled: true,
              autoScaleYaxis: true,
          }
      },
      title: {
          text: ''
      },
      responsive: [{
          breakpoint: 480,
          options: {
              legend: {
                  position: 'bottom',
                  offsetX: -10,
                  offsetY: 0
              }
          }
      }],
      plotOptions: {
          bar: {
              borderRadius: 0,
              horizontal: false,
          },
      },
      xaxis: {
          type: 'category',
          tickPlacement: 'on',
          categories: []
      },
      yaxis: {
          title: {
              text: ''
          }
      },
      legend: {
          position: 'right',
          offsetY: 40
      },
      fill: {
          opacity: 1
      }
    },
    donutChartOptions: {
      chart: {
          type: 'donut',
          toolbar: {
            show: true
          },
      },
      title: {
          text: ''
      },
      stroke: {
        width: 0
      },
      labels: [],
      responsive: [{
          breakpoint: 480,
          options: {
              chart: {
                  width: 200
              },
              legend: {
                  position: 'bottom'
              }
          }
      }]
    },
    horizontalBarChartOptions: {
      chart: {
        type: 'bar',
        height: 350,
        stacked: true,
        stackType: '100%',
        toolbar: {
            show: true
        },
        zoom: {
            type: 'y',
            enabled: true,
            autoScaleYaxis: true,
        }
      },
      plotOptions: {
        bar: {
          horizontal: true,
        },
      },
      title: {
        text: ''
      },
      xaxis: {
        type: 'category',
        tickPlacement: 'on',
        categories: [],
      },
      yaxis: {
        tickPlacement: 'on',
      },
      tooltip: {
        y: {
          formatter: function (val) {
            return val + "%"
          }
        }
      },
      fill: {
        opacity: 1
      
      },
      legend: {
        position: 'top',
        horizontalAlign: 'left',
        offsetX: 40,
      }
    },
    heatMapChartOptions: {
      chart: {
        height: 350,
        type: 'heatmap',
      },
      dataLabels: {
        enabled: false
      },
      colors: [ '#298596' ],
      title: {
        text: ''
      },
    }
};

export default {
    namespaced: true,
    state,
    mutations,
    actions,
    getters
}