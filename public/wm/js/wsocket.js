/*
* websocket wsocket.js
*
* use wsocket.init(callback function,init param{url,controller dir}); to init websocket connection
*
* use wsocket.doSend(@param); to send your message to server
* @param{'controller':'back server Gateway controllerName','action':'back server Gateway actionName','result':'your datas'}
*
* edit or add file in directory: js/jscontroller/***.js to get server's message and do your things
*
* created by wyne,QQ 366659539
*/
var wsocket ={
	//连接状态
	connected:false,
	debug:true,
	//默认控制器后缀
	cname:'Controller',
	token:{}, /*websocket登录验证用的token,由网页端生成传入*/
    /*是否已连接并登录*/
	isloged:false,
    /*检测断线重连间隔毫秒*/
    reconnect_time:15000,
	//初始化方法的回调方法
	callback:function(){},
	/*检查重新登录方法*/
	checkLogin:function(){
    	if(wsocket.connected == false){
            wsocket.reconnect_count--;
            console.log('重新连接中...');
            wsocket.websocket();
		}else if(!wsocket.isloged){
            wsocket.reconnect_count--;
            console.log('重新登录中...');
            wsocket.callback();
        }
	},
	//初始化方法
	init:function(callback,obj){
		this.url = (typeof obj == "object" && typeof obj.url == 'string') ? obj.url : "ws://127.0.0.1:8282/";	//websocket服务器地址,如 "ws://127.0.0.1:2347/"
		this.jsdoc = (typeof obj == "object" && typeof obj.jsdoc == 'string') ? obj.jsdoc : "/public/wm/js/jscontroller/";	//jscontroller文件夹路径,以"/"结尾,如  "/jscontroller/"
		this.token = (typeof obj == "object" && typeof obj.token == 'object') ? obj.token : {};
		if(this.debug)console.info("连接中...");
		this.websocket();
        this.callback = callback;
	},
	websocket:function(){
		//建立websocket连接
		if(window.MozWebSocket) {
        	websocket = new MozWebSocket(this.url);
        }else if(window.WebSocket){
        	websocket = new WebSocket(this.url);
        }else{
			websocket = null;
			console.error("你的浏览器不支持websocket");
		}
		if(websocket){
			websocket.onopen = function (evt) { wsocket.onOpen(evt) };
			websocket.onclose = function (evt) { wsocket.onClose(evt) };
			websocket.onmessage = function (evt) { wsocket.onMessage(evt) };
			websocket.onerror = function (evt) { wsocket.onError(evt) };
		}
		return websocket;
    },
	//连接
	onOpen:function(evt) {
        wsocket.connected = true;
        if(this.debug)console.info("连接成功");
		//连接成功后执行回调方法
		if(typeof this.callback == 'function')this.callback();
        setInterval(function(){
            wsocket.checkLogin();
        },this.reconnect_time+1000);
    },
	//关闭
    onClose:function(evt){
        wsocket.isloged = false;
        wsocket.connected = false;
        if(this.debug)console.log("断开连接");
	},
	//接收消息并处理
    onMessage:function(evt){
		var message = JSON.parse(evt.data);
		//if(message.status!='0'){message.controller='index';message.action='error';}
		if(eval('typeof '+message.controller+'=="object"')){
			if(eval('typeof '+message.controller+'.'+message.action+'=="function"')){
				eval(message.controller+'.'+message.action+"(message)");
			}else{
				console.error('方法: '+message.action+' 在 '+message.controller+'.js 控制器中不存在');
			}
		}else{
			$.getScript(this.jsdoc+message.controller+'.js').done(function(script, textStatus){
                if(this.debug)console.info('获取控制器文件 : '+message.controller+'.js 成功');
				wsocket.onMessage(evt);
		  	}).fail(function(jqxhr, settings, exception) {
				console.error('获取控制器文件 : '+this.jsdoc.message.controller+'.js 失败,或文件不存在');
		  	});
		}
	},
	//错误
    onError:function(evt){
		console.error('错误: ' + evt.data);
	},
	//发送消息
    doSend:function(data){
		if(wsocket.connected){
			data.result.token = this.token;
			websocket.send(JSON.stringify(data));
		}else{
			console.warn("正在连接中,请稍后");
		}
	}
}