import './styles/app.css';
import React from 'react';
import ReactDOM from 'react-dom/client';

// Exemple composant React simple
function Welcome() {
    return <h1>🎉 React fonctionne avec ClanStats!</h1>;
}

// Montage conditionnel si élément existe
const reactMount = document.getElementById('react-app');
if (reactMount) {
    const root = ReactDOM.createRoot(reactMount);
    root.render(<Welcome />);
}

console.log('✅ React ready for ClanStats!');
