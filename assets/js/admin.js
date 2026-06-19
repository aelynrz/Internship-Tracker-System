const addModal = document.getElementById('addCompanyModal');
const editModal = document.getElementById('editCompanyModal');

function openModal() {
    addModal.style.display = 'flex';
}

function closeModal() {
    addModal.style.display = 'none';
}

function openEditModal(id, name, industry, sup_id) {
    document.getElementById('edit_company_id').value = id;
    document.getElementById('edit_company_name').value = name;
    document.getElementById('edit_industry').value = industry;
    document.getElementById('edit_supervisor_id').value = sup_id;
    editModal.style.display = 'flex';
}

function closeEditModal() {
    editModal.style.display = 'none';
}

window.onclick = function(event) {
    if (event.target === addModal) closeModal();
    if (event.target === editModal) closeEditModal();
};