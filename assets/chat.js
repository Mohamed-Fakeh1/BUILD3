/* Client chat widget: floating round button + Telegram-style window */
let CWO=false,CWS='',CWU=0,CWT=null,CWA=null,CTX=null;const BT=document.title;
document.addEventListener('pointerdown',()=>{try{CTX=CTX||new(window.AudioContext||window.webkitAudioContext)();if(CTX.state=='suspended')CTX.resume()}catch(e){}});
function cwPing(){if(!CTX)return;try{[880,1175].forEach((f,i)=>{const o=CTX.createOscillator(),g=CTX.createGain(),t=CTX.currentTime+i*.13;o.type='sine';o.frequency.value=f;g.gain.setValueAtTime(0,t);g.gain.linearRampToValueAtTime(.12,t+.02);g.gain.exponentialRampToValueAtTime(.001,t+.3);o.connect(g);g.connect(CTX.destination);o.start(t);o.stop(t+.32)})}catch(e){}}
function cwNotify(){if(window.Notification&&Notification.permission=='granted'&&document.hidden)try{new Notification(T[L].cwt,{body:T[L].newmsg})}catch(e){}}
function cwTitle(){const n=CWU+MU;document.title=(n?'('+n+') ':'')+BT}
(function(){const w=document.createElement('div');w.id='cw';w.innerHTML=`<button id="cfab" aria-label="Chat"><i class="bi bi-chat-dots-fill"></i><b id="cbd" hidden></b></button>
<div id="cwin" hidden><div class="cw-h"><div class="cw-av"><i class="bi bi-headset"></i></div><div class="cw-t"><b id="cwt"></b><small id="cwst"></small></div><button id="cwx" aria-label="Close"><i class="bi bi-x-lg"></i></button></div>
<div id="cwm" class="cw-m"></div><div id="cwe" class="cw-e"></div><div id="cwf" class="cw-fc" hidden></div>
<div class="cw-c"><label class="cw-a" title="Attach"><i class="bi bi-paperclip"></i><input id="cwfi" type="file" hidden></label><textarea id="cwi" rows="1" maxlength="1000" data-own="1"></textarea><button id="cws" aria-label="Send"><i class="bi bi-send-fill"></i></button></div></div>`;document.body.appendChild(w);
$('#cfab').onclick=()=>CWO?cwClose():cwOpen();$('#cwx').onclick=cwClose;$('#cws').onclick=cwSend;
$('#cwi').addEventListener('keydown',e=>{if(e.key=='Enter'&&!e.shiftKey){e.preventDefault();cwSend()}});$('#cwi').addEventListener('input',cwGrow);
$('#cwfi').addEventListener('change',cwFile);setInterval(()=>{if(CWO&&!document.hidden)cwLoad()},3000);setInterval(()=>{if(!CWO&&me)cwUnread()},4000)})();
function cwLang(){$('#cwt').textContent=T[L].cwt;$('#cwi').placeholder=T[L].cwph;cwStatus();if(CWO)cwLoad(true)}
function cwStatus(){$('#cwst').textContent=CWT>0?'● '+T[L].cwon:T[L].cwaw;$('#cwst').className=CWT>0?'on':''}
function cwBoot(){cwLang();cwUnread()}
function chatNav(){const b=$('#ddb');if(b){b.hidden=!MU;b.textContent=MU||''}}
function cwBadge(){const b=$('#cbd');b.hidden=!CWU;b.textContent=CWU>9?'9+':CWU;chatNav();cwTitle()}
function cwOpen(){if(window.Notification&&Notification.permission=='default')Notification.requestPermission();if(!me){$('#an').textContent=T[L].chl;tg('login');return op('ma')}CWO=true;$('#cwin').hidden=false;$('#cfab').classList.add('open');cwLoad(true);setTimeout(()=>$('#cwi').focus(),150)}
function cwClose(){CWO=false;$('#cwin').hidden=true;$('#cfab').classList.remove('open')}
function cwGrow(){const i=$('#cwi');i.style.height='auto';const h=i.scrollHeight;i.style.height=Math.min(h,230)+'px';i.style.overflowY=h>230?'auto':'hidden'}
function cwFile(){const f=$('#cwfi').files[0],c=$('#cwf');c.hidden=!f;c.innerHTML=f?`<i class="bi bi-paperclip"></i> <span>${E(f.name)}</span> <button type="button" onclick="$('#cwfi').value='';cwFile()"><i class="bi bi-x"></i></button>`:''}
function cwErr(m){const e=$('#cwe');e.textContent=m;if(m)setTimeout(()=>{e.textContent=''},3500)}
const cwTm=d=>d.slice(11,16),cwDay=d=>new Date(d.replace(' ','T')).toLocaleDateString(L=='ar'?'ar-EG':'en-US',{day:'numeric',month:'long'});
async function cwLoad(force){if(!me)return;const r=await api('chat_msgs');if(!r.msgs)return;CWT=r.team;const ms=r.msgs,sig=ms.length+':'+(ms.length?ms[ms.length-1].id:0)+':'+ms.filter(m=>m.seen==1).length+':'+(CWT>0);cwStatus();if(sig==CWS&&!force)return;const box=$('#cwm'),near=box.scrollHeight-box.scrollTop-box.clientHeight<90;const grew=sig!=CWS;CWS=sig;let day='',h=`<div class="cw-b in wl">${T[L].cwwl}</div>`;
for(const m of ms){const d=m.created_at.slice(0,10);if(d!=day){day=d;h+=`<div class="cw-d"><span>${cwDay(m.created_at)}</span></div>`}const out=m.sender=='client',img=m.file&&/\.(jpe?g|png|gif|webp)$/i.test(m.file);
h+=`<div class="cw-b ${out?'out':'in'}">${!out&&m.an?`<div class="cw-n">${E(m.an)}</div>`:''}${m.file?(img?`<a href="${E(m.file)}" target="_blank"><img class="cw-img" src="${E(m.file)}"></a>`:`<a class="cw-ch" href="${E(m.file)}" target="_blank"><i class="bi bi-file-earmark"></i> ${T[L].fl}</a>`):''}${m.body?`<div class="cw-x">${E(m.body)}</div>`:''}<span class="cw-tm">${cwTm(m.created_at)}${out?`<i class="bi bi-check2${m.seen==1?'-all sn':''}"></i>`:''}</span></div>`}
box.innerHTML=h;if(force||near||grew)box.scrollTop=box.scrollHeight;const na=ms.filter(m=>m.sender=='admin').length;if(CWA!==null&&na>CWA){cwPing();cwNotify()}CWA=na;CWU=0;cwBadge()}
async function cwSend(){const b=$('#cwi').value.trim(),f=$('#cwfi').files[0];if(!b&&!f)return;const fd=new FormData();fd.append('body',b);if(f)fd.append('file',f);$('#cwi').value='';$('#cwfi').value='';cwFile();cwGrow();const r=await api('chat_send',fd);if(r.error)return cwErr(T[L]['e_'+r.error]||T[L].err);cwLoad(true)}
async function cwUnread(){if(!me){CWU=0;cwBadge();return}if(CWO)return;const r=await api('chat_unread');if(r.n===undefined)return;if(r.n>CWU){cwPing();cwNotify();const f=$('#cfab');f.classList.remove('ring');void f.offsetWidth;f.classList.add('ring')}CWU=r.n;cwBadge()}
