const addExpenseButton = document.getElementById('add-expense');
const amountInput = document.getElementById('amount');
const categorySelect = document.getElementById('category');
const descriptionInput = document.getElementById('description');
const dateInput = document.getElementById('date');
const expenseList = document.getElementById('expense-list');
const totalDisplay = document.getElementById('total');

let totalExpenses = 0;

addExpenseButton.addEventListener('click', () => {
  const amount = parseFloat(amountInput.value);
  const category = categorySelect.value;
  const description = descriptionInput.value;
  const date = dateInput.value;

  if (!amount || amount <= 0) {
    alert('Please enter a valid amount.');
    return;
  }

  // Add Expense to List
  const listItem = document.createElement('li');
  listItem.innerHTML = `
    ${date} - ${category}: ${description} - $${amount.toFixed(2)}
    <button class="delete-btn">Delete</button>
  `;

  const deleteButton = listItem.querySelector('.delete-btn');
  deleteButton.addEventListener('click', () => {
    totalExpenses -= amount;
    updateTotal();
    listItem.remove();
  });

  expenseList.appendChild(listItem);

  // Update Total
  totalExpenses += amount;
  updateTotal();

  // Clear Inputs
  amountInput.value = '';
  descriptionInput.value = '';
  dateInput.value = '';
});

function updateTotal() {
  totalDisplay.textContent = totalExpenses.toFixed(2);
}
