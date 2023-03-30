define(['jquery'], function($) {
   return {
       'addArrayPushEvent': function(arr, eventname){
           var originalPush = arr.push;
           arr.push = function(){
               for(var i = 0; i < arguments.length; i++)
               {
                   $(document).trigger(eventname, [arguments[i]]);
               }
               originalPush.apply(this, arguments);
           };
       },
       'init': function(){

           this.addArrayPushEvent(M.util.pending_js, 'pending_js_pushed');
           this.addArrayPushEvent(M.util.complete_js, 'complete_js_pushed');

       }
   };
});
