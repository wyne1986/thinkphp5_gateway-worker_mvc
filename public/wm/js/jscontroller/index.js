/*
* base index controller
* */
var index = {
	/* connect success function */
	connect:function(data){
		console.info('收到连接成功消息');
    },
    /* disconnect function */
	disconnect:function(data){
		console.warn('断开连接');
	},
    /* error message function */
	error:function(data){
		console.error(data.message);
	},
	login:function(data){
        wsocket.isloged = true;
	},
	joinGroup:function(data){

	},
	/* 前台用户调用后台弹窗 */
	showmessage:function(data){

		if(confirm(data.result.text)){
			window.location.href = data.result.url;
		}
		
		/*播放文本声音*/
        var bgurl = 'http://tts.baidu.com/text2audio?lan=zh&ie=UTF-8&spd=6&text='+encodeURI(data.result.text);
        bgsound.src(bgurl);
	}
}