const $=s=>document.querySelector(s),E=s=>String(s??'').replace(/[&<>"']/g,c=>'&#'+c.charCodeAt(0)+';');
(function(){const c=document.createElement('div');c.className='bgfx';const cl=['#6d5dfc','#00b8e6','#ff4fd8','#ffb84d','#22c55e'];for(let i=0;i<18;i++){const e=document.createElement('i'),s=22+Math.random()*80;e.className='sh s'+(i%4);e.style.cssText='width:'+s+'px;height:'+s+'px;left:'+Math.random()*100+'%;--c:'+cl[i%5]+';animation-duration:'+(16+Math.random()*20)+'s;animation-delay:-'+Math.random()*25+'s';c.appendChild(e)}document.body.prepend(c)})();
/* Ends the session only when the LAST open tab of this site is closed (reloads and tab switching keep it) */
function tabGuard(url){const id=Math.random().toString(36).slice(2),K='b3tab_',beat=()=>localStorage.setItem(K+id,Date.now());beat();setInterval(beat,5000);
addEventListener('pagehide',()=>{localStorage.removeItem(K+id);const now=Date.now();let other=false;for(let i=0;i<localStorage.length;i++){const k=localStorage.key(i);if(k.startsWith(K)&&now-localStorage.getItem(k)<12000)other=true}if(!other)navigator.sendBeacon(url)})}
document.addEventListener('input',e=>{const t=e.target;if(t.tagName=='TEXTAREA'&&!t.dataset.own){t.style.height='auto';t.style.height=t.scrollHeight+2+'px'}});
const rz=(...a)=>a.forEach(i=>{const e=$('#'+i);if(e)e.style.height=''});
