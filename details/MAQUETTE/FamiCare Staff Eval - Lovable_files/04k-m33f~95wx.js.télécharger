;!function(){try { var e="undefined"!=typeof globalThis?globalThis:"undefined"!=typeof global?global:"undefined"!=typeof window?window:"undefined"!=typeof self?self:{},n=(new e.Error).stack;n&&((e._debugIds|| (e._debugIds={}))[n]="40f92aef-7510-5c98-11ac-dd8ee3676d91")}catch(e){}}();
(globalThis.TURBOPACK||(globalThis.TURBOPACK=[])).push(["object"==typeof document?document.currentScript:void 0,643625,e=>{"use strict";let r="u">typeof navigator&&(!0===navigator.webdriver||navigator.userAgent.includes("HeadlessChrome"));e.s(["isPlaywright",0,r])},265675,e=>{"use strict";var r=e.i(753296),t=e.i(103867),a=e.i(643625);let o={get url(){return`file://${e.P("web/shared/lib/rudderstack-client.ts")}`}},s=void 0!==r.default&&"30uiF44drV1UnRmUEUVhfQ2L500"||o.env?.VITE_RUDDERSTACK_WRITE_KEY||null,i=void 0!==r.default&&"https://rs.lovable.dev"||o.env?.VITE_RUDDERSTACK_DATA_PLANE_URL||null,n=!!(s&&i),l=null,c=null,d=!1;async function h(){return a.isPlaywright?null:n?l||c||(c=(async()=>{try{console.log("Initializing RudderStack Analytics");let{RudderAnalytics:r}=await e.A(330014);l=new r;let a=(0,t.isDev)()?{"Facebook Pixel":!1,"Google Analytics":!1,"Google Ads":!1,"Google Tag Manager":!1}:{};return l.load(s,i,{integrations:{All:!0,...a},configUrl:"https://rs.lovable.dev/sourceConfig",destSDKBaseURL:"https://rs.lovable.dev/v3/modern/js-integrations",pluginsSDKBaseURL:"https://rs.lovable.dev/v3/modern/plugins",consentManagement:{enabled:!0,provider:"custom"}}),l}catch(e){return console.error("Failed to load RudderStack SDK",{error:e}),l=null,c=null,null}})()):(d||(d=!0,console.info("Missing RUDDERSTACK_WRITE_KEY or RUDDERSTACK_DATA_PLANE_URL in env; RudderStack will be disabled")),null)}e.s(["createRudderAnalytics",0,h])},185143,e=>{"use strict";var r=e.i(497289),t=e.i(900147),a=e.i(103867),o=e.i(265675);let s=`
  :root {
    --background: 45 40% 98%;
    --foreground: 0 0% 11%;
    --primary: 0 0% 11%;
    --primary-foreground: 45 40% 98%;
    --muted: 42 38% 95%;
    --muted-foreground: 60 1% 37%;
    --border: 45 17% 91%;
    --card: 45 40% 98%;
    --success-primary: 142 72% 29%;
    --success-primary-foreground: 138 76% 97%;
  }
  body { margin: 0; padding: 0; }
  .he-root {
    display: flex;
    min-height: 100vh;
    flex-direction: column;
    align-items: center;
    justify-content: center;
    padding: 2rem;
    text-align: center;
    font-family: 'Arial', sans-serif;
    background-color: hsl(var(--background));
    color: hsl(var(--foreground));
    box-sizing: border-box;
    -webkit-font-smoothing: antialiased;
    -moz-osx-font-smoothing: grayscale;
  }
  .he-title {
    font-size: 4rem;
    font-weight: 500;
    margin: 0 0 0.5rem;
    line-height: 1.2;
  }
  .he-text {
    max-width: 600px;
    margin: 1rem auto;
    font-size: 1.2rem;
    line-height: 1.5;
  }
  .he-actions {
    display: grid;
    grid-template-columns: repeat(4, auto);
    gap: 1rem;
    margin-top: 1rem;
    justify-content: center;
    width: 100%;
    max-width: 42rem;
  }
  .he-btn {
    background-color: hsl(var(--primary));
    color: hsl(var(--primary-foreground));
    border: none;
    border-radius: 5px;
    padding: 0.625rem 1.25rem;
    font-size: 1rem;
    font-weight: 700;
    font-family: inherit;
    cursor: pointer;
    text-decoration: none;
    display: inline-block;
    transition: opacity 0.15s;
    line-height: 1.5;
  }
  .he-btn:hover { opacity: 0.85; }
  .he-resolve { cursor: pointer; }
  .he-btn-success {
    background-color: hsl(var(--success-primary));
    color: hsl(var(--success-primary-foreground));
  }
  .he-details {
    margin-top: 2rem;
    width: 100%;
    max-width: 42rem;
    border: 1px solid hsl(var(--border));
    border-radius: 8px;
    padding: 1rem;
    text-align: left;
    background-color: hsl(var(--card));
  }
  .he-field { margin-bottom: 1rem; }
  .he-label {
    font-size: 0.875rem;
    color: hsl(var(--muted-foreground));
    margin-bottom: 0.25rem;
  }
  .he-pre {
    background-color: hsl(var(--muted));
    padding: 1rem;
    border-radius: 4px;
    font-size: 0.875rem;
    overflow-x: auto;
    white-space: pre-wrap;
    word-break: break-word;
    margin: 0;
  }
  .he-footer {
    margin-top: 2rem;
    margin-bottom: 2rem;
    font-size: 1.2rem;
    line-height: 1.5;
  }
  .he-link { color: inherit; text-decoration: underline; }
  .he-cta {
    margin-top: 1rem;
    padding: 1rem 1.5rem;
    border: 1px solid hsl(var(--border));
    border-radius: 8px;
    background-color: hsl(var(--muted));
    line-height: 1.5;
  }
  .he-cta-text {
    margin: 0;
    font-size: 0.875rem;
    color: hsl(var(--muted-foreground));
  }
  .he-cta-link {
    color: hsl(var(--foreground));
    font-weight: 500;
    text-decoration: underline;
    text-underline-offset: 4px;
  }
  @media (max-width: 600px) {
    .he-root { padding: 1.25rem; }
    .he-title { font-size: 2.25rem; }
    .he-text { font-size: 1rem; margin: 0.75rem auto; }
    .he-footer { font-size: 1rem; }
    .he-actions { grid-template-columns: 1fr; gap: 0.5rem; }
    .he-cta { padding: 0.875rem 1rem; }
  }
`;function i({error:e,reset:n}){let l=(0,a.isDev)(),[c,d]=(0,t.useState)(l),[h,m]=(0,t.useState)(!1),u=(0,t.useRef)(0),g=e instanceof Error?e.message:String(e),p=e instanceof Error?e.name:"Unknown",b=e instanceof Error?e.stack:void 0,f=e instanceof Error?e.digest:void 0;(0,t.useEffect)(()=>{globalThis.o11y?.captureException(e,{location:"GlobalError",level:"fatal"});let r=e=>{e?.track("react_fatal_error",{location:"GlobalError",level:"fatal",error_message:g,error_stack:b})};try{let e=window.rudderanalytics;e?r(e):(0,o.createRudderAnalytics)().then(r).catch(()=>{})}catch{}},[e]);let v=async()=>{let e=`Error Details:
Message: ${g||"No error message"}
Name: ${p||"Unknown"}
${f?`Digest: ${f}
`:""}
Stack Trace:
${b||"No stack trace available"}`;try{await navigator.clipboard.writeText(e),m(!0),setTimeout(()=>m(!1),2e3)}catch(e){console.error("Failed to copy to clipboard:",e)}};return(0,r.jsxs)(r.Fragment,{children:[(0,r.jsx)("style",{children:s}),(0,r.jsxs)("div",{className:"he-root",children:[(0,r.jsx)("h1",{className:"he-title",children:"Houston, we have a problem"}),(0,r.jsxs)("p",{className:"he-text",children:["We apologize for the inconvenience. Our team is working quickly to"," ",(0,r.jsx)("span",{className:"he-resolve",onClick:()=>{l?d(e=>!e):(u.current+=1,7===u.current&&(d(e=>!e),u.current=0))},children:"resolve"})," ","the issue."]}),(0,r.jsx)("p",{className:"he-text",children:"We understand this disruption may impact your work, and we're doing our best to minimize downtime."}),(0,r.jsx)("p",{className:"he-text",children:"Please try again in a few moments."}),(0,r.jsxs)("div",{className:"he-actions",children:[(0,r.jsx)("button",{className:"he-btn",onClick:()=>window.history.back(),children:"Back"}),(0,r.jsx)("button",{className:"he-btn",onClick:()=>window.location.href="/",children:"Return to homepage"}),(0,r.jsx)("button",{className:"he-btn",onClick:n,children:"Refresh page"}),(0,r.jsx)("a",{href:"https://status.lovable.dev/",target:"_blank",rel:"noopener noreferrer",className:"he-btn he-status-btn",children:"View status page"})]}),c&&(0,r.jsxs)("div",{className:"he-details",children:[(0,r.jsx)("button",{onClick:v,className:`he-btn he-field ${h?"he-btn-success":""}`,children:h?"✓ Copied!":"Copy to clipboard"}),(0,r.jsxs)("div",{className:"he-field",children:[(0,r.jsx)("div",{className:"he-label",children:"Message:"}),(0,r.jsx)("pre",{className:"he-pre",children:g||"No error message"})]}),(0,r.jsxs)("div",{className:"he-field",children:[(0,r.jsx)("div",{className:"he-label",children:"Error name:"}),(0,r.jsx)("pre",{className:"he-pre",children:p})]}),f&&(0,r.jsxs)("div",{className:"he-field",children:[(0,r.jsx)("div",{className:"he-label",children:"Error digest:"}),(0,r.jsx)("pre",{className:"he-pre",children:f})]}),(0,r.jsxs)("div",{className:"he-field",children:[(0,r.jsx)("div",{className:"he-label",children:"Stack trace:"}),(0,r.jsx)("pre",{className:"he-pre",children:b||"No stack trace available"})]})]}),(0,r.jsxs)("p",{className:"he-footer",children:["If the problem persists, contact us at"," ",(0,r.jsx)("a",{href:"https://lovable.dev/support",className:"he-link",children:"lovable.dev/support"}),"."]}),(0,r.jsx)("div",{className:"he-cta",children:(0,r.jsxs)("p",{className:"he-cta-text",children:["This one's on us. Think you could do better?"," ",(0,r.jsx)("a",{href:"/dashboard#prompt=Build%20me%20a%20Lovable%20clone",className:"he-cta-link",children:"Build it on Lovable"})]})})]})]})}e.s(["default",0,function({error:e,reset:t}){return(0,r.jsxs)("html",{lang:"en",children:[(0,r.jsxs)("head",{children:[(0,r.jsx)("title",{children:"500 - Houston, We Have a Problem"}),(0,r.jsx)("meta",{name:"viewport",content:"width=device-width, initial-scale=1"})]}),(0,r.jsx)("body",{children:(0,r.jsx)(i,{error:e,reset:t})})]})}],185143)},330014,e=>{e.v(r=>Promise.all(["static/chunks/0ipd-8~yxu_ga.js"].map(r=>e.l(r))).then(()=>r(972177)))}]);

//# debugId=40f92aef-7510-5c98-11ac-dd8ee3676d91
//# sourceMappingURL=0kchawa0bsm~s.js.map