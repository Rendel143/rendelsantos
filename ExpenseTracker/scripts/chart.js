// Initialize the chart variable
let expenseChart = null;

// Default chart data (empty categories initially)
let chartData = {
  labels: ['Food', 'Transport', 'Shopping', 'Other'],
  datasets: [{
    data: [0, 0, 0, 0], // Initialize data with zero amounts
    backgroundColor: ['#ff6384', '#36a2eb', '#ffce56', '#4caf50'],
    borderColor: '#fff',
    borderWidth: 2
  }]
};

// Initialize the chart when the page loads
function initChart() {
  const ctx = document.getElementById('expense-chart').getContext('2d');
  expenseChart = new Chart(ctx, {
    type: 'pie',
    data: chartData,
    options: {
      responsive: true,
      plugins: {
        legend: {
          position: 'top',
        },
        tooltip: {
          callbacks: {
            label: function(tooltipItem) {
              // Correct way to get the value of the item
              let label = tooltipItem.label || '';
              let value = tooltipItem.raw || 0;
              return label + ': $' + value.toFixed(2); // Return formatted value
            }
          }
        }
      }
    }
  });
}

// Call initChart when the page is ready
window.onload = initChart;
