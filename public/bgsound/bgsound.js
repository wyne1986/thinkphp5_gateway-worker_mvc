/*
* *背景语音提示插件,兼容手机端
* 引入插件后调用bgsound.init();方法即可
* 播放语音 bgsound.src('语音文件地址');
* 如需重新显示提示框,则调用bgsound.show();
* */
var bgsound = {
    welcome:'/public/static/bgsound/openmsg.mp3',
    title:'是否开启语音提示？',
    playable:false,
    initshow:true,
    callback:function(){},
    divdom:null,
    bgdom:null,
    init:function(obj,callback){
        bgsound.welcome = (typeof obj == "object" && typeof obj.welcome == 'string') ? obj.welcome : "/public/static/bgsound/openmsg.mp3";
        bgsound.title = (typeof obj == "object" && typeof obj.title == 'string') ? obj.title : "是否开启语音提示？";
        bgsound.callback = (typeof obj == "object" && typeof obj.callback == 'function') ? obj.callback : function(){};
        bgsound.initshow = (typeof obj == "object" && typeof obj.initshow == 'boolean') ? obj.initshow : true;
        var bg_audio_style = document.createElement('style');
        bg_audio_style.innerHTML = '#bg_audio_main{position:fixed;top:20%;width:160px;height:200px;border:1px solid gray;z-index:99999;background-color:#333;}' +
            '#bg_audio_title{position:relative;width:100%;height:auto;text-align:center;color:white;padding:50px 0;}' +
            '#bg_audio_btn{width:60%;margin:0 auto;}' +
            '#bg_audio_yes_div{width:36px;height:32px;position:absolute;margin:0 auto;border:1px solid gray;border-radius:6px;z-index:9999;}' +
            '#bg_audio_no{z-index:99999;position:absolute;margin-left:40%;text-align:center;line-height:32px;color:white;border:1px solid gray;width:36px;height:32px;overflow: hidden;}' +
            '#bg_audio_player{position:relative;left:0;top:0;margin:0;padding:0;width:100px;height:32px;opacity: 0;z-index:9999;}' +
            '#bg_audio_yes{position:absolute;top:0;left:0;text-align:center;line-height:32px;color:white;width:36px;height:32px;}';
        document.body.appendChild(bg_audio_style);
        var bg_audio_main = document.createElement('div');
        bg_audio_main.id = 'bg_audio_main';
        var awidth = document.body.clientWidth;
        bg_audio_main.style.left = (awidth-200)/2+'px';
        bg_audio_main.style.display = 'none';
        document.body.appendChild(bg_audio_main);
        var bg_audio_title = document.createElement('div');
        bg_audio_title.id = 'bg_audio_title';
        bg_audio_title.innerHTML = bgsound.title;
        bg_audio_main.appendChild(bg_audio_title);
        var bg_audio_btn = document.createElement('div');
        bg_audio_btn.id = 'bg_audio_btn';
        bg_audio_main.appendChild(bg_audio_btn);
        var bg_audio_yes_div = document.createElement('div');
        bg_audio_yes_div.id = 'bg_audio_yes_div';
        bg_audio_btn.appendChild(bg_audio_yes_div);
        var bg_audio_no = document.createElement('div');
        bg_audio_no.id = 'bg_audio_no';
        bg_audio_no.innerHTML = '否';
        bg_audio_btn.appendChild(bg_audio_no);
        var bg_audio_player = document.createElement('audio');
        bg_audio_player.id = 'bg_audio_player';
        bg_audio_player.controls = 'controls';
        bg_audio_player.src = bgsound.welcome;
        bg_audio_yes_div.appendChild(bg_audio_player);
        var bg_audio_yes = document.createElement('div');
        bg_audio_yes.id = 'bg_audio_yes';
        bg_audio_yes.innerHTML = '是';
        bg_audio_player.addEventListener('play',function(){bgsound.hide();bgsound.playable = true;});
        bg_audio_no.addEventListener('click',function(){bgsound.hide();bgsound.playable = false;});
        bg_audio_yes_div.appendChild(bg_audio_yes);
        bgsound.bgdom = bg_audio_player;
        bgsound.divdom = bg_audio_main;
        if(bgsound.initshow) bgsound.show();
        bgsound.callback(bgsound);
        return bg_audio_player;
    },
    show:function(){
        bgsound.divdom.style.display = 'block';
    },
    hide:function(){
        bgsound.divdom.style.display = 'none';
    },
    src:function(src){
        if(typeof src == 'string' && bgsound.bgdom instanceof HTMLAudioElement && bgsound.playable){
            bgsound.bgdom.src = src;
            bgsound.bgdom.play();
        }
    }
}