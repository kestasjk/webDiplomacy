"use strict";(self.webpackChunkbeta_src=self.webpackChunkbeta_src||[]).push([[728],{723:function(r,e,t){t.r(e),t.d(e,{default:function(){return ir}});var n=t(885),o=t(2791),i=t(4194);var a=t(3967),c=t(6162),s=t(2048),u=t(184),l=function(){return(0,u.jsx)("rect",{className:"trigger",height:"100%",width:"100%",fill:"black",style:{opacity:0}})},d=function(r){var e=r.province,t=r.x,n=r.y,o=(0,a.Z)();return(0,u.jsxs)("svg",{id:"".concat(e,"-center"),width:"34",height:"34",viewBox:"0 0 34 34",fill:"none",x:t,y:n,xmlns:"http://www.w3.org/2000/svg",children:[(0,u.jsx)("path",{d:"M17 32.9998C25.8366 32.9998 33 25.8364 33 16.9999C33 8.1634 25.8366 1 17 1C8.16344 1 1 8.1634 1 16.9999C1 25.8364 8.16344 32.9998 17 32.9998Z",stroke:o.palette.primary.main}),(0,u.jsx)("path",{d:"M17.0064 25.7269C21.8263 25.7269 25.7336 21.8196 25.7336 16.9997C25.7336 12.1797 21.8263 8.27243 17.0064 8.27243C12.1866 8.27243 8.2793 12.1797 8.2793 16.9997C8.2793 21.8196 12.1866 25.7269 17.0064 25.7269Z",fill:o.palette.primary.main}),(0,u.jsx)(l,{})]})},v=t(1413),D=function(r){var e=r.id,t=r.style,n=r.text,o=r.x,i=r.y,c=(0,a.Z)();return(0,u.jsx)("text",{className:"label",style:(0,v.Z)({fill:c.palette.primary.main,fontWeight:900,fontSize:"150%",userSelect:"none"},t),x:o,y:i,id:e,children:n},e)};D.defaultProps={id:void 0};var y,p=D,f=function(r){var e,t=r.provinceMapData,n=r.ownerCountryID,o=r.playerCountryID,i=r.highlightSelection,l=(0,a.Z)(),v=(0,s.T)(),D=(0,s.C)(c.vq),y=(D.user,D.members),f=t.province,h="none",I=0;if(n){var m,x,g=null===(m=y.find((function(r){return r.countryID===Number(n)})))||void 0===m?void 0:m.country;if(g&&"Sea"!==t.type)h=null===(x=l.palette[g])||void 0===x?void 0:x.main,I=.4}if(i&&o){var T,S,Z=null===(T=y.find((function(r){return r.countryID===o})))||void 0===T?void 0:T.country;if(Z)h=null===(S=l.palette[Z])||void 0===S?void 0:S.main,I=1}return(0,u.jsxs)("svg",{height:t.height,id:"".concat(f,"-province"),viewBox:t.viewBox,width:t.width,x:t.x,y:t.y,children:[(0,u.jsxs)("g",{onClick:function(r){return e=r,void v(c.I9.processMapClick({evt:e,clickProvince:f}));var e},children:[(null===(e=t.texture)||void 0===e?void 0:e.texture)&&(0,u.jsx)("path",{d:t.path,fill:t.texture.texture,id:"".concat(f,"-texture"),stroke:t.texture.stroke,strokeOpacity:t.texture.strokeOpacity,strokeWidth:t.texture.strokeWidth}),(0,u.jsx)("path",{d:t.path,fill:h,fillOpacity:I,id:"".concat(f,"-control-path"),stroke:l.palette.primary.main,strokeOpacity:1,strokeWidth:1})]}),t.centerPos&&(0,u.jsx)("g",{className:"no-pointer-events",children:(0,u.jsx)(d,{province:f,x:t.centerPos.x,y:t.centerPos.y})}),t.labels&&t.labels.map((function(r,e){var n=r.name,o=r.text,i=r.style,a=r.x,c=r.y,s=o,l="".concat(f,"-label-").concat(n);return s||(s=t.abbr),(0,u.jsx)("g",{className:"no-pointer-events",children:(0,u.jsx)(p,{id:l,name:n,style:i,text:s,x:a,y:c},l||e)},l)}))]})},h=t(5824),I=t(5734),m=function(r){var e=r.children,t=r.name,n=r.x,o=r.y;return(0,u.jsx)("svg",{className:"unit-slot",id:"".concat(t,"-unit-slot"),style:{overflow:"visible"},x:n,y:o,children:e})},x=t(5963),g=t(5469),T=t(1660);!function(r){r.NONE="none",r.HOLD="hold",r.DISBANDED="disbanded",r.DISLODGED="dislodged",r.DISLODGING="dislodging",r.BUILD="build"}(y||(y={}));var S,Z=function(r){var e=r.provinceMapData,t=r.units,n=r.highlightChoice,o=e.province,i={},a={};return t.filter((function(r){return r.mappedTerritory.province===o})).forEach((function(r){var e;switch(r.drawMode){case y.NONE:e=h.Z.NONE;break;case y.HOLD:e=h.Z.HOLD;break;case y.BUILD:case y.DISLODGING:e=h.Z.NONE;break;case y.DISLODGED:e=h.Z.DISLODGED;break;case y.DISBANDED:e=h.Z.DISBANDED;break;default:e=h.Z.NONE}var t=(0,u.jsx)(I.ZP,{id:"".concat(o,"-unit"),country:r.country,meta:r,type:r.unit.type,iconState:e});r.drawMode===y.DISLODGING?a[r.mappedTerritory.unitSlotName]=t:i[r.mappedTerritory.unitSlotName]=t})),(0,u.jsxs)("svg",{height:e.height,id:"".concat(o,"-province-overlay"),viewBox:e.viewBox,width:e.width,x:e.x,y:e.y,overflow:"visible",children:[n&&(0,u.jsx)("path",{d:e.path,fill:"none",fillOpacity:0,id:"".concat(o,"-choice-outline"),stroke:"black",strokeOpacity:1,strokeWidth:5}),e.unitSlots.filter((function(r){return r.name in i})).map((function(r){var e=r.name,t=r.x,n=r.y;return(0,u.jsx)(m,{name:e,x:t,y:n,children:i[e]},e)})),e.unitSlots.filter((function(r){return r.name in a})).map((function(r){var e=r.name,t=r.arrowReceiver,n="".concat(e,"-dislodging");return(0,u.jsx)(m,{name:n,x:t.x,y:t.y,children:a[e]},n)}))]})},P=t(5230),O=t(9234),w=function(r){var e=r.units,t=r.centersByProvince,n=r.phase,o=r.isLatestPhase,i=(0,s.C)(c.BU),a=(0,s.C)(c.Jw),l={};i.data.territoryStatuses.forEach((function(r){l[a.terrIDToProvince[r.id]]=r}));var d=(0,s.C)(c.RY),v=(0,s.C)(c.Jr),D=(0,s.C)(c.vq),y=(D.members,D.user),p=[],h=[];o&&y&&("Diplomacy"===n?d.inProgress?"Move"===d.type?d.viaConvoy?(p=[a.terrIDToProvince[a.unitToTerrID[d.unitID]]],h=v.legalViasByUnitID[d.unitID].map((function(r){return x.ZP[r.dest].province}))):(p=[a.terrIDToProvince[a.unitToTerrID[d.unitID]]],h=v.legalMoveDestsByUnitID[d.unitID].map((function(r){return x.ZP[r].province}))):"Support"===d.type?d.fromTerrID?(p=[a.terrIDToProvince[a.unitToTerrID[d.unitID]],a.terrIDToProvince[d.fromTerrID]],h=v.legalSupportsByUnitID[d.unitID][a.terrIDToProvince[d.fromTerrID]].map((function(r){return r.dest}))):(p=[a.terrIDToProvince[a.unitToTerrID[d.unitID]]],h=Object.keys(v.legalSupportsByUnitID[d.unitID])):"Convoy"===d.type&&(d.fromTerrID?(p=[a.terrIDToProvince[a.unitToTerrID[d.unitID]],a.terrIDToProvince[d.fromTerrID]],h=Object.keys(v.legalConvoysByUnitID[d.unitID][a.terrIDToProvince[d.fromTerrID]])):(p=[a.terrIDToProvince[a.unitToTerrID[d.unitID]]],h=Object.keys(v.legalConvoysByUnitID[d.unitID]))):(p=[],h=[]):"Retreats"===n?d.inProgress?"Retreat"===d.type&&(p=[a.terrIDToProvince[a.unitToTerrID[d.unitID]]],(h=v.legalRetreatDestsByUnitID[d.unitID].map((function(r){return x.ZP[r].province}))).push(p[0])):(p=[],h=Object.keys(v.legalRetreatDestsByUnitID).map((function(r){return a.terrIDToProvince[a.unitToTerrID[r]]}))):"Builds"===n&&(y.member.supplyCenterNo<y.member.unitNo?h=e.filter((function(r){return r.country===y.member.country})).map((function(r){return r.mappedTerritory.province})):y.member.supplyCenterNo>y.member.unitNo&&(h=v.possibleBuildDests.map((function(r){return x.ZP[r].province})))));var I=new Set(p),m=new Set(h),g=Object.values(P.Z).filter((function(r){return!r.playable})).map((function(r){var e;return(0,u.jsx)(f,{provinceMapData:r,ownerCountryID:null===(e=t[r.province])||void 0===e?void 0:e.ownerCountryID,playerCountryID:null===y||void 0===y?void 0:y.member.countryID,highlightSelection:!1},"".concat(r.province,"-province"))})),T=Object.values(P.Z).filter((function(r){return r.playable&&r.province!==O.Z.NAPLES&&r.province!==O.Z.ROME}));T.push(P.Z[O.Z.NAPLES]),T.push(P.Z[O.Z.ROME]);var S=T.map((function(r){var e,n=I.has(r.province);return(0,u.jsx)(f,{provinceMapData:r,ownerCountryID:null===(e=t[r.province])||void 0===e?void 0:e.ownerCountryID,playerCountryID:null===y||void 0===y?void 0:y.member.countryID,highlightSelection:n},"".concat(r.province,"-province"))})),w=T.map((function(r){var t=m.has(r.province);return(0,u.jsx)(Z,{provinceMapData:r,units:e,highlightChoice:t},"".concat(r.province,"-province-overlay"))}));return(0,u.jsxs)("g",{id:"wD-boardmap-v10.3.4 1",children:[(0,u.jsx)("g",{id:"unplayable",children:g}),(0,u.jsx)("g",{id:"playableProvinces",children:S}),(0,u.jsx)("g",{id:"playableProvinceOverlays",children:w})]})},E=t.p+"static/media/capturable-land.379f9cca7138664bf782.jpeg",j=t.p+"static/media/sea-texture.fd82ad462f655f49e1f0.png",b=t(4375);!function(r){r[r.CONVOY=0]="CONVOY",r[r.MOVE=1]="MOVE",r[r.SUPPORT=2]="SUPPORT",r[r.HOLD=3]="HOLD"}(S||(S={}));var N,C=S,B=t(5173),k=function(r){switch(r){case C.SUPPORT:return(0,u.jsx)(u.Fragment,{children:Object.entries(B.Z.palette.arrowColors).map((function(e){var t=(0,n.Z)(e,2),o=t[0],i=t[1];return(0,u.jsxs)("marker",{id:"arrowHead__".concat(C[r],"_").concat(b.Z[o]),markerWidth:12,markerHeight:8,refX:18,refY:3,orient:"auto",children:[(0,u.jsx)("polygon",{points:"0 0, 6 3, 0 6, 0 5, 4 3, 0 1",fill:i.main}),(0,u.jsx)("polygon",{points:"4 0, 10 3, 4 6, 4 5, 8 3, 4 1",fill:i.main})]},"arrowHead__".concat(C[r],"_").concat(b.Z[o]))}))});case C.HOLD:return(0,u.jsx)(u.Fragment,{children:Object.entries(B.Z.palette.arrowColors).map((function(e){var t=(0,n.Z)(e,2),o=t[0],i=t[1];return(0,u.jsx)("marker",{id:"arrowHead__".concat(C[r],"_").concat(b.Z[o]),markerWidth:90,markerHeight:90,refX:10,refY:45,orient:"auto",markerUnits:"userSpaceOnUse",strokeWidth:4,children:(0,u.jsx)("path",{d:" M 24 72 A 30 30 180 0 1 24 18",stroke:i.main})},"arrowHead__".concat(C[r],"_").concat(b.Z[o]))}))});default:return(0,u.jsx)(u.Fragment,{children:Object.entries(B.Z.palette.arrowColors).map((function(e){var t=(0,n.Z)(e,2),o=t[0],i=t[1];return(0,u.jsx)("marker",{id:"arrowHead__".concat(C[r],"_").concat(b.Z[o]),markerWidth:8,markerHeight:8,refX:o===b.Z.IMPLIED_FOREIGN?0:7.1,refY:4,orient:"auto",children:(0,u.jsx)("polygon",{points:"0 0, 8 4, 0 8",fill:i.main})},"arrowHead__".concat(C[r],"_").concat(b.Z[o]))}))})}},M=function(){return(0,u.jsxs)(u.Fragment,{children:[k(C.HOLD),k(C.MOVE),k(C.SUPPORT)]})},L=t(4942),A=t(4554);!function(r){r[r.Army=2]="Army",r[r.Fleet=4]="Fleet",r[r.All=6]="All"}(N||(N={}));var _,R,F=N,U=(_={Army:F.Army,Fleet:F.Fleet},(0,L.Z)(_,F.Army,"Army"),(0,L.Z)(_,F.Fleet,"Fleet"),R={},(0,L.Z)(R,F.Army,"Build Army"),(0,L.Z)(R,F.Fleet,"Build Fleet"),(0,L.Z)(R,"Build Army",F.Army),(0,L.Z)(R,"Build Fleet",F.Fleet),R),H=t(4861),G=t(9018),V=function(r){var e=r.availableOrder,t=r.clickCallback,n=r.country,o=r.canBuild,i=r.province,a=r.unitSlotName,c=r.toTerrID,s=P.Z[i],l=s.x+s.unitSlotsBySlotName[a].x,d=s.y+s.unitSlotsBySlotName[a].y,v=70,D=[],y={width:50,height:50},p={cursor:"pointer"},f=0+v/2,I=f-25;if(d-=70,o&F.Army&&D.push((0,u.jsxs)("g",{style:p,onClick:function(){t(e,F.Army,c)},children:[(0,u.jsx)("circle",{fill:"white",r:25,cx:f,cy:35}),(0,u.jsx)("svg",{x:I,y:10,style:y,children:(0,u.jsx)(H.ZP,{country:n,iconState:h.Z.BUILD})})]},"Army")),o&F.Fleet){var m=0;D.length&&(m=v-10,v=2*v-10),D.push((0,u.jsxs)("g",{style:p,onClick:function(){t(e,F.Fleet,c)},children:[(0,u.jsx)("circle",{fill:"white",r:25,cx:f+m,cy:35}),(0,u.jsx)("svg",{x:I+m,y:10,style:y,children:(0,u.jsx)(G.ZP,{country:n,iconState:h.Z.BUILD})})]},"Fleet"))}return l-=v/2,(0,u.jsxs)("svg",{x:l,y:d,children:[(0,u.jsx)("rect",{x:0,y:0,fill:"rgba(0,0,0,.7)",width:v,height:70,rx:10,ry:10}),D]})},Y=function(){var r=(0,s.T)(),e=(0,s.C)(c.Jw),t=(0,s.C)(c.RY),n=(0,s.C)((function(r){return r.game.overview.user.member}));if(!t||"Build"!==t.type)return(0,u.jsx)(A.Z,{});var o=e.terrIDToTerritory[t.toTerrID],i=x.ZP[o],a=i.province,l=i.unitSlotName,d="Coast"===P.Z[a].type?F.All:F.Army;return(0,u.jsx)(V,{availableOrder:t.orderID,canBuild:d,clickCallback:function(t,n,o){var i=o;"Build Army"===U[n]&&(i=e.terrIDToProvinceID[o]),r(c.I9.updateOrdersMeta((0,L.Z)({},t,{saved:!1,update:{type:U[n],toTerrID:i}}))),r(c.I9.resetOrder())},country:g.Z[n.country],province:a,unitSlotName:l,toTerrID:t.toTerrID},"".concat(a,"-").concat(l))},W=function(r){var e=r.province,t=r.unitSlotName,n=r.position,o=r.text,i=r.clickHandler,a=((0,s.T)(),P.Z[e]);if(!a||!a.unitSlotsBySlotName[t])return(0,u.jsx)(A.Z,{});var c=a.x+a.unitSlotsBySlotName[t].x,l=a.y+a.unitSlotsBySlotName[t].y,d=55+24*o.length*.4,v=0,D=0;if("top"===n)D=-84.5;else if("bottom"===n)D=84.5;else if("left"===n)v=-(d/2+50);else{if("right"!==n)throw Error(n);v=+(d/2+50)}var y=c-d/2+v,p=l-34.5+D;return(0,u.jsx)("svg",{x:y,y:p,filter:"drop-shadow(10px 10px 18px #222222)",onClick:i,children:(0,u.jsxs)("g",{style:{cursor:"pointer"},children:[(0,u.jsx)("rect",{x:0,y:0,fill:"white",width:d,height:69,rx:35,ry:35}),(0,u.jsx)("text",{x:d/2,y:34.5,textAnchor:"middle",alignmentBaseline:"middle",fontFamily:"Roboto",fontSize:24,style:{userSelect:"none"},fill:"black",children:o})]})})},z=function(r){var e,t,n=r.units,o=(0,s.T)(),i=(0,s.C)(c.RY),a=(0,s.C)(c.Jw),l=(0,s.C)(c.Jr);if(!i.inProgress||i.type||!i.unitID)return(0,u.jsx)(A.Z,{});var d=n.find((function(r){return r.unit.id===i.unitID})),v=x.ZP[a.unitToTerritory[i.unitID]],D=v.province,y=v.unitSlotName,p=function(r){var e=arguments.length>1&&void 0!==arguments[1]?arguments[1]:void 0;return function(){o(c.I9.updateOrder({type:r,viaConvoy:e}))}};return(0,u.jsxs)(u.Fragment,{children:[(0,u.jsx)(W,{province:D,unitSlotName:y,position:"left",text:"Hold",clickHandler:p("Hold")}),(0,u.jsx)(W,{province:D,unitSlotName:y,position:"right",text:"Move",clickHandler:p("Move")}),(0,u.jsx)(W,{province:D,unitSlotName:y,position:"top",text:"Support",clickHandler:p("Support")}),"Fleet"===(null===d||void 0===d||null===(e=d.unit)||void 0===e?void 0:e.type)&&"Sea"===v.provinceMapData.type&&l.hasAnyLegalConvoysByUnitID[i.unitID]&&(0,u.jsx)(W,{province:D,unitSlotName:y,position:"bottom",text:"Convoy",clickHandler:p("Convoy")})||(0,u.jsx)("g",{}),"Army"===(null===d||void 0===d||null===(t=d.unit)||void 0===t?void 0:t.type)&&l.legalViasByUnitID[i.unitID].length>0&&(0,u.jsx)(W,{province:D,unitSlotName:y,position:"bottom",text:"Via Convoy",clickHandler:p("Move","Yes")})]})},J=t(2750);function X(r,e){var t,o,i,a;switch(r){case"arrow":var c=e,s=(0,n.Z)(c,4),u=s[0],l=s[1];t=u+.75*(s[2]-u),o=l+.75*(s[3]-l),i=0,a=0;break;case"unit":var d=J.Z[e],v=x.ZP[d].provinceMapData,D=x.ZP[d].unitSlotName;t=v.x,o=v.y,v.unitSlotsBySlotName[D]&&(t+=v.unitSlotsBySlotName[D].x,o+=v.unitSlotsBySlotName[D].y),i=I.Vd,a=I.Lz;break;case"dislodger":var y=J.Z[e],p=x.ZP[y].provinceMapData,f=x.ZP[y].unitSlotName;t=p.x,o=p.y,p.unitSlotsBySlotName[f]&&(t+=p.unitSlotsBySlotName[f].arrowReceiver.x,o+=p.unitSlotsBySlotName[f].arrowReceiver.y),i=I.Vd,a=I.Lz;break;default:var h=J.Z[e],m=x.ZP[h].provinceMapData,g=x.ZP[h].unitSlotName;t=m.x,o=m.y,m.unitSlotsBySlotName[g]&&(t+=m.unitSlotsBySlotName[g].arrowReceiver.x,o+=m.unitSlotsBySlotName[g].arrowReceiver.y),i=12,a=12}return[t,o,i,a]}function q(r,e,t,o){var i=X(r,e),a=(0,n.Z)(i,4),c=a[0],s=a[1],u=a[2],l=a[3],d=X(t,o),v=(0,n.Z)(d,4),D=v[0],y=v[1],p=v[2];"unit"!==r&&"dislodger"!==r||(u*=.9,l*=.9);var f=function(r,e,t,n,o,i,a,c){var s=i-o,u=c-a;if(Math.abs(s)<=1e-10&&Math.abs(u)<=1e-10)return{x1:o,x2:i,y1:a,y2:c};var l=Math.atan2(u,s),d=o+e/2*Math.cos(l),v=a+r/2*Math.sin(l),D=i-n/2*Math.cos(l),y=c-t/2*Math.sin(l);return(D-d)*s+(y-v)*u<=1e-10?{x1:o,x2:i,y1:a,y2:c}:{x1:d,x2:D,y1:v,y2:y}}(l,u,v[3],p,c,D,s,y),h=f.x1,I=f.x2;return[h,f.y1,I,f.y2]}function K(r,e,t,o,i,a){var c,s,l=arguments.length>6&&void 0!==arguments[6]?arguments[6]:0,d=q(t,o,i,a),v=(0,n.Z)(d,4),D=v[0],y=v[1],p=v[2],f=v[3];switch(r===C.CONVOY?c="4 3":r===C.HOLD&&(c="12 3"),e){case b.Z.MOVE:case b.Z.CONVOY:case b.Z.IMPLIED:case b.Z.IMPLIED_FOREIGN:case b.Z.RETREAT:case b.Z.SUPPORT_HOLD:case b.Z.SUPPORT_MOVE:s=3.5;break;case b.Z.MOVE_FAILED:case b.Z.CONVOY_FAILED:case b.Z.SUPPORT_HOLD_FAILED:case b.Z.SUPPORT_MOVE_FAILED:s=3;break;default:s=3.5}if(l>0){var h=p-D,I=f-y,m=Math.sqrt(h*h+I*I);if(m>0){var x=h/m,g=I/m,T=-x;D+=l*g,y+=l*T}}return(0,u.jsx)("line",{x1:D,y1:y,x2:p,y2:f,markerEnd:"url(#arrowHead__".concat(C[r],"_").concat(b.Z[e],")"),stroke:B.Z.palette.arrowColors[e].main,strokeWidth:s,strokeDasharray:c},"".concat(D,"-").concat(y,"-").concat(p,"-").concat(f,"-").concat(r,"-").concat(e))}function Q(r,e){var t;return null!==(t=e[r])&&void 0!==t&&t.coastParentID?Number(e[r].coastParentID):r}var $=function(r){r.phase;var e=r.orders,t=r.units,o=(r.maps,r.territories),i=[],a={};return e.forEach((function(r){a[Q(r.terrID,o)]=r})),function(r,e,t){e.filter((function(r){return"Move"===r.type})).forEach((function(e){if(e.toTerrID){var n=x.ZP[t[e.terrID].name].territory,o=x.ZP[t[e.toTerrID].name].territory;r.push(K(C.MOVE,"Yes"===e.success?b.Z.MOVE:b.Z.MOVE_FAILED,"unit",n,"territory",o)),e.viaConvoy}}))}(i,e,o),function(r,e,t,n){var o={};e.filter((function(r){return"Support hold"===r.type})).forEach((function(r){var e=Q(r.terrID,n),t=r.toTerrID;o[e]=t})),e.filter((function(r){return"Support hold"===r.type})).forEach((function(e){if(e.toTerrID){var i=Q(e.terrID,n),a=x.ZP[n[e.terrID].name].territory,c=e.toTerrID,s=t[c],u=s?x.ZP[n[s.terrID].name].territory:x.ZP[n[e.toTerrID].name].territory,l="Yes"===e.success?b.Z.SUPPORT_HOLD:b.Z.SUPPORT_HOLD_FAILED,d=o[c]===i?6:0;r.push(K(C.HOLD,l,"unit",a,"unit",u,d))}}))}(i,e,a,o),function(r,e,t,n){e.filter((function(r){return"Support move"===r.type})).forEach((function(e){if(e.fromTerrID&&e.toTerrID){var o=x.ZP[n[e.terrID].name].territory,i=!1,a=e.fromTerrID,c=t[a];!c||"Move"!==c.type||c.terrID!==e.fromTerrID&&n[c.terrID].coastParentID!==e.fromTerrID.toString()||c.toTerrID!==e.toTerrID&&n[c.toTerrID].coastParentID!==e.toTerrID.toString()||(i=!0);var s=c?x.ZP[n[c.terrID].name].territory:x.ZP[n[e.fromTerrID].name].territory,u="Yes"===e.success?b.Z.SUPPORT_MOVE:b.Z.SUPPORT_MOVE_FAILED;if(i){var l=x.ZP[n[c.toTerrID].name].territory;r.push(K(C.SUPPORT,u,"unit",o,"arrow",q("unit",s,"territory",l)))}else{var d=x.ZP[n[e.toTerrID].name].territory;r.push(K(C.SUPPORT,u,"unit",o,"arrow",q("unit",s,"territory",d))),r.push(K(C.MOVE,b.Z.IMPLIED_FOREIGN,"unit",s,"territory",d))}}}))}(i,e,a,o),function(r,e,t,n){e.filter((function(r){return"Convoy"===r.type})).forEach((function(e){if(e.fromTerrID&&e.toTerrID){var o=x.ZP[n[e.terrID].name].territory,i=x.ZP[n[e.fromTerrID].name].territory,a=!1,c=t[e.fromTerrID];!c||"Move"!==c.type||c.terrID!==e.fromTerrID||c.toTerrID!==e.toTerrID&&n[c.toTerrID].coastParentID!==e.toTerrID.toString()||(a=!0);var s="Yes"===e.success?b.Z.CONVOY:b.Z.CONVOY_FAILED,u=x.ZP[n[e.toTerrID].name].territory;r.push(K(C.CONVOY,s,"unit",o,"arrow",q("unit",i,"territory",u))),a||r.push(K(C.MOVE,b.Z.IMPLIED_FOREIGN,"unit",i,"territory",u))}}))}(i,e,a,o),function(r,e,t){e.filter((function(r){return"Retreat"===r.type})).forEach((function(e){if(e.toTerrID){var n=x.ZP[t[e.terrID].name].territory,o=x.ZP[t[e.toTerrID].name].territory;r.push(K(C.MOVE,b.Z.RETREAT,"unit",n,"territory",o))}}))}(i,e,o),function(r,e,t){e.filter((function(r){return r.drawMode===y.DISLODGING})).forEach((function(e){if(null!==e.movedFromTerrID){var n=x.ZP[t[e.movedFromTerrID].name].territory,o=x.ZP[t[e.unit.terrID].name].territory;r.push(K(C.MOVE,b.Z.MOVE,"territory",n,"dislodger",o))}}))}(i,t,o),function(r,e,t){e.filter((function(r){return r.drawMode===y.BUILD})).forEach((function(e){var o=x.ZP[t[e.unit.terrID].name].territory,i=X("unit",o),a=(0,n.Z)(i,4),c=a[0],s=a[1],l=a[2],d=a[3];r.push((0,u.jsx)("circle",{cx:c,cy:s,r:1.4*(l+d)/4,fill:"none",stroke:"rgb(0,150,0)",strokeWidth:.05*(l+d)},"build-circle-".concat(o)))}))}(i,t,o),(0,u.jsx)("g",{id:"arrows",children:i})},rr=o.forwardRef((function(r,e){var t=r.units,n=r.phase,o=r.orders,i=r.maps,a=r.territories,c=r.centersByProvince,s=r.isLatestPhase;return(0,u.jsxs)("svg",{id:"map",fill:"none",ref:e,style:{width:"100%",height:"100%"},xmlns:"http://www.w3.org/2000/svg",children:[(0,u.jsx)("g",{id:"full-map-svg",children:(0,u.jsxs)("g",{id:"container",children:[(0,u.jsx)(w,{units:t,centersByProvince:c,phase:n,isLatestPhase:s}),(0,u.jsx)($,{phase:n,orders:o,units:t,maps:i,territories:a}),s&&(0,u.jsx)(Y,{}),s&&(0,u.jsx)(z,{units:t})]})}),(0,u.jsxs)("defs",{children:[(0,u.jsx)("pattern",{id:"capturable-land",patternUnits:"userSpaceOnUse",width:"1546",height:"1384",children:(0,u.jsx)("image",{href:E,x:"0",y:"0",width:"1546",height:"1384"})}),(0,u.jsx)("pattern",{id:"sea-texture",patternUnits:"userSpaceOnUse",width:"1546",height:"1384",children:(0,u.jsx)("image",{href:j,x:"0",y:"0",width:"1966",height:"1615"})}),M(),(0,u.jsxs)("filter",{id:"selectionGlow",height:"120%",width:"120%",x:"-10%",y:"-10%",children:[(0,u.jsx)("feMorphology",{operator:"dilate",radius:"5",in:"SourceAlpha",result:"thickerSource"}),(0,u.jsx)("feGaussianBlur",{stdDeviation:"8",in:"thickerSource",result:"blurredSource"}),(0,u.jsx)("feFlood",{floodColor:"rgb(100,200,255)",result:"glowColor"}),(0,u.jsx)("feComposite",{in:"glowColor",in2:"blurredSource",operator:"in",result:"selectionGlowGlow"})]}),(0,u.jsxs)("filter",{id:"choiceGlow",height:"120%",width:"120%",x:"-10%",y:"-10%",children:[(0,u.jsx)("feMorphology",{operator:"dilate",radius:"1",in:"SourceAlpha",result:"thickerSource"}),(0,u.jsx)("feGaussianBlur",{stdDeviation:"6",in:"thickerSource",result:"blurredSource"}),(0,u.jsx)("feFlood",{floodColor:"rgb(255,255,255)",result:"glowColor"}),(0,u.jsx)("feComposite",{in:"glowColor",in2:"blurredSource",operator:"in",result:"choicesGlowGlow"})]})]})]})})),er=o.memo(rr),tr=t(3622),nr=t(8539),or={DESKTOP:[.45,3],MOBILE_LG:[.32,1.6],MOBILE_LG_LANDSCAPE:[.3,1.6],MOBILE:[.32,1.6],MOBILE_LANDSCAPE:[.27,1.6],TABLET:[.6275,3],TABLET_LANDSCAPE:[.6,3]},ir=function(){var r=o.useRef(null),e=(0,tr.Z)(),t=(0,n.Z)(e,1)[0],a=(0,s.T)(),l=(0,s.C)(c.mT),d=function(r){return or[r]}((0,nr.Z)(t)),v=(0,n.Z)(d,2),D=v[0],p=v[1],f=(0,s.C)(c.MA),h=(0,s.C)(c.vq),I=(0,s.C)(c.XN),m=(0,s.C)(c.BU),S=(0,s.C)(c.Jw),Z=function(){if(f.viewedPhaseIdx>=I.phases.length-1&&"Playing"===I.status&&h.user){var r=[],e={};m.data.currentOrders&&m.data.currentOrders.forEach((function(r){e[r.id]=r})),Object.entries(l).forEach((function(t){var o=(0,n.Z)(t,2),i=o[0],a=o[1];if(e[i]){var c,s=0,u=0,l=0,d="",v="",D=a.originalOrder;if(D||(D=e[i]),D){if(D.fromTerrID&&(s=Number(D.fromTerrID)),D.toTerrID&&(u=Number(D.toTerrID)),(d=D.type)&&d.startsWith("Build ")){D.toTerrID&&(l=Number(D.toTerrID));var y=d.split(" ");v=(0,n.Z)(y,2)[1]}else if(D.unitID){var p=S.unitToTerrID[D.unitID];p&&(l=Number(p)),v=m.data.units[D.unitID].type}c="Yes"===D.viaConvoy?"Yes":"No"}if(a.update&&(void 0!==a.update.fromTerrID&&(s=Number(a.update.fromTerrID)),u=Number(a.update.toTerrID),d=a.update.type,c="Yes"===a.update.viaConvoy?"Yes":"No"),d&&v&&l){var f={countryID:I.countryID,dislodged:"No",fromTerrID:s,phase:h.phase,success:"Yes",terrID:l,toTerrID:u,turn:h.turn,type:d,unitType:v,viaConvoy:c};r.push(f)}}}));var t=I.phases.length>1?I.phases[I.phases.length-2].orders:[],o=function(r,e,t,o,i,a,c,s,u,l){var d=[],v=Object.fromEntries(e.map((function(r){return[r.id,r]}))),D={};Object.values(t).forEach((function(r){var e=l.terrIDToProvinceID[r.terrID];e in D?D[e]+=1:D[e]=1}));var p={};i.forEach((function(r){r.success&&"Move"===r.type&&(p[r.toTerrID.toString()]=r.terrID.toString())}));var f={};Object.entries(a).forEach((function(r){var e=(0,n.Z)(r,2),o=e[0],i=e[1],a=c.find((function(r){return r.id===o}));if(a){var s,u,l=(null===(s=t[a.unitID])||void 0===s?void 0:s.terrID)||(null===(u=i.update)||void 0===u?void 0:u.toTerrID);l&&(f[l]=i)}}));var h=s.member.unitNo-s.member.supplyCenterNo,I="Builds"===u&&h>0,m=!I||!(0,T.Z)(c,a);return Object.values(t).forEach((function(e){var t=r[e.terrID];if(t){var n=x.ZP[x.A8[t.name]];if(n){var i=o.find((function(r){return r.countryID.toString()===e.countryID}));if(i){var a,c,u,h,T,S,Z,P,O,w,E=i.country,j=l.terrIDToProvinceID[e.terrID],b=y.NONE,N=v[j]&&null!==v[j].unitID&&v[j].unitID!==e.id;"Hold"===(null===(a=f[e.terrID])||void 0===a||null===(c=a.update)||void 0===c?void 0:c.type)?b=y.HOLD:!N||"Disband"!==(null===(u=f[e.terrID])||void 0===u||null===(h=u.update)||void 0===h?void 0:h.type)&&"Disband"!==(null===(T=f[j])||void 0===T||null===(S=T.update)||void 0===S?void 0:S.type)?"Destroy"===(null===(Z=f[e.terrID])||void 0===Z||null===(P=Z.update)||void 0===P?void 0:P.type)||"Destroy"===(null===(O=f[j])||void 0===O||null===(w=O.update)||void 0===w?void 0:w.type)?b=y.DISBANDED:N?b=y.DISLODGED:D[j]>=2?b=y.DISLODGING:e.countryID===s.member.countryID.toString()&&I&&!m&&(b=y.DISLODGED):b=y.DISBANDED;var C=e.terrID in p?p[e.terrID]:null;d.push({country:g.Z[E],mappedTerritory:n,unit:e,drawMode:b,movedFromTerrID:C})}}}})),Object.entries(a).forEach((function(e,t){var i=(0,n.Z)(e,2),a=i[0],s=i[1].update;if(s&&s.type&&s.type.startsWith("Build ")&&null!==s.toTerrID&&c.find((function(r){return r.id===a}))){var u=r[s.toTerrID],l={id:(t+1e5).toString(),countryID:u.countryID,type:s.type.split(" ")[1],terrID:s.toTerrID};if(u){var v=x.ZP[x.A8[u.name]];if(v){var D=o.find((function(r){return r.countryID.toString()===l.countryID}));if(D){var p=D.country;d.push({country:g.Z[p],mappedTerritory:v,unit:l,drawMode:y.BUILD,movedFromTerrID:null})}}}}})),d}(m.data.territories,m.data.territoryStatuses,m.data.units,h.members,t,l,m.data.currentOrders?m.data.currentOrders:[],h.user,h.phase,S),i={};return m.data.territoryStatuses.forEach((function(r){var e=S.terrIDToProvince[r.id],t=r.ownerCountryID||"0";i[e]={ownerCountryID:t}})),{phase:h.phase,units:o,orders:r,centersByProvince:i,isLatestPhase:!0}}var a=I.phases[f.viewedPhaseIdx],c=a.units,s=f.viewedPhaseIdx>0?I.phases[f.viewedPhaseIdx-1].orders:[],u=function(r,e,t,n,o,i){var a=[],c={};e.forEach((function(r){r.terrID in c?c[r.terrID]+=1:c[r.terrID]=1}));var s={};n.forEach((function(r){r.success&&"Move"===r.type&&(s[r.toTerrID.toString()]=r.terrID.toString())}));var u={};return o.forEach((function(r){u[r.terrID]=r})),e.forEach((function(e,n){var o=r[e.terrID],l={id:n.toString(),countryID:e.countryID.toString(),type:e.unitType,terrID:e.terrID.toString()};if(o){var d=x.ZP[x.A8[o.name]];if(d){var v=t.find((function(r){return r.countryID.toString()===l.countryID}));if(v){var D,p,f,h,I,m=v.country,T=i.terrIDToProvinceID[e.terrID],S=y.NONE;"Hold"===(null===(D=u[e.terrID])||void 0===D?void 0:D.type)?S=y.HOLD:"Yes"!==e.retreating||"Disband"!==(null===(p=u[e.terrID])||void 0===p?void 0:p.type)&&"Disband"!==(null===(f=u[T])||void 0===f?void 0:f.type)?"Destroy"===(null===(h=u[e.terrID])||void 0===h?void 0:h.type)||"Destroy"===(null===(I=u[T])||void 0===I?void 0:I.type)?S=y.DISBANDED:"Yes"===e.retreating?S=y.DISLODGED:c[e.terrID]>=2&&(S=y.DISLODGING):S=y.DISBANDED;var Z=e.terrID in s?s[e.terrID]:null;a.push({country:g.Z[m],mappedTerritory:d,unit:l,drawMode:S,movedFromTerrID:Z})}}}})),o.forEach((function(e,n){if(e.type.startsWith("Build ")){var o=r[e.terrID],i={id:(n+1e5).toString(),countryID:e.countryID.toString(),type:e.type.split(" ")[1],terrID:e.terrID.toString()};if(o){var c=x.ZP[x.A8[o.name]];if(c){var s=t.find((function(r){return r.countryID.toString()===i.countryID}));if(s){var u=s.country;a.push({country:g.Z[u],mappedTerritory:c,unit:i,drawMode:y.BUILD,movedFromTerrID:null})}}}}})),a}(m.data.territories,c,h.members,s,a.orders,S),d={};return a.centers.forEach((function(r){d[S.terrIDToProvince[r.terrID]]={ownerCountryID:r.countryID.toString()}})),{phase:a.phase,units:u,orders:a.orders,centersByProvince:d,isLatestPhase:!1}}(),P=Z.phase,O=Z.units,w=Z.orders,E=Z.centersByProvince,j=Z.isLatestPhase,b=m.data.territories;return o.useLayoutEffect((function(){if(r.current){var e=i.Ys(r.current),n=e.select("#container"),o=function(r,e,t,n){var o=t,i=r.height*t;i<n.height&&(o=t+(1-i/n.height));var a,c=e.height*o,s=e.y*o,u=e.width*o,l=e.x*o,d=Math.abs(n.width-u),v=Math.abs(d/2),D=Math.abs(n.height-c),y=Math.abs(D/2),p=(r.y+r.height-(e.y+e.height))*o;return n.height>=c?(a=-s+y,y>p&&(a+=y-p)):a=-s-y,{scale:o,x:n.width>=u?-l+v:-l-v,y:a}}(n.node().getBBox(),e.select("#playableProvinces").node().getBBox(),D,t),a=o.scale,c=o.x,s=o.y,u=i.sPX().translateExtent([[0,0],[6010,3005]]).scaleExtent([a,p]).clickDistance(3).on("zoom",(function(r){var e=r.transform;n.attr("transform",e)}));e.on("wheel",(function(r){return r.preventDefault()})).call(u).call(u.transform,i.CRH.translate(c,s).scale(a)).on("dblclick.zoom",null)}}),[r,t]),o.useEffect((function(){setTimeout((function(){a(c.I9.updateOrdersMeta(l))}),500)}),[]),o.useEffect((function(){var r=function(r){27===(r.which||r.keyCode)&&(r.preventDefault(),a(c.I9.resetOrder()))};return window.addEventListener("keydown",r),function(){return window.removeEventListener("keydown",r)}})),(0,u.jsx)("div",{style:{width:t.width,height:t.height},children:(0,u.jsx)(er,{ref:r,units:O,phase:P,orders:w,maps:S,territories:b,centersByProvince:E,isLatestPhase:j})})}}}]);
//# sourceMappingURL=728.52d7b376.chunk.js.map