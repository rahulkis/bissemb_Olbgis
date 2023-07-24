(()=>{var e={31772:(e,t,n)=>{"use strict";var s=n(25148);function i(){}function o(){}o.resetWarningCache=i,e.exports=function(){function e(e,t,n,i,o,r){if(r!==s){var a=new Error("Calling PropTypes validators directly is not supported by the `prop-types` package. Use PropTypes.checkPropTypes() to call them. Read more at http://fb.me/use-check-prop-types");throw a.name="Invariant Violation",a}}function t(){return e}e.isRequired=e;var n={array:e,bigint:e,bool:e,func:e,number:e,object:e,string:e,symbol:e,any:e,arrayOf:t,element:e,elementType:e,instanceOf:t,node:e,objectOf:t,oneOf:t,oneOfType:t,shape:t,exact:t,checkPropTypes:o,resetWarningCache:i};return n.PropTypes=n,n}},7862:(e,t,n)=>{e.exports=n(31772)()},25148:e=>{"use strict";e.exports="SECRET_DO_NOT_PASS_THIS_OR_YOU_WILL_BE_FIRED"}},t={};function n(s){var i=t[s];if(void 0!==i)return i.exports;var o=t[s]={exports:{}};return e[s](o,o.exports,n),o.exports}n.n=e=>{var t=e&&e.__esModule?()=>e.default:()=>e;return n.d(t,{a:t}),t},n.d=(e,t)=>{for(var s in t)n.o(t,s)&&!n.o(e,s)&&Object.defineProperty(e,s,{enumerable:!0,get:t[s]})},n.o=(e,t)=>Object.prototype.hasOwnProperty.call(e,t),n.r=e=>{"undefined"!=typeof Symbol&&Symbol.toStringTag&&Object.defineProperty(e,Symbol.toStringTag,{value:"Module"}),Object.defineProperty(e,"__esModule",{value:!0})};var s={};(()=>{"use strict";n.r(s);const e=window.wp.element,t=window.wc.data;function i(e,t,n){return t in e?Object.defineProperty(e,t,{value:n,enumerable:!0,configurable:!0,writable:!0}):e[t]=n,e}const o=window.wp.i18n,r=window.wp.components,a=window.wp.compose,c=window.React;function l(e){return e.startsWith("{{/")?{type:"componentClose",value:e.replace(/\W/g,"")}:e.endsWith("/}}")?{type:"componentSelfClosing",value:e.replace(/\W/g,"")}:e.startsWith("{{")?{type:"componentOpen",value:e.replace(/\W/g,"")}:{type:"string",value:e}}function p(e,t){let n,s,i=[];for(let o=0;o<e.length;o++){const r=e[o];if("string"!==r.type){if(void 0===t[r.value])throw new Error(`Invalid interpolation, missing component node: \`${r.value}\``);if("object"!=typeof t[r.value])throw new Error(`Invalid interpolation, component node must be a ReactElement or null: \`${r.value}\``);if("componentClose"===r.type)throw new Error(`Missing opening component token: \`${r.value}\``);if("componentOpen"===r.type){n=t[r.value],s=o;break}i.push(t[r.value])}else i.push(r.value)}if(n){const o=function(e,t){const n=t[e];let s=0;for(let i=e+1;i<t.length;i++){const e=t[i];if(e.value===n.value){if("componentOpen"===e.type){s++;continue}if("componentClose"===e.type){if(0===s)return i;s--}}}throw new Error("Missing closing component token `"+n.value+"`")}(s,e),r=p(e.slice(s+1,o),t),a=(0,c.cloneElement)(n,{},r);if(i.push(a),o<e.length-1){const n=p(e.slice(o+1),t);i=i.concat(n)}}return i=i.filter(Boolean),0===i.length?null:1===i.length?i[0]:(0,c.createElement)(c.Fragment,null,...i)}function d(e){const{mixedString:t,components:n,throwErrors:s}=e;if(!n)return t;if("object"!=typeof n){if(s)throw new Error(`Interpolation Error: unable to process \`${t}\` because components is not an object`);return t}const i=function(e){return e.split(/(\{\{\/?\s*\w+\s*\/?\}\})/g).map(l)}(t);try{return p(i,n)}catch(e){if(s)throw new Error(`Interpolation Error: unable to process \`${t}\` because of error \`${e.message}\``);return t}}var m=n(7862),u=n.n(m);const h=window.lodash,w=window.wp.data,g=window.wc.tracks,_=window.wc.wcSettings;class b extends e.Component{constructor(){super(...arguments),i(this,"setDismissed",(e=>{this.props.updateOptions({woocommerce_shipping_dismissed_timestamp:e})})),i(this,"hideBanner",(()=>{document.getElementById("woocommerce-admin-print-label").style.display="none"})),i(this,"remindMeLaterClicked",(()=>{const{onCloseAll:e,trackElementClicked:t}=this.props;this.setDismissed(Date.now()),e(),this.hideBanner(),t("shipping_banner_dismiss_modal_remind_me_later")})),i(this,"closeForeverClicked",(()=>{const{onCloseAll:e,trackElementClicked:t}=this.props;this.setDismissed(-1),e(),this.hideBanner(),t("shipping_banner_dismiss_modal_close_forever")}))}render(){const{onClose:t,visible:n}=this.props;return n?(0,e.createElement)(r.Modal,{title:(0,o.__)("Are you sure?","woocommerce"),onRequestClose:t,className:"wc-admin-shipping-banner__dismiss-modal"},(0,e.createElement)("p",{className:"wc-admin-shipping-banner__dismiss-modal-help-text"},(0,o.__)("With WooCommerce Shipping you can Print shipping labels from your WooCommerce dashboard at the lowest USPS rates.","woocommerce")),(0,e.createElement)("div",{className:"wc-admin-shipping-banner__dismiss-modal-actions"},(0,e.createElement)(r.Button,{isSecondary:!0,onClick:this.remindMeLaterClicked},(0,o.__)("Remind me later","woocommerce")),(0,e.createElement)(r.Button,{isPrimary:!0,onClick:this.closeForeverClicked},(0,o.__)("I don't need this","woocommerce")))):null}}const v=(0,a.compose)((0,w.withDispatch)((e=>{const{updateOptions:n}=e(t.OPTIONS_STORE_NAME);return{updateOptions:n}})))(b),y=function(t){let{icon:n,size:s=24,...i}=t;return(0,e.cloneElement)(n,{width:s,height:s,...i})},S=window.wp.primitives,E=(0,e.createElement)(S.SVG,{xmlns:"http://www.w3.org/2000/svg",viewBox:"-2 -2 24 24"},(0,e.createElement)(S.Path,{d:"M10 2c4.42 0 8 3.58 8 8s-3.58 8-8 8-8-3.58-8-8 3.58-8 8-8zm1.13 9.38l.35-6.46H8.52l.35 6.46h2.26zm-.09 3.36c.24-.23.37-.55.37-.96 0-.42-.12-.74-.36-.97s-.59-.35-1.06-.35-.82.12-1.07.35-.37.55-.37.97c0 .41.13.73.38.96.26.23.61.34 1.06.34s.8-.11 1.05-.34z"})),f="install",k="activate",C="setup",P={download:(0,o.__)("download","woocommerce"),[f]:(0,o.__)("install","woocommerce"),[k]:(0,o.__)("activate","woocommerce"),[C]:(0,o.__)("set up","woocommerce"),start:(0,o.__)("start","woocommerce")};function B(t){let{isSetupError:n,errorReason:s}=t;return n?(0,e.createElement)("div",{className:"wc-admin-shipping-banner-install-error"},(0,e.createElement)(y,{icon:E,className:"warning-icon"}),(e=>{const t=e in P?P[e]:P.setup;return(0,o.sprintf)((0,o.__)("Unable to %s the plugin. Refresh the page and try again.","woocommerce"),t)})(s)):null}const L=window.wp.apiFetch;var x=n.n(L);const O=(0,_.getSetting)("wcAssetUrl",""),A="woocommerce-services";class T extends e.Component{constructor(e){var t;super(e),t=this,i(this,"isSetupError",(()=>this.state.wcsSetupError)),i(this,"closeDismissModal",(()=>{this.setState({isDismissModalOpen:!1}),this.trackElementClicked("shipping_banner_dismiss_modal_close_button")})),i(this,"openDismissModal",(()=>{this.setState({isDismissModalOpen:!0}),this.trackElementClicked("shipping_banner_dimiss")})),i(this,"hideBanner",(()=>{this.setState({showShippingBanner:!1})})),i(this,"createShippingLabelClicked",(()=>{const{activePlugins:e}=this.props;this.setState({isShippingLabelButtonBusy:!0}),this.trackElementClicked("shipping_banner_create_label"),e.includes(A)?this.acceptTosAndGetWCSAssets():this.installAndActivatePlugins(A)})),i(this,"woocommerceServiceLinkClicked",(()=>{this.trackElementClicked("shipping_banner_woocommerce_service_link")})),i(this,"trackBannerEvent",(function(e){let n=arguments.length>1&&void 0!==arguments[1]?arguments[1]:{};const{activePlugins:s,isJetpackConnected:i}=t.props;(0,g.recordEvent)(e,{banner_name:"wcadmin_install_wcs_prompt",jetpack_installed:s.includes("jetpack"),jetpack_connected:i,wcs_installed:s.includes(A),...n})})),i(this,"trackImpression",(()=>{this.trackBannerEvent("banner_impression")})),i(this,"trackElementClicked",(e=>{this.trackBannerEvent("banner_element_clicked",{element:e})})),i(this,"getInstallText",(()=>{const{activePlugins:e}=this.props;return e.includes(A)?(0,o.__)('You\'ve already installed WooCommerce Shipping. By clicking "Create shipping label", you agree to its {{tosLink}}Terms of Service{{/tosLink}}.',"woocommerce"):(0,o.__)('By clicking "Create shipping label", {{wcsLink}}WooCommerce Shipping{{/wcsLink}} will be installed and you agree to its {{tosLink}}Terms of Service{{/tosLink}}.',"woocommerce")}));const n=new URL(window.location.href).searchParams.get("post");this.state={showShippingBanner:!0,isDismissModalOpen:!1,setupErrorReason:C,orderId:parseInt(n,10),wcsAssetsLoaded:!1,wcsAssetsLoading:!1,wcsSetupError:!1,isShippingLabelButtonBusy:!1,installText:this.getInstallText(),isWcsModalOpen:!1}}componentDidMount(){const{showShippingBanner:e}=this.state;e&&this.trackImpression()}async installAndActivatePlugins(e){const{installPlugins:t,activatePlugins:n,isRequesting:s}=this.props;if(s)return!1;!0===(await t([e])).success?!0===(await n([e])).success?this.acceptTosAndGetWCSAssets():this.setState({setupErrorReason:k,wcsSetupError:!0}):this.setState({setupErrorReason:f,wcsSetupError:!0})}acceptTosAndGetWCSAssets(){return x()({path:"/wc/v1/connect/tos",method:"POST",data:{accepted:!0}}).then((()=>x()({path:"/wc/v1/connect/assets",method:"GET"}))).then((e=>this.loadWcsAssets(e))).catch((()=>this.setState({wcsSetupError:!0})))}generateMetaBoxHtml(e,t,n){const s=JSON.stringify(n).replace(/"/g,"&quot;");return`\n<div id="${e}" class="postbox">\n\t<div class="postbox-header">\n\t\t<h2 class="hndle"><span>${t}</span></h2>\n\t\t<div class="handle-actions">\n\t\t\t<button type="button" class="handlediv" aria-expanded="true">\n\t\t\t\t<span class="screen-reader-text">${(0,o.__)("Toggle panel:","woocommerce")} ${t}</span>\n\t\t\t\t<span class="toggle-indicator" aria-hidden="true"></span>\n\t\t\t</button>\n\t\t</div>\n\t</div>\n\t<div class="inside">\n\t\t<div class="wcc-root woocommerce wc-connect-create-shipping-label" data-args="${s}">\n\t\t</div>\n\t</div>\n</div>\n`}loadWcsAssets(e){let{assets:t}=e;if(this.state.wcsAssetsLoaded||this.state.wcsAssetsLoading)return void this.openWcsModal();this.setState({wcsAssetsLoading:!0});const n=t.wc_connect_admin_script,s=t.wc_connect_admin_style;if(void 0===window.wcsPluginData){const e=n.substring(0,n.lastIndexOf("/")+1);window.wcsPluginData={assetPath:e}}const{orderId:i}=this.state,{itemsCount:r}=this.props,a=this.generateMetaBoxHtml("woocommerce-order-label",(0,o.__)("Shipping Label","woocommerce"),{order:{id:i},context:"shipping_label",items:r});document.getElementById("woocommerce-order-data").insertAdjacentHTML("beforebegin",a);const c=this.generateMetaBoxHtml("woocommerce-order-shipment-tracking",(0,o.__)("Shipment Tracking","woocommerce"),{order:{id:i},context:"shipment_tracking",items:r});document.getElementById("woocommerce-order-actions").insertAdjacentHTML("afterend",c),window.jQuery&&(window.jQuery("#normal-sortables").sortable("refresh"),window.jQuery("#side-sortables").sortable("refresh"),window.jQuery("#woocommerce-order-label").hide()),Promise.all([new Promise(((e,t)=>{const s=document.createElement("script");s.src=n,s.async=!0,s.onload=e,s.onerror=t,document.body.appendChild(s)})),new Promise(((e,t)=>{if(""!==s){const n=document.getElementsByTagName("head")[0],i=document.createElement("link");i.rel="stylesheet",i.type="text/css",i.href=s,i.media="all",i.onload=e,i.onerror=t,n.appendChild(i)}else e()}))]).then((()=>{this.setState({wcsAssetsLoaded:!0,wcsAssetsLoading:!1,isShippingLabelButtonBusy:!1}),this.openWcsModal()}))}openWcsModal(){window.wcsGetAppStoreAsync&&window.wcsGetAppStoreAsync("wc-connect-create-shipping-label").then((e=>{const t=e.getState(),{orderId:n}=this.state,s=t.ui.selectedSiteId,i=e.subscribe((()=>{const t=e.getState(),r=(0,h.get)(t,["extensions","woocommerce","woocommerceServices",s,"shippingLabel",n],null),a=(0,h.get)(t,["extensions","woocommerce","woocommerceServices",s,"labelSettings"],null),c=(0,h.get)(t,["extensions","woocommerce","woocommerceServices",s,"packages"],null),l=(0,h.get)(t,["extensions","woocommerce","sites",s,"data","locations"]);r&&a&&a.meta&&c&&l&&(r.loaded&&a.meta.isLoaded&&c.isLoaded&&(0,h.isArray)(l)&&!this.state.isWcsModalOpen?(window.jQuery&&(this.setState({isWcsModalOpen:!0}),window.jQuery(".shipping-label__new-label-button").click()),e.dispatch({type:"NOTICE_CREATE",notice:{duration:1e4,status:"is-success",text:(0,o.__)("Plugin installed and activated","woocommerce")}})):r.showPurchaseDialog&&(i(),window.jQuery&&window.jQuery("#woocommerce-order-label").show()))}));document.getElementById("woocommerce-admin-print-label").style.display="none"}))}render(){const{isDismissModalOpen:t,showShippingBanner:n,isShippingLabelButtonBusy:s}=this.state;return n?(0,e.createElement)("div",null,(0,e.createElement)("div",{className:"wc-admin-shipping-banner-container"},(0,e.createElement)("img",{className:"wc-admin-shipping-banner-illustration",src:O+"images/shippingillustration.svg",alt:(0,o.__)("Shipping ","woocommerce")}),(0,e.createElement)("div",{className:"wc-admin-shipping-banner-blob"},(0,e.createElement)("h3",null,(0,o.__)("Print discounted shipping labels with a click.","woocommerce")),(0,e.createElement)("p",null,d({mixedString:this.state.installText,components:{tosLink:(0,e.createElement)(r.ExternalLink,{href:"https://wordpress.com/tos",target:"_blank",type:"external"}),wcsLink:(0,e.createElement)(r.ExternalLink,{href:"https://woocommerce.com/products/shipping/?utm_medium=product",target:"_blank",type:"external",onClick:this.woocommerceServiceLinkClicked})}})),(0,e.createElement)(B,{isSetupError:this.isSetupError(),errorReason:this.state.setupErrorReason})),(0,e.createElement)(r.Button,{disabled:s,isPrimary:!0,isBusy:s,onClick:this.createShippingLabelClicked},(0,o.__)("Create shipping label","woocommerce")),(0,e.createElement)("button",{onClick:this.openDismissModal,type:"button",className:"notice-dismiss",disabled:this.state.isShippingLabelButtonBusy},(0,e.createElement)("span",{className:"screen-reader-text"},(0,o.__)("Close Print Label Banner.","woocommerce")))),(0,e.createElement)(v,{visible:t,onClose:this.closeDismissModal,onCloseAll:this.hideBanner,trackElementClicked:this.trackElementClicked})):null}}T.propTypes={itemsCount:u().number.isRequired,isJetpackConnected:u().bool.isRequired,activePlugins:u().array.isRequired,activatePlugins:u().func.isRequired,installPlugins:u().func.isRequired,isRequesting:u().bool.isRequired};const M=(0,a.compose)((0,w.withSelect)((e=>{const{isPluginsRequesting:n,isJetpackConnected:s,getActivePlugins:i}=e(t.PLUGINS_STORE_NAME);return{isRequesting:n("activatePlugins")||n("installPlugins"),isJetpackConnected:s(),activePlugins:i()}})),(0,w.withDispatch)((e=>{const{activatePlugins:n,installPlugins:s}=e(t.PLUGINS_STORE_NAME);return{activatePlugins:n,installPlugins:s}})))(T),R=["wcAdminSettings","preloadSettings"],I=(0,_.getSetting)("admin",{}),j=Object.keys(I).reduce(((e,t)=>(R.includes(t)||(e[t]=I[t]),e)),{});function W(e){let t=arguments.length>1&&void 0!==arguments[1]&&arguments[1],n=arguments.length>2&&void 0!==arguments[2]?arguments[2]:e=>e;if(R.includes(e))throw new Error((0,o.__)("Mutable settings should be accessed via data store."));const s=j.hasOwnProperty(e)?j[e]:t;return n(s,t)}(0,_.getSetting)("adminUrl"),(0,_.getSetting)("countries"),(0,_.getSetting)("currency"),(0,_.getSetting)("locale"),(0,_.getSetting)("siteTitle"),(0,_.getSetting)("wcAssetUrl"),W("orderStatuses");const N=document.getElementById("wc-admin-shipping-banner-root"),D=N.dataset.args&&JSON.parse(N.dataset.args)||{},q=(0,t.withPluginsHydration)({...W("plugins"),jetpackStatus:W("dataEndpoints",{}).jetpackStatus})(M);(0,e.render)((0,e.createElement)(q,{itemsCount:D.items}),N)})(),(window.wc=window.wc||{}).printShippingLabelBanner=s})();