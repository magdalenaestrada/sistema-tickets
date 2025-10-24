import ApexCharts from "apexcharts";

// ✅ Ejemplo simple (puedes adaptarlo luego)
document.addEventListener("DOMContentLoaded", () => {
  const chartEl = document.querySelector("#salesChart");

  if (chartEl) {
    const options = {
      chart: {
        type: "line",
        height: 300,
        toolbar: { show: false },
      },
      series: [
        {
          name: "Ventas",
          data: [10, 41, 35, 51, 49, 62, 69],
        },
      ],
      xaxis: {
        categories: ["Lun", "Mar", "Mié", "Jue", "Vie", "Sáb", "Dom"],
      },
      colors: ["#4f46e5"],
    };

    const chart = new ApexCharts(chartEl, options);
    chart.render();
  }
});
