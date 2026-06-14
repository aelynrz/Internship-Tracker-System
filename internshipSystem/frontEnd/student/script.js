document.getElementById('internshipForm').addEventListener('submit', function(event) {
    event.preventDefault(); // Stop page refresh
  
    // Capture the inputs
    const company = document.getElementById('companyName').value;
    const position = document.getElementById('position').value;
    const duration = document.getElementById('duration').value;
  
    // Log values to verify form works
    console.log("Submitting details:", { company, position, duration });
  
    // Mock successful frontend tracking response
    alert(`Application for ${position} at ${company} submitted successfully!`);
    
    // Visually update the status layout
    const statusBadge = document.getElementById('applicationStatus');
    statusBadge.textContent = "Submitted / Under Review";
    statusBadge.style.backgroundColor = "#3498db"; 
  });
  