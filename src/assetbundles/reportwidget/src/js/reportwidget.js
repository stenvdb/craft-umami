import axios from 'axios';
import dayjs from 'dayjs';
import '../scss/reportwidget.scss';

window.Umami.ReportWidget = class UmamiReportWidget {
  constructor(config = {}) {
    this.config = Object.assign({
      before: dayjs().subtract(1, 'day').endOf('day')
    }, config);

    this.labels = [];
    this.range = [];
    this.pageviews = [];
    this.sessions = [];

    this.init();
  }

  init() {
    const range = this.getRange();

    // Set chart.js labels and initial data (= 0)
    range.forEach((step) => {
      this.labels.push(step.label);
      this.pageviews.push(0);
      this.sessions.push(0);
    });

    // Get Umami data
    axios.get('actions/umami/reports/report-widget', {
      params: {
        before: this.config.before.unix(),
        after: this.config.after.unix(),
        unit: this.config.period === 'year' ? 'month' : 'day'
      },
    }).then((response) => {
      // Update chart.js data array
      response.data.pageviews.forEach((row) => {
        const s = dayjs(row.t).unix();
        const i = range.findIndex(r => s >= r.start.unix() && s <= r.end.unix());
        this.pageviews[i] += parseInt(row.y, 10);
      });
      response.data.sessions.forEach((row) => {
        const s = dayjs(row.t).unix();
        const i = range.findIndex(r => s >= r.start.unix() && s <= r.end.unix());
        this.sessions[i] += parseInt(row.y, 10);
      });

      const options = {
        type: 'bar',
        data: {
          labels: this.labels,
          datasets: [
            {
              label: 'Pageviews',
              aspectRatio: 2.5,
              backgroundColor: '#a9cef8',
              borderColor: '#a9cef8',
              pointBackgroundColor: '#a9cef8',
              borderWidth: 3,
              pointRadius: 2,
              pointHitRadius: 4,
              lineTension: 0,
              data: this.pageviews
            },
            {
              label: 'Unique Visitors',
              aspectRatio: 2.5,
              backgroundColor: '#57a0f0',
              borderColor: '#57a0f0',
              pointBackgroundColor: '#57a0f0',
              borderWidth: 3,
              pointRadius: 2,
              pointHitRadius: 4,
              lineTension: 0,
              data: this.sessions
            }
          ]
        },
        options: {
          responsive: true,
          maintainAspectRatio: false,
          legend: {
            display: true
          },
          tooltips: {
            bodyFontColor: 'hsl(209, 18%, 30%)',
            backgroundColor: '#fff',
            borderColor: 'rgba(155, 155, 155, 0.1)',
            borderWidth: 1,
            caretPadding: 6,
            caretSize: 0,
            mode: 'index',
            titleFontColor: 'hsl(209, 18%, 30%)',
          },
          layout: {
            padding: {
              left: 0,
              right: 0,
              top: 0,
              bottom: 0
            }
          },
          scales: {
            yAxes: [{
              ticks: {
                autoSkip: true,
                autoSkipPadding: 5,
                beginAtZero: true,
                fontColor: '#4299E1',
                mirror: true
              },
              stacked: true,
              gridLines: {
                drawBorder: false,
              }
            }],
            xAxes: [{
              ticks: {
                autoSkip: true,
                autoSkipPadding: 10,
                fontColor: '#4299E1',
              },
              // labels: {
              //   display: false
              // },
              stacked: true,
              gridLines: {
                display: false,
                drawBorder: false,
              }
            }]
          }
        }
      };

      const ctx = document.querySelector(`#widget${this.config.id} #myChart`).getContext('2d');
      window.Umami.Global.renderChart(ctx, options);

      // Update total placeholder
      const sumEl = document.querySelector(`#widget${this.config.id} .js-fa-total`);
      const sum = this.pageviews.reduce((a, b) => a + b); // @TODO fix this
      sumEl.innerHTML = sum;
    });
  }

  setDefaults(range) {
    range.forEach((step) => {
      this.labels.push(step.label);
      this.pageviews.push(0);
      this.sessions.push(0);
    });
  }

  getRange() {
    switch (this.config.period) {
      case 'week': {
        this.config.after = dayjs().subtract(7, 'days').startOf('day');
        return this.getLastDays(7);
      }
      case 'month': {
        this.config.after = dayjs().subtract(30, 'days').startOf('day');
        return this.getLastDays(30);
      }
      case 'year': {
        this.config.after = dayjs().subtract(365, 'days').startOf('day');
        return this.getLastYear();
      }
      default: return [];
    }
  }

  getLastDays(days) {
    const a = [];
    for (let i = days; i > 0; i -= 1) {
      a.push({
        label: dayjs().subtract(i, 'days').format('D/M'),
        start: dayjs().subtract(i, 'days').startOf('day'),
        end: dayjs().subtract(i, 'days').endOf('day')
      });
    }
    return a;
  }

  getLastYear() {
    const a = [];

    // Get the first month
    a.push({
      label: dayjs().subtract(365, 'days').format('MMM'),
      start: dayjs().subtract(365, 'days').startOf('day'),
      end: dayjs().subtract(365, 'days').endOf('month').endOf('day'),
    });

    // Get the next 11 months
    for (let i = 1; i <= 11; i += 1) {
      a.push({
        label: a[i - 1].end.add(1, 'day').format('MMM'),
        start: a[i - 1].end.add(1, 'day').startOf('day'),
        end: a[i - 1].end.add(1, 'day').endOf('month')
      });
    }

    // Get the last month
    a.push({
      label: this.config.before.format('MMM'),
      start: a[a.length - 1].end.add(1, 'day'),
      end: this.config.before,
    });

    return a;
  }
};
