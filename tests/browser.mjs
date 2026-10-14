// Edge/CDP sin dependencias. Solo navega y envía formularios inválidos; no modifica datos existentes.
import {spawn} from 'node:child_process';
import {mkdir,writeFile} from 'node:fs/promises';
import path from 'node:path';
const root=process.cwd(), base=process.argv[2]||'http://127.0.0.1:8082';
const evidence=path.join(root,'docs/evidencias/revision-2');
const child=spawn('C:/Program Files (x86)/Microsoft/Edge/Application/msedge.exe',['--headless=new','--disable-gpu','--no-sandbox','--no-first-run','--remote-debugging-port=9223','--remote-debugging-address=127.0.0.1','--user-data-dir='+path.join(root,'storage/edge-profile-revision-2') ,'about:blank'],{windowsHide:true,stdio:'ignore'});
const keepAlive=setInterval(()=>{},1000);let ws,sequence=0;
const pending=new Map(),results=[],layouts=[],contrasts=[];
function check(name,ok,detail=''){results.push((ok?'PASS ':'FAIL ')+name+(detail?' | '+detail:''));if(!ok)throw Error(name+' '+detail);}
function send(method,params={}){const id=++sequence;return new Promise((resolve,reject)=>{const timer=setTimeout(()=>{pending.delete(id);reject(Error('CDP timeout '+method));},15000);pending.set(id,{resolve:v=>{clearTimeout(timer);resolve(v);},reject:e=>{clearTimeout(timer);reject(e);}});ws.send(JSON.stringify({id,method,params}));});}
async function evaluate(expression){const r=await send('Runtime.evaluate',{expression,returnByValue:true,awaitPromise:true});if(r.exceptionDetails)throw Error(JSON.stringify(r.exceptionDetails));return r.result.value;}
async function navigate(route){
    const expected=new URL(base+route).href;
    await send('Page.navigate',{url:expected});
    for(let i=0;i<80;i++){await new Promise(r=>setTimeout(r,60));if(await evaluate('location.href==='+JSON.stringify(expected)+' && document.readyState==="complete" && !!document.querySelector("main")'))return;}
    throw Error('Carga incompleta '+route);
}
async function key(key,code,keyCode,modifiers=0){await send('Input.dispatchKeyEvent',{type:'keyDown',key,code,windowsVirtualKeyCode:keyCode,modifiers});await send('Input.dispatchKeyEvent',{type:'keyUp',key,code,windowsVirtualKeyCode:keyCode,modifiers});}
async function dimensions(width,scale=1){await send('Emulation.setDeviceMetricsOverride',{width,height:900,deviceScaleFactor:scale,mobile:false});}
async function screenshot(name){await evaluate('scrollTo(0,0)');const r=await send('Page.captureScreenshot',{format:'png',captureBeyondViewport:true});await writeFile(path.join(evidence,name+'.png'),Buffer.from(r.data,'base64'));}
async function layout(label){
    const details=await evaluate('('+function(){
        const controls=[...document.querySelectorAll('main input:not([type=hidden]),main textarea,main select,button,a.button,header nav > a,.card h2 a,.error-summary a,.pagination a')];
        const badBounds=controls.filter(el=>{const r=el.getBoundingClientRect();return r.width>0 && (r.left < -1 || r.right>innerWidth+1 || r.height<43);}).map(el=>el.id||el.textContent.trim());
        const visible=controls.filter(el=>el.getBoundingClientRect().width>0);
        const overlap=[];
        for(let i=0;i<visible.length;i++)for(let j=i+1;j<visible.length;j++){const a=visible[i].getBoundingClientRect(),b=visible[j].getBoundingClientRect();if(Math.min(a.right,b.right)-Math.max(a.left,b.left)>1&&Math.min(a.bottom,b.bottom)-Math.max(a.top,b.top)>1)overlap.push([visible[i].id||visible[i].textContent.trim(),visible[j].id||visible[j].textContent.trim()]);}
        const labels=[...document.querySelectorAll('input:not([type=hidden]),textarea,select')].every(el=>el.labels?.length>0);
        const descriptions=[...document.querySelectorAll('[aria-describedby]')].every(el=>el.getAttribute('aria-describedby').split(/\s+/).every(id=>!!document.getElementById(id)));
        return {width:innerWidth,overflow:document.documentElement.scrollWidth>innerWidth,badBounds,overlap,labels,descriptions,h1:document.querySelectorAll('main h1').length};
    }+')()');
    layouts.push({label,...details});
    check('Diseño '+label,!details.overflow&&!details.badBounds.length&&!details.overlap.length,JSON.stringify({width:details.width,overflow:details.overflow,badBounds:details.badBounds,overlap:details.overlap}));
    check('Etiquetas y semántica '+label,details.labels&&details.descriptions&&details.h1===1);
}
async function keyboard(label){
    const count=await evaluate('('+function(){
        const items=[...document.querySelectorAll('a[href],button,input:not([type=hidden]),textarea,select,[tabindex="0"]')].filter(el=>!el.disabled&&el.getClientRects().length>0);
        items.forEach((el,i)=>el.dataset.qaTabKey='tab-'+i);
        document.body.tabIndex=-1;document.body.focus();document.body.removeAttribute('tabindex');
        return items.length;
    }+')()');
    const visited=[];let ordered=true,visible=true;
    for(let i=0;i<count;i++){
        await key('Tab','Tab',9);
        const item=await evaluate('({key:document.activeElement.dataset.qaTabKey,outline:getComputedStyle(document.activeElement).outlineStyle,width:parseFloat(getComputedStyle(document.activeElement).outlineWidth)})');
        visited.push(item.key);ordered&&=item.key==='tab-'+i;visible&&=item.outline!=='none'&&item.width>=2;
    }
    check('Teclado '+label,ordered&&visible,'Controles alcanzados: '+count);
    if(count>1){await key('Tab','Tab',9,8);check('Retroceso teclado '+label,await evaluate('document.activeElement.dataset.qaTabKey')==='tab-'+(count-2));}
}
async function submitInvalid(kind){
    if(kind==='news')await evaluate('document.querySelector("#title").value="     ";document.querySelector("#summary").value="breve";document.querySelector("#content").value="breve";document.querySelector("#category_id").value="1";document.querySelector("main form").submit();');
    else await evaluate('document.querySelector("#name").value="     ";document.querySelector("main form").submit();');
    for(let i=0;i<80;i++){await new Promise(r=>setTimeout(r,60));if(await evaluate('!!document.querySelector("[data-error-summary]")'))return;}
    throw Error('Sin respuesta de validación');
}
try{
    await mkdir(evidence,{recursive:true});let target;
    for(let i=0;i<80;i++){try{target=(await(await fetch('http://127.0.0.1:9223/json')).json()).find(t=>t.type==='page');if(target)break;}catch{}await new Promise(r=>setTimeout(r,100));}
    if(!target)throw Error('Edge no disponible');
    ws=new WebSocket(target.webSocketDebuggerUrl);await new Promise((r,j)=>{ws.addEventListener('open',r,{once:true});ws.addEventListener('error',j,{once:true});});
    ws.addEventListener('message',e=>{const d=JSON.parse(e.data);if(pending.has(d.id)){const p=pending.get(d.id);pending.delete(d.id);d.error?p.reject(Error(JSON.stringify(d.error))):p.resolve(d.result);}});
    await send('Page.enable');
    const publicRoutes=[['inicio','/index.php'],['detalle','/index.php?r=detail&id=1'],['login','/index.php?r=login']];
    for(const width of [360,390,768,1440])for(const [name,route] of publicRoutes){
        await dimensions(width);await navigate(route);await layout(width+'px '+name);
        if([390,1440].includes(width))await keyboard(width+'px '+name);
        if((width===360&&name==='detalle')||(width===390&&name==='login')||(width===1440&&name==='inicio'))await screenshot(name+'-'+width);
    }
    await dimensions(390);await navigate('/index.php');
    await key('Tab','Tab',9);check('Salto al contenido recibe foco',await evaluate('document.activeElement.classList.contains("skip")'));
    await key('Enter','Enter',13);check('Salto activa main',await evaluate('document.activeElement.id==="contenido"'));
    await navigate('/index.php?r=login');
    await evaluate('document.querySelector("#email").value="admin@actualidad.test";document.querySelector("#password").value="DemoHuancayo2026!";document.querySelector("main form").requestSubmit();');
    for(let i=0;i<80;i++){await new Promise(r=>setTimeout(r,60));if(await evaluate('location.search.includes("dashboard") && document.querySelector("main h1")?.textContent.includes("Resumen")'))break;}
    check('Login navegador',await evaluate('document.querySelector("main h1")?.textContent.includes("Resumen")'));
    const adminRoutes=[['panel','/index.php?r=dashboard'],['noticias','/index.php?r=news'],['categorias','/index.php?r=categories'],['crear-noticia','/index.php?r=news-edit'],['editar-noticia','/index.php?r=news-edit&id=1'],['crear-categoria','/index.php?r=category-edit'],['editar-categoria','/index.php?r=category-edit&id=1'],['eliminar-noticia','/index.php?r=news-delete&id=1'],['eliminar-categoria','/index.php?r=category-delete&id=1']];
    for(const width of [360,390,768,1440])for(const [name,route] of adminRoutes){
        await dimensions(width);await navigate(route);await layout(width+'px '+name);
        if([390,1440].includes(width))await keyboard(width+'px '+name);
        if((width===360&&name==='crear-noticia')||(width===390&&['categorias','eliminar-noticia','eliminar-categoria'].includes(name))||(width===768&&name==='panel'))await screenshot(name+'-'+width);
        if(name==='eliminar-noticia'){check('Cancelar noticia '+width,await evaluate('!![...document.querySelectorAll("main a")].find(a=>a.textContent.includes("Cancelar")&&a.href.includes("r=news"))'));await evaluate('[...document.querySelectorAll("main a")].find(a=>a.textContent.includes("Cancelar")).focus()');await key('Enter','Enter',13);await new Promise(r=>setTimeout(r,150));check('Regreso al listado '+width,await evaluate('location.search.includes("r=news")&&!location.search.includes("delete")'));}
        if(name==='eliminar-categoria')check('Categoría asociada bloqueada '+width,await evaluate('document.querySelector("main").textContent.includes("Reasigne")&&!document.querySelector("main .danger")'));
    }
    for(const width of [360,390,768,1440])for(const kind of ['news','category']){
        await dimensions(width);await navigate('/index.php?r='+(kind==='news'?'news-edit':'category-edit'));await submitInvalid(kind);await layout(width+'px errores '+kind);
        check('Resumen de errores enfocado '+width+' '+kind,await evaluate('document.activeElement.hasAttribute("data-error-summary")'));
        check('Errores con texto y enlaces '+width+' '+kind,await evaluate('[...document.querySelectorAll("[aria-invalid=true]")].every(el=>{const error=document.getElementById(el.id+"-error");return error?.textContent.includes("Error:")&&!!document.querySelector(".error-summary a[href=\\"#"+el.id+"\\"]");})'));
        await evaluate('document.querySelector(".error-summary a").focus()');await key('Enter','Enter',13);await new Promise(r=>setTimeout(r,50));
        check('Enlace de error enfoca campo '+width+' '+kind,await evaluate('document.activeElement.getAttribute("aria-invalid")==="true"'));
        if(width===360)await screenshot('errores-'+kind+'-'+width);
    }
    // Escala de texto real del DOM a 200%, y layout equivalente al zoom de página a 200%.
    // No se presenta este método como pulsación de Ctrl+ ni como prueba en un teléfono físico.
    await dimensions(1440);await navigate('/index.php?r=news-edit');
    const fontBefore=await evaluate('parseFloat(getComputedStyle(document.querySelector("#title")).fontSize)');
    await evaluate('document.documentElement.style.fontSize="200%"');
    check('Texto y controles ampliados al 200%',await evaluate('parseFloat(getComputedStyle(document.querySelector("#title")).fontSize)')===2*fontBefore);
    await layout('texto200 formulario noticia');await screenshot('texto-200-formulario');
    await evaluate('document.documentElement.style.fontSize=""');
    for(const [name,route] of [...publicRoutes.filter(r=>r[0]!=='login'),...adminRoutes]){
        await dimensions(720,2);await navigate(route);await layout('zoom200 equivalente '+name);
        if(name==='crear-noticia')await screenshot('zoom-200-formulario');
    }
    await dimensions(390);await navigate('/index.php?r=news-edit');await submitInvalid('news');
    const samples=await evaluate('('+function(){
        function luminance(rgb){const c=rgb.slice(0,3).map(v=>{v/=255;return v<=.04045?v/12.92:((v+.055)/1.055)**2.4;});return .2126*c[0]+.7152*c[1]+.0722*c[2];}
        const selectors=['body','main p','label','.field-help','.error','.error-summary a','button','.secondary'];
        return selectors.map(selector=>{const el=document.querySelector(selector);if(!el)return null;const fg=getComputedStyle(el).color.match(/[\d.]+/g).map(Number);let node=el,bg;while(node){bg=getComputedStyle(node).backgroundColor.match(/[\d.]+/g).map(Number);if(bg.length===3||bg[3]>0)break;node=node.parentElement;}if(!node)bg=[255,255,255];const a=luminance(fg),b=luminance(bg);return {selector,ratio:(Math.max(a,b)+.05)/(Math.min(a,b)+.05)};}).filter(Boolean);
    }+')()');
    contrasts.push(...samples);check('Contraste de muestras >=4.5:1',samples.every(s=>s.ratio>=4.5),JSON.stringify(samples.map(s=>({selector:s.selector,ratio:Number(s.ratio.toFixed(2))}))));
    check('Sin dependencia audiovisual',await evaluate('!document.querySelector("audio,video")'));
    const version=await send('Browser.getVersion');results.unshift('Navegador '+version.product+'. Anchos CSS 360/390/768/1440. Zoom de layout equivalente: viewport 720 y salida 1440; además fuente raíz duplicada. Sin dispositivo físico ni lector de pantalla.');
    results.push(results.filter(r=>r.startsWith('PASS ')).length+' comprobaciones aprobadas, 0 fallos.');
    await writeFile(path.join(evidence,'navegador.txt'),results.join('\n')+'\n');
    await writeFile(path.join(evidence,'pantallas.json'),JSON.stringify(layouts,null,2)+'\n');
    await writeFile(path.join(evidence,'contraste.json'),JSON.stringify(contrasts,null,2)+'\n');
    console.log(results[0]+'\n'+results.at(-1));await send('Browser.close');
}catch(error){console.error(error);process.exitCode=1;await writeFile(path.join(evidence,'navegador.txt'),results.join('\n')+'\nERROR '+error+'\n');}
finally{clearInterval(keepAlive);ws?.close();child.kill();}
