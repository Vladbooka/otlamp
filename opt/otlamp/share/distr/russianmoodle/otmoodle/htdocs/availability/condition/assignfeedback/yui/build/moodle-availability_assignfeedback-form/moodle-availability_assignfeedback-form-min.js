YUI.add("moodle-availability_assignfeedback-form",function(o,e){M.availability_assignfeedback=M.availability_assignfeedback||{},M.availability_assignfeedback.form=o.Object(M.core_availability.plugin),M.availability_assignfeedback.form.initInner=function(e){var a,s,i,n,l,t,o;if(this.assigns={values:[],options:[],feedbacks:[]},e!==undefined&&e.suitablemodules!==undefined&&0<e.suitablemodules.length){for(a=[],s=[],i=[],n=0;n<e.suitablemodules.length;n++)for(t in l=e.suitablemodules[n],s.push(l.cmid),a.push('<option value="'+l.cmid+'" >'+l.name+"</option>"),i[l.cmid]={options:[],values:[]},l.feedbacks)o=l.feedbacks[t],i[l.cmid].options.push('<option value="'+t+'">'+o+"</option>"),i[l.cmid].values.push(t);this.assigns={values:s,options:a,feedbacks:i}}},M.availability_assignfeedback.form.getNode=function(e){var a,s,i,n,l=M.str.availability_assignfeedback,t="";return 0==this.assigns.options.length&&(t=' disabled="disabled" '),a='<select name="assign" '+t+' class="custom-select mx-1"><option value="0">'+l.chooseassign+"</option>"+this.assigns.options.join("")+"</select>",s='<select name="assignfeedback" class="custom-select mx-1">'+('<option value="0" selected="selected">'+l.chooseassignfeedback+"</option>")+"</select>",i=l.inassign+a+l.needfeedback+s,n=o.Node.create('<div class="form-group">'+i+"</div>"),e.assign!==undefined&&-1!==this.assigns.values.indexOf(e.assign)&&n.one("select[name=assign]").set("value",e.assign),M.availability_assignfeedback.form.addedEvents||(M.availability_assignfeedback.form.addedEvents=!0,o.one(".availability-field").delegate("valuechange",function(){M.core_availability.form.update()},".availability_assignfeedback select")),n.one("select[name=assign]").on("change",function(){var e=n;M.availability_assignfeedback.form.loadAssignFeedbacks(e)}),M.availability_assignfeedback.form.loadAssignFeedbacks(n,e.assignfeedback),n},M.availability_assignfeedback.form.loadAssignFeedbacks=function(e,a){var s,i,n,l,t=M.str.availability_assignfeedback,o=e.one("select[name=assign]"),d=e.one("select[name=assignfeedback]");void 0===a?null!==(i=d.one("option:checked"))&&(s=i.get("value")):s=a,null!==o&&null!==d&&(null!==(n=o.one("option:checked"))&&0!==n.get("value")?(d.removeAttribute("disabled"),l='<option value="0" selected="selected">'+t.chooseassignfeedback+"</option>","undefined"!=typeof this.assigns.feedbacks[n.get("value")]&&d.setHTML(l+this.assigns.feedbacks[n.get("value")].options.join("")),null!==s&&-1!==("undefined"==typeof this.assigns.feedbacks[n.get("value")]?-1:this.assigns.feedbacks[n.get("value")].values.indexOf(s))&&d.set("value",s)):d.set("disabled","disabled"))},M.availability_assignfeedback.form.fillValue=function(e,a){var s,i=a.one("select[name=assign] option:checked");null!==i&&(e.assign=i.get("value")),null!==(s=a.one("select[name=assignfeedback] option:checked"))&&(e.assignfeedback=s.get("value"))},M.availability_assignfeedback.form.fillErrors=function(e,a){var s=parseInt(a.one("select[name=assign]").get("value"),10);0===s&&e.push("availability_assignfeedback:error_selectcmid"),0===parseInt(a.one("select[name=assignfeedback]").get("value"),10)&&e.push("availability_assignfeedback:error_selectfeedbackcode")}},"@VERSION@",{requires:["base","node","event","moodle-core_availability-form"]});