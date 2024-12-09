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

reactRoot.render(
    <React.StrictMode>
        <App />
    </React.StrictMode>
);