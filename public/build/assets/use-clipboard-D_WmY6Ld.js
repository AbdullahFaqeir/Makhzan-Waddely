import{c as a}from"./index-BwE6w5yz.js";import{r as u}from"./main-owSUWNVS.js";function d(o,e){const[s,c]=u.useState(!1),t=(e==null?void 0:e.successDuration)??2e3;return u.useEffect(()=>{if(s&&t){const r=setTimeout(()=>{c(!1)},t);return()=>{clearTimeout(r)}}},[s,t]),[s,()=>{const r=a(o);c(r)}]}export{d as u};
//# sourceMappingURL=use-clipboard-D_WmY6Ld.js.map
