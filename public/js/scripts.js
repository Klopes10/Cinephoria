function toggleDropdown() {
    const menu = document.getElementById("dropdownMenu");
    menu.style.display = menu.style.display === "block" ? "none" : "block";
}

document.addEventListener('click', function (e) {
    const dropdown = document.getElementById("userDropdown");
    const menu = document.getElementById("dropdownMenu");

    if (!dropdown.contains(e.target)) {
        menu.style.display = "none";
    }
});


