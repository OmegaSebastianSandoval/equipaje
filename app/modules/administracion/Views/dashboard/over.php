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
      radial-gradient(circle at 20% 10%, rgba(245, 184, 65, 0.15), transparent 45%),
      radial-gradient(circle at 85% 85%, rgba(46, 111, 103, 0.12), transparent 40%),
      var(--dash-bg);
    border-radius: 18px;
  }

  .dashboard-charts-grid {
    display: grid;
    grid-template-columns: 1fr 1fr;
    gap: 1.2rem;
  }

  .chart-card {
    position: relative;
    padding: 1.25rem;
    background: linear-gradient(150deg, rgba(255, 255, 255, 0.95), rgba(255, 251, 244, 0.88));
    border: 1px solid var(--dash-border);
    border-radius: 16px;
    box-shadow: var(--dash-shadow);
    overflow: hidden;
  }

  .chart-card.full-width {
    grid-column: 1 / -1;
  }

  .chart-card h3 {
    margin: 0 0 0.15rem;
    font: 700 1.1rem/1.2 "Barlow", sans-serif;
    color: var(--dash-ink);
    letter-spacing: 0.02em;
  }

  .chart-card p {
    margin: 0 0 1rem;
    font: 500 0.8rem/1.2 "Space Grotesk", sans-serif;
    color: var(--dash-muted);
    text-transform: uppercase;
    letter-spacing: 0.08em;
  }

  .chart-wrap {
    position: relative;
    height: 280px;
  }

  @media (max-width: 991px) {
    .dashboard-charts-grid {
      grid-template-columns: 1fr;
    }
  }
</style>

<div class="row mb-3">
  <div class="col-12">
    <h2 class="font-weight-bold" style="font-family: 'Barlow', sans-serif; color: var(--dash-ink);">Dashboard
      Estadísticas Over</h2>
    <p style="font-family: 'Space Grotesk', sans-serif; color: var(--dash-muted);">Análisis detallado de maletas en
      contingencia</p>
  </div>
</div>

<section class="container-fluid dashboard-charts mb-5">
  <div class="dashboard-charts-grid">

    <!-- GRAFICAS EXCLUSIVAS OVER -->
    <article class="chart-card">
      <h3>Maletas por Tipo Over</h3>
      <p>Distribución de estados prioritarios</p>
      <div class="chart-wrap">
        <canvas id="ChartOverTipo"></canvas>
      </div>
    </article>

    <article class="chart-card">
      <h3>Diferenciación Over 1</h3>
      <p>Nacional vs Internacional</p>
      <div class="chart-wrap">
        <canvas id="ChartOverLugar"></canvas>
      </div>
    </article>

    <!-- GRAFICAS DEL DASHBOARD NORMAL (FILTRADAS O COMPARATIVAS) -->
    <article class="chart-card">
      <h3>Maletas por Aerolínea</h3>
      <p>Comparativo operacional</p>
      <div class="chart-wrap">
        <canvas id="ChartOne"></canvas>
      </div>
    </article>

    <article class="chart-card">
      <h3>Estado de Maletas</h3>
      <p>Distribución general (Reclamadas/Sin Reclamar)</p>
      <div class="chart-wrap">
        <canvas id="ChartTwo"></canvas>
      </div>
    </article>

    <article class="chart-card full-width">
      <h3>Tendencia de Ingreso Over</h3>
      <p>Frecuencia de registro por hora</p>
      <div class="chart-wrap">
        <canvas id="ChartOverHora"></canvas>
      </div>
    </article>

  </div>
</section>

<script src="/components/chart/chart.min.js"></script>
<script src="/components/chart/chartjs-plugin-datalabels@2.js"></script>

<script>
  Chart.defaults.font.family = 'Space Grotesk, sans-serif';
  Chart.defaults.color = '#61564d';

  const commonOptions = {
    responsive: true,
    maintainAspectRatio: false,
    plugins: {
      legend: {
        position: 'bottom',
        labels: { boxWidth: 12, boxHeight: 12, usePointStyle: true, pointStyle: 'circle', padding: 15 }
      },
      tooltip: {
        backgroundColor: 'rgba(43, 38, 33, 0.94)',
        titleFont: { family: 'Barlow, sans-serif', weight: '600' },
        bodyFont: { family: 'Space Grotesk, sans-serif' },
        padding: 10,
        cornerRadius: 8
      },
      datalabels: {
        color: '#ffffff',
        font: {
          family: 'Barlow, sans-serif',
          weight: '700',
          size: 13
        },
        textShadowColor: 'rgba(0, 0, 0, 0.3)',
        textShadowBlur: 4,
        formatter: (value) => value
      }
    }
  };

  // 1. Chart Over Tipo (1, 2, 3)
  new Chart(document.getElementById('ChartOverTipo').getContext('2d'), {
    type: 'doughnut',
    data: {
      labels: [<?php foreach ($this->graficaOverTipo as $v) {
        echo "'Tipo " . $v->maleta_over_tipo . "',";
      } ?>],
      datasets: [{
        data: [<?php foreach ($this->graficaOverTipo as $v) {
          echo $v->cantidad . ",";
        } ?>],
        backgroundColor: ['rgba(245, 184, 65, 0.96)', 'rgba(46, 111, 103, 0.9)', 'rgba(141, 128, 116, 0.92)'],
        borderColor: '#fff7eb',
        borderWidth: 3,
        hoverOffset: 12

      }]
    },
    options: commonOptions,
    plugins: [ChartDataLabels]
  });

  // 2. Chart Over Lugar (N vs I para Over 1)
  new Chart(document.getElementById('ChartOverLugar').getContext('2d'), {
    type: 'pie',
    data: {
      labels: [<?php foreach ($this->graficaOverLugar as $v) {
        $label = ($v->maleta_over_lugar == 'N') ? 'Nacional' : (($v->maleta_over_lugar == 'I') ? 'Internacional' : 'Sin Definir');
        echo "'" . $label . "',";
      } ?>],
      datasets: [{
        data: [<?php foreach ($this->graficaOverLugar as $v) {
          echo $v->cantidad . ",";
        } ?>],
        backgroundColor: ['rgba(46, 111, 103, 0.95)', 'rgba(217, 152, 34, 0.9)', 'rgba(183, 169, 154, 0.88)'],
        borderColor: '#fff7eb',
        borderWidth: 3,
        hoverOffset: 12
      }]
    },
    options: commonOptions,
    plugins: [ChartDataLabels]
  });

  // 3. Chart Maletas por Aerolinea (Comparativo)
  new Chart(document.getElementById('ChartOne').getContext('2d'), {
    type: 'bar',
    data: {
      labels: [<?php foreach ($this->grafica1 as $v) {
        echo "'" . $v->aerolinea_nombre . "',";
      } ?>],
      datasets: [{
        label: 'Cantidad',
        data: [<?php foreach ($this->grafica1 as $v) {
          echo $v->cantidad . ",";
        } ?>],
        backgroundColor: 'rgba(245, 184, 65, 0.9)',
        borderRadius: 8,
        maxBarThickness: 40
      }]
    },
    options: Object.assign({}, commonOptions, {
      plugins: { legend: { display: false } },
      scales: {
        y: { beginAtZero: true, grid: { color: 'rgba(117,105,93,0.1)' } },
        x: { grid: { display: false } }
      }
    })
  });

  // 4. Chart Estado de Maletas
  new Chart(document.getElementById('ChartTwo').getContext('2d'), {
    type: 'pie',
    data: {
      labels: [<?php foreach ($this->grafica2 as $v) {
        $estado = $v->maleta_estado == '' ? 'Sin Reclamar' : 'Reclamada';
        echo "'" . $estado . "',";
      } ?>],
      datasets: [{
        data: [<?php foreach ($this->grafica2 as $v) {
          echo $v->cantidad . ",";
        } ?>],
        backgroundColor: ['rgba(245, 184, 65, 0.96)', 'rgba(46, 111, 103, 0.9)'],
        borderColor: '#fff7eb',
        borderWidth: 3
      }]
    },
    options: Object.assign({}, commonOptions, {
      plugins: Object.assign({}, commonOptions.plugins, {
        datalabels: Object.assign({}, commonOptions.plugins.datalabels, {
          display: true
        })
      })
    }),
    plugins: [ChartDataLabels]
  });

  // 5. Chart Tendencia Over Hora
  new Chart(document.getElementById('ChartOverHora').getContext('2d'), {
    type: 'line',
    data: {
      labels: [<?php foreach ($this->graficaOverHora as $v) {
        echo "'" . $v->hora . ":00',";
      } ?>],
      datasets: [{
        label: 'Ingresos Over',
        data: [<?php foreach ($this->graficaOverHora as $v) {
          echo $v->cantidad . ",";
        } ?>],
        fill: true,
        borderColor: 'rgba(46, 111, 103, 0.95)',
        backgroundColor: 'rgba(46, 111, 103, 0.12)',
        tension: 0.4,
        pointRadius: 4,
        pointBackgroundColor: '#fff',
        pointBorderColor: 'rgba(46, 111, 103, 1)'
      }]
    },
    options: Object.assign({}, commonOptions, {
      scales: {
        y: { beginAtZero: true, grid: { color: 'rgba(117,105,93,0.1)' } },
        x: { grid: { color: 'rgba(117,105,93,0.05)' } }
      }
    })
  });
</script>