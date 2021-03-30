import * as actions from './actions';
import * as getters from './getters';
import * as mutations from './mutations';

const state = {
    summary: {},
    taxonomiesList: {},
    collectionsList: {},
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
          categories: [],
          tooltip: { enabled: true }
      },
      yaxis: {
          title: {
              text: ''
          },
          tooltip: { enabled: true }
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
          height: 350,
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
        tooltip: { enabled: true }
      },
      yaxis: {
        tickPlacement: 'on',
        tooltip: { enabled: true }
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
    areaChartOptions: {
      chart: {
        height: 200,
        type: 'area',
        toolbar: {
          show: true,
          tools: {
              download: true,
              selection: false,
              zoom: true,
              zoomin: true,
              zoomout: true,
              pan: true,
          }
        },
      },
      dataLabels: {
        enabled: false
      },
      stroke: {
        width: 1,
        curve: 'smooth'
      },
      fill: {
        opacity: 1,
        type: 'gradient',
        gradient: {
          shade: 'light',
          type: "vertical",
          opacityFrom: 0.65,
          opacityTo: 0.35,
        }
      },
      legend: {
        position: 'top',
        horizontalAlign: 'left'
      },
      xaxis: {
        type: 'datetime'
      },
      yaxis: {
        labels: {
            minWidth: 64
        }
      }
    }
};

export default {
    namespaced: true,
    state,
    mutations,
    actions,
    getters
}