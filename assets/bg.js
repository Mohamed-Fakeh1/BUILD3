(()=>{const cv=document.createElement('canvas');cv.id='bgc';document.body.append(cv);const cx=cv.getContext('2d');
let h1=250,h2=190;function rh(hex){const n=parseInt(hex.slice(1),16),r=(n>>16)/255,g=(n>>8&255)/255,b=(n&255)/255,M=Math.max(r,g,b),m=Math.min(r,g,b),d=M-m;if(!d)return 0;let h=M==r?((g-b)/d)%6:M==g?(b-r)/d+2:(r-g)/d+4;return (h*60+360)%360}
function th(){const cs=getComputedStyle(document.documentElement);h1=rh(cs.getPropertyValue('--c1').trim());h2=rh(cs.getPropertyValue('--c2').trim());orbs.forEach((o,i)=>o.h=i%2?h2:h1)}addEventListener('themechange',th);
let W,H,D,pts=[],orbs=[],mx=-9999,my=-9999,sx=-9999,sy=-9999,t=0,GAP=36;
const R=170,reduce=matchMedia('(prefers-reduced-motion:reduce)').matches;
function init(){const dpr=Math.min(devicePixelRatio||1,2);W=innerWidth;H=innerHeight;cv.width=W*dpr;cv.height=H*dpr;cv.style.width=W+'px';cv.style.height=H+'px';cx.setTransform(dpr,0,0,dpr,0,0);
GAP=W<700?30:36;pts=[];const c=Math.ceil(W/GAP)+2,r=Math.ceil(H/GAP)+2;D={c,r};
for(let j=0;j<r;j++)for(let i=0;i<c;i++){const x=(i-.5)*GAP,y=(j-.5)*GAP;pts.push({ox:x,oy:y,x,y,vx:0,vy:0})}
orbs=[0,1,2,3,4].map(i=>({a:Math.random()*6.28,s:.0004+Math.random()*.0006,rx:W*(.2+Math.random()*.3),ry:H*(.15+Math.random()*.3),cx:W*(.2+i*.15),cy:H*(.3+Math.random()*.4),r:180+Math.random()*160,h:i%2?h2:h1}))}
init();th();addEventListener('resize',init);
addEventListener('mousemove',e=>{mx=e.clientX;my=e.clientY});addEventListener('mouseleave',()=>{mx=my=-9999});
addEventListener('touchmove',e=>{const p=e.touches[0];mx=p.clientX;my=p.clientY},{passive:true});addEventListener('touchend',()=>{mx=my=-9999});
function frame(){requestAnimationFrame(frame);if(document.body.classList.contains('nofx')||document.hidden){return}
t++;sx+=(mx-sx)*.18;sy+=(my-sy)*.18;cx.clearRect(0,0,W,H);
cx.globalCompositeOperation='source-over';
for(const o of orbs){o.a+=o.s*16;const x=o.cx+Math.cos(o.a)*o.rx*.5,y=o.cy+Math.sin(o.a*1.3)*o.ry*.5;const g=cx.createRadialGradient(x,y,0,x,y,o.r);g.addColorStop(0,'hsla('+o.h+',95%,72%,.28)');g.addColorStop(1,'hsla('+o.h+',95%,72%,0)');cx.fillStyle=g;cx.fillRect(x-o.r,y-o.r,o.r*2,o.r*2)}
// physics: wave drift + spring + mouse repulsion
for(const p of pts){const wx=Math.sin(t*.012+p.oy*.012)*5+Math.cos(t*.008+p.ox*.01)*3,wy=Math.cos(t*.011+p.ox*.012)*5+Math.sin(t*.009+p.oy*.01)*3;
let fx=(p.ox+(reduce?0:wx)-p.x)*.06,fy=(p.oy+(reduce?0:wy)-p.y)*.06;
const dx=p.x-sx,dy=p.y-sy,d=Math.hypot(dx,dy);if(d<R&&d>.1){const f=(1-d/R);const k=f*f*9;fx+=dx/d*k;fy+=dy/d*k}
p.vx=(p.vx+fx)*.82;p.vy=(p.vy+fy)*.82;p.x+=p.vx;p.y+=p.vy;p.m=Math.min(1,Math.hypot(p.x-p.ox,p.y-p.oy)/46)}
// mesh lines
const c=D.c;cx.lineWidth=1;
for(let j=0;j<D.r;j++)for(let i=0;i<c;i++){const p=pts[j*c+i];
if(i<c-1){const q=pts[j*c+i+1],m=(p.m+q.m)/2;cx.strokeStyle='hsla('+(h1+(h2-h1)*m)+',80%,'+(66-m*8)+'%,'+(.22+m*.6)+')';cx.beginPath();cx.moveTo(p.x,p.y);cx.lineTo(q.x,q.y);cx.stroke()}
if(j<D.r-1){const q=pts[(j+1)*c+i],m=(p.m+q.m)/2;cx.strokeStyle='hsla('+(h1+(h2-h1)*m)+',80%,'+(66-m*8)+'%,'+(.22+m*.6)+')';cx.beginPath();cx.moveTo(p.x,p.y);cx.lineTo(q.x,q.y);cx.stroke()}}
// dots
for(const p of pts){const m=p.m;cx.fillStyle='hsla('+(h1+(h2-h1)*m)+',85%,'+(62-m*6)+'%,'+(.35+m*.65)+')';cx.beginPath();cx.arc(p.x,p.y,1.4+m*2.6,0,6.28);cx.fill()}
}
frame()})();
