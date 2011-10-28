
//---------------

var domWatcher=new DOMWatcher();

function DOMWatcher() {}

DOMWatcher.prototype.waitForLoad=function(fnc) {
  if(fnc == null) {
alert('ERROR: DOMWatcher\n CallBack function is null\nCheck the first argument of waitForLoad()');
    return;
  }
  this.fnc=fnc;
  if(typeof window.addEventListener != 'undefined') {
//    window.addEventListener('DOMContentLoaded',this.fnc,false);
    window.addEventListener('load',this.fnc,false);
  } else {
    if(typeof window.attachEvent != 'undefined') {
      window.attachEvent('onload',this.fnc);
    } else {
alert('ERROR: DOMWatcher\nCannot set WaitForLoad');
    }
  }
}

//---------------

function Monitor(pageHandler,last,mode,bypass) {
  this.lastFocus = last;
  this.nextFocus = null;
  this.mode = mode;
  this.bypass = bypass;
  this.pageHandler = pageHandler;
  this.messageStyle = 'POST';
  domWatcher.waitForLoad(this.eventCallback);
};

Monitor.identity=function() {
  return 'CRT VieleRETS SOA Interface, Version ' + Monitor.rawVersion();
};

Monitor.rawVersion=function() {
  return '1.1.7';
};

Monitor.initialize=function(target) {
  if (!target.pageHandler.isActive()) {
    return
  }
  Monitor.seedDom(target);
  target.capture('FOCUS','PAGE', 'NULL',document.URL);
}

Monitor.seedDom=function(target) {
//
// set last focus 
//
  var inputs = document.getElementsByTagName('input'); 
  for(var i=0;i < inputs.length;i++) {
    var trackingName=inputs[i].name;
    if (trackingName == '') {
      trackingName=inputs[i].type;
    }
    if (trackingName == target.lastFocus) {
      var found = false;
      var inputsx = document.getElementsByTagName('input'); 
      for(var j=0;j < inputsx.length;j++) {
        var trackingName=inputsx[j].name;
        if (trackingName == '') {
          trackingName=inputsx[j].type;
        }
        if (trackingName == inputs[i].value) {
          found = true;
          inputsx[j].focus();
          break;
        }
      }
      if (!found) {
        var selects = document.getElementsByTagName('select'); 
        for(var j=0;j < selects.length;j++) {
          if (selects[j].name == inputs[i].value) {
             selects[j].focus();
             break;
          }
        }
      }
      break;
    }
  }

//
// array of elements that bypass AJAX processing
//
  var bypass_array=[];
  if (target.bypass == ''){
alert('AJAX_BYPASS  not defined');
  }
  var inputs = document.getElementsByTagName('input'); 
  for(var i=0;i < inputs.length;i++) {
    if (inputs[i].type == 'hidden') {
      if (inputs[i].name == target.bypass) {
        var proceed = true;
        var buffer = inputs[i].value;
        var bypass_count = 0;
        while (proceed) {
          var marker = buffer.indexOf(',');
          if (marker > 0) {
            var chunk = buffer.substr(0, marker);
            bypass_array[bypass_count] = chunk;
            buffer = buffer.substr(marker + 1,buffer.length);
          } else {
            proceed = false;
          }
          ++bypass_count;
        }
      }
    }
  }

//
// process inputs 
//
  var inputs = document.getElementsByTagName('input'); 
  for(var i=0;i < inputs.length;i++) {

//
// check against bypass array
//
    var found = false;
    for(var j=0;j < bypass_array.length;j++) {
      if (inputs[i].name == bypass_array[j]) {
        found = true;
      }
    }
    if (!found){
      if (inputs[i].name == '') {
alert('widget ' + inputs[i].type + ' is not named');
      }
//alert('process widget ' + inputs[i].name);
      switch(inputs[i].type) {
        case 'button':
          target.addActionEvent(inputs[i],'click','CLICK','BUTTON',inputs[i].name,document.URL);
          break;
        case 'checkbox':
          target.addActionEvent(inputs[i],'click','CHANGE','INPUT',inputs[i].name,document.URL);
          break;
        case 'radio':
          target.addActionEvent(inputs[i],'click','CHANGE','INPUT',inputs[i].name,document.URL);
          break;
        case 'reset':
          break;
        case 'submit':
          break;
        case 'text':
          target.addActionEvent(inputs[i],'change','CHANGE','INPUT',inputs[i].name,document.URL);
          break;
        case 'textarea':
          target.addActionEvent(inputs[i],'change','CHANGE','INPUT',inputs[i].name,document.URL);
        case 'hidden':
          break;
        default:
alert('widget ' + inputs[i].type + ' is not handled');
      }
      target.addFocusEvent(inputs[i],document.URL);
    }
  } 

//
// process selects
//
  var selects = document.getElementsByTagName('select'); 
  for(var i=0;i < selects.length;i++) {
    target.addActionEvent(selects[i],'change','CHANGE','SELECT',selects[i].name,document.URL);
    target.addFocusEvent(selects[i],document.URL);
  } 
 
}

Monitor.prototype.addFocusEvent=function(element) {
  var fnc=new Function("monitor.captureFocus('"+element.name+"');");
  evt='mouseover';
  if(typeof element.removeEventListener != 'undefined') {
    element.removeEventListener(evt,fnc,false);
    element.addEventListener(evt,fnc,false);
  } else {
    if(typeof element.attachEvent != 'undefined') {
      element.detachEvent('on'+evt,fnc);
      element.attachEvent('on'+evt,fnc);
    }
  }
}

Monitor.prototype.captureFocus=function(name) {
  this.nextFocus = name;
}

Monitor.prototype.setMessageStyle=function(value) {
  this.messageStyle=value;
}

Monitor.prototype.addActionEvent=function(element,evt,type,widget,name,page) {
  var fnc=new Function("monitor.capture('"+type+"','"+widget+"','"+name+"','"+page+"');");
  if(typeof element.removeEventListener != 'undefined') {
    element.removeEventListener(evt,fnc,false);
    element.addEventListener(evt,fnc,false);
  } else {
    if(typeof element.attachEvent != 'undefined') {
      element.detachEvent('on'+evt,fnc);
      element.attachEvent('on'+evt,fnc);
    }
  }
}

Monitor.prototype.eventCallback=function(j1) {
  Monitor.initialize(monitor);
}

Monitor.prototype.capture=function(type,widget,name,page) {
//alert(Monitor.identity() + " - capture: " + type + ' ' + widget + ' ' + name + ' ' + page);
//alert(Monitor.identity() + " - capture: " + type + ' ' + widget + ' ' + name + ' ' + page + ' ' + this.nextFocus);
  this.pageHandler.displayWait();

//
// collect dom information 
//
  var index=[];
  var indexCount=0;
  var env=[];
  var multiCount=0;
  var multiFlag=[];
  var multiValue=[];
  var multiVariable=null;
  var multiAction=null;
  var mapCount=0;
  var mapFlag=[];
  var mapValue=[];
  var mapIndex=[];

  var value='NULL';
  var inputs = document.getElementsByTagName('input'); 
  for(var i=0;i < inputs.length;i++) {
    var tempName=inputs[i].name;
    if (tempName == '') {
      tempName=inputs[i].type;
    }

    var temp=null;
    switch(inputs[i].type) {
      case 'radio':
        if (inputs[i].checked) {
          temp=inputs[i].value;
          if (tempName == name) {
            value=temp;
          }
        }
        break;

      case 'checkbox':
        if (tempName.indexOf('SOA_ARRAY__') == 0) {
          var tempName=tempName.substr(11,tempName.length);
          tempName=tempName.substr(tempName.indexOf('__')+2,tempName.length);
          var found=false;
          for(var j=0;j < multiFlag.length;j++) {
            if (multiFlag[j] == tempName) {
              found=true;
              break;
            }
          }
          if (!found) {
            multiFlag[multiCount]=tempName;
            multiValue[tempName]='';
            ++multiCount;
          }
          if (inputs[i].checked) {
            multiValue[tempName]+=inputs[i].value+','; 
          }
        } else {
          if (inputs[i].checked) {
            temp='true';
          } else {
            temp='false';
          }
          if (tempName == name) {
            value=temp;
          }
        }
        break;

      case 'text':
        if (tempName.indexOf('SOA_MAP__') == 0) {
          if (inputs[i].value.length > 0) {
            var tempName=tempName.substr(9,tempName.length);
            mapVariable=tempName.substr(tempName.indexOf('__')+2,tempName.length);
            mapAction=tempName.substr(0,tempName.indexOf(mapVariable)-2);
            tempName = mapVariable;
            var found=false;
            for(var j=0;j < mapFlag.length;j++) {
              if (mapFlag[j] == tempName) {
                found=true;
                break;
              }
            }
            if (!found) {
              mapFlag[mapCount]=tempName;
              mapValue[tempName]='';
              mapIndex[tempName]='';
              ++mapCount;
            }
            mapValue[tempName]+=inputs[i].value+','; 
            mapIndex[tempName]+=mapAction+','; 
          }
        } else {
          temp=inputs[i].value;
          if (tempName == name) {
            value=temp;
          }
        }
        break;

      case 'button':
        if (name == tempName) {
          if (tempName.indexOf('SOA_MULTI__') == 0) {
            tempName=name.substr(11,name.length);
            multiVariable=tempName.substr(tempName.indexOf('__')+2,tempName.length);
            multiAction=tempName.substr(0,tempName.indexOf(multiVariable)-2);
            if (multiAction == 'FILTER') {
              tempName='SOA_ACTION';
              temp='FILTER__' + multiVariable;
              value=temp;
            }
          } else {
            if (tempName.indexOf('SOA_BUTTON__') == 0) {
                 tempName='SOA_ACTION';
                 temp=name.substr(12,name.length);
                 value=temp;
            } else {
                temp=inputs[i].value;
                value=temp;
            }
          }
        }
        break;

      default:
        temp=inputs[i].value;
    }
    if (temp != null) {
      env[tempName]=temp;
      index[indexCount]=tempName;
      ++indexCount;
    }
  }

  if (multiCount > 0) { 
    if (multiAction == 'ALL' || multiAction == 'NONE') {
      for(var i=0;i < multiFlag.length;i++) {
        if (multiFlag[i] == multiVariable) {
          var inputs = document.getElementsByTagName('input'); 
          for(var j=0;j < inputs.length;j++) {
            if (inputs[j].type == 'checkbox') {
              if (inputs[j].name.indexOf('SOA_ARRAY__') == 0) {
                tempName=inputs[j].name.substr(11,inputs[j].name.length);
                tempName=tempName.substr(tempName.indexOf('__')+2,tempName.length);
                if (tempName == multiVariable) {
                  if (multiAction == 'ALL') {
                    multiValue[multiFlag[i]]+=inputs[j].value+',';
                    inputs[j].checked = true;
                  } else {
                    inputs[j].checked = false;
                  } 
                }
              }
              if (multiAction == 'NONE') {
                multiValue[multiFlag[i]]='';
              }
            }
          }
        }
      }
    }
    var tempName;
    if (name.indexOf('SOA_ARRAY__') == 0) {
      tempName=name.substr(11,name.length);
      tempName=tempName.substr(tempName.indexOf('__')+2,tempName.length);
    }
    for(var i=0;i < multiFlag.length;i++) {
      var temp=multiValue[multiFlag[i]]; 
      temp=temp.substring(0,temp.length-1);
      env[multiFlag[i]]=temp;
      index[indexCount]=multiFlag[i];
      ++indexCount;
      if (multiFlag[i] == tempName) {
        if (name.indexOf(tempName) > 0) {
          value = temp;
          name =tempName;
        }
      }
    }
  }

  var selects=document.getElementsByTagName('select'); 
  for(var i=0;i < selects.length;i++) {
    var temp = selects[i].value;
    var tempName=selects[i].name;
    if (selects[i].type != 'select-one') {
      temp='';
      var options = selects[i].childNodes;
      for(var j=0;j < options.length;j++) {
        if (options[j].selected) {
          temp+=options[j].value+',';
        }
      }
      temp=temp.substring(0,temp.length-1);
      tempName=tempName.substring(0,tempName.length-2);
    } else {
      if (tempName.indexOf('SOA_MAP__') == 0) {
        var tempName=tempName.substr(9,tempName.length);
        mapVariable=tempName.substr(tempName.indexOf('__')+2,tempName.length);
        mapAction=tempName.substr(0,tempName.indexOf(mapVariable)-2);
        tempName = mapVariable;
        var found=false;
        for(var j=0;j < mapFlag.length;j++) {
          if (mapFlag[j] == tempName) {
            found=true;
            break;
          }
        }
        if (!found) {
          mapFlag[mapCount]=tempName;
          mapValue[tempName]='';
          mapIndex[tempName]='';
          ++mapCount;
        }
        mapValue[tempName]+=selects[i].value+','; 
        mapIndex[tempName]+=mapAction+','; 
      }
    }
    if (tempName == name) {
      value=temp;
    }
    env[tempName]=temp;
    index[indexCount]=tempName;
    ++indexCount;
  }

  if (mapCount > 0) { 
    var tempName;
    if (name.indexOf('SOA_MAP__') == 0) {
      tempName=name.substr(9,name.length);
      tempName=tempName.substr(tempName.indexOf('__')+2,tempName.length);
    }
    for(var i=0;i < mapFlag.length;i++) {
      var dataName = mapFlag[i];
      var temp=mapValue[dataName];
      temp=temp.substring(0,temp.length-1);
      env[dataName]=temp;
      index[indexCount]=dataName;
      ++indexCount;
      if (dataName == tempName) {
        if (name.indexOf(dataName) > 0) {
          value = temp;
          name = dataName;
        }
      }
      var tempIndex=mapIndex[dataName]; 
      tempIndex=tempIndex.substring(0,tempIndex.length-1);
      var indexName = dataName + '_INDEX';
      env[indexName]=tempIndex;
      index[indexCount]=indexName;
      ++indexCount;
    }
  }

//
// get mode 
//
  var foundMode = false;
  var inputs = document.getElementsByTagName('input'); 
  for(var i=0;i < inputs.length;i++) {
    var trackingName=inputs[i].name;
    if (trackingName == '') {
      trackingName=inputs[i].type;
    }
    if (trackingName == this.mode) {
      foundMode = true;
      break;
    }
  }
//
// blank out div
//
/*
  var aDoc = document.getElementById(this.pageHandler.widget);
  var aParent = aDoc.parentNode;
  var newDiv=document.createElement('div');
  newDiv.id=this.pageHandler.widget;
  newDiv.innerHTML='';
  aParent.replaceChild(newDiv, aDoc);
*/
//
// create a socket to the HTTP server
//
  var xhttp;
  if (window.XMLHttpRequest) {
  // If IE7, Mozilla, Safari, and so on: Use native object.
    xhttp = new XMLHttpRequest();
  } else {
    if (window.ActiveXObject) {
      var msxmlhttp = new Array('Msxml2.XMLHTTP.5.0',
                                'Msxml2.XMLHTTP.4.0',
                                'Msxml2.XMLHTTP.3.0',
                                'Msxml2.XMLHTTP',
                                'Microsoft.XMLHTTP');
      for (var i = 0; i < msxmlhttp.length; i++) {
        try {
          xhttp = new ActiveXObject(msxmlhttp[i]);
          break;
        } 
        catch (e) {
          xhttp = null;
        }
      }
if(xhttp == null) {
alert(Monitor.identity() + " - error finding HTTP Requestor");
exit();
}
    }
  }

//
// define a callback handler
//
  var handler = this.pageHandler;
  xhttp.onreadystatechange = function() {
//
//   0: Uninitialized
//   1: Loading
//   2: Loaded
//   3: Interactive
//   4: Finished
//  alert(Monitor.identity() + " - state: " + xhttp.readyState);
    if(xhttp.readyState == 4) {
      try {
//alert(Monitor.identity() + " - response: " + xhttp.status);
        switch (xhttp.status) {
          case 0:
            break;

          case 200:
//alert(Monitor.identity() + " - response: " + xhttp.responseText);
            if (xhttp.responseText != '') {
              var xdom;
              var xroot;
              if (window.DOMParser) {
                xdom = new DOMParser();
                xroot = xdom.parseFromString(xhttp.responseText, 'text/xml')
                xroot = xroot.childNodes[0]; 
              } else {
                if (window.ActiveXObject) {
                  var msxmldom = new Array('MSXML2.DomDocument',
                                           'Microsoft.DomDocument',
                                           'MSXML.DomDocument',
                                           'MSXML3.DomDocument');
                  for (var i = 0; i < msxmldom.length; i++) {
                    try {
                      xdom = new ActiveXObject(msxmldom[i]);
                      break;
                    } 
                    catch (e) {
                      xdom = null;
                    }
                  }
if(xdom == null) {
alert(Monitor.identity() + " - error finding XML Parser");
exit(0);
}
                  xdom.async='false'; 
                  xdom.loadXML(xhttp.responseText); 
                  xroot = xdom.documentElement;
                }
              }

              handler.receive(xroot);
              delete xdom;
//              exit(0);
          
            }
            break;

          case 404:
alert(Monitor.identity() + '\n' +
      'Server Error - HTTP ' + xhttp.status + 
      ' - Calling handler [' + handler.getName() + ']');
            break;

          case 500:
            break;

          default:
alert(Monitor.identity() + '\n' +
      'Server Error - HTTP ' + xhttp.status + 
      ' Calling handler [' + handler.getName() + ']');
        }
      }
      catch (focus_e) {
//alert(focus_e);
      }
    }
  }

//
// send a message the the HTTP server
//
  var args='viele_type=' + type + 
           '&viele_widget=' + widget + 
           '&viele_name=' + name +
           '&viele_value=' + encodeURIComponent(value) +
           '&viele_page=' + page +
           '&viele_nextFocus=' + this.nextFocus +
           '&viele_time=' + (new Date()).getTime() +
           '&viele_version=' + Monitor.rawVersion();
  for(var i=0;i < index.length;i++) {
    args+='&viele_env['+index[i]+']='+encodeURIComponent(env[index[i]]);
  }
//  if (this.pageHandler.isMonitored()) {
//    args+='&viele_verbose=true';
//  }
  if (foundMode) {
    args+='&viele_mode=true';
  }
//alert(Monitor.identity() + " - args: " + args);

//  var display='';
//  for(var i=0;i < index.length;i++) {
//    display+='&viele_env['+index[i]+']='+env[index[i]] + "\n";
//  }
//alert(Monitor.identity() + " - args: " + display);

//  xhttp.abort();
  if (this.messageStyle == 'GET') {
    xhttp.open('get',operatingHandler + '?' + args,true);
    xhttp.send('?' + args);
  } else {
    xhttp.open('post',operatingHandler,true);
    xhttp.setRequestHeader('Content-Type','application/x-www-form-urlencoded');
    xhttp.send(args);
  }
  delete xhttp;

}

//----------------

function PageHandler(widget,navbarWidget) {
  this.name='Page Handler';
  this.widget=widget;
  this.navbarWidget=navbarWidget;
// status 
  this.trackStatus=false;
// monitor window
  this.setMonitoring(false);
}

PageHandler.prototype.setTrackStatus=function(value,floatWidget) {
  if (value) {
    this.trackStatus=true;
    this.floatWidget=floatWidget;
    this.statusWidth=500;
//    this.statusHeight=70;
    this.statusFontHeight=14;
  } else {
    this.trackStatus=false;
  }
}

PageHandler.prototype.setMonitoring=function(value) {
  if (value) {
    this.monitored=true;
    this.monitorWidth=830;
    this.monitorHeight=150;
    this.clear();
  } else {
    this.monitored=false;
  }
}

PageHandler.prototype.isMonitored=function() {
  return this.monitored;
}

PageHandler.prototype.isActive=function() {
  var aDoc = document.getElementById(this.widget);
  if (aDoc == null) {
    return false;
  }
  return true;
}

PageHandler.prototype.getName=function() {
  return this.name;
}

PageHandler.prototype.clear=function() {
  this.display('');
}

PageHandler.prototype.displayWait=function() {
  if (this.trackStatus) {
    this.process_blanket(this.floatWidget);
    var blanket = document.getElementById('blanket');
    if (!blanket.filters) {
      blanket.style.opacity = '0.40';
    } else {
      blanket.style.filter= 'alpha(opacity=40)';
    }
    blanket.style.display = 'block';
    var el = document.getElementById(this.floatWidget);
    if (el.filters) {
      for(var i=0; i<document.images.length; i++) {
        var img = document.images[i];
        var imgName = img.src.toUpperCase();
        if (imgName.substring(imgName.length-3, imgName.length) == 'PNG') {

//alert(img.src + ' ' + img.width + ' ' + img.height);
//alert(img.src + ' ' + img.style.width + ' ' + img.style.height);
//         var strNewHTML = '<span ' 
//         + ' style="width:412px;height:174px;' 
//         + 'filter:progid:DXImageTransform.Microsoft.AlphaImageLoader'
//         + '(src=\'' + img.src + '\', sizingMethod=\'scale\');"></span>'; 
//         var strNewHTML =  '<img src="' + img.src + '"/>';
//         img.outerHTML = strNewHTML
//alert(strNewHTML);
//         img.style.display = 'inline-block'; 

//         img.style.width = '412px';
//         img.style.height = '174px'; 
//         img.style.filter = "progid:DXImageTransform.Microsoft.AlphaImageLoader(src='" + 
//                            img.src + "', sizingMethod='scale')"; 

//         img.style.filter = "progid:DXImageTransform.Microsoft.AlphaImageLoader(src='" + 
//                            img.src + "')"; 
        }
      }
    }
    el.style.display = 'block';
    this.opacIn = 0;
    this.fadeIn();
  }
}

PageHandler.prototype.fadeIn=function(){
//alert('image ' + statusImage);
  if(this.opacIn < 100){
    this.opacIn+=20;
    var el = document.getElementById(this.floatWidget);
    if (el.filters) {
/*
      this.opac+=2;
      try {
        var travel = document.body.parentNode.scrollWidth
                   - el.style.width.substring(0,el.style.width.indexOf('px'))
                   -20;
        el.style.left = Math.floor((travel * this.opac)/100) + 'px';
      }
      catch (e) {
alert("catch ie fade: " + e);
      }
      setTimeout('pageHandler.fadeIn()', 200);
*/
      el.style.filter= 'alpha(opacity=' + this.opacIn + ')';
    } else {
      el.style.opacity = this.opacIn/100;
    }
    setTimeout('pageHandler.fadeIn()', 100);
  }
}
//--------

PageHandler.prototype.display=function(text) {
  var paintIt = true;
  if (this.trackStatus) {
//
// stop the fadeIn
//
    if (this.opacOut > 0) {
      paintIt = false;
//
// fade out
//
      var el = document.getElementById(this.floatWidget);
      this.opacOut-=20;
      if (el.filters) {
        el.style.filter= 'alpha(opacity=' + this.opacOut + ')';
      } else {
        el.style.opacity = this.opacOut/100;
      }

//alert('here '+this.opacOut);
//alert(text);
      setTimeout("pageHandler.display('" + text + "')", 60);
//      setTimeout("pageHandler.display(" + text + ")", 100);
    }
  }
  if (paintIt) {
    try {
// application page
      var aDoc = document.getElementById(this.widget);
      var aParent = aDoc.parentNode;
      var newDiv=document.createElement('div');
      newDiv.id=this.widget;
      newDiv.innerHTML=text;
      aParent.replaceChild(newDiv, aDoc);
      if (this.trackStatus) {
        this.process_blanket(this.floatWidget);
        var blanket = document.getElementById('blanket');
        blanket.style.display = 'none';
        var el = document.getElementById(this.floatWidget);
        el.style.display = 'none';
      }
// reload dom
      Monitor.seedDom(monitor);
    }
    catch (e) {
//alert("catch: " + e);
//
// OK to swallow this
//
    }
  }
}

PageHandler.prototype.process_blanket=function(popUpDivVar) {
// calculate blanket height
  if (typeof window.innerWidth != 'undefined') {
    viewportheight = window.innerHeight;
  } else {
    viewportheight = document.documentElement.clientHeight;
  }
  if ((viewportheight > document.body.parentNode.scrollHeight) && (viewportheight > document.body.parentNode.clientHeight))  {
    blanket_height = viewportheight;
  } else {
    if (document.body.parentNode.clientHeight > document.body.parentNode.scrollHeight) {
      blanket_height = document.body.parentNode.clientHeight;
    } else {
      blanket_height = document.body.parentNode.scrollHeight;
    }
  }

// set blanket height
  var blanket = document.getElementById('blanket');
  blanket.style.height = blanket_height + 'px';

// calculate scroll 
  var scrOfY = 0;
  var scrOfX = 0;
  if( typeof( window.pageYOffset ) == 'number' ) {
    //Netscape compliant
    scrOfY = window.pageYOffset;
    scrOfX = window.pageXOffset;
  } else if( document.body && ( document.body.scrollLeft || document.body.scrollTop ) ) {
    //DOM compliant
    scrOfY = document.body.scrollTop;
    scrOfX = document.body.scrollLeft;
  } else if( document.documentElement && ( document.documentElement.scrollLeft || document.documentElement.scrollTop ) ) {
    //IE6 standards compliant mode
    scrOfY = document.documentElement.scrollTop;
    scrOfX = document.documentElement.scrollLeft;
  }

  if (typeof window.innerWidth != 'undefined') {
    viewportwidth = window.innerHeight;
  } else {
// IE 6
    viewportwidth = document.documentElement.clientHeight;
//    blanket.style.width = 0;
    blanket.style.width = document.body.parentNode.scrollWidth;
    blanket.style.height = document.body.parentNode.scrollHeight + scrOfY;
  }

  if ((viewportwidth > document.body.parentNode.scrollWidth) && (viewportwidth > document.body.parentNode.clientWidth)) {
    window_width = viewportwidth;
  } else {
    if (document.body.parentNode.clientWidth > document.body.parentNode.scrollWidth) {
      window_width = document.body.parentNode.clientWidth;
    } else {
      window_width = document.body.parentNode.scrollWidth;
    }
  }

// set popup height
  var popUpDiv = document.getElementById(popUpDivVar);
  var font_height = this.statusFontHeight;
//  popUpDiv.style.height = (font_height*2) + (popUpDiv.style.padding*2);
  popUpDiv.style.padding = font_height;
  popUpDiv.style.height = font_height*4;
  popUpDiv.style.width = this.statusWidth;
  var pop_width = popUpDiv.style.width.substring(0,popUpDiv.style.width.indexOf('px'));
  popUpDiv_width=(window_width/2)-(pop_width/2);
  popUpDiv.style.top = (scrOfY + 250) + 'px';
  popUpDiv.style.left = popUpDiv_width + 'px';
}

PageHandler.prototype.displayError=function(text) {
  alert(Monitor.identity() + "\r\n ERROR - " + text);
}

PageHandler.prototype.receive=function(response) {
//
// stop fadein and initialize fadeOut
//
  this.opacOut = this.opacIn;
  this.opacIn = 100;

//
// process response
//
  for(var j=0;j < response.childNodes.length;j++) {
    var tag=response.childNodes[j]; 
    switch (tag.nodeName) {
      case 'ERROR':
        this.displayError(tag.childNodes[0].nodeValue);
        break;

      case 'HTML':
        this.display(tag.childNodes[0].nodeValue);
        break;

      case 'MONITOR':
        if (this.isMonitored()) {
          var aHandle = window.open('',this.widget+'_monitor','width='+this.monitorWidth+',height='+this.monitorHeight);
          aHandle.document.write('<html><title>' + this.name + '</title><body>'+tag.childNodes[0].nodeValue+'<br/><a href="." onClick="javascript:window.close();return false;">Close Monitor</a></body></html>');
          aHandle.document.close();
        }
        break;

      case 'UPDATE':
        var found = false;
        for(var i=0;i < tag.childNodes.length;i++) {
          var name=tag.childNodes[i].getAttribute('name');

//
//  see if there are any changes to "input" types
//
          var inputs = document.getElementsByTagName('input'); 
          for(var k=0;k < inputs.length;k++) {
            if (inputs[k].name == name) {
              switch(inputs[k].type) {
                case 'radio':
                  inputs[k].checked=false;
                  if (inputs[k].value == tag.childNodes[i].getAttribute('value')) {
                    inputs[k].checked=true;
                  }
                  break;

                case 'checkbox':
                  inputs[k].checked=false;
                  if (inputs[k].value == tag.childNodes[i].getAttribute('value')) {
                    inputs[k].checked=true;
                  }
                  break;

                default:
                  inputs[k].value = tag.childNodes[i].getAttribute('value');
              }
              found = true;
              break;
            }
          }

//
//  see if there are any changes to "select" types
//
          if (!found) {
            var selects = document.getElementsByTagName('select'); 
            for(var k=0;k < selects.length;k++) {
              if (selects[k].name == name) {
                selects[k].value = input.value;
                found = true;
                break;
              }
            }
          }
          if (found) {
            break;
          } 
        } 

    }
  }

};

//----------------

