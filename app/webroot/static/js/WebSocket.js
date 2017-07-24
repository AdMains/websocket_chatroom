/**
 * Created by pai on 17-7-12.
 */
var socketServer,
webSocket,
host,
uid,
token,
olList = [],    //在线用户
channelList = [],    //频道列表
connected = 0,
defaultChannel,
ownerId;

var chatCMD = {
    LOGIN : 1, //登录
    LOGIN_SUCC : 2, //登录成功
    RELOGIN : 3,      //重复登录
    NEED_LOGIN : 4, //需要登录
    LOGIN_ERROR : 5,  //登录失败
    HB : 6,           //心跳
    CHAT : 7,         //聊天
    OLLIST : 8,      //获取在线列表
    LOGOUT: 9,        //退出
    ERROR : -1,			//错误
    NEWCHANNEL : 11,
    GETCHANNEL : 12,
    DELCHANNEL : 13,
};

init = function (hst,port,id,tk,channel,owner) {
    host = hst;
    uid = id;
    token = tk;
    defaultChannel = channel;//默认频道
    ownerId = owner;//频道房主

    socketServer = 'ws://' + host + ':' + port;
    webSocket = new WebSocket(socketServer);
    webSocket.onopen = onSocketOpen;
    webSocket.onmessage = onSocketMessage;
    webSocket.onclose = onSocketClose;
};

//上线提示，验证权限
onSocketOpen = function (e) {
    var sendObj = {
        type: chatCMD.LOGIN,
        uid: uid,
        token: token,
        channel: defaultChannel,
    };
    webSocket.send(JSON.stringify(sendObj));
};

onSocketClose = function (e) {
    $("#chat_content").append('<p>聊天服务器关闭中.</p>');
    connected = 0;
};

//接收消息
onSocketMessage = function (e) {
    try {
        var data = JSON.parse(e.data);
        switch (data[0]) {
            case chatCMD.LOGIN:
                $("#chat_content").append('<p>欢迎来到WebSocket 聊天室~~</p>');
                break;
            case chatCMD.ERROR:
                alert("请重新连接服务器");
                break;
            case chatCMD.RELOGIN:
                alert("亲，好像掉线了，请重新登录！");
                break;
            case chatCMD.LOGIN_SUCC:
                connected = 1;
                if(data[1][0] == uid) { //自已
                    $("#chat_content").append('<p>成功连接服务器，快去和小伙伴们聊天吧~~</p>');
                    var sendObj = {
                        type: chatCMD.OLLIST,
                        message: [ownerId,defaultChannel]
                    };
                    webSocket.send(JSON.stringify(sendObj));
                    $("#ollist").html('<p>正在获取在线列表</p>');

                } else { //别人
                    $("#chat_content").append('<p>'+data[1][1]+' 来到了聊天室！</p>');
                    olList[data[1][0]] = data[1];//这里是直接给数组新增一项
                    parseOl();
                }
                //获取频道列表，这段代码放在这里，同时也是为了更新频道中房主信息
                sendObj = {
                    type: chatCMD.GETCHANNEL,
                };
                webSocket.send(JSON.stringify(sendObj));
                break;
            case chatCMD.OLLIST:    //获取在线列表
                olList = data[1];
                parseOl();
                break;
            case chatCMD.GETCHANNEL:    //获取频道列表
                channelList = data[1];
                parseChannel();
                break;
            case chatCMD.CHAT:
                if(data[1][2] < 1) {
                    $("#chat_content").append('<p>' + olList[data[1][0]][1] + ' 对 大家 说： ' + data[1][1] +'</p>');
                } else {
                    if(data[1][0] == uid) {
                        $("#chat_content").append('<p> 你 对 ' + olList[data[1][2]][1] + ' 说： ' + data[1][1] +'</p>');
                    } else {
                        $("#chat_content").append('<p>' + olList[data[1][0]][1] + ' 对 你 说： ' + data[1][1] +'</p>');
                    }
                }
                break;
            case chatCMD.NEWCHANNEL:
                $("#chat_content").append('<p>'+olList[data[1][0]][1]+' 创建了新频道'+data[1][1]+'！</p>');
                var k = data[1][0];//uid
                var arr = channelList[k];
                if(typeof arr !== "undefined" && arr && arr.length>0) {
                    var j = arr.length;
                    arr[j+1] = data[1][1];
                }else{
                    arr = [];
                    arr[0] = data[1][1];
                }
                channelList[k] = arr;
                parseChannel();
                break;
            case chatCMD.DELCHANNEL:
                //服务器端，会关闭当前频道内的所有连接
                $("#chat_content").append('<p>'+olList[data[1][0]][1]+' 删除了频道'+data[1][1]+'！</p>');
                for(var i in channelList[data[1][0]]){
                    if(data[1][1] == channelList[data[1][0]][i]){
                        delete channelList[data[1][0]][i];
                        // break;
                    }
                }
                parseChannel();
                break;
            case chatCMD.LOGOUT:
                $("#chat_content").append('<p>'+olList[data[1][0]][1]+' 退出了聊天室！</p>');
                delete olList[data[1][0]];
                parseOl();
                break;
        }
    } catch (e) {
    }
};

//发送消息
sendMsg = function () {
    if (!connected) {
        alert('服务器没有连接');
        return;
    }
    var msgContent = $.trim($("#msgContent").val());
    if (msgContent == "") {
        alert('请输入聊天内容');
        $("#msgContent").focus();
        return;
    }
    var sendTo = parseInt($("#sendTo").val());
    if(sendTo == uid) {
        alert("不能和自己聊天～");
        return ;
    }
    var sendObj = {
        type: chatCMD.CHAT,
        message: [sendTo, msgContent,defaultChannel]
    };
    webSocket.send(JSON.stringify(sendObj));
    $("#msgContent").val('');
};

function parseOl() {
    var html = '';
    var shtml = '<option value="0">所有人</option>';
    for(var key in olList) {
        if( olList[key][1] == '#'){
            continue;
        }
        if(key != uid) {
            shtml += '<option value="'+key+'">'+olList[key][1]+'</option>';
        }
        html += '<p id="ol_'+key+'">'+olList[key][1]+'</p>';
    }
    $("#sendTo").html(shtml);
    $('#ollist').html(html);
}

newChannel = function(){
    var channelName = $.trim($("#newChannel").val());
    var sendObj = {
        type: chatCMD.NEWCHANNEL,
        message: [uid,channelName]
    };
    if(defaultChannel !== 'ALL'){
        alert('请前往ALL大厅创建频道');
        return;
    }
    webSocket.send(JSON.stringify(sendObj));
    $("#newChannel").val('');
};

delChannel = function(e){
    var channelName = e.parentNode.parentNode.getElementsByTagName('span')[0].innerHTML;
    var ownerId = e.parentNode.parentNode.getElementsByTagName('input')[0].value;
    if('ALL' !== defaultChannel){
        alert("请先前往ALL聊天室，再来删除当前聊天室！");return;
    }
    var sendObj = {
        type: chatCMD.DELCHANNEL,
        message: [ownerId,channelName]
    };
    webSocket.send(JSON.stringify(sendObj));
};
enterChannel = function(e){
    var channelName = e.parentNode.parentNode.getElementsByTagName('span')[0].innerHTML;
    var ownerId = e.parentNode.parentNode.getElementsByTagName('input')[0].value;

    var url='http://'+host+"?a=main\\main&m=main&"+"uid="+uid+"&token="+token+"&ownerId="+ownerId+"&channelName="+channelName;
    window.location.href = url;

};

function parseChannel() {

    var ds = '';
    var chtml = '<tr><th scope="row">superAdmin</th><td><span>ALL</span><input type="hidden" value="0"></td><td><button class="btn btn-primary btn-xs active" type="button" onclick="enterChannel(this)">进入</button></td><td><button class="btn btn-default btn-xs disabled" type="button">删除</button></td></tr>';
    for(var key in channelList){
        if(key == uid){
            ds = '<button class="btn btn-primary btn-xs" type="button" onclick="delChannel(this)">删除</button>';
        }else{
            ds = '';
        }
        var arr = channelList[key];
        if(typeof arr !== "undefined" && arr && arr.length>0){
            for(var i in arr){
                if(typeof olList[key] === "undefined"){
                    olList[key] = [];
                    olList[key][1]= '#';//此处可拓展，在客户端维护一个储存房主信息的数组，和房主是否在线无关
                }
                chtml += '<tr><th scope="row">'+olList[key][1]+'</th><td><span>'+arr[i]+'</span><input type="hidden" value="'+ key +'">'+'</td><td><button class="btn btn-primary btn-xs active" type="button" onclick="enterChannel(this)">进入</button></td><td>'+ds+'</td></tr>';
            }
        }
    }
    $("#channelList").html(chtml);
}

jQuery(document).keypress(function (e) {
    if (e.ctrlKey && e.which == 13 || e.which == 10) {
        jQuery("#sendBtn").click();
    } else if (e.shiftKey && e.which == 13 || e.which == 10) {
        jQuery("#sendBtn").click();
    }

});





