document.addEventListener("DOMContentLoaded",() => {
    let form = document.getElementById('taincan-search-bar-block');
    if (form) {
        form.addEventListener('submit', ((e) => {
            e.preventDefault();
            let input = document.getElementById('taincan-search-bar-block_input');
            if (input) {
                if (input.value)
                    window.location.href = e.target.action + '?search=' + input.value;
                return;
            }
        })); 
    } 
}, false);        