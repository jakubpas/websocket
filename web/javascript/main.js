$(function () {
    var colours = ['007AFF', 'FF7000', 'FF7000', '15E25F', 'CFC700', 'CFC700', 'CF1100', 'CF00BE', 'F00'];
    var color = colours[Math.floor(Math.random() * colours.length)];
    var wsUri = 'ws://' + $('.container').data('host') + ':' + $('.container').data('port');
    var webSocket = new WebSocket(wsUri);

    webSocket.onOpen = function () {
        $('#message_box').append('<div class="system_msg">Connected!</div>'); //notify user
    };

    $('#send-btn').click(function () {
        sendMessage();
    });

    $('#message').bind('keypress', function (e) {
        if (e.keyCode == 13) {
            sendMessage();
        }
    });

    var sendMessage = (function () {
        var myMessage = $('#message').val();
        var myName = $('#name').val();

        if (myName == '') {
            alert("Enter your Name please!");
            return;
        }
        if (myMessage == '') {
            alert("Enter Some message Please!");
            return;
        }

        var msg = {
            type: 'user',
            message: myMessage,
            name: myName,
            color: color
        };
        webSocket.send(JSON.stringify(msg));
    });

    webSocket.onmessage = function (ev) {
        var msg = JSON.parse(ev.data); //PHP sends Json data
        var type = msg.type; //message type
        var uMsg = msg.message; //message text
        var uname = msg.name; //user name
        var uColor = msg.color; //color

        if (type == 'user') {
            $('#message_box').append("<div><span class=\"user_name\" style=\"color:#" + uColor + "\">" + uname + "</span> : <span class=\"user_message\">" + uMsg + "</span></div>");
        }
        if (type == 'system') {
            $('#message_box').append("<div class=\"system_msg\">" + uMsg + "</div>");
        }

        $('#message').val(''); //reset text
    };

    webSocket.onError = function (ev) {
        $('#message_box').append("<div class=\"system_error\">Error Occurred - " + ev.data + "</div>");
    };
    webSocket.onclose = function () {
        $('#message_box').append("<div class=\"system_msg\">Connection Closed</div>");
    };
});
