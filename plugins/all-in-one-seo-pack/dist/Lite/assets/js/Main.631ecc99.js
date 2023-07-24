var c=Object.defineProperty,u=Object.defineProperties;var d=Object.getOwnPropertyDescriptors;var i=Object.getOwnPropertySymbols;var f=Object.prototype.hasOwnProperty,_=Object.prototype.propertyIsEnumerable;var n=(t,o,r)=>o in t?c(t,o,{enumerable:!0,configurable:!0,writable:!0,value:r}):t[o]=r,e=(t,o)=>{for(var r in o||(o={}))f.call(o,r)&&n(t,r,o[r]);if(i)for(var r of i(o))_.call(o,r)&&n(t,r,o[r]);return t},m=(t,o)=>u(t,d(o));import h from"./AdditionalInformation.d2f64136.js";import S from"./Category.d8aff096.js";import g from"./Features.2f8c5a86.js";import y from"./Import.a22f4d4a.js";import v from"./LicenseKey.14c3bbb6.js";import w from"./SearchAppearance.cbd6ce3c.js";import x from"./SmartRecommendations.8bc61e7b.js";import z from"./Success.ca5d59e1.js";import A from"./Welcome.e130cbe8.js";import{b as s,a as p,c as I}from"./index.d328c175.js";import{n as O}from"./vueComponentNormalizer.87056a83.js";import"./ToolsSettings.004b222f.js";import"./helpers.db3922d1.js";import"./index.a4161053.js";import"./client.94d919c5.js";import"./_commonjsHelpers.f40d732e.js";import"./default-i18n.abde8d59.js";import"./constants.a1b1778a.js";import"./isArrayLikeObject.a4a9229a.js";import"./cleanForSlug.e9a761bb.js";import"./Modal.1216ab78.js";import"./Image.ec6b7346.js";import"./MaxCounts.5a7ca2fd.js";import"./Img.53f489b6.js";import"./Phone.3d9368d6.js";import"./RadioToggle.98e1e7ec.js";import"./SocialProfiles.9591afec.js";import"./Checkbox.5873a8d2.js";import"./Checkmark.e7547654.js";import"./Textarea.d161fc2e.js";import"./Index.cb0f42fe.js";import"./SettingsRow.eb71f07b.js";import"./Row.13b6f3f1.js";import"./Plus.a9b9ba75.js";import"./Header.fffa631d.js";import"./Logo.1a5e022a.js";import"./Steps.f359c40f.js";import"./HighlightToggle.47bdd2a8.js";import"./Radio.99a9886d.js";import"./HtmlTagsEditor.9f04fc4c.js";import"./Editor.22528024.js";import"./UnfilteredHtml.35e34c73.js";import"./ImageSeo.0ea16b4e.js";import"./NewsChannel.fc0a5ed5.js";import"./Pencil.d547ebca.js";import"./ProBadge.7c0de2f7.js";import"./popup.25df8419.js";import"./params.bea1a08d.js";import"./GoogleSearchPreview.c269028d.js";import"./PostTypeOptions.1d37105d.js";import"./Tooltip.3ec20ff5.js";import"./QuestionMark.83ebd18e.js";import"./Book.b6a9040c.js";import"./VideoCamera.896ed18d.js";var $=function(){var t=this,o=t.$createElement,r=t._self._c||o;return r(t.$route.name,{tag:"component"})},L=[];const M={components:{AdditionalInformation:h,Category:S,Features:g,Import:y,LicenseKey:v,SearchAppearance:w,SmartRecommendations:x,Success:z,Welcome:A},computed:e(e(e(e({},s("wizard",["shouldShowImportStep"])),s(["isUnlicensed"])),p("wizard",["stages"])),p(["internalOptions"])),methods:m(e({},I("wizard",["setStages","loadState"])),{deleteStage(t){const o=[...this.stages],r=o.findIndex(l=>t===l);r!==-1&&this.$delete(o,r),this.setStages(o)}}),mounted(){if(this.internalOptions.internal.wizard){const t=JSON.parse(this.internalOptions.internal.wizard);delete t.currentStage,delete t.stages,delete t.licenseKey,this.loadState(t)}this.shouldShowImportStep||this.deleteStage("import"),this.isUnlicensed||this.deleteStage("license-key"),this.$isPro&&this.deleteStage("smart-recommendations")}},a={};var R=O(M,$,L,!1,U,null,null,null);function U(t){for(let o in a)this[o]=a[o]}var Ft=function(){return R.exports}();export{Ft as default};