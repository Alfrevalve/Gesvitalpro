import 'bootstrap';
import PerfectScrollbar from 'perfect-scrollbar';
import { Menu } from './components/menu';

document.addEventListener('DOMContentLoaded', function() {
    // Initialize menu
    Menu.init();

    // Initialize perfect scrollbar
    const container = document.querySelector(".layout-menu");
    if (container) {
        new PerfectScrollbar(container);
    }
});
