<?php
require_once "LovelyCat.php";
//普通实例化
$lovelyCat = new  lovelyCat();
switch ($lovelyCat->type) {//以下仅展示几种常见的场景，其他的请自行根据实际自由发挥！
    case 100://私聊消息
        $lovelyCat->sendTextMsg("我收到了你的私聊消息：" . $lovelyCat->msg);
        break;
    case 200://群聊消息
        $lovelyCat->sendTextMsg("我收到了群聊消息：" . $lovelyCat->msg);
        break;
    case  400://新人入群
        //{
        //	"group_wxid": "xxx@chatroom",
        //	"group_name": "xxx",
        //	"guest": [{
        //		"wxid": "xxx",
        //		"nickname": "xxx"
        //	}],
        //	"inviter": {
        //		"wxid": "xxx",
        //		"nickname": "xxx"
        //	}
        //}
        $origin = json_decode($lovelyCat->msg, true);
        $msg = sprintf("%s拉了新人入群，撒花欢迎：%s！！", $origin['inviter']['nickname'], $origin['guest'][0]['nickname']);
        $lovelyCat->sendTextMsg($msg);
        break;
    case 410://有人退群
        //msg 消息体：
        //{
        //	"member_wxid": "xxx",
        //	"member_nickname": "xxx",
        //	"group_wxid": "11111@chatroom",
        //	"group_name": "xxx",
        //	"timestamp": 1575890752
        //}
        $origin = json_decode($lovelyCat->msg, true);
        $msg = sprintf("万般留不住，%s终于还是走了~", $origin['member_nickname']);
        $lovelyCat->sendTextMsg($msg);
        break;
    case 500:
        //收到好友请求，自动同意好友申请
        $lovelyCat->agreeFriendVerify();
        break;
    case 600:
        //收到二维码转账
        //{
        //	"to_wxid": "wxid_9c6d4r3taosh22",
        //	"msgid": 1705897420,
        //	"received_money_index": "1",
        //	"money": "0.01",
        //	"total_money": "0.01",
        //	"remark": "",
        //	"scene_desc": "个人收款完成",
        //	"scene": 3,
        //	"timestamp": 1575891497
        //}
        $origin = json_decode($lovelyCat->msg, true);
        $msg = sprintf("收到扫码转账，金额：%s元", $origin['money']);
        file_put_contents("receive_money.log", $msg . PHP_EOL, FILE_APPEND);
        //此处可以执行日志记录知道的操作
        break;
    case 700:
        //收到转账
        //{
        //	"paysubtype": "3",
        //	"is_arrived": 1,
        //	"is_received": 1,
        //	"receiver_pay_id": "1000050101201912090003409856290",
        //	"payer_pay_id": "100005010119120900084341523899983197",
        //	"money": "0.01",
        //	"remark": "",
        //	"robot_pay_id": "1000050101201912090003409856290",
        //	"pay_id": "100005010119120900084341523899983197",
        //	"update_msg": "receiver_pay_id、payer_pay_id属性为robot_pay_id、pay_id的新名字，内容是一样的，建议更换"
        //}
        $origin = json_decode($lovelyCat->msg, true);
        $msg = sprintf("我收到了你的转账，金额：%s元", $origin['money']);
        $lovelyCat->acceptTransfer();//同意接收转账
        $lovelyCat->sendTextMsg($msg);//发送回执
        break;
    default:
        echo "OK";
        break;
}
