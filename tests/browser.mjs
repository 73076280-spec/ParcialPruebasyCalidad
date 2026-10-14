// Edge mediante Chrome DevTools Protocol, sin paquetes npm ni modificar perfiles del usuario.
import {spawn} from 'node:child_process';
import {mkdir,writeFile} from 'node:fs/promises';
import path from 'node:path';
const root=process.cwd(), base=process.argv[2]||'http://127.0.0.1:8080';
const edge='C:/Program Files (x86)/Microsoft/Edge/Application/msedge.exe';
const child=spawn(edge,['--headless=new','--disable-gpu','--no-sandbox','--no-first-run','--remote-debugging-port=9223','--remote-debugging-address=127.0.0.1',`--user-data-dir=${path.join(root,'storage/edge-profile-qa')}`,'about:blank'],{windowsHide:true,stdio:'ignore'});
const keepAlive=setInterval(()=>{},1000);
let ws;let sequence=0;const pending=new Map();const results=[];
function check(name,condition){results.push(`${condition?'PASS':'FAIL'} ${name}`);if(!condition)throw Error(name);}
async function send(method,params={}){const id=++sequence;return new Promise((resolve,reject)=>{const timer=setTimeout(()=>{pending.delete(id);reject(Error('CDP timeout '+method));},15000);pending.set(id,{resolve:v=>{clearTimeout(timer);resolve(v);},reject:e=>{clearTimeout(timer);reject(e);}});ws.send(JSON.stringify({id,method,params}));});}
async function evaluate(expression){const r=await send('Runtime.evaluate',{expression,returnByValue:true,awaitPromise:true});if(r.exceptionDetails)throw Error(JSON.stringify(r.exceptionDetails));return r.result.value;}
async function navigate(route){await send('Page.navigate',{url:base+route});for(let i=0;i<80;i++){await new Promise(r=>setTimeout(r,100));if(await evaluate('document.readyState === "complete" && !!document.querySelector("main")'))return;}throw Error('Carga incompleta');}
async function screenshot(name){const r=await send('Page.captureScreenshot',{format:'png',captureBeyondViewport:true});await writeFile(path.join(root,'docs/evidencias',name+'.png'),Buffer.from(r.data,'base64'));}
try{
    await mkdir(path.join(root,'docs/evidencias'),{recursive:true});let target;
    for(let i=0;i<80;i++){try{target=(await(await fetch('http://127.0.0.1:9223/json')).json()).find(t=>t.type==='page');if(target)break;}catch{}await new Promise(r=>setTimeout(r,100));}
    if(!target)throw Error('Edge no disponible');ws=new WebSocket(target.webSocketDebuggerUrl);await new Promise((r,j)=>{ws.addEventListener('open',r,{once:true});ws.addEventListener('error',j,{once:true});});
    ws.addEventListener('message',event=>{const d=JSON.parse(event.data);if(d.id&&pending.has(d.id)){const p=pending.get(d.id);pending.delete(d.id);d.error?p.reject(Error(JSON.stringify(d.error))):p.resolve(d.result);}});
    await send('Page.enable');await send('Emulation.setDeviceMetricsOverride',{width:1440,height:1000,deviceScaleFactor:1,mobile:false});
    await navigate('/index.php');check('B01 escritorio sin desbordamiento',await evaluate('document.documentElement.scrollWidth <= innerWidth'));
    check('B02 seis noticias y filtros',await evaluate('document.querySelectorAll(".card").length===6 && !!document.querySelector("label[for=q]")'));
    await send('Input.dispatchKeyEvent',{type:'keyDown',key:'Tab',code:'Tab',windowsVirtualKeyCode:9});await send('Input.dispatchKeyEvent',{type:'keyUp',key:'Tab',code:'Tab',windowsVirtualKeyCode:9});
    check('B03 salto accesible por teclado',await evaluate('document.activeElement.classList.contains("skip") && document.activeElement.getBoundingClientRect().top >= 0'));
    await send('Input.dispatchKeyEvent',{type:'keyDown',key:'Enter',code:'Enter',windowsVirtualKeyCode:13});await send('Input.dispatchKeyEvent',{type:'keyUp',key:'Enter',code:'Enter',windowsVirtualKeyCode:13});
    check('B04 salto al contenido',await evaluate('document.activeElement.id === "contenido"'));await screenshot('inicio-escritorio');
    await send('Emulation.setDeviceMetricsOverride',{width:390,height:844,deviceScaleFactor:1,mobile:true});await navigate('/index.php');
    check('B05 celular sin desbordamiento',await evaluate('document.documentElement.scrollWidth <= innerWidth'));
    check('B06 una columna en celular',await evaluate('getComputedStyle(document.querySelector(".grid")).gridTemplateColumns.split(" ").length===1'));await screenshot('inicio-celular');
    await navigate('/index.php?r=login');check('B07 etiquetas de login',await evaluate('[...document.querySelectorAll("input:not([type=hidden])")].every(i=>!!document.querySelector(`label[for="${i.id}"]`))'));await screenshot('login-celular');
    await evaluate(`document.querySelector('#email').value='admin@actualidad.test';document.querySelector('#password').value='DemoHuancayo2026!';document.querySelector('main form').requestSubmit();`);
    for(let i=0;i<80;i++){await new Promise(r=>setTimeout(r,100));if(await evaluate('location.search.includes("dashboard") && !!document.querySelector("main h1")'))break;}
    check('B08 login navegador',await evaluate('document.querySelector("main h1").textContent.includes("Resumen")'));
    await navigate('/index.php?r=news-edit');check('B09 formulario móvil sin desbordamiento',await evaluate('document.documentElement.scrollWidth <= innerWidth'));
    await screenshot('formulario-celular');
    await evaluate(`document.querySelector('#title').value='Prueba';document.querySelector('#summary').value='breve';document.querySelector('#content').value='breve';document.querySelector('#category_id').value='1';document.querySelector('main form').submit();`);
    await new Promise(r=>setTimeout(r,500));check('B10 errores con aria-describedby',await evaluate('document.querySelectorAll("[aria-invalid=true]").length>=2 && [...document.querySelectorAll("[aria-invalid=true]")].every(i=>!!document.getElementById(i.getAttribute("aria-describedby")))'));
    check('B11 foco en primer error',await evaluate('document.activeElement.getAttribute("aria-invalid")==="true"'));await screenshot('errores-celular');
    await send('Emulation.setDeviceMetricsOverride',{width:1440,height:1000,deviceScaleFactor:1,mobile:false});await navigate('/index.php?r=dashboard');await screenshot('panel-escritorio');
    const version=await send('Browser.getVersion');results.unshift(`Navegador: ${version.product}; escritorio 1440x1000; celular emulado 390x844.`);
    console.log(results.join('\n'));await writeFile(path.join(root,'docs/evidencias/navegador.txt'),results.join('\n')+'\n');
    await send('Browser.close');
}catch(error){console.error(error);process.exitCode=1;await writeFile(path.join(root,'docs/evidencias/navegador.txt'),results.join('\n')+'\nERROR '+error+'\n');}
finally{clearInterval(keepAlive);ws?.close();child.kill();}
