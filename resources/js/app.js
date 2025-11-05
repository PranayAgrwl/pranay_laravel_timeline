import 'bootstrap';

// You can add the initialization code here:
document.addEventListener('DOMContentLoaded', function () {
    // This function will run when the whole page (including your Livewire table) is ready.
    var tooltipTriggerList = [].slice.call(document.querySelectorAll('[data-bs-toggle="tooltip"]'));
    var tooltipList = tooltipTriggerList.map(function (tooltipTriggerEl) {
        return new bootstrap.Tooltip(tooltipTriggerEl);
    });
});