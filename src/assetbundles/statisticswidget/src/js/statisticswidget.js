import axios from 'axios';
import dayjs from 'dayjs';
import '../scss/statisticswidget.scss';

window.Umami.StatisticsWidget = class UmamiStatisticsWidget {
  constructor(config = {}) {
    this.config = Object.assign({
      before: dayjs().subtract(1, 'day').endOf('day')
    }, config);

    this.wrapper = document.querySelector(`#widget${this.config.id} .js-fa-realtime`);
    this.uniqueVisEl = document.querySelector(`#widget${this.config.id} .js-fa-unique-visitors`);
    this.pageviewsEl = document.querySelector(`#widget${this.config.id} .js-fa-pageviews`);
    this.avgTimeEl = document.querySelector(`#widget${this.config.id} .js-fa-avg-time`);
    this.bounceRateEl = document.querySelector(`#widget${this.config.id} .js-fa-bounce-rate`);

    this.onGet = this.get.bind(this);

    this.init();
  }

  init() {
    this.get();
    setInterval(this.onGet, 5000);

    this.getStats();
  }

  get() {
    axios.get('actions/umami/reports/realtime-widget').then((response) => {
      this.wrapper.innerHTML = response.data[0].x;
    });
  }

  getStats() {
    // Set the after date
    this.setAfter();

    axios.get('actions/umami/reports/site-stats', {
      params: {
        before: this.config.before.unix(),
        after: this.config.after.unix()
      },
    }).then((response) => {
      this.uniqueVisEl.innerHTML = response.data.uniques;
      this.pageviewsEl.innerHTML = response.data.pageviews;
      const t = (response.data.totaltime && response.data.pageviews)
        ? response.data.totaltime / (response.data.pageviews - response.data.bounces) : 0;
      this.avgTimeEl.innerHTML = dayjs()
        .hour(0).minute(0).second(t)
        .millisecond(0)
        .format('mm:ss');
      const num = Math.min(response.data.uniques, response.data.bounces);
      this.bounceRateEl.innerHTML = `${response.data.uniques ? (num / response.data.uniques) * 100 : 0}%`;
    });
  }

  setAfter() {
    switch (this.config.period) {
      case 'week': {
        this.config.after = dayjs().subtract(7, 'days').startOf('day');
        break;
        // return this.getLastDays(7);
      }
      case 'month': {
        this.config.after = dayjs().subtract(30, 'days').startOf('day');
        break;
        // return this.getLastDays(30);
      }
      case 'year': {
        this.config.after = dayjs().subtract(365, 'days').startOf('day');
        break;
        // return this.getLastYear();
      }
      default: break;
    }
  }
};
