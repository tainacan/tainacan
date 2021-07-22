import './style.scss';

export default (element) => {
    let form = (element && element.children && element.children[0] && element.children[0].children && element.children[0].children[0]) ? element.children[0].children[0] : document.getElementById('tainacan-search-bar-block');
    if (form) {

        form.addEventListener('submit', ((e) => {
            e.preventDefault();
            let input = document.getElementById('tainacan-search-bar-block_input');
            if (input) {
                if (input.value)
                    window.location.href = e.target.action + '?s=' + input.value;
                return;
            }
        })); 
    } 
}