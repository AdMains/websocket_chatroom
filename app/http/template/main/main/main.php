<?php include(TPL_PATH.'header.php');?>
<?php include('main_additional.php');?>

<div class="container">
    <div>
        <div class="user_list">
            <div class="panel panel-default">
                <div class="panel-heading">
                    <h3 class="panel-title">在线用户</h3>
                </div>
                <div class="panel-body h500" id="ollist">

                </div>
            </div>
        </div>
        <div class="chat_list">
            <div class="panel panel-default">
                <div class="panel-heading">
                    <h3 class="panel-title">聊天区</h3>
                </div>
                <div id="chat_content" class="panel-body h500">
                    <p>WebSocket chatroom~~</p>
                </div>
            </div>
        </div>
    </div>
    <div class="chat_send">
        <div class="col-lg-6">
            <div class="input-group">
                <select class="form-control" id="sendTo" style="width:100px;">
                    <option value="0">全体</option>
                </select>
                <input type="text" id="msgContent" class="form-control">
      <span class="input-group-btn">
        <button class="btn btn-default" type="button" id="sendBtn" onclick="sendMsg()">发言</button>(ctrl+enter发送)
      </span>
            </div><!-- /input-group -->
        </div><!-- /.col-lg-6 -->
    </div>
</div> <!-- /container -->
<script src="<?php echo $static_url;?>js/jquery-1.9.1.js"></script>
<script src="<?php echo $static_url;?>js/WebSocket.js"></script>
<script src="<?php echo $static_url;?>js/bootstrap.min.js"></script>
<script type="text/javascript">
    init('<?php echo $app_host; ?>', 8992,<?php echo $uid; ?>,'<?php echo $token; ?>','<?php echo $defaultChannel; ?>',<?php echo $ownerId; ?>);
</script>
<?php include(TPL_PATH.'footer.php');?>
