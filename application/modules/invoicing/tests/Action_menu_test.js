'use strict';
const assert = require('assert');
const factory = require('../../../../assets/js/invoicing/action-menu.js');
class Element {
    constructor(tag) { this.tagName=tag; this.children=[]; this.attributes={}; this.style={}; this.handlers={}; this.hidden=false; this.textContent=''; this.offsetWidth=220; this.offsetHeight=120; this.tokens=new Set(); this.classList={contains: name=>this.tokens.has(name), remove: name=>this.tokens.delete(name)}; }
    set className(value) { this.tokens=new Set(value.split(/\s+/)); }
    get className() { return [...this.tokens].join(' '); }
    setAttribute(key,value) { this.attributes[key]=String(value); }
    getAttribute(key) { return this.attributes[key]??null; }
    appendChild(child) { if(child.parentNode)child.parentNode.children=child.parentNode.children.filter(node=>node!==child); this.children.push(child);child.parentNode=this;return child; }
    replaceChildren(...children) { for(const child of this.children)child.parentNode=null;this.children=[];children.forEach(child=>this.appendChild(child)); }
    contains(node) { return this===node || this.children.some(child=>child.contains(node)); }
    querySelector(selector) { return this.querySelectorAll(selector)[0]??null; }
    querySelectorAll(selector) { const result=[];for(const child of this.children){if(selector==='a,button' ? ['a','button'].includes(child.tagName) : child.classList.contains(selector.slice(1)))result.push(child);result.push(...child.querySelectorAll(selector));}return result; }
    addEventListener(type,handler) { this.handlers[type]=handler; }
    fire(type,event={}) { this.handlers[type]?.({target:this,preventDefault(){},...event}); }
    focus() { document.activeElement=this; }
    getBoundingClientRect() { return this.rect??{left:920,right:980,top:550,bottom:580}; }
}
const document={body:new Element('body'),handlers:{},createElement:tag=>new Element(tag),addEventListener(type,handler){this.handlers[type]=handler;}};
const root={document,innerWidth:1000,innerHeight:600,handlers:{},addEventListener(type,handler){this.handlers[type]=handler;}};
let checks=0;
function check(ok,message){checks++;assert.ok(ok,message);}
function makeCell(){const cell=new Element('td');document.body.appendChild(cell);for(const title of ['Create Invoice','Create Invoice Vuca','Print Invoice']){const link=new Element('a');link.className='btn btn-sm btn-info pilih_print_inv';link.setAttribute('title',title);link.setAttribute('href','/invoicing/'+title);link.setAttribute('data-id_inv','INV-1');link.setAttribute('data-toggle','modal');link.setAttribute('data-target','#modal_print');cell.appendChild(link);}return cell;}
const api=factory(root), cell=makeCell(), originals=[...cell.children];
api.enhance(cell);
const button=cell.querySelector('.invoice-action-toggle'), menu=cell.querySelector('.invoice-action-menu');
check(!!button && button.textContent==='Aksi','One labelled dropdown trigger');
check(menu.hidden && menu.children.length===3,'All available actions initially hidden');
check(button.getAttribute('aria-expanded')==='false','Initial accessible closed state');
for(const [i,item] of menu.children.entries()){
    check(item===originals[i],'Keep original action node');
    check(item.getAttribute('data-id_inv')==='INV-1' && item.getAttribute('data-target')==='#modal_print' && item.getAttribute('data-toggle')==='modal','Preserve modal data');
    check(item.getAttribute('href')==='/invoicing/'+item.getAttribute('title'),'Preserve navigation URL');
    check(item.classList.contains('pilih_print_inv') && !item.classList.contains('btn'),'Keep event selector, remove button presentation');
    check(item.children[0].tagName==='i' && item.children[0].getAttribute('aria-hidden')==='true','Decorative action icon hidden from screen readers');
    check(item.children[1].textContent===item.getAttribute('aria-label'),'Visible label and accessible name agree');
}
check(originals[0].children[0].classList.contains('fa-file-text-o') && originals[0].getAttribute('data-action-tone')==='blue','Regular create uses blue document icon');
check(originals[1].children[0].classList.contains('fa-files-o') && originals[1].getAttribute('data-action-tone')==='green','Vuca create uses green documents icon');
check(originals[2].children[0].classList.contains('fa-print'),'Print action uses printer icon');
api.enhance(cell);check(cell.children.length===2,'Enhancement is idempotent');
button.fire('click');check(menu.parentNode===document.body && !menu.hidden,'Portal escapes scroll table');
check(button.getAttribute('aria-expanded')==='true','Open state exposed');
check(menu.style.left==='760px' && menu.style.top==='430px','Right aligned and opens above near viewport bottom');
check(document.activeElement===originals[0],'Opening focuses first item');
document.handlers.keydown({key:'ArrowUp',preventDefault(){}});check(document.activeElement===originals[2],'Arrow up wraps');
document.handlers.keydown({key:'Home',preventDefault(){}});check(document.activeElement===originals[0],'Home focuses first');
document.handlers.keydown({key:'End',preventDefault(){}});check(document.activeElement===originals[2],'End focuses last');
document.handlers.keydown({key:'Escape',preventDefault(){}});check(menu.hidden && menu.parentNode===cell && document.activeElement===button,'Escape restores menu and trigger focus');
button.rect={left:980,right:1040,top:550,bottom:580};button.fire('click');check(menu.style.left==='772px','Clamp overflowing trigger to viewport right edge');api.close();
button.fire('keydown',{key:'ArrowUp'});check(document.activeElement===originals[2],'Trigger arrow up opens last item');
document.handlers.keydown({key:'Tab',preventDefault(){throw Error('Tab must stay native');}});check(menu.hidden,'Tab exits menu');
button.fire('click');document.handlers.click({target:new Element('outside')});check(menu.hidden,'Click outside closes');
button.fire('click');menu.fire('click',{target:originals[0].children[0]});check(menu.hidden && originals[0].getAttribute('data-id_inv')==='INV-1','Clicking nested action icon closes menu and keeps delegated handler attributes');
button.fire('click');root.handlers.scroll({target:menu});check(!menu.hidden,'Internal menu scroll stays open');
root.handlers.scroll({target:cell});check(menu.hidden,'Table/window scroll closes stale position');
button.fire('click');root.handlers.resize();check(menu.hidden,'Resize closes');
const second=makeCell();api.enhance(second);button.fire('click');second.querySelector('.invoice-action-toggle').fire('click');check(menu.hidden,'Only one row menu open');
api.close();check(second.querySelector('.invoice-action-menu').hidden,'Public close supports DataTables redraw');
const empty=new Element('td');api.enhance(empty);check(!empty.querySelector('.invoice-action-toggle'),'No empty dropdown when server returns no actions');
originals[1].setAttribute('aria-disabled','true');button.fire('click');document.handlers.keydown({key:'ArrowDown',preventDefault(){}});check(document.activeElement===originals[2],'Keyboard skips unavailable actions');
api.close();
for(const [title,icon,tone] of [['View Invoice','fa-eye','blue'],['Revisi Invoice','fa-pencil','amber'],['Print Kwitansi','fa-file-pdf-o','green'],['Close Invoice','fa-times-circle','red'],['__proto__','fa-ellipsis-h','blue']]){
    const extra=new Element('td'), action=new Element('a');action.setAttribute('title',title);action.setAttribute('href','/original');extra.appendChild(action);api.enhance(extra);
    check(action.children[0].classList.contains(icon) && action.getAttribute('data-action-tone')===tone && action.getAttribute('href')==='/original','Semantic icon/tone with safe fallback: '+title);
}
console.log('PASS: '+checks+' invoice action menu checks');
