document.getElementById('switch-to-register').addEventListener('click', () => {
    document.getElementById('login-form').style.display = 'none';
    document.getElementById('register-form').style.display = 'block';
    document.getElementById('form-title').textContent = 'Register';
  });
  
  document.getElementById('switch-to-login').addEventListener('click', () => {
    document.getElementById('register-form').style.display = 'none';
    document.getElementById('login-form').style.display = 'block';
    document.getElementById('form-title').textContent = 'Login';
  });
  
  document.getElementById('register-button').addEventListener('click', () => {
    const name = document.getElementById('register-name').value;
    const email = document.getElementById('register-email').value;
    const password = document.getElementById('register-password').value;
  
    if (name && email && password) {
      localStorage.setItem('user', JSON.stringify({ name, email, password }));
      alert('Registration successful! Please login.');
      document.getElementById('switch-to-login').click();
    } else {
      alert('Please fill out all fields.');
    }
  });
  
  document.getElementById('login-button').addEventListener('click', () => {
    const email = document.getElementById('login-email').value;
    const password = document.getElementById('login-password').value;
    const user = JSON.parse(localStorage.getItem('user'));
  
    if (user && user.email === email && user.password === password) {
      window.location.href = 'dashboard.html';
    } else {
      alert('Invalid credentials. Please try again.');
    }
  });
  