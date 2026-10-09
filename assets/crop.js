/* Profile photo cropper: pick, drag, zoom, export 600x600 */
var CB=null;
function cropPick(){const i=document.createElement('input');i.type='file';i.accept='image/*';i.onchange=()=>{const f=i.files[0];if(!f)return;const im=new Image();im.onload=()=>cropOpen(im);im.src=URL.createObjectURL(f)};i.click()}
function cropOpen(im){const S=320,mn=Math.max(S/im.width,S/im.height);let z=1,ox=(S-im.width*mn)/2,oy=(S-im.height*mn)/2,dr=null;
mdl('قص الصورة وتحديد الحجم',`<div class="text-center"><canvas id="cc" width="${S}" height="${S}" style="border-radius:50%;box-shadow:0 0 0 4px #6d5dfc;cursor:grab;touch-action:none;max-width:100%"></canvas><p class="small text-secondary mt-3 mb-1">اسحب الصورة لتحريكها، واستخدم الشريط للتكبير والتصغير</p><input type="range" id="cz" class="form-range" min="1" max="4" step="0.01" value="1"></div>`,'اعتماد الصورة',()=>{const o=document.createElement('canvas');o.width=o.height=600;const k=600/S;o.getContext('2d').drawImage(im,ox*k,oy*k,im.width*mn*z*k,im.height*mn*z*k);o.toBlob(b=>{CB=b;$('#pp').src=URL.createObjectURL(b);$('#pp').style.display='block';MD().hide()},'image/jpeg',.9)});
const cv=$('#cc'),cx=cv.getContext('2d');
const draw=()=>{const w=im.width*mn*z,h=im.height*mn*z;ox=Math.min(0,Math.max(S-w,ox));oy=Math.min(0,Math.max(S-h,oy));cx.clearRect(0,0,S,S);cx.drawImage(im,ox,oy,w,h)};draw();
$('#cz').oninput=e=>{const n=+e.target.value,c=S/2,k=n/z;ox=c-(c-ox)*k;oy=c-(c-oy)*k;z=n;draw()};
cv.onpointerdown=e=>{dr={x:e.clientX,y:e.clientY,ox,oy};cv.setPointerCapture(e.pointerId);cv.style.cursor='grabbing'};
cv.onpointermove=e=>{if(!dr)return;const r=S/cv.getBoundingClientRect().width;ox=dr.ox+(e.clientX-dr.x)*r;oy=dr.oy+(e.clientY-dr.y)*r;draw()};
cv.onpointerup=()=>{dr=null;cv.style.cursor='grab'}}
