const filterToggle = document.querySelector('.filter-toggle');
const filterContainer = document.querySelector('.filter-container');

filterToggle.addEventListener('click', function() {
    filterContainer.classList.toggle('active');
});

function toggleFilters() {
    var filterBar = document.getElementById("filter-bar");
    if (filterBar.style.display === "none") {
        filterBar.style.display = "block";
    } else {
        filterBar.style.display = "none";
    }
}