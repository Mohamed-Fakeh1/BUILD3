(()=>{const PRE=[['البنفسجي','#6d5dfc','#00b8e6'],['الأخضر','#10b981','#06b6d4'],['الوردي','#ec4899','#f59e0b'],['البرتقالي','#f97316','#ef4444'],['الأزرق','#2563eb','#06b6d4'],['الذهبي','#d97706','#eab308'],['الأحمر','#e11d48','#7c3aed'],['الليلي','#334155','#0ea5e9']];
const rgb=h=>{const n=parseInt(h.slice(1),16);return (n>>16)+','+(n>>8&255)+','+(n&255)};
const cur=(c)=>"url(\"data:image/svg+xml,"+encodeURIComponent("<svg xmlns='http://www.w3.org/2000/svg' width='28' height='28' viewBox='0 0 28 28'><path d='M4 2l18 9-8 2.5L10.5 22z' fill='"+c+"' stroke='#fff' stroke-width='1.6' stroke-linejoin='round'/></svg>")+"\") 4 2,";
const st=document.createElement('style');document.head.appendChild(st);
function apply(a,b,save){const r=document.documentElement.style;r.setProperty('--c1',a);r.setProperty('--c2',b);r.setProperty('--c1r',rgb(a));r.setProperty('--c2r',rgb(b));
const m=document.querySelector('meta[name=theme-color]');m&&m.setAttribute('content',a);
st.textContent='html,body{cursor:'+cur(a)+'auto}a,button,[role=button],.gl.proj,.stars span,.pw span,select,label[for]{cursor:'+cur(b)+'pointer}';
dispatchEvent(new Event('themechange'));if(save)try{localStorage.setItem('th',JSON.stringify([a,b]))}catch(e){}
const i1=document.getElementById('thc1'),i2=document.getElementById('thc2');if(i1){i1.value=a;i2.value=b}}
let s=null;try{s=JSON.parse(localStorage.getItem('th'))}catch(e){}
apply(...(s||[PRE[0][1],PRE[0][2]]),false);
const css=document.createElement('style');css.textContent="#thb{position:relative}#thp{position:fixed;top:78px;inset-inline-end:16px;z-index:2000;width:270px;background:rgba(255,255,255,.97);backdrop-filter:blur(16px);border:1px solid rgba(var(--c1r),.2);border-radius:20px;box-shadow:0 20px 60px rgba(40,30,120,.25);padding:16px;display:none}#thp.on{display:block;animation:cwpop .25s}#thp h6{font-weight:800;margin:0 0 10px}.thg{display:grid;grid-template-columns:repeat(4,1fr);gap:10px}.thg button{height:42px;border-radius:50%;border:3px solid #fff;box-shadow:0 0 0 1px #d5d9ee;cursor:pointer;transition:.2s}.thg button:hover{transform:scale(1.12)}.thc{display:flex;gap:10px;margin-top:14px;align-items:center;font-size:13px}.thc input{width:44px;height:34px;border:0;padding:0;background:none;cursor:pointer}.thc span{flex:1}#thr{width:100%;margin-top:12px;border:1px solid #d5d9ee;background:#fff;border-radius:12px;padding:6px;font-size:13px}";document.head.appendChild(css);
const pan=document.createElement('div');pan.id='thp';pan.innerHTML='<h6><i class="bi bi-palette"></i> ألوان الموقع</h6><div class="thg">'+PRE.map((p,i)=>'<button title="'+p[0]+'" data-i="'+i+'" style="background:linear-gradient(135deg,'+p[1]+','+p[2]+')"></button>').join('')+'</div><div class="thc"><span>اللون الأساسي</span><input type="color" id="thc1"></div><div class="thc" style="margin-top:6px"><span>اللون الثاني</span><input type="color" id="thc2"></div><button id="thr">رجوع للألوان الأصلية</button>';document.body.appendChild(pan);
const bt=document.getElementById('thf');
const v=()=>[document.getElementById('thc1').value,document.getElementById('thc2').value];
bt.onclick=e=>{e.stopPropagation();pan.classList.toggle('on')};document.addEventListener('click',e=>{if(!pan.contains(e.target))pan.classList.remove('on')});
pan.onclick=e=>{const b=e.target.closest('.thg button');if(b){const p=PRE[b.dataset.i];apply(p[1],p[2],true)}if(e.target.id=='thr')apply(PRE[0][1],PRE[0][2],true)};
pan.oninput=e=>{if(e.target.type=='color')apply(...v(),true)};
const c=document.getElementById('thc1');c.value=getComputedStyle(document.documentElement).getPropertyValue('--c1').trim();document.getElementById('thc2').value=getComputedStyle(document.documentElement).getPropertyValue('--c2').trim();
})();
