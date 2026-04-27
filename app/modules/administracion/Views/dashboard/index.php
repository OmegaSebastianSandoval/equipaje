<link rel="preconnect" href="https://fonts.googleapis.com">
<link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
<link
  href="https://fonts.googleapis.com/css2?family=Barlow:wght@500;600;700&family=Space+Grotesk:wght@400;500;600&display=swap"
  rel="stylesheet">

<style>
  :root {
    --dash-bg: #f3f0e9;
    --dash-paper: #fffdf8;
    --dash-ink: #2f2a25;
    --dash-muted: #7b736b;
    --dash-primary: #f5b841;
    --dash-primary-strong: #d99822;
    --dash-secondary: #8d8074;
    --dash-accent: #2e6f67;
    --dash-grid: rgba(117, 105, 93, 0.18);
    --dash-border: rgba(84, 74, 65, 0.15);
    --dash-shadow: 0 20px 40px rgba(53, 41, 30, 0.1);
  }

  .dashboard-charts {
    padding-top: 1.5rem;
    padding-bottom: 2.5rem;
    background:
      radial-gradient(circle at 20% 10%, rgba(245, 184, 65, 0.2), transparent 45%),
      radial-gradient(circle at 85% 85%, rgba(46, 111, 103, 0.16), transparent 40%),
      var(--dash-bg);
    border-radius: 18px;
  }

  .dashboard-charts-grid {
    display: grid;
    grid-template-columns: 1.05fr 1fr;
    grid-template-areas:
      "chart-one chart-two"
      "chart-three chart-two";
    gap: 1.2rem;
  }

  .chart-card {
    position: relative;
    padding: 1rem 1.15rem 1.25rem;
    background: linear-gradient(150deg, rgba(255, 255, 255, 0.95), rgba(255, 251, 244, 0.88));
    border: 1px solid var(--dash-border);
    border-radius: 16px;
    box-shadow: var(--dash-shadow);
    overflow: hidden;
  }

  /* .chart-card::before {
    content: "";
    position: absolute;
    left: 0;
    top: 0;
    width: 100%;
    height: 4px;
    background: linear-gradient(90deg, var(--dash-primary), var(--dash-accent));
    opacity: 0.8;
  } */

  .chart-card h3 {
    margin: 0 0 0.15rem;
    font: 700 1rem/1.2 "Barlow", sans-serif;
    color: var(--dash-ink);
    letter-spacing: 0.02em;
  }

  .chart-card p {
    margin: 0 0 0.85rem;
    font: 500 0.8rem/1.2 "Space Grotesk", sans-serif;
    color: var(--dash-muted);
    text-transform: uppercase;
    letter-spacing: 0.08em;
  }

  .chart-wrap {
    position: relative;
    height: 255px;
  }

  .chart-one {
    grid-area: chart-one;
  }

  .chart-two {
    grid-area: chart-two;
  }

  .chart-two .chart-wrap {
    height: 530px;
  }

  .chart-three {
    grid-area: chart-three;
  }

  @media (max-width: 991px) {
    .dashboard-charts-grid {
      grid-template-columns: 1fr;
      grid-template-areas:
        "chart-three"
        "chart-one"
        "chart-two";
    }

    .chart-two .chart-wrap {
      height: 360px;
    }
  }
</style>

<section class="container-fluid dashboard-charts my-4">
  <div class="dashboard-charts-grid">

    <article class="chart-card chart-one">
      <h3>Maletas por Aerolinea</h3>
      <p>Comparativo por operacion</p>
      <div class="chart-wrap">
        <canvas id="ChartOne"></canvas>
      </div>
    </article>
    <article class="chart-card chart-two">
      <h3>Estado de Maletas</h3>
      <p>Distribucion general</p>
      <div class="chart-wrap">
        <canvas id="ChartTwo"></canvas>
      </div>
    </article>
    <article class="chart-card chart-three">
      <h3>Tendencia por Hora</h3>
      <p>Registro de reclamaciones</p>
      <div class="chart-wrap">
        <canvas id="ChartThree"></canvas>
      </div>
    </article>

  </div>
</section>

<script src="/components/chart/chart.min.js"></script>
<script src="/components/chart/chartjs-plugin-datalabels@2.js"></script>
<script>
  Chart.defaults.font.family = 'Space Grotesk, sans-serif';
  Chart.defaults.color = '#61564d';

  var commonOptions = {
    responsive: true,
    maintainAspectRatio: false,
    plugins: {
      legend: {
        labels: {
          boxWidth: 12,
          boxHeight: 12,
          usePointStyle: true,
          pointStyle: 'circle'
        }
      },
      tooltip: {
        backgroundColor: 'rgba(43, 38, 33, 0.94)',
        titleFont: {
          family: 'Barlow, sans-serif',
          weight: '600'
        },
        bodyFont: {
          family: 'Space Grotesk, sans-serif'
        },
        padding: 10,
        displayColors: true,
        caretPadding: 6,
        cornerRadius: 8
      }
    }
  };

  var ctxOne = document.getElementById('ChartOne').getContext('2d');
  var chartOne = new Chart(ctxOne, {
    type: 'bar',
    data: {
      labels: [
        <?php
        foreach ($this->grafica1 as $value) {
          echo "'" . $value->aerolinea_nombre . "',";
        }
        ?>
      ],
      datasets: [{
        label: 'Maletas por Aerolinea',
        data: [
          <?php
          foreach ($this->grafica1 as $value) {
            echo $value->cantidad . ",";
          }
          ?>
        ],
        backgroundColor: [
          'rgba(245, 184, 65, 0.95)',
          'rgba(141, 128, 116, 0.92)',
          'rgba(46, 111, 103, 0.9)',
          'rgba(217, 152, 34, 0.9)',
          'rgba(183, 169, 154, 0.88)'
        ],
        borderColor: [
          'rgba(217, 152, 34, 1)',
          'rgba(119, 107, 95, 1)',
          'rgba(36, 88, 82, 1)',
          'rgba(172, 123, 24, 1)',
          'rgba(147, 133, 118, 1)'
        ],
        borderWidth: 1.5,
        borderRadius: 8,
        hoverBorderWidth: 2,
        maxBarThickness: 48
      }]
    },
    options: Object.assign({}, commonOptions, {
      plugins: Object.assign({}, commonOptions.plugins, {
        legend: {
          display: false
        }
      }),
      scales: {
        x: {
          ticks: {
            color: '#584e46',
            font: {
              size: 11,
              weight: '500'
            }
          },
          grid: {
            display: false
          }
        },
        y: {
          beginAtZero: true,
          ticks: {
            precision: 0,
            color: '#70655b'
          },
          grid: {
            color: 'rgba(117, 105, 93, 0.18)',
            drawBorder: false
          }
        }
      }
    })
  });
</script>

<script>
  var ctxTwo = document.getElementById('ChartTwo').getContext('2d');
  var chartTwo = new Chart(ctxTwo, {
    type: 'pie',
    data: {
      labels: [
        <?php
        foreach ($this->grafica2 as $value) {
          $estado = $value->maleta_estado == '' ? 'Sin Reclamar' : 'Reclamada';
          echo "'" . $estado . "',";
        }
        ?>
      ],
      datasets: [{
        label: 'Maletas por Estado',
        data: [
          <?php
          foreach ($this->grafica2 as $value) {
            echo $value->cantidad . ",";
          }
          ?>
        ],
        backgroundColor: [
          'rgba(245, 184, 65, 0.96)',
          'rgba(141, 128, 116, 0.92)',
          'rgba(46, 111, 103, 0.9)'
        ],
        borderColor: '#fff7eb',
        borderWidth: 3,
        hoverOffset: 10
      }]
    },
    options: Object.assign({}, commonOptions, {
      plugins: Object.assign({}, commonOptions.plugins, {
        legend: {
          position: 'bottom',
          labels: {
            padding: 16,
            boxWidth: 13,
            boxHeight: 13,
            color: '#544b44',
            font: {
              size: 12,
              weight: '600'
            }
          }
        },
        datalabels: {
          color: '#2f2a25',
          font: {
            family: 'Barlow, sans-serif',
            weight: '700',
            size: 11
          },
          formatter: function (value) {
            return value;
          }
        }
      })
    }),
    plugins: [ChartDataLabels]
  });
</script>

<script>
  var ctxThree = document.getElementById('ChartThree').getContext('2d');
  var chartThree = new Chart(ctxThree, {
    type: 'line',
    data: {
      labels: [
        <?php
        foreach ($this->grafica3 as $value) {
          echo "'" . $value->hora . "',";
        }
        ?>
      ],
      datasets: [{
        label: 'Maletas por Hora de reclamacion',
        data: [
          <?php
          foreach ($this->grafica1 as $value) {
            echo $value->cantidad . ",";
          }
          ?>
        ],
        fill: true,
        borderColor: 'rgba(245, 184, 65, 0.96)',
        backgroundColor: 'rgba(245, 184, 65, 0.17)',
        borderWidth: 2.5,
        tension: 0.35,
        pointRadius: 4,
        pointHoverRadius: 6,
        pointBorderWidth: 2,
        pointBackgroundColor: '#fffaf2',
        pointBorderColor: 'rgba(245, 184, 65, 0.96)'
      }]
    },
    options: Object.assign({}, commonOptions, {
      plugins: Object.assign({}, commonOptions.plugins, {
        legend: {
          display: false
        }
      }),
      scales: {
        x: {
          ticks: {
            color: '#5f554e',
            maxRotation: 0,
            font: {
              size: 11,
              weight: '500'
            }
          },
          grid: {
            color: 'rgba(117, 105, 93, 0.11)',
            drawBorder: false
          }
        },
        y: {
          beginAtZero: true,
          ticks: {
            precision: 0,
            color: '#70655b'
          },
          grid: {
            color: 'rgba(117, 105, 93, 0.18)',
            drawBorder: false
          }
        }
      }
    })
  });
</script>