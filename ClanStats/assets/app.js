import './styles/app.css';
import React from 'react';
let createRoot;
try { createRoot = require('react-dom/client').createRoot; }
catch(e){
  console.warn('react-dom/client non disponible, fallback vers react-dom');
  const ReactDOM = require('react-dom');
  createRoot = (container) => ({ render: (el) => ReactDOM.render(el, container) });
}
function ClanStatsApp(){
  const [message] = React.useState('🎮 ClanStats React 18+ OPTIMISÉ! 🚀');
  const [counter,setCounter] = React.useState(0);
  const [lastUpdate,setLastUpdate] = React.useState(new Date().toLocaleTimeString());
  React.useEffect(()=>{ console.log('🔄 mounted at', new Date().toLocaleTimeString()); },[]);
  return (
    <div className="clanstats-app">
      <h1>{message}</h1>
      <div className="hot-reload-demo">
        <button onClick={()=>setCounter(c=>c+1)} className="btn btn-primary">Compteur: {counter}</button>
        <button onClick={()=>setLastUpdate(new Date().toLocaleTimeString())} className="btn btn-secondary">🕒 Update: {lastUpdate}</button>
      </div>
      <div className="hot-reload-info">
        <p>React {React.version}</p>
        <small>ws://127.0.0.1:8081/ws</small>
      </div>
    </div>
  );
}
let root=null;
function render(){
  const mount = document.getElementById('react-app');
  if(!mount) return;
  if(!root){ root = createRoot(mount); console.log('🎯 createRoot initialized'); }
  root.render(<ClanStatsApp/>);
  console.log('🎯 rendered at', new Date().toLocaleTimeString());
}
render();
if(module.hot){ console.log('🔥 HMR available'); module.hot.accept(err=>{ if(err) console.error('HMR error',err); else render(); }); module.hot.accept('./styles/app.css',()=>console.log('🎨 CSS reloaded')); }
