document.addEventListener("DOMContentLoaded",() => {
    let form = document.getElementById('tainacan-search-bar-block');
    if (form) {
        form.addEventListener('submit', ((e) => {
            e.preventDefault();
            let input = document.getElementById('tainacan-search-bar-block_input');
            if (input) {
                if (input.value)
                    window.location.href = e.target.action + '?search=' + input.value;
                return;
            }
        })); 
    } 
}, false);        