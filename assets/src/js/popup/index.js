import React from 'react';
import { createRoot } from 'react-dom/client';
import App from './customizer/app';

// Add Tailwind CSS to head
// const tailwindLink = document.createElement('link');
// tailwindLink.rel = 'stylesheet';
// tailwindLink.href = 'https://cdn.jsdelivr.net/npm/tailwindcss@2.2.19/dist/tailwind.min.css';
// document.head.appendChild(tailwindLink);



// Create root element once during initialization
const root = document.createElement('div');
document.body.appendChild(root);
const reactRoot = createRoot(root);

window.__ = (str, def = '') => {
    return fwpSiteConfig.i18n[str] || def;
}

reactRoot.render(
    <React.StrictMode>
        <App />
    </React.StrictMode>
);

document.querySelectorAll('#sidebar_offcanvas .minicart-aside .widget_shopping_cart_title b:not([data-handled])').forEach(cartTitle => {
    cartTitle.dataset.handled = true;
    cartTitle.parentElement.style.display = 'flex';
    cartTitle.parentElement.style.flexWrap = 'nowrap';
    cartTitle.parentElement.style.alignItems = 'center';
    cartTitle.parentElement.style.justifyContent = 'space-between';
    // 
    const clearButton = document.createElement('button');
    clearButton.style.color = '#333';
    clearButton.style.border = 'none';
    clearButton.style.padding = '3px 7px';
    clearButton.style.backgroundColor = 'none';
    clearButton.style.textDecoration = 'underline';
    clearButton.innerHTML = 'Clear';
    clearButton.addEventListener('click', (event) => {
        clearButton.innerText = "Cleaning...";
        event.preventDefault();event.stopPropagation();
        fetch(`${fwpSiteConfig.ajaxUrl}?action=teddybear/project/ajax/empty/cart&_nonce=${fwpSiteConfig.ajax_nonce}`).then(res => res.json()).then(res => clearButton.innerText = "Cleared").then(res => location.reload()).catch(err => console.error(err));
    });
    cartTitle.parentElement.appendChild(clearButton);
});