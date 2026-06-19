function openEditModal(id, name, email, role, matric, cgpa, major, company_id, contact_number) {
    document.getElementById('edit_user_id').value = id;
    document.getElementById('edit_name').value = name;
    document.getElementById('edit_email').value = email;
    document.getElementById('edit_role').value = role;
    document.getElementById('edit_contact_number').value = contact_number;
    
    document.getElementById('dynamic_student_fields').style.display = 'none';
    document.getElementById('dynamic_supervisor_fields').style.display = 'none';

    if (role === 'Student') {
        document.getElementById('dynamic_student_fields').style.display = 'block';
        document.getElementById('edit_matric').value = matric;
        document.getElementById('edit_cgpa').value = cgpa;
        document.getElementById('edit_major').value = major;
    } else if (role === 'Supervisor') {
        document.getElementById('dynamic_supervisor_fields').style.display = 'block';
        document.getElementById('edit_company_id').value = company_id;
    }

    document.getElementById('editUserModal').style.display = 'flex';
}

window.onclick = function(event) {
    if (event.target == document.getElementById('addStudentModal')) document.getElementById('addStudentModal').style.display = 'none';
    if (event.target == document.getElementById('addSupervisorModal')) document.getElementById('addSupervisorModal').style.display = 'none';
    if (event.target == document.getElementById('editUserModal')) document.getElementById('editUserModal').style.display = 'none';
}