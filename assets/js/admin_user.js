// Wait for the HTML to fully load before attaching events
document.addEventListener('DOMContentLoaded', function() {
    
    // 1. Logic for the Edit Buttons (Using Data Attributes)
    const editButtons = document.querySelectorAll('.edit-user-btn');

    editButtons.forEach(button => {
        button.addEventListener('click', function() {
            
            // Read the data securely from the button's data-* attributes
            const userId = this.getAttribute('data-userid');
            const name = this.getAttribute('data-name');
            const email = this.getAttribute('data-email');
            const role = this.getAttribute('data-role');
            const matric = this.getAttribute('data-matric');
            const cgpa = this.getAttribute('data-cgpa');
            const major = this.getAttribute('data-major');
            const companyId = this.getAttribute('data-companyid');
            const contact = this.getAttribute('data-contact');

            // Populate the standard modal form inputs
            document.getElementById('edit_user_id').value = userId;
            document.getElementById('edit_name').value = name;
            document.getElementById('edit_email').value = email;
            document.getElementById('edit_role').value = role;
            document.getElementById('edit_contact_number').value = contact;

            // Hide/Show dynamic fields based on the user's role
            const studentFields = document.getElementById('dynamic_student_fields');
            const supervisorFields = document.getElementById('dynamic_supervisor_fields');

            // Reset both to hidden first
            studentFields.style.display = 'none';
            supervisorFields.style.display = 'none';

            if (role === 'Student') {
                studentFields.style.display = 'block';
                document.getElementById('edit_matric').value = matric;
                document.getElementById('edit_cgpa').value = cgpa;
                document.getElementById('edit_major').value = major;
            } else if (role === 'Supervisor') {
                supervisorFields.style.display = 'block';
                document.getElementById('edit_company_id').value = companyId;
            }

            // Display the modal on the screen
            document.getElementById('editUserModal').style.display = 'flex';
        });
    });
});

// 2. Logic to close modals when clicking on the dark overlay background
window.onclick = function(event) {
    const addStudentModal = document.getElementById('addStudentModal');
    const addSupervisorModal = document.getElementById('addSupervisorModal');
    const editUserModal = document.getElementById('editUserModal');

    if (event.target == addStudentModal) {
        addStudentModal.style.display = 'none';
    }
    if (event.target == addSupervisorModal) {
        addSupervisorModal.style.display = 'none';
    }
    if (event.target == editUserModal) {
        editUserModal.style.display = 'none';
    }
};