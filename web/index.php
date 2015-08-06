<?php
require_once '../config.php';
?>
<!DOCTYPE html>
<html>
<head>
    <meta charset='UTF-8'/>
    <link href="//maxcdn.bootstrapcdn.com/bootstrap/3.3.5/css/bootstrap.min.css" rel="stylesheet">
    <script src="//code.jquery.com/jquery-2.1.4.min.js"></script>
    <script src="javascript/main.js"></script>
</head>
<body>
<div class="container" data-host="<?=$host?>" data-port="<?=$port?>">
    <div class="row" style="height: 50px"></div>
    <div class="row">
        <div class="col-md-6 col-md-offset-3 well">
            <div class="form-control message_box" id="message_box" style="height: 200px; overflow-y: scroll;margin-left: 15px;margin-right: 30px;width: 526px;"></div>
            <form class="navbar-form navbar-left" role="search">
                <div class="form-group">
                    <input type="text" class="form-control" name="name" id="name" placeholder="Your Name" style="width: 156px;">
                    <input type="text" class="form-control" name="message" id="message" placeholder="Message" style="width: 276px;">
                </div>
                <button id="send-btn" type="button" class="btn btn-default">Submit</button>
            </form>
        </div>
    </div>
</div>
</body>
</html>