// Exportación local sin dependencias: Markdown conservado como fuente, HTML y PDF con Edge.
import {readFile,writeFile,mkdir} from 'node:fs/promises';
import {spawn} from 'node:child_process';
import path from 'node:path';
import {pathToFileURL} from 'node:url';
const root=process.cwd();
const escape=s=>s.replaceAll('&','&amp;').replaceAll('<','&lt;').replaceAll('>','&gt;').replaceAll('"','&quot;');
function inline(s){return escape(s).replace(/`([^`]+)`/g,'<code>$1</code>').replace(/\*\*([^*]+)\*\*/g,'<strong>$1</strong>').replace(/\[([^\]]+)\]\((https?:[^)]+)\)/g,'<a href="$2">$1</a>');}
function markdown(text){
    const lines=text.split(/\r?\n/);let html='',paragraph=[],list=false,code=false,table=false,caseOpen=false;
    const flush=()=>{if(paragraph.length){html+='<p>'+inline(paragraph.join(' '))+'</p>';paragraph=[];}};
    const close=()=>{flush();if(list){html+='</ul>';list=false;}if(table){html+='</tbody></table>';table=false;}};
    for(const line of lines){
        if(line.startsWith('```')){close();html+=code?'</code></pre>':'<pre><code>';code=!code;continue;}
        if(code){html+=escape(line)+'\n';continue;}
        if(!line.trim()){close();continue;}
        const heading=line.match(/^(#{1,6}) (.+)$/);
        if(heading){close();if(caseOpen){html+='</section>';caseOpen=false;}if(heading[1].length===3&&heading[2].startsWith('CP')){html+='<section class="case">';caseOpen=true;}html+=`<h${heading[1].length}>${inline(heading[2])}</h${heading[1].length}>`;continue;}
        if(line.startsWith('|')){flush();if(list){html+='</ul>';list=false;}if(/^\|[\s:|-]+\|$/.test(line))continue;const cells=line.split('|').slice(1,-1);if(!table){html+='<table><thead><tr>'+cells.map(c=>'<th>'+inline(c.trim())+'</th>').join('')+'</tr></thead><tbody>';table=true;}else html+='<tr>'+cells.map(c=>'<td>'+inline(c.trim())+'</td>').join('')+'</tr>';continue;}
        const bullet=line.match(/^(?:- |\d+\. )(.+)$/);if(bullet){flush();if(table){html+='</tbody></table>';table=false;}if(!list){html+='<ul>';list=true;}html+='<li>'+inline(bullet[1])+'</li>';continue;}
        if(list){html+='</ul>';list=false;}paragraph.push(line.trim());
    }close();if(caseOpen)html+='</section>';return html;
}
const style=`@page{size:A4;margin:18mm 16mm}*{box-sizing:border-box}body{font:11pt/1.5 Arial,sans-serif;color:#36281f;margin:0;padding:0}h1{font:700 28pt/1.15 Georgia,serif;color:#58331f;margin:0 0 20px}h2{font-size:16pt;margin:24px 0 10px;border-bottom:1px solid #d7b99c;padding-bottom:6px;break-after:avoid}h3{font-size:13pt;margin:16px 0 8px;break-after:avoid}p{margin:8px 0 12px}li{margin:5px 0}ul{padding-left:22px}table{width:100%;border-collapse:collapse;font-size:9.5pt;margin:15px 0;table-layout:fixed}th,td{padding:8px;border:1px solid #cbbbad;text-align:left;vertical-align:top;overflow-wrap:anywhere}th{background:#f4e8db}thead{display:table-header-group}tr{break-inside:avoid}a{color:#734018;overflow-wrap:anywhere}code{font:9pt Consolas,monospace;overflow-wrap:anywhere}pre{background:#f5efe7;padding:12px;white-space:pre-wrap;overflow-wrap:anywhere;break-inside:avoid}.case{break-inside:avoid}.case li{margin:4px 0}.case ul{margin:8px 0 18px}@media screen{body{padding:36px;max-width:794px;background:white;margin:auto}html{background:#eee}}`;
const evidenceDir=path.join(root,'docs/evidencias/revision-2');
await mkdir(evidenceDir,{recursive:true});
let plannedCaseCount=0;
for(const name of ['informe','plan-de-pruebas']){
    const source=await readFile(path.join(root,'docs',name+'.md'),'utf8');
    if(name==='plan-de-pruebas'){
        plannedCaseCount=(source.match(/^### CP[0-9]+/gm)||[]).length;
        const declared=source.match(/([0-9]+) casos planificados/);
        if(declared && plannedCaseCount!==Number(declared[1]))throw Error('Casos declarados distintos de los encabezados del plan');
    }
    let body=markdown(source);
    if(name==='informe')body=body.replace('<h2>Portada</h2>','<section class="cover"><h2>Portada</h2>').replace('<h2>Introducción</h2>','</section><h2>Introducción</h2>');
    await writeFile(path.join(root,'docs',name+'.html'),`<!doctype html><html lang="es"><head><meta charset="utf-8"><title>${name} · Actualidad Huancayo</title><style>${style}.cover{min-height:700px;break-after:page}.cover p{margin:24px 0}</style></head><body>${body}</body></html>`);
}
const child=spawn('C:/Program Files (x86)/Microsoft/Edge/Application/msedge.exe',['--headless=new','--disable-gpu','--no-sandbox','--no-first-run','--remote-debugging-port=9224',`--user-data-dir=${path.join(root,'storage/edge-export')}`,'about:blank'],{windowsHide:true,stdio:'ignore'});
const keepAlive=setInterval(()=>{},1000);let ws,id=0;const pending=new Map();
function send(method,params={}){const n=++id;return new Promise((resolve,reject)=>{const t=setTimeout(()=>{pending.delete(n);reject(Error(method+' timeout'));},15000);pending.set(n,{resolve:r=>{clearTimeout(t);resolve(r);},reject:e=>{clearTimeout(t);reject(e);}});ws.send(JSON.stringify({id:n,method,params}));});}
const logs=[];
try{
    let target;for(let i=0;i<80;i++){try{target=(await(await fetch('http://127.0.0.1:9224/json')).json()).find(t=>t.type==='page');if(target)break;}catch{}await new Promise(r=>setTimeout(r,100));}
    if(!target)throw Error('Edge no disponible');ws=new WebSocket(target.webSocketDebuggerUrl);await new Promise((r,j)=>{ws.addEventListener('open',r,{once:true});ws.addEventListener('error',j,{once:true});});
    ws.addEventListener('message',e=>{const d=JSON.parse(e.data);if(pending.has(d.id)){const p=pending.get(d.id);pending.delete(d.id);d.error?p.reject(Error(JSON.stringify(d.error))):p.resolve(d.result);}});
    await send('Page.enable');await send('Emulation.setDeviceMetricsOverride',{width:794,height:1123,deviceScaleFactor:1,mobile:false});
    for(const name of ['informe','plan-de-pruebas']){
        await send('Page.navigate',{url:pathToFileURL(path.join(root,'docs',name+'.html')).href});await new Promise(r=>setTimeout(r,400));
        const info=await send('Runtime.evaluate',{expression:'JSON.stringify({title:document.title,overflow:document.documentElement.scrollWidth>innerWidth,cases:document.querySelectorAll(".case").length,text:document.body.innerText.length})',returnByValue:true});
        const summary=JSON.parse(info.result.value);if(summary.overflow||summary.text<5000)throw Error('Documento incompleto o desbordado');if(name==='plan-de-pruebas'&&summary.cases!==plannedCaseCount)throw Error('Faltan casos');
        const shot=await send('Page.captureScreenshot',{format:'png'});await writeFile(path.join(evidenceDir,name+'-vista.png'),Buffer.from(shot.data,'base64'));
        const pdf=await send('Page.printToPDF',{printBackground:true,preferCSSPageSize:true,displayHeaderFooter:true,headerTemplate:'<div style="font:8px Arial;width:100%;text-align:center;color:#665">Actualidad Huancayo · Universidad Continental</div>',footerTemplate:'<div style="font:8px Arial;width:100%;text-align:center;color:#665">Ramos Ore Dennis Jhordan · <span class="pageNumber"></span> / <span class="totalPages"></span></div>'});
        const buffer=Buffer.from(pdf.data,'base64');await writeFile(path.join(root,'docs',name+'.pdf'),buffer);
        const pages=(buffer.toString('latin1').match(/\/Type\s*\/Page\b/g)||[]).length;
        if(pages<1||!buffer.subarray(0,5).equals(Buffer.from('%PDF-')))throw Error('PDF inválido');
        logs.push(`${name}: ${buffer.length} bytes, ${pages} páginas PDF; HTML sin desbordamiento; ${summary.cases} casos; ${summary.text} caracteres de texto. Vista inicial capturada para revisión.`);
    }
    await writeFile(path.join(evidenceDir,'exportacion.txt'),logs.join('\n')+'\n');console.log(logs.join('\n'));await send('Browser.close');
}catch(e){console.error(e);process.exitCode=1;}finally{clearInterval(keepAlive);ws?.close();child.kill();}
