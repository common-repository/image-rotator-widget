(function(a){a.fn.easySlider=function(b){var c={prevId:"prevBtn",prevText:"Previous",nextId:"nextBtn",nextText:"Next",controlsShow:!0,controlsBefore:"",controlsAfter:"",controlsFade:!0,firstId:"firstBtn",firstText:"First",firstShow:!1,lastId:"lastBtn",lastText:"Last",lastShow:!1,vertical:!1,speed:800,auto:!1,pause:2e3,continuous:!1,numeric:!1,numericId:"controls"},b=a.extend(c,b);this.each(function(){function l(c){c=parseInt(c)+1,a("li","#"+b.numericId).removeClass("current"),a("li#"+b.numericId+c).addClass("current")}function m(){i>h&&(i=0),i<0&&(i=h),b.vertical?a("ul",c).css("margin-left",i*f*-1):a("ul",c).css("margin-left",i*e*-1),g=!0,b.numeric&&l(i)}function n(d,j){if(g){g=!1;var k=i;switch(d){case"next":i=k>=h?b.continuous?i+1:h:i+1;break;case"prev":i=i<=0?b.continuous?i-1:0:i-1;break;case"first":i=0;break;case"last":i=h;break;default:i=d}var l=Math.abs(k-i),q=l*b.speed;b.vertical?(p=i*f*-1,a("ul",c).animate({marginTop:p},{queue:!1,duration:q,complete:m})):(p=i*e*-1,a("ul",c).animate({marginLeft:p},{queue:!1,duration:q,complete:m})),!b.continuous&&b.controlsFade&&(i==h?(a("a","#"+b.nextId).hide(),a("a","#"+b.lastId).hide()):(a("a","#"+b.nextId).show(),a("a","#"+b.lastId).show()),i==0?(a("a","#"+b.prevId).hide(),a("a","#"+b.firstId).hide()):(a("a","#"+b.prevId).show(),a("a","#"+b.firstId).show())),j&&clearTimeout(o),b.auto&&d=="next"&&!j&&(o=setTimeout(function(){n("next",!1)},l*b.speed+b.pause))}}var c=a(this),d=a("li",c).length,e=a("li",c).width(),f=a("li",c).height(),g=!0;c.width(e),c.height(f),c.css("overflow","hidden");var h=d-1,i=0;a("ul",c).css("width",d*e),b.continuous&&(a("ul",c).prepend(a("ul li:last-child",c).clone().css("margin-left","-"+e+"px")),a("ul",c).append(a("ul li:nth-child(2)",c).clone()),a("ul",c).css("width",(d+1)*e)),b.vertical||a("li",c).css("float","left");if(b.controlsShow){var j=b.controlsBefore;b.numeric?j+='<ol id="'+b.numericId+'"></ol>':(b.firstShow&&(j+='<span id="'+b.firstId+'"><a href="javascript:void(0);">'+b.firstText+"</a></span>"),j+=' <span id="'+b.prevId+'"><a href="javascript:void(0);">'+b.prevText+"</a></span>",j+=' <span id="'+b.nextId+'"><a href="javascript:void(0);">'+b.nextText+"</a></span>",b.lastShow&&(j+=' <span id="'+b.lastId+'"><a href="javascript:void(0);">'+b.lastText+"</a></span>")),j+=b.controlsAfter,a(c).after(j)}if(b.numeric)for(var k=0;k<d;k++)a(document.createElement("li")).attr("id",b.numericId+(k+1)).html("<a rel="+k+' href="javascript:void(0);">'+(k+1)+"</a>").appendTo(a("#"+b.numericId)).click(function(){n(a("a",a(this)).attr("rel"),!0)});else a("a","#"+b.nextId).click(function(){n("next",!0)}),a("a","#"+b.prevId).click(function(){n("prev",!0)}),a("a","#"+b.firstId).click(function(){n("first",!0)}),a("a","#"+b.lastId).click(function(){n("last",!0)});var o;b.auto&&(o=setTimeout(function(){n("next",!1)},b.pause)),b.numeric&&l(0),!b.continuous&&b.controlsFade&&(a("a","#"+b.prevId).hide(),a("a","#"+b.firstId).hide())})}})(jQuery)