import{w as n,an as r,ao as o,aH as a,x as e,k as u}from"./main-owSUWNVS.js";function p(){const{trans:t}=n();return r({mutationFn:s=>i(s),onSuccess:()=>{a(t(e("Subscription renewed.")))},onError:s=>o(s)})}function i({subscriptionId:t}){return u.post(`billing/subscriptions/${t}/resume`).then(s=>s.data)}export{p as u};
//# sourceMappingURL=use-resume-subscription-DWoReR-s.js.map
