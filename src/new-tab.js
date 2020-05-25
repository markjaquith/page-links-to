import 'core-js/features/symbol';
import 'core-js/features/symbol/iterator';
import elementClosest from 'element-closest';
import { handleClicks, handleLoad } from './lib/newTab';

// Add Element.closest() polyfill for Internet Explorer.
elementClosest(window);

// Listen to clicks for links that should be opened in a new tab.
handleClicks();

// Manipulate new-tab links that exist on load.
handleLoad();
