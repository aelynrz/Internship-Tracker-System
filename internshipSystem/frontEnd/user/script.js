function switchTab(tabName) {
  // Get all form elements and tab buttons
  const loginForm = document.getElementById('loginFormContainer');
  const signupForm = document.getElementById('signupFormContainer');
  const buttons = document.querySelectorAll('.tab-btn');

  // Reset active states for buttons
  buttons.forEach(btn => btn.classList.remove('active'));

  // Toggle forms and set active tab highlight
  if (tabName === 'login') {
      loginForm.classList.add('active');
      signupForm.classList.remove('active');
      buttons[0].classList.add('active');
  } else if (tabName === 'signup') {
      signupForm.classList.add('active');
      loginForm.classList.remove('active');
      buttons[1].classList.add('active');
  }
}
