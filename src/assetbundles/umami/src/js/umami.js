import Chart from 'chart.js';
import '../scss/umami.scss';

window.Umami = {};
window.Umami.Global = class Umami {
  // Expose Chart.js once
  static renderChart(ctx, options) {
    new Chart(ctx, options);
  }
};

window.addEventListener('DOMContentLoaded', () => {
  new window.Umami.Global();
});
