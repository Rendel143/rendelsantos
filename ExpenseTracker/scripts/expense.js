let totalExpenses = 0;
const expenses = [];

// Event listener for adding an expense
document.getElementById('add-expense').addEventListener('click', () => {
  const amount = parseFloat(document.getElementById('amount').value);
  const category = document.getElementById('category').value;
  const description = document.getElementById('description').value || "No Description";
  const date = document.getElementById('date').value;

  if (amount && date) {
    totalExpenses += amount;
    expenses.push({ amount, category, description, date });

    // Update the UI
    updateExpenseList();
    updateTotal();
    updateChart();  // This will update the chart with the new data
  } else {
    alert('Please fill in all required fields.');
  }
});

// Update the expense list in the UI
function updateExpenseList() {
  const expenseList = document.getElementById('expense-list');
  expenseList.innerHTML = '';

  expenses.forEach(expense => {
    const li = document.createElement('li');
    li.innerHTML = `    
      <strong>${expense.category}</strong>: $${expense.amount} <br>
      <small>${expense.description} | ${expense.date}</small>
    `;
    expenseList.appendChild(li);
  });
}

// Update the total expenses in the UI
function updateTotal() {
  document.getElementById('total').textContent = totalExpenses.toFixed(2);
}

// Update the pie chart with real-time expense data
function updateChart() {
  // Calculate the total expenses for each category
  const categoryTotals = {
    Food: 0,
    Transport: 0,
    Shopping: 0,
    Other: 0
  };

  expenses.forEach(expense => {
    categoryTotals[expense.category] += expense.amount;
  });

  // Prepare the new data for the chart
  const newData = [
    categoryTotals.Food,
    categoryTotals.Transport,
    categoryTotals.Shopping,
    categoryTotals.Other
  ];

  // Update the chart data
  if (expenseChart) {
    expenseChart.data.datasets[0].data = newData;
    expenseChart.update(); // Re-render the chart with the updated data
  }
}
