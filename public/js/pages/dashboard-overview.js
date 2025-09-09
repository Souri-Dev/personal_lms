  fetch('/api/dashboard/stats')
  .then(res => res.json())
  .then(data => {
    var options = {
      series: data.series,
      chart: { 
        type: "bar",
        height: 200,
        parentHeightOffset: 0,
        zoom: { enabled: false },
        toolbar: { show: false },
        animations: { enabled: false },
        stacked: true },
        plotOptions: { bar: { columnWidth: "50%" } },
      xaxis: { 
        categories: data.categories,
        labels: { padding: 0 },
        tooltip: { enabled: false },
        axisBorder: { show: false } },
    yaxis: { labels: { padding: 4 } },
        colors: ["rgba(113, 104, 238, 1)",
                "rgba(113, 104, 238, 0.6)",
                "rgba(113, 104, 238, 0.2)",],
        legend: { position: "top", show: !0, horizontalAlign: "right", show: false },
        fill: { opacity: 1 },
        grid: {
            padding: { top: -20, right: 0, bottom: 0 },
            strokeDashArray: 4,
            xaxis: { lines: { show: !0 } },
        },
        };
    new ApexCharts(document.querySelector("#students_per_subject_chart"), options).render();
  });
